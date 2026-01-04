<?php
// Active le mode strict pour les types
declare(strict_types=1);
// Déclare l'espace de noms pour ce contrôleur
namespace Mini\Controllers;

// Importe la classe de base Controller du noyau
use Mini\Core\Controller;
// Importe le modèle Categorie
use Mini\Models\Categorie;

// Déclare la classe finale CategorieController qui hérite de Controller
final class CategorieController extends Controller
{
    // Retourne la liste des catégories au format JSON
    public function categoriesJson(): void
    {
        // Définit le header Content-Type pour indiquer que la réponse est du JSON
        header('Content-Type: application/json; charset=utf-8');
        // Crée une instance Categorie et récupère tous les enregistrements
        $cat = new Categorie();
        $data = $cat->getAll();
        // Encode les données en JSON et les affiche
        echo json_encode($data, JSON_PRETTY_PRINT);
    }
}
