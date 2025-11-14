<?php
// Gestion des en-têtes CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Gérer les requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    // Inscription
    public function register($data) {
        // Validation des données
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            return $this->jsonResponse(['success' => false, 'message' => 'Tous les champs sont requis'], 400);
        }

        // Validation du format email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Email invalide'], 400);
        }

        // Validation du mot de passe
        if (strlen($data['password']) < 8) {
            return $this->jsonResponse(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères'], 400);
        }

        // Vérifier si l'utilisateur existe déjà
        if ($this->userModel->findByEmailOrUsername($data['email'])) {
            return $this->jsonResponse(['success' => false, 'message' => 'Cet email est déjà utilisé'], 400);
        }

        if ($this->userModel->findByEmailOrUsername($data['username'])) {
            return $this->jsonResponse(['success' => false, 'message' => 'Ce nom d\'utilisateur est déjà utilisé'], 400);
        }

        // Créer l'utilisateur
        $result = $this->userModel->create($data['username'], $data['email'], $data['password']);

        if ($result['success']) {
            // Envoyer l'email de vérification (simulation)
            $this->sendVerificationEmail($data['email'], $result['verification_token']);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Inscription réussie ! Vérifiez votre email pour activer votre compte.'
            ], 201);
        }

        return $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de l\'inscription'], 500);
    }

    // Connexion
    public function login($data) {
        // Validation des données
        if (empty($data['username']) || empty($data['password'])) {
            return $this->jsonResponse(['success' => false, 'message' => 'Tous les champs sont requis'], 400);
        }

        // Trouver l'utilisateur
        $user = $this->userModel->findByEmailOrUsername($data['username']);

        if (!$user) {
            return $this->jsonResponse(['success' => false, 'message' => 'Identifiants incorrects'], 401);
        }

        // Vérifier le mot de passe
        if (!$this->userModel->verifyPassword($user, $data['password'])) {
            return $this->jsonResponse(['success' => false, 'message' => 'Identifiants incorrects'], 401);
        }

        // Vérifier si le compte est vérifié
        if (!$user['is_verified']) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Veuillez vérifier votre email avant de vous connecter'
            ], 403);
        }

        // Générer un token JWT (simulation basique)
        $token = $this->generateToken($user['id']);

        return $this->jsonResponse([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email']
            ]
        ]);
    }

    // Vérification de l'email
    public function verify($token) {
        if (empty($token)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Token invalide'], 400);
        }

        if ($this->userModel->verify($token)) {
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Votre compte a été vérifié avec succès !'
            ]);
        }

        return $this->jsonResponse(['success' => false, 'message' => 'Token invalide ou expiré'], 400);
    }

    // Récupération de mot de passe
    public function forgotPassword($data) {
        if (empty($data['email'])) {
            return $this->jsonResponse(['success' => false, 'message' => 'Email requis'], 400);
        }

        $user = $this->userModel->findByEmailOrUsername($data['email']);

        if ($user) {
            $resetToken = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $this->userModel->setResetToken($data['email'], $resetToken, $expiry);
            $this->sendResetEmail($data['email'], $resetToken);
        }

        // Toujours retourner le même message pour des raisons de sécurité
        return $this->jsonResponse([
            'success' => true,
            'message' => 'Si un compte existe avec cet email, vous recevrez un lien de réinitialisation'
        ]);
    }

    // Génération d'un token simple (à remplacer par une vraie implémentation JWT)
    private function generateToken($userId) {
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'user_id' => $userId,
            'exp' => time() + (24 * 60 * 60) // 24 heures
        ]));
        $signature = hash_hmac('sha256', "$header.$payload", JWT_SECRET);
        
        return "$header.$payload.$signature";
    }

    // Vérification du token
    public function verifyToken($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        list($header, $payload, $signature) = $parts;
        $validSignature = hash_hmac('sha256', "$header.$payload", JWT_SECRET);

        if ($signature !== $validSignature) {
            return false;
        }

        $payloadData = json_decode(base64_decode($payload), true);
        
        if ($payloadData['exp'] < time()) {
            return false;
        }

        return $payloadData['user_id'];
    }

    // Envoyer un email de vérification (simulation)
    private function sendVerificationEmail($email, $token) {
        $verificationLink = SITE_URL . "/api/auth.php?action=verify&token=" . $token;
        
        // En production, utiliser une vraie bibliothèque d'envoi d'emails
        $subject = "Vérification de votre compte Camagru";
        $message = "Cliquez sur ce lien pour vérifier votre compte : " . $verificationLink;
        
        // mail($email, $subject, $message);
        error_log("Verification email to $email: $verificationLink");
    }

    // Envoyer un email de réinitialisation (simulation)
    private function sendResetEmail($email, $token) {
        $resetLink = SITE_URL . "/reset-password.html?token=" . $token;
        
        $subject = "Réinitialisation de votre mot de passe Camagru";
        $message = "Cliquez sur ce lien pour réinitialiser votre mot de passe : " . $resetLink;
        
        // mail($email, $subject, $message);
        error_log("Reset email to $email: $resetLink");
    }

    // Réponse JSON
    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}

// Routage
$controller = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['action'])) {
        switch ($data['action']) {
            case 'register':
                $controller->register($data);
                break;
            case 'login':
                $controller->login($data);
                break;
            case 'forgot_password':
                $controller->forgotPassword($data);
                break;
            default:
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Action inconnue']);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'verify' && isset($_GET['token'])) {
        $controller->verify($_GET['token']);
    }
}
