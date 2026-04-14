<?php $pageTitle = 'Gallery – Camagru'; ?>
<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<div class="gallery-page">
    <h1>Gallery</h1>

    <?php if (empty($imageData)): ?>
        <p class="empty-state">No photos yet. <a href="<?= APP_URL ?>/edit">Be the first!</a></p>
    <?php else: ?>
        <div class="gallery-grid">
            <?php foreach ($imageData as $item): ?>
                <?php $img = $item['image']; ?>
                <article class="gallery-card" data-id="<?= $img['id'] ?>">
                    <img src="<?= APP_URL ?>/uploads/<?= htmlspecialchars($img['filename']) ?>"
                         alt="Photo by <?= htmlspecialchars($img['username']) ?>"
                         loading="lazy">
                    <div class="card-meta">
                        <span class="card-author">by <?= htmlspecialchars($img['username']) ?></span>
                        <span class="card-date"><?= htmlspecialchars(substr($img['created_at'], 0, 16)) ?></span>
                    </div>
                    <div class="card-actions">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <button class="like-btn <?= $item['liked'] ? 'liked' : '' ?>"
                                    data-id="<?= $img['id'] ?>"
                                    data-csrf="<?= htmlspecialchars($csrf) ?>">
                                &#10084; <span class="like-count"><?= $item['likes'] ?></span>
                            </button>
                        <?php else: ?>
                            <span class="likes-display">&#10084; <?= $item['likes'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="comments-section">
                        <h3>Comments (<?= count($item['comments']) ?>)</h3>
                        <ul class="comments-list">
                            <?php foreach ($item['comments'] as $c): ?>
                                <li>
                                    <strong><?= htmlspecialchars($c['username']) ?></strong>
                                    <?= htmlspecialchars($c['content']) ?>
                                    <time><?= htmlspecialchars(substr($c['created_at'], 0, 16)) ?></time>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form class="comment-form" data-id="<?= $img['id'] ?>" data-csrf="<?= htmlspecialchars($csrf) ?>">
                                <input type="text" name="content" placeholder="Add a comment…" maxlength="500" required>
                                <button type="submit">Post</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($pages > 1): ?>
        <nav class="pagination">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
                <a href="?page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script src="<?= APP_URL ?>/js/gallery.js"></script>
<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
