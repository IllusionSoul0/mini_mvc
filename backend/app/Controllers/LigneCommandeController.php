<?php
// Active le mode strict pour les types
declare(strict_types=1);

// Déclare l'espace de noms pour ce contrôleur
namespace Mini\Controllers;

// Importe les classes utiles
use Mini\Core\Controller;
use Mini\Core\Database;
use Mini\Models\Ligne_Commande;
use Mini\Models\Produit;

// Déclare la classe finale LigneCommandeController qui hérite de Controller
final class LigneCommandeController extends Controller
{
    
    // Supprime une ligne de commande spécifiée
    public function deleteLigneCommande() {
        // Définit le header Content-Type pour indiquer que la réponse est du JSON
        header('Content-Type: application/json; charset=utf-8');

        // Vérifie que la méthode HTTP est DELETE
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée. Utilisez DELETE.'], JSON_PRETTY_PRINT);
            return;
        }

        // Analyse le JSON du body de la requête
        $input = json_decode(file_get_contents('php://input'), true);
        // Valide que les champs requis sont présents : { id_commande, produit }
        if ($input === null || !isset($input['id_commande']) || !isset($input['produit'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Champs id_commande et produit requis.'], JSON_PRETTY_PRINT);
            return;
        }

        // Recherche le produit par son nom
        $id_produit_data = Produit::findByName($input['produit']);
        if (!$id_produit_data) {
            http_response_code(404);
            echo json_encode(['error' => 'Produit non trouvé'], JSON_PRETTY_PRINT);
            return;
        }
        $id_produit = (int)$id_produit_data['id'];

        // Recherche la ligne de commande correspondante
        $id_commande = (int)$input['id_commande'];
        $id_ligne_data = Ligne_Commande::findByIdCommandeAndIdProduit($id_commande, $id_produit);
        if (!$id_ligne_data) {
            http_response_code(404);
            echo json_encode(['error' => 'Ligne de commande non trouvée'], JSON_PRETTY_PRINT);
            return;
        }
        $id_ligne = (int)$id_ligne_data['id'];

        $pdo = Database::getPDO();
        try {
            $pdo->beginTransaction();

            // Fetch the ligne to get prix_total and quantite
            $ligneData = Ligne_Commande::findById($id_ligne);
            if (!$ligneData) {
                throw new \Exception('Ligne introuvable');
            }

            $prix_total = floatval($ligneData['prix_total']);
            $quantite = intval($ligneData['quantite']);
            $id_produit_ligne = intval($ligneData['id_produit']);

            // Delete the ligne
            $stmtDel = $pdo->prepare('DELETE FROM ligne_commande WHERE id = ?');
            $stmtDel->execute([$id_ligne]);

            // Restore product stock
            $stmtStock = $pdo->prepare('UPDATE produit SET stock = stock + ? WHERE id = ?');
            $stmtStock->execute([$quantite, $id_produit_ligne]);

            // Recompute commande montant from remaining lignes
            $stmtSum = $pdo->prepare('SELECT COALESCE(SUM(prix_total),0) as total FROM ligne_commande WHERE id_commande = ?');
            $stmtSum->execute([$id_commande]);
            $nouveau_montant = floatval($stmtSum->fetchColumn());

            // Update commande montant
            $stmtUpd = $pdo->prepare('UPDATE commande SET montant = ? WHERE id = ?');
            $stmtUpd->execute([$nouveau_montant, $id_commande]);

            $pdo->commit();

            echo json_encode(['succes' => true, 'id' => $id_ligne, 'montant' => $nouveau_montant], JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            $pdo->rollBack();
            error_log('[deleteLigneCommande] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT);
        }
    }
}
