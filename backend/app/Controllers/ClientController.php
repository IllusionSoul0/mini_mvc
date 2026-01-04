<?php

// Active le mode strict pour la vérification des types
declare(strict_types=1);
// Déclare l'espace de noms pour ce contrôleur
namespace Mini\Controllers;
// Importe la classe de base Controller du noyau
use Mini\Core\Controller;
use Mini\Models\Client;

// Déclare la classe finale ClientController qui hérite de Controller
final class ClientController extends Controller
{
    // Endpoint racine de l'API - retourne un simple JSON au lieu de rendre le frontend
    public function index(): void
    {
        // Définit le header Content-Type pour indiquer que la réponse est du JSON
        header('Content-Type: application/json; charset=utf-8');
        // Encode et affiche un message JSON simple
        echo json_encode(['message' => 'Mini MVC API running'], JSON_PRETTY_PRINT);
    }

    public function clientsJson(): void
    {
        // Récupère tous les utilisateurs
        $clients = Client::getAll();
        
        // Définit le header Content-Type pour indiquer que la réponse est du JSON
        header('Content-Type: application/json; charset=utf-8');
        
        // Encode les données en JSON et les affiche
        echo json_encode($clients, JSON_PRETTY_PRINT);
    }

    public function createClient(): void
    {
        // Définit le header Content-Type pour indiquer que la réponse est du JSON
        header('Content-Type: application/json; charset=utf-8');
        
        // Vérifie que la méthode HTTP est POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée. Utilisez POST.'], JSON_PRETTY_PRINT);
            return;
        }
        
        // Récupère les données JSON du body de la requête
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Si pas de JSON, essaie de récupérer depuis $_POST
        if ($input === null) {
            $input = $_POST;
        }
        
        // Valide les données requises (now requires nom + email + mdp)
        if (empty($input['nom']) || empty($input['email']) || empty($input['mdp'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Les champs "nom", "email" et "mdp" sont requis.'], JSON_PRETTY_PRINT);
            return;
        }
        
        // Valide le format de l'email
        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Format d\'email invalide.'], JSON_PRETTY_PRINT);
            return;
        }

        // Vérifie unicité email
        if (Client::findByEmail($input['email'])) {
            http_response_code(409);
            echo json_encode(['error' => 'Email déjà utilisé.'], JSON_PRETTY_PRINT);
            return;
        }
        
        
        // Crée une nouvelle instance Client and store password in plain text (simple project convention)
        $client = new Client();
        $client->setNom($input['nom']);
        $client->setEmail($input['email']);
        $client->setMdp($input['mdp']);
        
        // Sauvegarde l'utilisateur
        if ($client->save()) {
            // récupère l'utilisateur pour retourner l'id
            $created = Client::findByEmail($input['email']);
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Utilisateur créé avec succès.',
                'client' => [
                    'id' => $created['id'],
                    'nom' => $created['nom'],
                    'email' => $created['email']
                ]
            ], JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la création de l\'utilisateur.'], JSON_PRETTY_PRINT);
        }
    }

    public function login(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée. Utilisez POST.'], JSON_PRETTY_PRINT);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if ($input === null) {
            http_response_code(400);
            echo json_encode(['error' => 'Corps JSON attendu.'], JSON_PRETTY_PRINT);
            return;
        }

        if (empty($input['email']) || empty($input['mdp'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email et mot de passe requis.'], JSON_PRETTY_PRINT);
            return;
        }

        $email = $input['email'];
        $mdp = $input['mdp'];

        $client = Client::seConnecter($email, $mdp);
        if ($client === false) {
            http_response_code(401);
            echo json_encode(['error' => 'Identifiants invalides.'], JSON_PRETTY_PRINT);
            return;
        }

        $_SESSION['client_id'] = $client['id'];

        echo json_encode(['success' => true, 'client' => ['id' => $client['id'], 'email' => $client['email'], 'nom' => $client['nom']]], JSON_PRETTY_PRINT);
    }

    public function logout(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        session_unset();
        session_destroy();
        echo json_encode(['success' => true], JSON_PRETTY_PRINT);
    }
}
