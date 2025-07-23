<?php
// Page de test pour diagnostiquer les problèmes
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test de Configuration RemboursePRO</h1>";

// Test 1: Version PHP
echo "<h2>1. Version PHP</h2>";
echo "Version PHP: " . phpversion() . "<br>";
if (version_compare(phpversion(), '7.4.0', '>=')) {
    echo "✅ Version PHP compatible<br>";
} else {
    echo "❌ Version PHP trop ancienne (7.4+ requis)<br>";
}

// Test 2: Extensions PHP
echo "<h2>2. Extensions PHP</h2>";
$required_extensions = ['pdo', 'pdo_mysql', 'session', 'json'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ Extension $ext: Disponible<br>";
    } else {
        echo "❌ Extension $ext: Manquante<br>";
    }
}

// Test 3: Fichiers de configuration
echo "<h2>3. Fichiers de Configuration</h2>";
$config_files = [
    'config/database.php',
    'config/auth.php',
    'assets/css/style.css'
];

foreach ($config_files as $file) {
    if (file_exists($file)) {
        echo "✅ Fichier $file: Trouvé<br>";
    } else {
        echo "❌ Fichier $file: Manquant<br>";
    }
}

// Test 4: Connexion à la base de données
echo "<h2>4. Test de Connexion Base de Données</h2>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "✅ Connexion à la base de données: Réussie<br>";
        
        // Test des tables
        $tables = ['users', 'reimbursements', 'site_settings'];
        foreach ($tables as $table) {
            try {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM $table");
                $stmt->execute();
                echo "✅ Table $table: Existe<br>";
            } catch (Exception $e) {
                echo "❌ Table $table: Manquante ou erreur<br>";
            }
        }
    } else {
        echo "❌ Connexion à la base de données: Échec<br>";
    }
} catch (Exception $e) {
    echo "❌ Erreur de connexion: " . $e->getMessage() . "<br>";
}

// Test 5: Permissions des dossiers
echo "<h2>5. Permissions des Dossiers</h2>";
$directories = ['uploads', 'uploads/profiles', 'logs'];
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "✅ Dossier $dir: Créé<br>";
        } else {
            echo "❌ Dossier $dir: Impossible à créer<br>";
        }
    } else {
        echo "✅ Dossier $dir: Existe<br>";
    }
    
    if (is_writable($dir)) {
        echo "✅ Dossier $dir: Écriture autorisée<br>";
    } else {
        echo "❌ Dossier $dir: Écriture refusée<br>";
    }
}

// Test 6: Sessions
echo "<h2>6. Test des Sessions</h2>";
if (session_start()) {
    echo "✅ Sessions: Fonctionnelles<br>";
    $_SESSION['test'] = 'ok';
    if (isset($_SESSION['test'])) {
        echo "✅ Écriture session: OK<br>";
        unset($_SESSION['test']);
    }
} else {
    echo "❌ Sessions: Problème<br>";
}

echo "<h2>Configuration Recommandée pour Hostinger</h2>";
echo "<pre>";
echo "Dans config/database.php, modifiez :\n";
echo "private \$host = 'localhost';\n";
echo "private \$db_name = 'votre_nom_de_bdd'; // Nom de votre BDD Hostinger\n";
echo "private \$username = 'votre_utilisateur'; // Utilisateur BDD Hostinger\n";
echo "private \$password = 'votre_mot_de_passe'; // Mot de passe BDD Hostinger\n";
echo "</pre>";

echo "<p><strong>Si tous les tests sont verts, supprimez ce fichier test_connection.php pour la sécurité.</strong></p>";
?>