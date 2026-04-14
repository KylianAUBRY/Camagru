<?php

require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/core/Database.php';
require_once dirname(__DIR__) . '/core/Mailer.php';
require_once dirname(__DIR__) . '/models/ImageModel.php';
require_once dirname(__DIR__) . '/models/CommentModel.php';
require_once dirname(__DIR__) . '/models/LikeModel.php';
require_once dirname(__DIR__) . '/models/UserModel.php';

class GalleryController extends Controller
{
    private ImageModel   $images;
    private CommentModel $comments;
    private LikeModel    $likes;
    private UserModel    $users;

    public function __construct()
    {
        $this->images   = new ImageModel();
        $this->comments = new CommentModel();
        $this->likes    = new LikeModel();
        $this->users    = new UserModel();
    }

    public function index(): void
    {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = GALLERY_PER_PAGE;
        $total   = $this->images->countAll();
        $pages   = (int)ceil($total / $perPage);
        $images  = $this->images->getPaginated($page, $perPage);

        $loggedUserId = $_SESSION['user_id'] ?? null;
        $likedIds     = [];
        if ($loggedUserId) {
            $imageIds = array_column($images, 'id');
            $likedIds = $this->likes->getLikedImageIds($loggedUserId, $imageIds);
        }

        $imageData = [];
        foreach ($images as $img) {
            $imageData[] = [
                'image'       => $img,
                'likes'       => $this->likes->countByImage($img['id']),
                'comments'    => $this->comments->getByImage($img['id']),
                'liked'       => in_array($img['id'], $likedIds),
            ];
        }

        $csrf     = $this->csrfToken();
        $username = $_SESSION['username'] ?? null;
        $this->render('gallery/index', compact('imageData', 'page', 'pages', 'csrf', 'username'));
    }

    public function like(): void
    {
        if (!$this->isLoggedIn()) {
            $this->json(['error' => 'Not authenticated'], 401);
        }

        $this->verifyCsrf();

        $imageId = (int)($_POST['image_id'] ?? 0);
        $image   = $this->images->findById($imageId);
        if (!$image) {
            $this->json(['error' => 'Image not found'], 404);
        }

        $liked = $this->likes->toggle($imageId, $_SESSION['user_id']);
        $count = $this->likes->countByImage($imageId);
        $this->json(['liked' => $liked, 'count' => $count]);
    }

    public function comment(): void
    {
        if (!$this->isLoggedIn()) {
            $this->json(['error' => 'Not authenticated'], 401);
        }

        $this->verifyCsrf();

        $imageId = (int)($_POST['image_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');

        if ($content === '' || strlen($content) > 500) {
            $this->json(['error' => 'Comment must be 1–500 characters.'], 422);
        }

        $image = $this->images->findById($imageId);
        if (!$image) {
            $this->json(['error' => 'Image not found'], 404);
        }

        $this->comments->create($imageId, $_SESSION['user_id'], $content);

        $author = $this->users->findById($image['user_id']);
        if ($author && $author['notify_comments'] && $author['id'] !== $_SESSION['user_id']) {
            $imageUrl  = APP_URL . '/gallery';
            $commenter = htmlspecialchars($_SESSION['username']);
            $body = "<p><strong>$commenter</strong> commented on your photo on Camagru:</p>"
                  . "<blockquote>" . htmlspecialchars($content) . "</blockquote>"
                  . "<p><a href=\"$imageUrl\">View on Camagru</a></p>";
            Mailer::send($author['email'], 'New comment on your photo', $body);
        }

        $this->json([
            'username'   => htmlspecialchars($_SESSION['username']),
            'content'    => htmlspecialchars($content),
            'created_at' => date('Y-m-d H:i'),
        ]);
    }
}
