<?php
// Déclare l'espace de noms pour ce modèle
namespace Mini\Models;

// Importe la classe Database pour accéder à la base de données
use Mini\Core\Database;
// Importe la classe PDO pour les requêtes préparées
use PDO;

// Déclare la classe Client pour représenter les données d'un client
class Client
{
    private $id;
    private $nom;
    private $prenom;
    private $adresse;
    private $email;
    private $mdp;

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
    public function getPrenom()
    {
        return $this->prenom;
    }

    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
    }
    public function getAdresse()
    {
        return $this->adresse;
    }

    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }
    public function getMdp()
    {
        return $this->mdp;
    }

    public function setMdp($mdp)
    {
        $this->mdp = $mdp;
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
        $stmt = $pdo->query("SELECT * FROM Client ORDER BY id DESC");
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
        $stmt = $pdo->prepare("SELECT * FROM Client WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un utilisateur par son email
     * @param string $email
     * @return array|null
     */
    public static function findByEmail($email)
    {
        $pdo = Database::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM Client WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function seConnecter($email, $mdp)
    {
        $pdo = Database::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM Client WHERE email = ?");
        $stmt->execute([$email]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$client) return false;
        if (!isset($client['mdp'])) return false;
        if ($client['mdp'] === $mdp) {
            return $client;
        }
        return false;
    }

    /**
     * Crée un nouvel utilisateur
     * @return bool
     */
    public function save()
    {
        $pdo = Database::getPDO();
        $stmt = $pdo->prepare("INSERT INTO Client (nom, prenom, adresse, email, mdp) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$this->nom, $this->prenom, $this->adresse, $this->email, $this->mdp]);
    }

    /**
     * Met à jour les informations d’un utilisateur existant
     * @return bool
     */
    public function update()
    {
        $pdo = Database::getPDO();
        $stmt = $pdo->prepare("UPDATE Client SET nom = ?, prenom = ?, adresse = ?, email = ?, mdp = ? WHERE id = ?");
        return $stmt->execute([$this->nom, $this->prenom, $this->adresse, $this->email, $this->mdp, $this->id]);
    }

    /**
     * Supprime un utilisateur
     * @return bool
     */
    public function delete()
    {
        $pdo = Database::getPDO();
        $stmt = $pdo->prepare("DELETE FROM Client WHERE id = ?");
        return $stmt->execute([$this->id]);
    }
}
