<?php

require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/core/Database.php';
require_once dirname(__DIR__) . '/models/UserModel.php';

class UserController extends Controller
{
    private UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    public function profile(): void
    {
        $this->requireAuth();
        $user  = $this->users->findById($_SESSION['user_id']);
        $error = $this->getFlash('error');
        $csrf  = $this->csrfToken();
        $username = $_SESSION['username'];
        $this->render('user/profile', compact('user', 'error', 'csrf', 'username'));
    }

    public function profilePost(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $newUsername = trim($_POST['username'] ?? '');
        $newEmail    = trim($_POST['email'] ?? '');
        $notify      = isset($_POST['notify_comments']) ? 1 : 0;
        $password    = $_POST['password'] ?? '';
        $confirm     = $_POST['confirm'] ?? '';

        if ($newUsername === '' || $newEmail === '') {
            $this->flash('error', 'Username and email are required.');
            $this->redirect('/profile');
        }

        if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $newUsername)) {
            $this->flash('error', 'Username must be 3-50 characters (letters, numbers, underscore).');
            $this->redirect('/profile');
        }

        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Invalid email address.');
            $this->redirect('/profile');
        }

        $userId = $_SESSION['user_id'];

        if ($this->users->usernameExists($newUsername, $userId)) {
            $this->flash('error', 'Username already taken.');
            $this->redirect('/profile');
        }

        if ($this->users->emailExists($newEmail, $userId)) {
            $this->flash('error', 'Email already registered.');
            $this->redirect('/profile');
        }

        if ($password !== '') {
            if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
                $this->flash('error', 'Password must be at least 8 characters with an uppercase letter and a number.');
                $this->redirect('/profile');
            }
            if ($password !== $confirm) {
                $this->flash('error', 'Passwords do not match.');
                $this->redirect('/profile');
            }
            $this->users->updatePassword($userId, $password);
        }

        $this->users->updateProfile($userId, $newUsername, $newEmail, $notify);
        $_SESSION['username'] = $newUsername;

        $this->flash('error', 'Profile updated successfully.');
        $this->redirect('/profile');
    }
}
