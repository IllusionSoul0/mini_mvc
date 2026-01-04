<?php

// Active le mode strict pour la vérification des types
declare(strict_types=1);
// Déclare l'espace de noms pour ce contrôleur
namespace Mini\Controllers;
// Importe la classe de base Controller du noyau
use Mini\Core\Controller;
use Mini\Models\Produit;

// Déclare la classe finale ProduitController qui hérite de Controller
final class ProduitController extends Controller
{
    // Affiche la liste des produits (redirige vers produitsJson)
    public function listProduits(): void
    {
        // Retourne la liste JSON pour utilisation API
        $this->produitsJson();
    }

    // Retourne la liste des produits au format JSON
    public function produitsJson(): void
    {
        // Définit le header Content-Type pour indiquer que la réponse est du JSON
        header('Content-Type: application/json; charset=utf-8');
        // Si un id est fourni en query param, retourne le produit correspondant
        if (!empty($_GET['id'])) {
            $id = intval($_GET['id']);
            $produit = Produit::findById($id);
            if (!$produit) {
                http_response_code(404);
                echo json_encode(['error' => 'Produit non trouvé'], JSON_PRETTY_PRINT);
                return;
            }
            echo json_encode($produit, JSON_PRETTY_PRINT);
            return;
        }

        // Récupère tous les produits et les encode en JSON
        $produits = Produit::getAll();
        echo json_encode($produits, JSON_PRETTY_PRINT);
    }

    public function createProduit(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Vérifie que la méthode HTTP est POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée. Utilisez POST.'], JSON_PRETTY_PRINT);
            return;
        }

        // Récupère les données JSON du body de la requête
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input === null) {
            $input = $_POST;
        }

        // Valide les données requises
        if (empty($input['nom']) || !isset($input['prix']) || !isset($input['stock'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Les champs "nom", "prix" et "stock" sont requis.'], JSON_PRETTY_PRINT);
            return;
        }

        // Valide le prix (doit être un nombre positif)
        if (!is_numeric($input['prix']) || floatval($input['prix']) < 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Le prix doit être un nombre positif.'], JSON_PRETTY_PRINT);
            return;
        }

        // Valide le stock (doit être un entier positif)
        if (!is_numeric($input['stock']) || intval($input['stock']) < 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Le stock doit être un entier positif.'], JSON_PRETTY_PRINT);
            return;
        }

        // Valide l'URL de l'image si fournie
        $image = $input['image'] ?? '';
        if (!empty($image) && !filter_var($image, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            echo json_encode(['error' => 'L\'URL de l\'image n\'est pas valide.'], JSON_PRETTY_PRINT);
            return;
        }

        // Crée une nouvelle instance Produit
        $produit = new Produit();
        $produit->setNom($input['nom']);
        $produit->setDescription($input['description'] ?? '');
        $produit->setPrix(floatval($input['prix']));
        $produit->setStock(intval($input['stock']));
        $produit->setImage($image);
        $produit->setActif(isset($input['actif']) ? (bool)$input['actif'] : true);
        $produit->setIdCategorie(isset($input['id_categorie']) ? intval($input['id_categorie']) : null);

        // Sauvegarde le produit
        if ($produit->save()) {
            http_response_code(201);
            echo json_encode(['success' => true, 'message' => 'Produit créé avec succès.'], JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la création du produit.'], JSON_PRETTY_PRINT);
        }
    }
}

