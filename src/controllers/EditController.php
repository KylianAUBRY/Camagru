<?php

require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/core/Database.php';
require_once dirname(__DIR__) . '/models/ImageModel.php';

class EditController extends Controller
{
    private ImageModel $images;

    public function __construct()
    {
        $this->images = new ImageModel();
    }

    public function index(): void
    {
        $this->requireAuth();

        $overlays  = $this->getOverlays();
        $myImages  = $this->images->getAllByUser($_SESSION['user_id']);
        $csrf      = $this->csrfToken();
        $username  = $_SESSION['username'];
        $this->render('edit/index', compact('overlays', 'myImages', 'csrf', 'username'));
    }

    public function capture(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $overlay = trim($_POST['overlay'] ?? '');
        $imageData = $_POST['image_data'] ?? '';

        if ($overlay === '' || $imageData === '') {
            $this->json(['error' => 'Missing data.'], 422);
        }

        $overlayPath = OVERLAY_DIR . basename($overlay);
        if (!file_exists($overlayPath) || !$this->isValidOverlay($overlay)) {
            $this->json(['error' => 'Invalid overlay.'], 422);
        }

        if (!preg_match('/^data:image\/(jpeg|png|webp);base64,/', $imageData, $m)) {
            $this->json(['error' => 'Invalid image data.'], 422);
        }

        $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
        $raw    = base64_decode($base64, true);
        if ($raw === false || strlen($raw) > MAX_FILE_SIZE) {
            $this->json(['error' => 'Image too large or invalid.'], 422);
        }

        $webcam = @imagecreatefromstring($raw);
        if (!$webcam) {
            $this->json(['error' => 'Cannot process image.'], 422);
        }

        $filename = $this->compose($webcam, $overlayPath);
        imagedestroy($webcam);

        if (!$filename) {
            $this->json(['error' => 'Image processing failed.'], 500);
        }

        $this->images->create($_SESSION['user_id'], $filename);
        $this->json(['filename' => $filename, 'url' => APP_URL . '/uploads/' . $filename]);
    }

    public function upload(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $overlay = trim($_POST['overlay'] ?? '');
        if ($overlay === '' || !$this->isValidOverlay($overlay)) {
            $this->json(['error' => 'Invalid overlay.'], 422);
        }

        $overlayPath = OVERLAY_DIR . basename($overlay);
        if (!file_exists($overlayPath)) {
            $this->json(['error' => 'Overlay not found.'], 422);
        }

        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['error' => 'Upload failed.'], 422);
        }

        $file = $_FILES['photo'];
        if ($file['size'] > MAX_FILE_SIZE) {
            $this->json(['error' => 'File too large (max 10MB).'], 422);
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($mime, $allowed, true)) {
            $this->json(['error' => 'Only JPEG, PNG, WebP and GIF images are allowed.'], 422);
        }

        $webcam = @imagecreatefromstring(file_get_contents($file['tmp_name']));
        if (!$webcam) {
            $this->json(['error' => 'Cannot process image.'], 422);
        }

        $filename = $this->compose($webcam, $overlayPath);
        imagedestroy($webcam);

        if (!$filename) {
            $this->json(['error' => 'Image processing failed.'], 500);
        }

        $this->images->create($_SESSION['user_id'], $filename);
        $this->json(['filename' => $filename, 'url' => APP_URL . '/uploads/' . $filename]);
    }

    public function delete(string $idStr): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $id    = (int)$idStr;
        $image = $this->images->findById($id);

        if (!$image || $image['user_id'] != $_SESSION['user_id']) {
            $this->json(['error' => 'Not found or forbidden.'], 403);
        }

        $filePath = UPLOAD_DIR . $image['filename'];
        $this->images->delete($id, $_SESSION['user_id']);

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $this->json(['success' => true]);
    }

    private function compose($webcam, string $overlayPath): ?string
    {
        $w = imagesx($webcam);
        $h = imagesy($webcam);

        $canvas = imagecreatetruecolor($w, $h);
        imagecopy($canvas, $webcam, 0, 0, 0, 0, $w, $h);

        $ext = strtolower(pathinfo($overlayPath, PATHINFO_EXTENSION));
        switch ($ext) {
            case 'png':
                $overlay = @imagecreatefrompng($overlayPath);
                break;
            case 'webp':
                $overlay = @imagecreatefromwebp($overlayPath);
                break;
            default:
                $overlay = @imagecreatefromjpeg($overlayPath);
        }

        if (!$overlay) {
            imagedestroy($canvas);
            return null;
        }

        $ow = imagesx($overlay);
        $oh = imagesy($overlay);

        $resized = imagecreatetruecolor($w, $h);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $overlay, 0, 0, 0, 0, $w, $h, $ow, $oh);
        imagedestroy($overlay);

        imagealphablending($canvas, true);
        imagecopy($canvas, $resized, 0, 0, 0, 0, $w, $h);
        imagedestroy($resized);

        $filename = bin2hex(random_bytes(16)) . '.jpg';
        $dest     = UPLOAD_DIR . $filename;

        if (!imagejpeg($canvas, $dest, 90)) {
            imagedestroy($canvas);
            return null;
        }
        imagedestroy($canvas);

        return $filename;
    }

    private function getOverlays(): array
    {
        $files = glob(OVERLAY_DIR . '*.png');
        if (!$files) {
            return [];
        }
        $list = [];
        foreach ($files as $f) {
            $list[] = basename($f);
        }
        return $list;
    }

    private function isValidOverlay(string $name): bool
    {
        if (preg_match('/[\/\\\\]/', $name)) {
            return false;
        }
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        return in_array($ext, ['png', 'webp'], true);
    }
}
