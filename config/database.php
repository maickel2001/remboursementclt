<?php
// Configuration de la base de données pour Hostinger
class Database {
    // Configuration sécurisée via variables d'environnement
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $charset;
    public $conn;

    public function __construct() {
        // Charger les variables d'environnement ou utiliser les valeurs par défaut
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->db_name = $_ENV['DB_NAME'] ?? 'u634930929_ktloee';
        $this->username = $_ENV['DB_USERNAME'] ?? 'u634930929_ktloee';
        $this->password = $_ENV['DB_PASSWORD'] ?? 'Ino1234@'; // Valeur par défaut temporaire
        $this->charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            // Afficher l'erreur détaillée pour le debug
            die("Erreur de connexion à la base de données: " . $exception->getMessage() . 
                "<br>Host: " . $this->host . 
                "<br>Database: " . $this->db_name . 
                "<br>Username: " . $this->username);
        }
        return $this->conn;
    }
}

// Test de connexion simple
function testDatabaseConnection() {
    try {
        $database = new Database();
        $conn = $database->getConnection();
        if ($conn) {
            return true;
        }
    } catch (Exception $e) {
        return false;
    }
    return false;
}
?>