<?php
// Configuration de la base de données pour Hostinger
class Database {
    // Configuration par défaut pour Hostinger
    private $host = 'localhost';
    private $db_name = 'u634930929_ktloee';
    private $username = 'u634930929_ktloee';
    private $password = 'Ino1234@';
    private $charset = 'utf8mb4';
    public $conn;

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