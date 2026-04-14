<?php

require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/core/Database.php';
require_once dirname(__DIR__) . '/core/Mailer.php';
require_once dirname(__DIR__) . '/models/UserModel.php';

class AuthController extends Controller
{
    private UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    public function login(): void
    {
        $this->requireGuest();
        $error = $this->getFlash('error');
        $csrf  = $this->csrfToken();
        $this->render('auth/login', compact('error', 'csrf'));
    }

    public function loginPost(): void
    {
        $this->requireGuest();
        $this->verifyCsrf();

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $this->flash('error', 'All fields are required.');
            $this->redirect('/login');
        }

        $user = $this->users->findByUsername($username);
        if (!$user || !password_verify($password, $user['password'])) {
            $this->flash('error', 'Invalid username or password.');
            $this->redirect('/login');
        }

        if (!$user['is_verified']) {
            $this->flash('error', 'Please verify your email address first.');
            $this->redirect('/login');
        }

        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $this->redirect('/');
    }

    public function register(): void
    {
        $this->requireGuest();
        $error = $this->getFlash('error');
        $csrf  = $this->csrfToken();
        $this->render('auth/register', compact('error', 'csrf'));
    }

    public function registerPost(): void
    {
        $this->requireGuest();
        $this->verifyCsrf();

        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm'] ?? '';

        if ($username === '' || $email === '' || $password === '' || $confirm === '') {
            $this->flash('error', 'All fields are required.');
            $this->redirect('/register');
        }

        if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
            $this->flash('error', 'Username must be 3-50 characters (letters, numbers, underscore).');
            $this->redirect('/register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Invalid email address.');
            $this->redirect('/register');
        }

        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $this->flash('error', 'Password must be at least 8 characters with an uppercase letter and a number.');
            $this->redirect('/register');
        }

        if ($password !== $confirm) {
            $this->flash('error', 'Passwords do not match.');
            $this->redirect('/register');
        }

        if ($this->users->usernameExists($username)) {
            $this->flash('error', 'Username already taken.');
            $this->redirect('/register');
        }

        if ($this->users->emailExists($email)) {
            $this->flash('error', 'Email already registered.');
            $this->redirect('/register');
        }

        $userId = $this->users->create($username, $email, $password);
        $user   = $this->users->findById($userId);

        $verifyUrl = APP_URL . '/verify?token=' . urlencode($user['verify_token']);
        $body = '<h2>Welcome to Camagru!</h2>'
              . '<p>Please confirm your account by clicking the link below:</p>'
              . '<p><a href="' . $verifyUrl . '">' . $verifyUrl . '</a></p>';
        Mailer::send($email, 'Verify your Camagru account', $body);

        $this->flash('error', 'Registration successful! Please check your email to activate your account.');
        $this->redirect('/login');
    }

    public function verify(): void
    {
        $token = trim($_GET['token'] ?? '');
        if ($token === '') {
            $this->flash('error', 'Invalid verification link.');
            $this->redirect('/login');
        }

        $user = $this->users->findByVerifyToken($token);
        if (!$user) {
            $this->flash('error', 'Invalid or expired verification link.');
            $this->redirect('/login');
        }

        $this->users->verify($user['id']);
        $this->flash('error', 'Account verified! You can now log in.');
        $this->redirect('/login');
    }

    public function forgot(): void
    {
        $this->requireGuest();
        $error = $this->getFlash('error');
        $csrf  = $this->csrfToken();
        $this->render('auth/forgot', compact('error', 'csrf'));
    }

    public function forgotPost(): void
    {
        $this->requireGuest();
        $this->verifyCsrf();

        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Invalid email address.');
            $this->redirect('/forgot-password');
        }

        $user = $this->users->findByEmail($email);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $this->users->setResetToken($user['id'], $token);
            $resetUrl = APP_URL . '/reset-password?token=' . urlencode($token);
            $body = '<h2>Password Reset</h2>'
                  . '<p>Click the link below to reset your password (valid for 1 hour):</p>'
                  . '<p><a href="' . $resetUrl . '">' . $resetUrl . '</a></p>';
            Mailer::send($email, 'Reset your Camagru password', $body);
        }

        $this->flash('error', 'If that email exists, a reset link has been sent.');
        $this->redirect('/login');
    }

    public function reset(): void
    {
        $token = trim($_GET['token'] ?? '');
        $user  = $token ? $this->users->findByResetToken($token) : null;
        if (!$user) {
            $this->flash('error', 'Invalid or expired reset link.');
            $this->redirect('/login');
        }
        $error = $this->getFlash('error');
        $csrf  = $this->csrfToken();
        $this->render('auth/reset', compact('error', 'csrf', 'token'));
    }

    public function resetPost(): void
    {
        $this->verifyCsrf();

        $token    = trim($_POST['token'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm'] ?? '';

        $user = $token ? $this->users->findByResetToken($token) : null;
        if (!$user) {
            $this->flash('error', 'Invalid or expired reset link.');
            $this->redirect('/login');
        }

        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $this->flash('error', 'Password must be at least 8 characters with an uppercase letter and a number.');
            $this->redirect('/reset-password?token=' . urlencode($token));
        }

        if ($password !== $confirm) {
            $this->flash('error', 'Passwords do not match.');
            $this->redirect('/reset-password?token=' . urlencode($token));
        }

        $this->users->updatePassword($user['id'], $password);
        $this->flash('error', 'Password updated! You can now log in.');
        $this->redirect('/login');
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: ' . APP_URL . '/login');
        exit;
    }
}
