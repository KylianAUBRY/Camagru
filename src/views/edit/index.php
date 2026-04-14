<?php $pageTitle = 'Edit – Camagru'; ?>
<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<div class="edit-page">
    <div class="edit-main">
        <h1>Create a Photo</h1>

        <div class="camera-area">
            <div class="preview-wrapper">
                <video id="webcam" autoplay playsinline muted></video>
                <canvas id="overlay-preview" class="overlay-canvas"></canvas>
            </div>

            <div class="upload-toggle">
                <label>
                    <input type="checkbox" id="use-upload"> Use file upload instead of webcam
                </label>
            </div>

            <div id="upload-area" class="hidden">
                <label for="photo-upload" class="btn-secondary">Choose image</label>
                <input type="file" id="photo-upload" accept="image/*" class="hidden">
                <span id="upload-filename">No file chosen</span>
                <img id="upload-preview" src="" alt="" class="hidden">
            </div>
        </div>

        <div class="overlays-section">
            <h2>Choose an overlay</h2>
            <div class="overlays-list">
                <?php foreach ($overlays as $overlay): ?>
                    <label class="overlay-item">
                        <input type="radio" name="overlay" value="<?= htmlspecialchars($overlay) ?>">
                        <img src="<?= APP_URL ?>/overlays/<?= htmlspecialchars($overlay) ?>"
                             alt="<?= htmlspecialchars(pathinfo($overlay, PATHINFO_FILENAME)) ?>">
                    </label>
                <?php endforeach; ?>
                <?php if (empty($overlays)): ?>
                    <p>No overlays available.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="edit-controls">
            <button id="capture-btn" class="btn-primary" disabled>
                &#128247; Capture Photo
            </button>
            <button id="upload-btn" class="btn-primary hidden" disabled>
                &#128190; Apply Overlay &amp; Save
            </button>
            <span id="edit-status" class="status-msg"></span>
        </div>
    </div>

    <aside class="edit-sidebar">
        <h2>My Photos</h2>
        <div class="my-photos-list" id="my-photos">
            <?php foreach ($myImages as $img): ?>
                <div class="my-photo-item" data-id="<?= $img['id'] ?>">
                    <img src="<?= APP_URL ?>/uploads/<?= htmlspecialchars($img['filename']) ?>"
                         alt="My photo"
                         loading="lazy">
                    <button class="delete-btn"
                            data-id="<?= $img['id'] ?>"
                            data-csrf="<?= htmlspecialchars($csrf) ?>"
                            title="Delete">&#10005;</button>
                </div>
            <?php endforeach; ?>
            <?php if (empty($myImages)): ?>
                <p class="empty-state">No photos yet.</p>
            <?php endif; ?>
        </div>
    </aside>
</div>

<script>
    window.APP_URL  = <?= json_encode(APP_URL) ?>;
    window.CSRF     = <?= json_encode($csrf) ?>;
</script>
<script src="<?= APP_URL ?>/js/camera.js"></script>
<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
