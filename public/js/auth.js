// Configuration de l'API
const API_URL = '../backend';

// Gestion du formulaire de connexion
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        const errorMessage = document.getElementById('errorMessage');
        
        try {
            const response = await fetch(`${API_URL}/api/auth.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'login',
                    username: username,
                    password: password
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Sauvegarder le token et les infos utilisateur
                localStorage.setItem('token', data.token);
                localStorage.setItem('user', JSON.stringify(data.user));
                
                // Rediriger vers la page caméra
                window.location.href = 'camera.html';
            } else {
                errorMessage.textContent = data.message || 'Erreur de connexion';
                errorMessage.classList.add('show');
            }
        } catch (error) {
            console.error('Erreur:', error);
            errorMessage.textContent = 'Erreur de connexion au serveur';
            errorMessage.classList.add('show');
        }
    });
}

// Gestion du formulaire d'inscription
const registerForm = document.getElementById('registerForm');
if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const username = document.getElementById('username').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const errorMessage = document.getElementById('errorMessage');
        const successMessage = document.getElementById('successMessage');
        
        // Réinitialiser les messages
        errorMessage.classList.remove('show');
        successMessage.classList.remove('show');
        
        // Validation côté client
        if (password !== confirmPassword) {
            errorMessage.textContent = 'Les mots de passe ne correspondent pas';
            errorMessage.classList.add('show');
            return;
        }
        
        if (password.length < 8) {
            errorMessage.textContent = 'Le mot de passe doit contenir au moins 8 caractères';
            errorMessage.classList.add('show');
            return;
        }
        
        try {
            const response = await fetch(`${API_URL}/api/auth.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'register',
                    username: username,
                    email: email,
                    password: password
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                successMessage.textContent = 'Inscription réussie ! Vérifiez votre email pour activer votre compte.';
                successMessage.classList.add('show');
                
                // Réinitialiser le formulaire
                registerForm.reset();
                
                // Rediriger vers la page de connexion après 3 secondes
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 3000);
            } else {
                errorMessage.textContent = data.message || 'Erreur lors de l\'inscription';
                errorMessage.classList.add('show');
            }
        } catch (error) {
            console.error('Erreur:', error);
            errorMessage.textContent = 'Erreur de connexion au serveur';
            errorMessage.classList.add('show');
        }
    });
}

// Gestion du mot de passe oublié
const forgotPassword = document.getElementById('forgotPassword');
if (forgotPassword) {
    forgotPassword.addEventListener('click', (e) => {
        e.preventDefault();
        // TODO: Implémenter la fonctionnalité de récupération de mot de passe
        alert('Fonctionnalité à venir');
    });
}
