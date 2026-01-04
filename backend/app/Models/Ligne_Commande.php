<?php
// Déclare l'espace de noms pour ce modèle
namespace Mini\Models;

// Importe la classe Database pour accéder à la base de données
use Mini\Core\Database;
// Importe la classe PDO pour les requêtes préparées
use PDO;

// Déclare la classe Ligne_Commande pour représenter les lignes (articles) d'une commande
class Ligne_Commande
{
    private $id;
    private $id_commande;
    private $id_produit;
    private $quantite;
    private $prix_unitaire;
    private $prix_total;

    // =====================
    // Getters / Setters
    // =====================

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getIdCommande()
    {
        return $this->id_commande;
    }

    public function setIdCommande($id_commande)
    {
        $this->id_commande = $id_commande;
    }

    public function getIdProduit()
    {
        return $this->id_produit;
    }

    public function setIdProduit($id_produit)
    {
        $this->id_produit = $id_produit;
    }

    public function getQuantite()
    {
        return $this->quantite;
    }

    public function setQuantite($quantite)
    {
        $this->quantite = $quantite;
    }

    public function getPrixUnitaire()
    {
        return $this->prix_unitaire;
    }

    public function setPrixUnitaire($prix_unitaire)
    {
        $this->prix_unitaire = $prix_unitaire;
    }
    public function getPrixTotal()
    {
        return $this->prix_total;
    }

    public function setPrixTotal($prix_total)
    {
        $this->prix_total = $prix_total;
    }

    // =====================
    // Méthodes CRUD
    // =====================

    /**
     * Récupère tous les utilisateurs
     * @return array
     */
    public static function getAll()
    {
        $pdo = Database::getPDO();
        $stmt = $pdo->query("SELECT * FROM Ligne_Commande ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un utilisateur par son ID
     * @param int $id
     * @return array|null
     */
    public static function findById($id)
    {
        $pdo = Database::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM Ligne_Commande WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un utilisateur par son id_commande
     * @param string $id_commande
     * @return array|null
     */
    public static function findByIdCommande($id_commande)
    {
        $pdo = Database::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM Ligne_Commande WHERE id_commande = ?");
        $stmt->execute([$id_commande]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function findByIdCommandeAndIdProduit($id_commande, $id_produit) {
        $pdo = Database::getPDO();
        $stmt = $pdo->prepare("SELECT id FROM Ligne_Commande WHERE id_commande = ? AND id_produit = ?");
        $stmt->execute([$id_commande, $id_produit]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un nouvel utilisateur
     * @return bool
     */
    public function save()
    {
        $pdo = Database::getPDO();
        // columns: id_commande, id_produit, quantite, prix_unitaire, prix_total
        $stmt = $pdo->prepare("INSERT INTO Ligne_Commande (id_commande, id_produit, quantite, prix_unitaire, prix_total) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$this->id_commande, $this->id_produit, $this->quantite, $this->prix_unitaire, $this->prix_total]);
    }

    /**
     * Met à jour les informations d’un utilisateur existant
     * @return bool
     */
    public function update()
    {
        $pdo = Database::getPDO();
        $stmt = $pdo->prepare("UPDATE Ligne_Commande SET id_commande = ?, id_produit = ?, quantite = ?, prix_unitaire = ?, prix_total = ? WHERE id = ?");
        return $stmt->execute([$this->id_commande, $this->id_produit, $this->quantite, $this->prix_unitaire, $this->prix_total, $this->id]);
    }

    /**
     * Supprime un utilisateur
     * @return bool
     */
    public function delete()
    {
        $pdo = Database::getPDO();
        $stmt = $pdo->prepare("DELETE FROM Ligne_Commande WHERE id = ?");
        return $stmt->execute([$this->id]);
    }
}
