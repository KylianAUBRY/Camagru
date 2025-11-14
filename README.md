# Camagru - Projet 42

Un clone d'Instagram minimaliste permettant de créer et partager des photos avec des filtres/stickers.

## 📋 Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache ou Nginx)
- Navigateur moderne avec support de la webcam

## 🚀 Installation

### 1. Cloner le repository

```bash
git clone https://github.com/votre-username/Camagru.git
cd Camagru
```

### 2. Configuration de la base de données

Créer la base de données et importer le schéma :

```bash
mysql -u root -p < config/database.sql
```

### 3. Configuration

Modifier le fichier `config/config.php` avec vos paramètres :

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'camagru');
define('DB_USER', 'votre_utilisateur');
define('DB_PASS', 'votre_mot_de_passe');

define('SITE_URL', 'http://localhost:8080');
define('JWT_SECRET', 'votre_secret_key_tres_securisee'); // À changer !
```

### 4. Permissions

S'assurer que le dossier `public/uploads` est accessible en écriture :

```bash
chmod 755 public/uploads
```

### 5. Démarrer le serveur

**Option 1 : Serveur PHP intégré (développement)**

```bash
cd public
php -S localhost:8080
```

**Option 2 : Apache**

Pointer votre VirtualHost vers le dossier `public/`

## 📁 Structure du projet

```
Camagru/
├── backend/
│   ├── api/
│   │   ├── auth.php          # API d'authentification
│   │   └── images.php        # API de gestion des images
│   ├── models/
│   │   ├── User.php          # Modèle utilisateur
│   │   └── Image.php         # Modèle image
│   └── controllers/
├── config/
│   ├── config.php            # Configuration générale
│   ├── database.php          # Connexion à la base de données
│   └── database.sql          # Schéma de la base de données
├── public/
│   ├── css/
│   │   └── style.css         # Styles CSS
│   ├── js/
│   │   ├── app.js            # Script principal
│   │   ├── auth.js           # Gestion de l'authentification
│   │   └── camera.js         # Gestion de la caméra
│   ├── images/               # Images statiques
│   ├── uploads/              # Images uploadées par les utilisateurs
│   ├── index.html            # Page d'accueil
│   ├── login.html            # Page de connexion
│   ├── register.html         # Page d'inscription
│   ├── camera.html           # Page de capture photo
│   └── .htaccess             # Configuration Apache
└── README.md
```

## 🎯 Fonctionnalités

### ✅ Implémentées

- Inscription et connexion utilisateur
- Vérification d'email (token)
- Capture de photos via webcam
- Upload d'images depuis l'ordinateur
- Application de stickers sur les photos
- Galerie d'images publique
- Gestion des images personnelles
- Likes sur les images
- Commentaires sur les images
- Suppression de ses propres images

### 🔄 À implémenter

- Récupération de mot de passe
- Notifications par email
- Pagination de la galerie
- Filtres d'images avancés
- Édition de profil
- Recherche d'utilisateurs
- Mode sombre

## 🔐 Sécurité

- Mots de passe hashés avec `password_hash()`
- Validation des données côté serveur
- Protection CSRF
- Vérification des types MIME pour les uploads
- Limitation de la taille des fichiers
- Token JWT pour l'authentification
- Protection des routes sensibles

## 🛠️ Technologies utilisées

### Frontend
- HTML5
- CSS3 (avec Flexbox et Grid)
- JavaScript vanilla (ES6+)
- WebRTC API (pour la webcam)
- Canvas API (pour les stickers)

### Backend
- PHP 7.4+
- PDO (MySQL)
- JWT pour l'authentification

## 📝 API Endpoints

### Authentification

- `POST /backend/api/auth.php` - Inscription/Connexion
  - Action: `register`, `login`, `forgot_password`
  
- `GET /backend/api/auth.php?action=verify&token=xxx` - Vérification email

### Images

- `GET /backend/api/images.php` - Liste toutes les images
- `GET /backend/api/images.php?action=my_images` - Mes images (authentifié)
- `POST /backend/api/images.php` - Upload d'une image (authentifié)
- `DELETE /backend/api/images.php` - Suppression d'une image (authentifié)
- `POST /backend/api/images.php` - Like/Unlike/Comment (authentifié)

## 📄 Licence

Projet réalisé dans le cadre du cursus 42.

## 👤 Auteur

Votre nom - [@votre-github](https://github.com/votre-username)
