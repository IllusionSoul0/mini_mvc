<?php

// Retenir son utilisation  => Database::getPDO()
// Design Pattern : Singleton
/**
 * Classe qui va nous permettre de nous connecter à notre base de données = oshop
 */
namespace Mini\Core;

use PDO;

class Database
{
    /** @var PDO */
    private $dbh;
    private static $_instance;
    private function __construct()
    {
        // Récupération des données du fichier de config
        // la fonction parse_ini_file parse le fichier et retourne un array associatif
        $configData = parse_ini_file(__DIR__ . '/../config.ini');

        try {
            $this->dbh = new PDO(
                "pgsql:host={$configData['DB_HOST']};port=5432;dbname={$configData['DB_NAME']}",
                $configData['DB_USERNAME'],
                $configData['DB_PASSWORD']
            );
        } catch (\Exception $exception) {
            echo 'Erreur de connexion...<br>';
            echo $exception->getMessage() . '<br>';
            echo '<pre>';
            echo $exception->getTraceAsString();
            echo '</pre>';
            exit;
        }
    }
    // the unique method you need to use
    public static function getPDO()
    {
        // If no instance => create one
        if (empty(self::$_instance)) {
            self::$_instance = new Database();
        }
        return self::$_instance->dbh;
    }
}