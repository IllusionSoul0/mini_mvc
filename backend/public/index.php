<?php
// Active le mode strict pour les types
declare(strict_types=1);

// Charge l'autoloader Composer
require dirname(__DIR__) . '/vendor/autoload.php';

// Configure les en-têtes CORS pour autoriser le frontend
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gère les requêtes OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Importe la classe routeur
use Mini\Core\Router;

// Démarre la session PHP
session_start();

// Table des routes minimaliste
$routes = [
    ['GET', '/', [Mini\Controllers\ClientController::class, 'index']],
    ['GET', '/clients', [Mini\Controllers\ClientController::class, 'clients']],
    ['POST', '/clients', [Mini\Controllers\ClientController::class, 'createClient']],
    ['GET', '/clients/json', [Mini\Controllers\ClientController::class, 'clientsJson']],
    ['GET', '/produits', [Mini\Controllers\ProduitController::class, 'listProduits']],
    ['GET', '/produits/json', [Mini\Controllers\ProduitController::class, 'produitsJson']],
    ['GET', '/produits/create', [Mini\Controllers\ProduitController::class, 'showCreateProduitForm']],
    ['POST', '/produits', [Mini\Controllers\ProduitController::class, 'createProduit']],
    ['POST', '/clients/login', [Mini\Controllers\ClientController::class, 'login']],
    ['POST', '/clients/logout', [Mini\Controllers\ClientController::class, 'logout']],
    ['POST', '/commandes', [Mini\Controllers\CommandeController::class, 'createCommande']],
    ['GET', '/commandes/json', [Mini\Controllers\CommandeController::class, 'commandesByClientJson']],
    ['POST', '/commandes/statut', [Mini\Controllers\CommandeController::class, 'updateStatut']],
    ['PUT', '/commandes', [Mini\Controllers\CommandeController::class, 'updateCommande']],
    ['DELETE', '/lignecommande', [Mini\Controllers\LigneCommandeController::class, 'deleteLigneCommande']],
    ['GET', '/categories/json', [Mini\Controllers\CategorieController::class, 'categoriesJson']],
];

// Bootstrap du router - crée une instance et dirige la requête
$router = new Router($routes);
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);