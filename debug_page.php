<?php
// Page de debug pour identifier les problèmes
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html><head><title>Debug Page</title>";
echo "<style>
body { 
    font-family: Arial; 
    margin: 20px; 
    background: #0f172a !important; 
    color: #ffffff !important; 
}
.ok { color: #4ade80; font-weight: bold; }
.error { color: #f87171; font-weight: bold; }
.warning { color: #fbbf24; font-weight: bold; }
.container { 
    background: rgba(255,255,255,0.1); 
    padding: 20px; 
    border-radius: 15px; 
    backdrop-filter: blur(20px); 
}
</style>";
echo "</head><body>";

echo "<div class='container'>";
echo "<h1>🔍 Debug RemboursePRO</h1>";

// Test 1: Vérifier les erreurs PHP
echo "<h2>1. Erreurs PHP</h2>";
if (error_get_last()) {
    $error = error_get_last();
    echo "<span class='error'>❌ Dernière erreur PHP: " . $error['message'] . " dans " . $error['file'] . " ligne " . $error['line'] . "</span><br>";
} else {
    echo "<span class='ok'>✅ Aucune erreur PHP détectée</span><br>";
}

// Test 2: Session
echo "<h2>2. Test Session</h2>";
session_start();
if (isset($_SESSION['user_id'])) {
    echo "<span class='ok'>✅ Session active - User ID: " . $_SESSION['user_id'] . "</span><br>";
} else {
    echo "<span class='warning'>⚠️ Aucune session active</span><br>";
}

// Test 3: Base de données
echo "<h2>3. Test Base de Données</h2>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "<span class='ok'>✅ Connexion BDD réussie</span><br>";
        
        // Test des utilisateurs
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users");
        $stmt->execute();
        $result = $stmt->fetch();
        echo "<span class='ok'>✅ Utilisateurs dans la BDD: " . $result['count'] . "</span><br>";
        
    } else {
        echo "<span class='error'>❌ Connexion BDD échouée</span><br>";
    }
} catch (Exception $e) {
    echo "<span class='error'>❌ Erreur BDD: " . $e->getMessage() . "</span><br>";
}

// Test 4: Fichiers CSS
echo "<h2>4. Test CSS</h2>";
if (file_exists('assets/css/style.css')) {
    $cssSize = filesize('assets/css/style.css');
    echo "<span class='ok'>✅ CSS trouvé - Taille: " . $cssSize . " bytes</span><br>";
} else {
    echo "<span class='error'>❌ CSS manquant</span><br>";
}

// Test 5: Permissions
echo "<h2>5. Test Permissions</h2>";
$dirs = ['uploads', 'uploads/profiles', 'logs'];
foreach ($dirs as $dir) {
    if (is_dir($dir) && is_writable($dir)) {
        echo "<span class='ok'>✅ $dir: OK</span><br>";
    } else {
        echo "<span class='error'>❌ $dir: Problème</span><br>";
    }
}

echo "<h2>6. Actions Recommandées</h2>";
echo "<ul>";
echo "<li>Si erreurs PHP: Corrigez les variables non définies</li>";
echo "<li>Si CSS ne charge pas: Vérifiez le chemin assets/css/style.css</li>";
echo "<li>Si BDD échoue: Vérifiez config/database.php</li>";
echo "<li>Testez la connexion avec les comptes: client@test.com / client123</li>";
echo "</ul>";

echo "</div>";
echo "</body></html>";
?>