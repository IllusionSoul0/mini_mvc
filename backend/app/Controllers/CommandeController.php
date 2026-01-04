<?php
// Active le mode strict pour les types
declare(strict_types=1);
// Déclare l'espace de noms pour ce contrôleur
namespace Mini\Controllers;

// Importe les classes utiles
use Mini\Core\Controller;
use Mini\Core\Database;
use Mini\Models\Commande;
use Mini\Models\Ligne_Commande;
use Mini\Models\Produit;

// Déclare la classe finale CommandeController qui hérite de Controller
final class CommandeController extends Controller
{
    /**
     * Crée une commande et ses lignes depuis le JSON envoyé en POST.
     *
     * JSON attendu : { id_client, adresse_livraison, items: [{ id_produit, quantite }] }
     * Réponses : 201 + { success: true, id_commande, commande } ou 4xx/5xx en cas d'erreur.
     */
    public function createCommande(): void
    {
        // Définit le header Content-Type pour indiquer que la réponse est du JSON
        header('Content-Type: application/json; charset=utf-8');

        // Vérifie que la méthode HTTP est POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée. Utilisez POST.'], JSON_PRETTY_PRINT);
            return;
        }

        // Analyse le JSON du body de la requête
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input === null) {
            http_response_code(400);
            echo json_encode(['error' => 'Corps JSON attendu.'], JSON_PRETTY_PRINT);
            return;
        }

        // Valide les champs requis
        if (empty($input['id_client']) || empty($input['items']) || !is_array($input['items'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Champs id_client et items (tableau) requis.'], JSON_PRETTY_PRINT);
            return;
        }

        $id_client = intval($input['id_client']);
        if ($id_client <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'id_client invalide.'], JSON_PRETTY_PRINT);
            return;
        }

        $adresse = trim((string)($input['adresse_livraison'] ?? ''));
        $items = $input['items'];

        $pdo = Database::getPDO();

        try {
            $pdo->beginTransaction();

            $montant = 0.0;
            foreach ($items as $item) {
                $pid = intval($item['id_produit'] ?? 0);
                $qty = intval($item['quantite'] ?? 0);
                if ($pid <= 0 || $qty <= 0) {
                    throw new \Exception('Produit ou quantité invalide');
                }

                $produit = Produit::findById($pid);
                if (!$produit) {
                    throw new \Exception("Produit $pid introuvable");
                }

                if (intval($produit['stock']) < $qty) {
                    throw new \Exception("Stock insuffisant pour le produit {$produit['nom']}");
                }

                $prix_unitaire = floatval($produit['prix']);
                $montant += $prix_unitaire * $qty;
            }

            $commande = new Commande();
            $commande->setIdClient($id_client);
            $commande->setStatut('en attente');
            $commande->setMontant($montant);
            $commande->setAdresseLivraison($adresse);

            if (!$commande->save()) {
                throw new \Exception('Erreur lors de la création de la commande');
            }

            // Debug: log saved commande with its assigned ID
            $cmdId = intval($commande->getId());
            if ($cmdId <= 0) {
                $cmdId = intval($pdo->lastInsertId());
            }
            error_log('[DEBUG commande] ' . json_encode([
                'id' => $cmdId,
                'id_client' => $commande->getIdClient(),
                'statut' => $commande->getStatut(),
                'montant' => $commande->getMontant(),
                'adresse_livraison' => $commande->getAdresseLivraison(),
            ]));

            $cmdId = intval($commande->getId());
            if ($cmdId <= 0) {
                $cmdId = intval($pdo->lastInsertId());
            }

            foreach ($items as $item) {
                $pid = intval($item['id_produit']);
                $qty = intval($item['quantite']);

                $produit = Produit::findById($pid);
                $prix_unitaire = floatval($produit['prix']);
                $prix_total = $prix_unitaire * $qty;

                $ligne = new Ligne_Commande();
                $ligne->setIdCommande($cmdId);
                $ligne->setIdProduit($pid);
                $ligne->setQuantite($qty);
                $ligne->setPrixUnitaire($prix_unitaire);
                $ligne->setPrixTotal($prix_total);

                if (!$ligne->save()) {
                    throw new \Exception('Erreur lors de la création d\'une ligne de commande');
                }

                $stmt = $pdo->prepare('UPDATE produit SET stock = stock - ? WHERE id = ?');
                $stmt->execute([$qty, $pid]);
            }

            $pdo->commit();

            // Return the saved commande (as associative array)
            $saved = Commande::findById($cmdId);

            http_response_code(201);
            echo json_encode(['success' => true, 'id_commande' => $cmdId, 'commande' => $saved], JSON_PRETTY_PRINT);
            return;
        } catch (\Exception $e) {
            $pdo->rollBack();
            http_response_code(400);
            error_log('[commande] error: ' . $e->getMessage());
            echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT);
            return;
        }
    }


    /**
     * Retourne les commandes d'un client au format JSON
     * GET /commandes/json?id_client=...
     */
    public function commandesByClientJson(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (empty($_GET['id_client'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Paramètre id_client requis.'], JSON_PRETTY_PRINT);
            return;
        }

        $id_client = intval($_GET['id_client']);
        $pdo = Database::getPDO();
        $stmt = $pdo->prepare('SELECT * FROM commande WHERE id_client = ? ORDER BY id DESC');
        $stmt->execute([$id_client]);
        $commandes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Récupère les lignes pour chaque commande
        foreach ($commandes as &$cmd) {
            $stmt2 = $pdo->prepare('SELECT lc.*, p.nom as produit_nom FROM ligne_commande lc JOIN produit p ON lc.id_produit = p.id WHERE lc.id_commande = ?');
            $stmt2->execute([$cmd['id']]);
            $cmd['lignes'] = $stmt2->fetchAll(\PDO::FETCH_ASSOC);
        }

        echo json_encode($commandes, JSON_PRETTY_PRINT);
    }

    // Met à jour le statut d'une commande (POST /commandes/statut)
    public function updateStatut(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée. Utilisez POST.'], JSON_PRETTY_PRINT);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if ($input === null || empty($input['id']) || empty($input['statut'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Champs id et statut requis.'], JSON_PRETTY_PRINT);
            return;
        }

        $allowed = ['en attente', 'payée', 'expédiée', 'livrée', 'annulée'];
        $statut = $input['statut'];
        if (!in_array($statut, $allowed, true)) {
            http_response_code(400);
            echo json_encode(['error' => 'Statut invalide.'], JSON_PRETTY_PRINT);
            return;
        }

        $id = intval($input['id']);

        // Update only the statut column to avoid overwriting other fields with null
        $pdo = Database::getPDO();
        $stmt = $pdo->prepare('UPDATE commande SET statut = ? WHERE id = ?');
        $stmt->execute([$statut, $id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true], JSON_PRETTY_PRINT);
        } else {
            // If no rows affected, the commande was not found
            http_response_code(404);
            echo json_encode(['error' => 'Commande introuvable ou pas de changement.'], JSON_PRETTY_PRINT);
        }
    }

    public function updateCommande(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée. Utilisez PUT.'], JSON_PRETTY_PRINT);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if ($input === null || empty($input['id_commande'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Champ id_commande requis.'], JSON_PRETTY_PRINT);
            return;
        }

        $id_commande = intval($input['id_commande']);
        if ($id_commande <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'id_commande invalide.'], JSON_PRETTY_PRINT);
            return;
        }

        // Items et adresse_livraison sont optionnels
        $items = isset($input['items']) && is_array($input['items']) ? $input['items'] : null;
        $adresse_livraison = isset($input['adresse_livraison']) ? trim((string)$input['adresse_livraison']) : null;

        $pdo = Database::getPDO();

        try {
            $pdo->beginTransaction();

            // Check if commande exists and is in 'en attente' status
            $commande = Commande::findById($id_commande);
            if (!$commande) {
                throw new \Exception('Commande introuvable');
            }
            if ($commande['statut'] !== 'en attente') {
                throw new \Exception('Impossible de modifier une commande qui n\'est pas en attente');
            }

            // Si adresse_livraison fournie, met à jour la commande
            if ($adresse_livraison !== null) {
                $stmt_addr = $pdo->prepare('UPDATE commande SET adresse_livraison = ? WHERE id = ?');
                $stmt_addr->execute([$adresse_livraison, $id_commande]);
            }

            // Si items fournis, traite les articles
            if ($items !== null) {
                $montant_ajout = 0.0;
                foreach ($items as $item) {
                    $pid = intval($item['id_produit'] ?? 0);
                    $qty = intval($item['quantite'] ?? 0);
                    if ($pid <= 0 || $qty <= 0) {
                        throw new \Exception('Produit ou quantité invalide');
                    }

                    $produit = Produit::findById($pid);
                    if (!$produit) {
                        throw new \Exception("Produit $pid introuvable");
                    }

                    if (intval($produit['stock']) < $qty) {
                        throw new \Exception("Stock insuffisant pour le produit {$produit['nom']}");
                    }

                    $prix_unitaire = floatval($produit['prix']);
                    $montant_ajout += $prix_unitaire * $qty;

                    // Check if this product is already in the commande
                    $stmt_check = $pdo->prepare('SELECT id FROM ligne_commande WHERE id_commande = ? AND id_produit = ?');
                    $stmt_check->execute([$id_commande, $pid]);
                    $existing_line = $stmt_check->fetch(\PDO::FETCH_ASSOC);

                    if ($existing_line) {
                        // Update existing line: add quantity and recalculate price
                        $stmt_update = $pdo->prepare('UPDATE ligne_commande SET quantite = quantite + ?, prix_total = prix_total + ? WHERE id = ?');
                        $prix_total_ajout = $prix_unitaire * $qty;
                        $stmt_update->execute([$qty, $prix_total_ajout, $existing_line['id']]);
                    } else {
                        // Create new line
                        $prix_total = $prix_unitaire * $qty;
                        $ligne = new Ligne_Commande();
                        $ligne->setIdCommande($id_commande);
                        $ligne->setIdProduit($pid);
                        $ligne->setQuantite($qty);
                        $ligne->setPrixUnitaire($prix_unitaire);
                        $ligne->setPrixTotal($prix_total);

                        if (!$ligne->save()) {
                            throw new \Exception('Erreur lors de la création d\'une ligne de commande');
                        }
                    }

                    // Update stock
                    $stmt_stock = $pdo->prepare('UPDATE produit SET stock = stock - ? WHERE id = ?');
                    $stmt_stock->execute([$qty, $pid]);
                }

                // Update commande montant
                $nouveau_montant = floatval($commande['montant']) + $montant_ajout;
                $stmt_montant = $pdo->prepare('UPDATE commande SET montant = ? WHERE id = ?');
                $stmt_montant->execute([$nouveau_montant, $id_commande]);
            }

            $pdo->commit();

            // Return the updated commande
            $updated = Commande::findById($id_commande);

            http_response_code(200);
            echo json_encode(['success' => true, 'commande' => $updated], JSON_PRETTY_PRINT);
            return;
        } catch (\Exception $e) {
            $pdo->rollBack();
            http_response_code(400);
            error_log('[updateCommande] error: ' . $e->getMessage());
            echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT);
            return;
        }
    }
}
