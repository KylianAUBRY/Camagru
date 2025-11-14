// Configuration de l'API
const API_URL = '../backend';

// Vérifier si l'utilisateur est connecté
function checkAuth() {
    const token = localStorage.getItem('token');
    const loginLink = document.getElementById('loginLink');
    const registerLink = document.getElementById('registerLink');
    
    if (token) {
        // Utilisateur connecté
        if (loginLink) loginLink.innerHTML = '<a href="camera.html">Caméra</a>';
        if (registerLink) registerLink.innerHTML = '<a href="#" id="logoutLink">Déconnexion</a>';
        
        const logoutLink = document.getElementById('logoutLink');
        if (logoutLink) {
            logoutLink.addEventListener('click', (e) => {
                e.preventDefault();
                logout();
            });
        }
    }
}

// Fonction de déconnexion
function logout() {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    window.location.href = 'index.html';
}

// Charger les images de la galerie
async function loadGallery() {
    const container = document.getElementById('imagesContainer');
    if (!container) return;

    try {
        const response = await fetch(`${API_URL}/api/images.php`);
        const data = await response.json();

        if (data.success && data.images) {
            container.innerHTML = '';
            data.images.forEach(image => {
                const card = createImageCard(image);
                container.appendChild(card);
            });
        } else {
            container.innerHTML = '<p>Aucune image disponible pour le moment.</p>';
        }
    } catch (error) {
        console.error('Erreur lors du chargement de la galerie:', error);
        container.innerHTML = '<p>Erreur lors du chargement des images.</p>';
    }
}

// Créer une carte d'image
function createImageCard(image) {
    const card = document.createElement('div');
    card.className = 'image-card';
    
    card.innerHTML = `
        <img src="${API_URL}/uploads/${image.filename}" alt="Photo">
        <div class="image-info">
            <p><strong>${image.username}</strong></p>
            <p>${formatDate(image.created_at)}</p>
            <p>❤️ ${image.likes || 0} | 💬 ${image.comments || 0}</p>
        </div>
    `;
    
    return card;
}

// Formater une date
function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000); // différence en secondes

    if (diff < 60) return 'À l\'instant';
    if (diff < 3600) return `Il y a ${Math.floor(diff / 60)} min`;
    if (diff < 86400) return `Il y a ${Math.floor(diff / 3600)} h`;
    if (diff < 604800) return `Il y a ${Math.floor(diff / 86400)} j`;
    
    return date.toLocaleDateString('fr-FR');
}

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    checkAuth();
    loadGallery();
});
