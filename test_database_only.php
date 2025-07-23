<?php
// Test spécifique pour la base de données Hostinger
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Connexion Base de Données Hostinger</h1>";

// Paramètres de connexion
$host = 'localhost';
$db_name = 'u634930929_ktloee';
$username = 'u634930929_ktloee';
$password = 'Ino1234@';

echo "<h2>Paramètres de connexion :</h2>";
echo "Host: $host<br>";
echo "Database: $db_name<br>";
echo "Username: $username<br>";
echo "Password: " . str_repeat('*', strlen($password)) . "<br><br>";

// Test 1: Connexion simple
echo "<h2>Test 1: Connexion de base</h2>";
try {
    $dsn = "mysql:host=$host;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connexion au serveur MySQL réussie<br>";
    
    // Test 2: Vérifier si la base existe
    echo "<h2>Test 2: Vérification de la base de données</h2>";
    $stmt = $pdo->query("SHOW DATABASES LIKE '$db_name'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Base de données '$db_name' trouvée<br>";
        
        // Test 3: Connexion à la base spécifique
        echo "<h2>Test 3: Connexion à la base spécifique</h2>";
        $dsn_with_db = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";
        $pdo_db = new PDO($dsn_with_db, $username, $password);
        $pdo_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✅ Connexion à la base '$db_name' réussie<br>";
        
        // Test 4: Lister les tables
        echo "<h2>Test 4: Tables existantes</h2>";
        $stmt = $pdo_db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (count($tables) > 0) {
            echo "✅ Tables trouvées: " . implode(', ', $tables) . "<br>";
        } else {
            echo "⚠️ Aucune table trouvée - vous devez exécuter database_hostinger.sql<br>";
        }
        
    } else {
        echo "❌ Base de données '$db_name' non trouvée<br>";
        echo "<strong>Action requise:</strong> Créez la base de données dans votre panneau Hostinger<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion: " . $e->getMessage() . "<br>";
    echo "<br><strong>Code d'erreur:</strong> " . $e->getCode() . "<br>";
    
    // Messages d'aide selon le code d'erreur
    switch ($e->getCode()) {
        case 1045:
            echo "<strong>Problème:</strong> Nom d'utilisateur ou mot de passe incorrect<br>";
            echo "<strong>Solution:</strong> Vérifiez vos identifiants dans le panneau Hostinger<br>";
            break;
        case 1049:
            echo "<strong>Problème:</strong> Base de données inexistante<br>";
            echo "<strong>Solution:</strong> Créez la base de données dans votre panneau Hostinger<br>";
            break;
        case 2002:
            echo "<strong>Problème:</strong> Serveur MySQL inaccessible<br>";
            echo "<strong>Solution:</strong> Vérifiez l'adresse du serveur (peut être différente de 'localhost')<br>";
            break;
        default:
            echo "<strong>Problème:</strong> Erreur inconnue<br>";
            echo "<strong>Solution:</strong> Contactez le support Hostinger<br>";
    }
}

echo "<h2>Actions recommandées :</h2>";
echo "<ol>";
echo "<li>Si la connexion échoue, vérifiez vos identifiants dans le panneau Hostinger</li>";
echo "<li>Si la base n'existe pas, créez-la dans 'Bases de données MySQL'</li>";
echo "<li>Si tout fonctionne mais pas de tables, exécutez database_hostinger.sql dans phpMyAdmin</li>";
echo "<li>Une fois résolu, supprimez ce fichier de test</li>";
echo "</ol>";
?>