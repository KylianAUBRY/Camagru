// Configuration de l'API
const API_URL = '../backend';

// Variables globales
let stream = null;
let selectedSticker = null;
let capturedImage = null;

// Vérifier l'authentification
function checkAuth() {
    const token = localStorage.getItem('token');
    if (!token) {
        window.location.href = 'login.html';
        return false;
    }
    return true;
}

// Initialiser la caméra
async function initCamera() {
    const video = document.getElementById('video');
    
    try {
        stream = await navigator.mediaDevices.getUserMedia({ 
            video: { width: 640, height: 480 },
            audio: false 
        });
        video.srcObject = stream;
    } catch (error) {
        console.error('Erreur d\'accès à la caméra:', error);
        alert('Impossible d\'accéder à la caméra. Assurez-vous d\'avoir donné les permissions nécessaires.');
    }
}

// Arrêter la caméra
function stopCamera() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
}

// Charger les stickers
async function loadStickers() {
    const container = document.getElementById('stickersContainer');
    if (!container) return;

    // Pour l'instant, utiliser des stickers de démonstration
    const demoStickers = [
        { id: 1, name: '😀', type: 'emoji' },
        { id: 2, name: '🎉', type: 'emoji' },
        { id: 3, name: '❤️', type: 'emoji' },
        { id: 4, name: '⭐', type: 'emoji' },
        { id: 5, name: '🎈', type: 'emoji' },
        { id: 6, name: '🌟', type: 'emoji' },
    ];

    container.innerHTML = '';
    demoStickers.forEach(sticker => {
        const item = document.createElement('div');
        item.className = 'sticker-item';
        item.innerHTML = `<span style="font-size: 3rem;">${sticker.name}</span>`;
        item.dataset.stickerId = sticker.id;
        item.dataset.stickerName = sticker.name;
        
        item.addEventListener('click', () => {
            // Retirer la sélection précédente
            document.querySelectorAll('.sticker-item').forEach(s => s.classList.remove('selected'));
            // Sélectionner le nouveau sticker
            item.classList.add('selected');
            selectedSticker = sticker.name;
        });
        
        container.appendChild(item);
    });
}

// Capturer une photo
function capturePhoto() {
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const context = canvas.getContext('2d');
    const saveBtn = document.getElementById('saveBtn');
    
    // Définir les dimensions du canvas
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    // Dessiner l'image vidéo sur le canvas
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    // Ajouter le sticker si sélectionné
    if (selectedSticker) {
        context.font = '100px Arial';
        context.fillText(selectedSticker, canvas.width / 2 - 50, canvas.height / 2);
    }
    
    // Afficher le canvas et cacher la vidéo
    canvas.style.display = 'block';
    video.style.display = 'none';
    
    // Convertir le canvas en blob
    canvas.toBlob((blob) => {
        capturedImage = blob;
        saveBtn.style.display = 'inline-block';
    }, 'image/png');
}

// Upload d'une image
function handleFileUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    const canvas = document.getElementById('canvas');
    const video = document.getElementById('video');
    const context = canvas.getContext('2d');
    const saveBtn = document.getElementById('saveBtn');
    
    const reader = new FileReader();
    reader.onload = (e) => {
        const img = new Image();
        img.onload = () => {
            canvas.width = img.width;
            canvas.height = img.height;
            context.drawImage(img, 0, 0);
            
            // Ajouter le sticker si sélectionné
            if (selectedSticker) {
                context.font = '100px Arial';
                context.fillText(selectedSticker, canvas.width / 2 - 50, canvas.height / 2);
            }
            
            canvas.style.display = 'block';
            video.style.display = 'none';
            
            canvas.toBlob((blob) => {
                capturedImage = blob;
                saveBtn.style.display = 'inline-block';
            }, 'image/png');
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

// Sauvegarder l'image
async function saveImage() {
    if (!capturedImage) {
        alert('Aucune image à sauvegarder');
        return;
    }
    
    const token = localStorage.getItem('token');
    const formData = new FormData();
    formData.append('image', capturedImage, 'photo.png');
    formData.append('action', 'upload');
    
    try {
        const response = await fetch(`${API_URL}/api/images.php`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Image sauvegardée avec succès !');
            resetCamera();
            loadMyPhotos();
        } else {
            alert(data.message || 'Erreur lors de la sauvegarde');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la sauvegarde de l\'image');
    }
}

// Réinitialiser la caméra
function resetCamera() {
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const saveBtn = document.getElementById('saveBtn');
    
    canvas.style.display = 'none';
    video.style.display = 'block';
    saveBtn.style.display = 'none';
    
    capturedImage = null;
    selectedSticker = null;
    
    // Retirer la sélection des stickers
    document.querySelectorAll('.sticker-item').forEach(s => s.classList.remove('selected'));
}

// Charger mes photos
async function loadMyPhotos() {
    const container = document.getElementById('myPhotosContainer');
    if (!container) return;
    
    const token = localStorage.getItem('token');
    
    try {
        const response = await fetch(`${API_URL}/api/images.php?action=my_images`, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.images && data.images.length > 0) {
            container.innerHTML = '';
            data.images.forEach(image => {
                const card = createImageCard(image);
                container.appendChild(card);
            });
        } else {
            container.innerHTML = '<p>Vous n\'avez pas encore de photos.</p>';
        }
    } catch (error) {
        console.error('Erreur:', error);
        container.innerHTML = '<p>Erreur lors du chargement de vos photos.</p>';
    }
}

// Créer une carte d'image
function createImageCard(image) {
    const card = document.createElement('div');
    card.className = 'image-card';
    
    card.innerHTML = `
        <img src="${API_URL}/uploads/${image.filename}" alt="Photo">
        <div class="image-info">
            <p>${formatDate(image.created_at)}</p>
            <button class="btn btn-secondary" onclick="deleteImage(${image.id})">Supprimer</button>
        </div>
    `;
    
    return card;
}

// Supprimer une image
async function deleteImage(imageId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette image ?')) {
        return;
    }
    
    const token = localStorage.getItem('token');
    
    try {
        const response = await fetch(`${API_URL}/api/images.php`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({
                action: 'delete',
                image_id: imageId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadMyPhotos();
        } else {
            alert(data.message || 'Erreur lors de la suppression');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la suppression de l\'image');
    }
}

// Formater une date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Gestion de la déconnexion
const logoutLink = document.getElementById('logoutLink');
if (logoutLink) {
    logoutLink.addEventListener('click', (e) => {
        e.preventDefault();
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        stopCamera();
        window.location.href = 'index.html';
    });
}

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    if (!checkAuth()) return;
    
    initCamera();
    loadStickers();
    loadMyPhotos();
    
    // Événements
    const captureBtn = document.getElementById('captureBtn');
    const uploadBtn = document.getElementById('uploadBtn');
    const fileInput = document.getElementById('fileInput');
    const saveBtn = document.getElementById('saveBtn');
    
    if (captureBtn) captureBtn.addEventListener('click', capturePhoto);
    if (uploadBtn) uploadBtn.addEventListener('click', () => fileInput.click());
    if (fileInput) fileInput.addEventListener('change', handleFileUpload);
    if (saveBtn) saveBtn.addEventListener('click', saveImage);
});

// Nettoyer la caméra à la fermeture
window.addEventListener('beforeunload', () => {
    stopCamera();
});
