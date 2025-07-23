<?php
// Test spécifique des mots de passe
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test des Mots de Passe RemboursePRO</h1>";

// Test 1: Génération de nouveaux hashs
echo "<h2>1. Génération de nouveaux hashs</h2>";
$passwords = ['admin123', 'client123'];

foreach ($passwords as $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "<strong>$password</strong> : $hash<br>";
    
    // Vérification immédiate
    if (password_verify($password, $hash)) {
        echo "✅ Vérification OK<br><br>";
    } else {
        echo "❌ Vérification ÉCHEC<br><br>";
    }
}

// Test 2: Test avec les hashs de la base
echo "<h2>2. Test avec les hashs actuels de la base</h2>";
$currentHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

foreach ($passwords as $password) {
    if (password_verify($password, $currentHash)) {
        echo "✅ '$password' fonctionne avec le hash actuel<br>";
    } else {
        echo "❌ '$password' ne fonctionne PAS avec le hash actuel<br>";
    }
}

// Test 3: Connexion à la base pour vérifier
echo "<h2>3. Test de connexion base de données</h2>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "✅ Connexion BDD OK<br>";
        
        // Vérifier les comptes
        $query = "SELECT email, password FROM users WHERE email IN ('admin@remboursepro.com', 'client@test.com')";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        echo "<h3>Comptes trouvés dans la base :</h3>";
        foreach ($users as $user) {
            echo "Email: {$user['email']}<br>";
            echo "Hash: {$user['password']}<br>";
            
            // Test des mots de passe
            if (password_verify('admin123', $user['password'])) {
                echo "✅ 'admin123' fonctionne<br>";
            }
            if (password_verify('client123', $user['password'])) {
                echo "✅ 'client123' fonctionne<br>";
            }
            echo "<br>";
        }
        
    } else {
        echo "❌ Connexion BDD ÉCHEC<br>";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
}

echo "<h2>4. Solution recommandée</h2>";
echo "<p>Si les tests échouent, exécutez cette requête SQL dans phpMyAdmin :</p>";
echo "<pre>";
echo "UPDATE users SET password = '" . password_hash('admin123', PASSWORD_DEFAULT) . "' WHERE email = 'admin@remboursepro.com';\n";
echo "UPDATE users SET password = '" . password_hash('client123', PASSWORD_DEFAULT) . "' WHERE email = 'client@test.com';";
echo "</pre>";
?>