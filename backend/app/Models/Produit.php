<?php
// Déclare l'espace de noms pour ce modèle
namespace Mini\Models;

// Importe la classe Database pour accéder à la base de données
use Mini\Core\Database;
// Importe la classe PDO pour les requêtes préparées
use PDO;

// Déclare la classe Produit pour représenter les données d'un produit
class Produit
{
    private $id;
    private $nom;
    private $description;
    private $prix;
    private $image;
    private $stock;
    private $actif;
    private $id_categorie;

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

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getPrix()
    {
        return $this->prix;
    }

    public function setPrix($prix)
    {
        $this->prix = $prix;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getStock()
    {
        return $this->stock;
    }

    public function setStock($stock)
    {
        $this->stock = $stock;
    }

    public function getActif()
    {
        return $this->actif;
    }

    public function setActif($actif)
    {
        $this->actif = $actif;
    }

    public function getIdCategorie()
    {
        return $this->id_categorie;
    }

    public function setIdCategorie($id_categorie)
    {
        $this->id_categorie = $id_categorie;
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
        $stmt = $pdo->query("SELECT * FROM Produit ORDER BY id DESC");
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
        $stmt = $pdo->prepare("SELECT * FROM Produit WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un utilisateur par son nom
     * @param string $nom
     * @return array|null
     */
    public static function findByName($nom)
    {
        $pdo = Database::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM Produit WHERE nom = ?");
        $stmt->execute([$nom]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un nouvel utilisateur
     * @return bool
     */
    public function save()
    {
        $pdo = Database::getPDO();
        $stmt = $pdo->prepare("INSERT INTO Produit (nom, description, prix, image, stock, actif, id_categorie) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$this->nom, $this->description, $this->prix, $this->image, $this->stock, $this->actif, $this->id_categorie]);
    }

    /**
     * Met à jour les informations d’un utilisateur existant
     * @return bool
     */
    public function update()
    {
        $pdo = Database::getPDO();
        $stmt = $pdo->prepare("UPDATE Produit SET nom = ?, description = ?, prix = ?, image = ?, stock = ?, actif = ?, id_categorie = ? WHERE id = ?");
        return $stmt->execute([$this->nom, $this->description, $this->prix, $this->image, $this->stock, $this->actif, $this->id_categorie, $this->id]);
    }

    /**
     * Supprime un utilisateur
     * @return bool
     */
    public function delete()
    {
        $pdo = Database::getPDO();
        $stmt = $pdo->prepare("DELETE FROM Produit WHERE id = ?");
        return $stmt->execute([$this->id]);
    }
}
