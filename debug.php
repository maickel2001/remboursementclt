<?php
// Page de debug pour identifier les problèmes
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html><head><title>Debug RemboursePRO</title></head><body>";
echo "<h1>Debug RemboursePRO</h1>";

// Informations PHP
echo "<h2>Informations PHP</h2>";
echo "Version PHP: " . phpversion() . "<br>";
echo "Serveur: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script actuel: " . __FILE__ . "<br>";

// Test des fichiers
echo "<h2>Test des fichiers</h2>";
$files_to_check = [
    'config/database.php',
    'config/auth.php',
    'assets/css/style.css',
    'login.php',
    'register.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✅ $file existe<br>";
    } else {
        echo "❌ $file manquant<br>";
    }
}

// Test de la base de données
echo "<h2>Test Base de Données</h2>";
try {
    if (file_exists('config/database.php')) {
        require_once 'config/database.php';
        $database = new Database();
        $conn = $database->getConnection();
        echo "✅ Connexion BDD réussie<br>";
    } else {
        echo "❌ Fichier database.php manquant<br>";
    }
} catch (Exception $e) {
    echo "❌ Erreur BDD: " . $e->getMessage() . "<br>";
}

// Test des sessions
echo "<h2>Test Sessions</h2>";
try {
    session_start();
    echo "✅ Sessions fonctionnelles<br>";
} catch (Exception $e) {
    echo "❌ Erreur sessions: " . $e->getMessage() . "<br>";
}

echo "<h2>Actions à effectuer</h2>";
echo "<ol>";
echo "<li>Configurez config/database.php avec vos identifiants Hostinger</li>";
echo "<li>Exécutez database_hostinger.sql dans phpMyAdmin</li>";
echo "<li>Créez les dossiers uploads/ et logs/ avec permissions 755</li>";
echo "<li>Testez avec test_connection.php</li>";
echo "</ol>";

echo "<p><a href='test_connection.php'>Lancer le test complet</a></p>";
echo "</body></html>";
?>