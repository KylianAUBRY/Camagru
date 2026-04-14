<?php

declare(strict_types=1);

session_start();

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/core/Database.php';
require_once dirname(__DIR__) . '/core/Router.php';
require_once dirname(__DIR__) . '/core/Controller.php';

$router = new Router();

// Auth routes
$router->get('/login',          'AuthController', 'login');
$router->post('/login',         'AuthController', 'loginPost');
$router->get('/register',       'AuthController', 'register');
$router->post('/register',      'AuthController', 'registerPost');
$router->get('/logout',         'AuthController', 'logout');
$router->get('/verify',         'AuthController', 'verify');
$router->get('/forgot-password','AuthController', 'forgot');
$router->post('/forgot-password','AuthController','forgotPost');
$router->get('/reset-password', 'AuthController', 'reset');
$router->post('/reset-password','AuthController', 'resetPost');

// Gallery routes
$router->get('/',               'GalleryController', 'index');
$router->get('/gallery',        'GalleryController', 'index');
$router->post('/gallery/like',  'GalleryController', 'like');
$router->post('/gallery/comment','GalleryController','comment');

// Edit routes
$router->get('/edit',           'EditController', 'index');
$router->post('/edit/capture',  'EditController', 'capture');
$router->post('/edit/upload',   'EditController', 'upload');
$router->post('/edit/delete/{id}','EditController','delete');

// Profile routes
$router->get('/profile',        'UserController', 'profile');
$router->post('/profile',       'UserController', 'profilePost');

$method = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'];

$router->dispatch($method, $uri);
