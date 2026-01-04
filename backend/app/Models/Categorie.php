<?php
// Déclare l'espace de noms pour ce modèle
namespace Mini\Models;

// Importe la classe Database pour accéder à la base de données
use Mini\Core\Database;
// Importe la classe PDO pour les requêtes préparées
use PDO;

// Déclare la classe Categorie pour représenter les données d'une catégorie
class Categorie
{
    private $id;
    private $nom;
    private $description;
    private $image;

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

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getAll()
    {
        $pdo = Database::getPDO();
        $stmt = $pdo->query("SELECT * FROM Categorie ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}