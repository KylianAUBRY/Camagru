(function () {
    'use strict';

    const webcam        = document.getElementById('webcam');
    const overlayCanvas = document.getElementById('overlay-preview');
    const captureBtn    = document.getElementById('capture-btn');
    const uploadBtn     = document.getElementById('upload-btn');
    const statusMsg     = document.getElementById('edit-status');
    const useUpload     = document.getElementById('use-upload');
    const uploadArea    = document.getElementById('upload-area');
    const photoUpload   = document.getElementById('photo-upload');
    const uploadFilename= document.getElementById('upload-filename');
    const uploadPreview = document.getElementById('upload-preview');
    const myPhotosList  = document.getElementById('my-photos');

    const overlayCtx    = overlayCanvas ? overlayCanvas.getContext('2d') : null;
    let selectedOverlay = null;
    let stream          = null;
    let uploadFile      = null;

    // === Overlay selection ===
    document.querySelectorAll('input[name="overlay"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            selectedOverlay = this.value;
            updateButtonState();
            if (!useUpload.checked) {
                drawOverlayPreview();
            }
        });
    });

    // === Webcam init ===
    function startWebcam() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            setStatus('Webcam not available. Use file upload.');
            return;
        }
        navigator.mediaDevices.getUserMedia({ video: true, audio: false })
            .then(function (s) {
                stream = s;
                webcam.srcObject = s;
                webcam.classList.remove('hidden');
            })
            .catch(function () {
                setStatus('Cannot access webcam. Use file upload mode.');
            });
    }

    // === Overlay canvas preview ===
    function drawOverlayPreview() {
        if (!overlayCanvas || !selectedOverlay) return;
        var parent = overlayCanvas.parentElement;
        overlayCanvas.width  = parent.offsetWidth;
        overlayCanvas.height = parent.offsetHeight;
        overlayCtx.clearRect(0, 0, overlayCanvas.width, overlayCanvas.height);
        var img = new Image();
        img.onload = function () {
            overlayCtx.drawImage(img, 0, 0, overlayCanvas.width, overlayCanvas.height);
        };
        img.src = APP_URL + '/overlays/' + encodeURIComponent(selectedOverlay);
    }

    // === Toggle upload mode ===
    useUpload.addEventListener('change', function () {
        if (this.checked) {
            uploadArea.classList.remove('hidden');
            captureBtn.classList.add('hidden');
            uploadBtn.classList.remove('hidden');
            if (stream) {
                stream.getTracks().forEach(function (t) { t.stop(); });
                stream = null;
                webcam.srcObject = null;
            }
            webcam.classList.add('hidden');
            if (overlayCtx) overlayCtx.clearRect(0, 0, overlayCanvas.width, overlayCanvas.height);
        } else {
            uploadArea.classList.add('hidden');
            captureBtn.classList.remove('hidden');
            uploadBtn.classList.add('hidden');
            webcam.classList.remove('hidden');
            startWebcam();
            if (selectedOverlay) drawOverlayPreview();
        }
        updateButtonState();
    });

    // === File input ===
    photoUpload.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            uploadFile = this.files[0];
            uploadFilename.textContent = uploadFile.name;
            var reader = new FileReader();
            reader.onload = function (e) {
                uploadPreview.src = e.target.result;
                uploadPreview.classList.remove('hidden');
            };
            reader.readAsDataURL(uploadFile);
        } else {
            uploadFile = null;
            uploadFilename.textContent = 'No file chosen';
            uploadPreview.classList.add('hidden');
            uploadPreview.src = '';
        }
        updateButtonState();
    });

    // === Button state ===
    function updateButtonState() {
        if (useUpload.checked) {
            uploadBtn.disabled = !(selectedOverlay && uploadFile);
        } else {
            captureBtn.disabled = !selectedOverlay;
        }
    }

    // === Capture ===
    captureBtn.addEventListener('click', function () {
        if (!selectedOverlay) return;
        if (!stream) {
            setStatus('Webcam not available.');
            return;
        }

        var canvas = document.createElement('canvas');
        canvas.width  = webcam.videoWidth  || 640;
        canvas.height = webcam.videoHeight || 480;
        var ctx = canvas.getContext('2d');
        ctx.drawImage(webcam, 0, 0, canvas.width, canvas.height);
        var imageData = canvas.toDataURL('image/jpeg', 0.9);

        setStatus('Processing…');
        captureBtn.disabled = true;

        sendCapture(imageData, selectedOverlay);
    });

    // === Upload + compose ===
    uploadBtn.addEventListener('click', function () {
        if (!selectedOverlay || !uploadFile) return;
        setStatus('Processing…');
        uploadBtn.disabled = true;

        var formData = new FormData();
        formData.append('csrf_token', CSRF);
        formData.append('overlay', selectedOverlay);
        formData.append('photo', uploadFile);

        fetch(APP_URL + '/edit/upload', {
            method: 'POST',
            body: formData,
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.error) {
                setStatus('Error: ' + data.error);
            } else {
                setStatus('Saved!');
                addToSidebar(data.filename, data.url);
            }
            uploadBtn.disabled = false;
            updateButtonState();
        })
        .catch(function () {
            setStatus('Network error.');
            uploadBtn.disabled = false;
            updateButtonState();
        });
    });

    function sendCapture(imageData, overlay) {
        var formData = new FormData();
        formData.append('csrf_token', CSRF);
        formData.append('overlay', overlay);
        formData.append('image_data', imageData);

        fetch(APP_URL + '/edit/capture', {
            method: 'POST',
            body: formData,
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.error) {
                setStatus('Error: ' + data.error);
            } else {
                setStatus('Photo saved!');
                addToSidebar(data.filename, data.url);
            }
            captureBtn.disabled = false;
            updateButtonState();
        })
        .catch(function () {
            setStatus('Network error.');
            captureBtn.disabled = false;
            updateButtonState();
        });
    }

    // === Add photo to sidebar ===
    function addToSidebar(filename, url) {
        var empty = myPhotosList.querySelector('.empty-state');
        if (empty) empty.remove();

        var item = document.createElement('div');
        item.className  = 'my-photo-item';
        item.dataset.id = '';

        var img = document.createElement('img');
        img.src = url;
        img.alt = 'My photo';

        var btn = document.createElement('button');
        btn.className   = 'delete-btn';
        btn.title       = 'Delete';
        btn.textContent = '\u2715';
        btn.dataset.csrf = CSRF;

        item.appendChild(img);
        item.appendChild(btn);

        // We do a quick fetch to get the image id from the filename
        // by listing after upload – simpler: reload page
        myPhotosList.insertBefore(item, myPhotosList.firstChild);
    }

    // === Delete ===
    myPhotosList.addEventListener('click', function (e) {
        var btn = e.target.closest('.delete-btn');
        if (!btn) return;
        var item = btn.closest('.my-photo-item');
        var id   = item.dataset.id || btn.dataset.id;
        if (!id) {
            // Newly added item without id – reload page
            location.reload();
            return;
        }
        if (!confirm('Delete this photo?')) return;

        var formData = new FormData();
        formData.append('csrf_token', btn.dataset.csrf || CSRF);

        fetch(APP_URL + '/edit/delete/' + encodeURIComponent(id), {
            method: 'POST',
            body: formData,
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                item.remove();
                if (!myPhotosList.querySelector('.my-photo-item')) {
                    var p = document.createElement('p');
                    p.className = 'empty-state';
                    p.textContent = 'No photos yet.';
                    myPhotosList.appendChild(p);
                }
            } else {
                alert('Could not delete: ' + (data.error || 'unknown error'));
            }
        })
        .catch(function () { alert('Network error.'); });
    });

    // === Helpers ===
    function setStatus(msg) {
        if (statusMsg) statusMsg.textContent = msg;
    }

    // === Init ===
    startWebcam();

    window.addEventListener('resize', function () {
        if (selectedOverlay && !useUpload.checked) drawOverlayPreview();
    });

}());
