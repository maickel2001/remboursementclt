<?php
// Test ultra simple pour diagnostiquer Hostinger
echo "<h1>Test Simple RemboursePRO</h1>";

// Test 1: PHP fonctionne
echo "<h2>1. PHP fonctionne ✅</h2>";
echo "Version PHP: " . phpversion() . "<br>";

// Test 2: Fichiers présents
echo "<h2>2. Test des fichiers</h2>";
$files = ['config/database.php', 'config/auth.php', 'assets/css/style.css'];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file existe<br>";
    } else {
        echo "❌ $file manquant<br>";
    }
}

// Test 3: Base de données
echo "<h2>3. Test Base de Données</h2>";
try {
    $host = 'localhost';
    $db_name = 'u634930929_ktloee';
    $username = 'u634930929_ktloee';
    $password = 'Ino1234@';
    
    echo "Tentative de connexion avec :<br>";
    echo "Host: $host<br>";
    echo "Database: $db_name<br>";
    echo "Username: $username<br>";
    echo "Password: " . str_repeat('*', strlen($password)) . "<br><br>";
    
    $dsn = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connexion BDD réussie<br>";
    
    // Test de base sans tables spécifiques
    try {
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "✅ Tables trouvées: " . implode(', ', $tables) . "<br>";
    } catch (Exception $e) {
        echo "⚠️ Aucune table trouvée (normal si première installation)<br>";
    }
    
    // Test des tables
    $tables = ['users', 'reimbursements', 'site_settings'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "✅ Table $table: $count enregistrement(s)<br>";
        } catch (Exception $e) {
            echo "❌ Table $table: Manquante (exécutez database_hostinger.sql)<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erreur BDD détaillée: " . $e->getMessage() . "<br>";
    echo "<br><strong>Solutions possibles:</strong><br>";
    echo "1. Vérifiez que la base de données 'u634930929_ktloee' existe dans votre panneau Hostinger<br>";
    echo "2. Vérifiez que l'utilisateur 'u634930929_ktloee' a les permissions sur cette base<br>";
    echo "3. Vérifiez que le mot de passe 'Ino1234@' est correct<br>";
    echo "4. Contactez le support Hostinger si le problème persiste<br>";
}

// Test 4: Sessions
echo "<h2>4. Test Sessions</h2>";
try {
    if (session_start()) {
        echo "✅ Sessions fonctionnelles<br>";
        $_SESSION['test'] = 'ok';
        if (isset($_SESSION['test'])) {
            echo "✅ Écriture session OK<br>";
            unset($_SESSION['test']);
        }
    }
} catch (Exception $e) {
    echo "❌ Erreur sessions: " . $e->getMessage() . "<br>";
}

echo "<h2>5. Actions à effectuer</h2>";
echo "<ol>";
echo "<li>Si la BDD échoue, exécutez database_hostinger.sql dans phpMyAdmin</li>";
echo "<li>Créez les dossiers uploads/ et logs/ avec permissions 755</li>";
echo "<li>Si tout est vert, testez <a href='index.php'>index.php</a></li>";
echo "</ol>";

echo "<p><strong>Une fois que tout fonctionne, supprimez ce fichier simple_test.php</strong></p>";
?>