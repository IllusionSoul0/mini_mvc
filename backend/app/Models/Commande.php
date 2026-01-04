<?php
// Déclare l'espace de noms pour ce modèle
namespace Mini\Models;

// Importe la classe Database pour accéder à la base de données
use Mini\Core\Database;
// Importe la classe PDO pour les requêtes préparées
use PDO;

// Déclare la classe Commande pour représenter les données d'une commande
class Commande
{
    private $id;
    private $id_client;
    private $statut;
    private $montant;
    private $adresse_livraison;

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

    public function getIdClient()
    {
        return $this->id_client;
    }

    public function setIdClient($id_client)
    {
        $this->id_client = $id_client;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function setStatut($statut)
    {
        $this->statut = $statut;
    }

    public function getMontant()
    {
        return $this->montant;
    }

    public function setMontant($montant)
    {
        $this->montant = $montant;
    }

    public function getAdresseLivraison()
    {
        return $this->adresse_livraison;
    }

    public function setAdresseLivraison($adresse_livraison)
    {
        $this->adresse_livraison = $adresse_livraison;
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
        $stmt = $pdo->query("SELECT * FROM commande ORDER BY id DESC");
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
        $stmt = $pdo->prepare("SELECT * FROM commande WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un utilisateur par son id_client
     * @param int $id_client
     * @return array|null
     */
    public static function findByIdClient($id_client)
    {
        $pdo = Database::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM commande WHERE id_client = ?");
        $stmt->execute([$id_client]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un nouvel utilisateur
     * @return bool
     */
    public function save()
    {
        $pdo = Database::getPDO();
        // Use RETURNING id to get the newly inserted id reliably on Postgres
        $stmt = $pdo->prepare("INSERT INTO commande (id_client, statut, montant, adresse_livraison) VALUES (?, ?, ?, ?) RETURNING id");
        $stmt->execute([$this->id_client, $this->statut, $this->montant, $this->adresse_livraison]);
        $this->id = $stmt->fetchColumn();
        return $this->id !== false;
    }

    /**
     * Met à jour les informations d’un utilisateur existant
     * @return bool
     */
    public function update()
    {
        $pdo = Database::getPDO();
        $stmt = $pdo->prepare("UPDATE commande SET id_client = ?, statut = ?, montant = ?, adresse_livraison = ? WHERE id = ?");
        return $stmt->execute([$this->id_client, $this->statut, $this->montant, $this->adresse_livraison, $this->id]);
    }

    /**
     * Supprime un utilisateur
     * @return bool
     */
    public function delete()
    {
        $pdo = Database::getPDO();
        $stmt = $pdo->prepare("DELETE FROM commande WHERE id = ?");
        return $stmt->execute([$this->id]);
    }
}
