<?php
// Gestion des en-têtes CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Gérer les requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Image.php';
require_once __DIR__ . '/auth.php';

class ImageController {
    private $imageModel;
    private $authController;

    public function __construct() {
        $this->imageModel = new Image();
        $this->authController = new AuthController();
    }

    // Récupérer toutes les images
    public function getAll() {
        $images = $this->imageModel->getAll();
        
        return $this->jsonResponse([
            'success' => true,
            'images' => $images
        ]);
    }

    // Récupérer les images de l'utilisateur connecté
    public function getMyImages($userId) {
        $images = $this->imageModel->getByUserId($userId);
        
        return $this->jsonResponse([
            'success' => true,
            'images' => $images
        ]);
    }

    // Upload d'une image
    public function upload($userId) {
        // Vérifier qu'un fichier a été uploadé
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Erreur lors de l\'upload du fichier'
            ], 400);
        }

        $file = $_FILES['image'];

        // Vérifier la taille du fichier
        if ($file['size'] > MAX_FILE_SIZE) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Le fichier est trop volumineux (max 5 MB)'
            ], 400);
        }

        // Vérifier le type MIME
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Type de fichier non autorisé'
            ], 400);
        }

        // Générer un nom de fichier unique
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('img_') . '_' . time() . '.' . $extension;
        $destination = UPLOAD_DIR . $filename;

        // Créer le dossier uploads s'il n'existe pas
        if (!file_exists(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0755, true);
        }

        // Déplacer le fichier uploadé
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Sauvegarder dans la base de données
            $result = $this->imageModel->create($userId, $filename);

            if ($result['success']) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Image uploadée avec succès',
                    'image_id' => $result['image_id'],
                    'filename' => $filename
                ], 201);
            }

            // Supprimer le fichier si l'insertion en base a échoué
            unlink($destination);
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la sauvegarde en base de données'
            ], 500);
        }

        return $this->jsonResponse([
            'success' => false,
            'message' => 'Erreur lors du déplacement du fichier'
        ], 500);
    }

    // Supprimer une image
    public function delete($data, $userId) {
        if (!isset($data['image_id'])) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'ID de l\'image requis'
            ], 400);
        }

        if ($this->imageModel->delete($data['image_id'], $userId)) {
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Image supprimée avec succès'
            ]);
        }

        return $this->jsonResponse([
            'success' => false,
            'message' => 'Erreur lors de la suppression ou image non trouvée'
        ], 404);
    }

    // Ajouter un like
    public function addLike($data, $userId) {
        if (!isset($data['image_id'])) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'ID de l\'image requis'
            ], 400);
        }

        if ($this->imageModel->addLike($userId, $data['image_id'])) {
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Like ajouté'
            ]);
        }

        return $this->jsonResponse([
            'success' => false,
            'message' => 'Vous avez déjà liké cette image'
        ], 400);
    }

    // Retirer un like
    public function removeLike($data, $userId) {
        if (!isset($data['image_id'])) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'ID de l\'image requis'
            ], 400);
        }

        if ($this->imageModel->removeLike($userId, $data['image_id'])) {
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Like retiré'
            ]);
        }

        return $this->jsonResponse([
            'success' => false,
            'message' => 'Erreur lors du retrait du like'
        ], 400);
    }

    // Ajouter un commentaire
    public function addComment($data, $userId) {
        if (!isset($data['image_id']) || !isset($data['comment'])) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'ID de l\'image et commentaire requis'
            ], 400);
        }

        if (empty(trim($data['comment']))) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Le commentaire ne peut pas être vide'
            ], 400);
        }

        $commentId = $this->imageModel->addComment($userId, $data['image_id'], $data['comment']);

        if ($commentId) {
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Commentaire ajouté',
                'comment_id' => $commentId
            ], 201);
        }

        return $this->jsonResponse([
            'success' => false,
            'message' => 'Erreur lors de l\'ajout du commentaire'
        ], 500);
    }

    // Récupérer les commentaires d'une image
    public function getComments($imageId) {
        $comments = $this->imageModel->getComments($imageId);
        
        return $this->jsonResponse([
            'success' => true,
            'comments' => $comments
        ]);
    }

    // Extraire le token de l'en-tête Authorization
    private function getTokenFromHeader() {
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }

    // Vérifier l'authentification
    private function authenticate() {
        $token = $this->getTokenFromHeader();
        
        if (!$token) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Token d\'authentification manquant'
            ], 401);
        }

        $userId = $this->authController->verifyToken($token);
        
        if (!$userId) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Token invalide ou expiré'
            ], 401);
        }

        return $userId;
    }

    // Réponse JSON
    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}

// Routage
$controller = new ImageController();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'my_images') {
        $userId = $controller->authenticate();
        $controller->getMyImages($userId);
    } elseif (isset($_GET['action']) && $_GET['action'] === 'comments' && isset($_GET['image_id'])) {
        $controller->getComments($_GET['image_id']);
    } else {
        $controller->getAll();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $controller->authenticate();
    
    if (isset($_POST['action']) && $_POST['action'] === 'upload') {
        $controller->upload($userId);
    } else {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['action'])) {
            switch ($data['action']) {
                case 'like':
                    $controller->addLike($data, $userId);
                    break;
                case 'unlike':
                    $controller->removeLike($data, $userId);
                    break;
                case 'comment':
                    $controller->addComment($data, $userId);
                    break;
                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Action inconnue']);
            }
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $userId = $controller->authenticate();
    $data = json_decode(file_get_contents('php://input'), true);
    $controller->delete($data, $userId);
}
