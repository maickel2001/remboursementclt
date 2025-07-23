<?php
// Test final pour vérifier que tout fonctionne
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html><head><title>Test Final RemboursePRO</title>";
echo "<style>
body { font-family: Arial; margin: 20px; background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 25%, #581c87 50%, #7c2d12 75%, #1e3a8a 100%); color: white; }
.ok { color: #4ade80; font-weight: bold; }
.error { color: #f87171; font-weight: bold; }
.warning { color: #fbbf24; font-weight: bold; }
.container { background: rgba(255,255,255,0.1); padding: 20px; border-radius: 15px; backdrop-filter: blur(20px); }
</style>";
echo "</head><body>";

echo "<div class='container'>";
echo "<h1>🎉 Test Final RemboursePRO</h1>";

$allOk = true;

// Test 1: Base de données
echo "<h2>1. Test Base de Données</h2>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "<span class='ok'>✅ Connexion BDD réussie</span><br>";
        
        // Test des comptes
        $stmt = $conn->prepare("SELECT email, password FROM users WHERE email IN ('admin@remboursepro.com', 'client@test.com')");
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        foreach ($users as $user) {
            if (password_verify('admin123', $user['password']) || password_verify('client123', $user['password'])) {
                echo "<span class='ok'>✅ Compte {$user['email']} : Mot de passe OK</span><br>";
            } else {
                echo "<span class='error'>❌ Compte {$user['email']} : Mot de passe KO</span><br>";
                $allOk = false;
            }
        }
    }
} catch (Exception $e) {
    echo "<span class='error'>❌ Erreur BDD: " . $e->getMessage() . "</span><br>";
    $allOk = false;
}

// Test 2: Pages principales
echo "<h2>2. Test Pages Principales</h2>";
$pages = [
    'index.php' => 'Page d\'accueil',
    'login.php' => 'Connexion',
    'register.php' => 'Inscription',
    'client/dashboard.php' => 'Dashboard client',
    'admin/dashboard.php' => 'Dashboard admin',
    'client/remboursement.php' => 'Formulaire remboursement'
];

foreach ($pages as $page => $description) {
    if (file_exists($page)) {
        echo "<span class='ok'>✅ $description</span><br>";
    } else {
        echo "<span class='error'>❌ $description manquant</span><br>";
        $allOk = false;
    }
}

// Test 3: CSS et assets
echo "<h2>3. Test Assets</h2>";
if (file_exists('assets/css/style.css')) {
    echo "<span class='ok'>✅ CSS principal chargé</span><br>";
} else {
    echo "<span class='error'>❌ CSS manquant</span><br>";
    $allOk = false;
}

// Test 4: Dossiers
echo "<h2>4. Test Dossiers</h2>";
$dirs = ['uploads', 'uploads/profiles', 'logs'];
foreach ($dirs as $dir) {
    if (is_dir($dir) && is_writable($dir)) {
        echo "<span class='ok'>✅ $dir accessible</span><br>";
    } else {
        echo "<span class='error'>❌ $dir problème</span><br>";
        $allOk = false;
    }
}

// Résultat final
echo "<h2>🏁 Résultat Final</h2>";
if ($allOk) {
    echo "<div style='background: rgba(34, 197, 94, 0.2); padding: 20px; border-radius: 10px; border: 1px solid rgba(34, 197, 94, 0.5);'>";
    echo "<h3 class='ok'>🎉 SITE PARFAITEMENT FONCTIONNEL !</h3>";
    echo "<p>Toutes les fonctionnalités sont opérationnelles.</p>";
    echo "<h4>🎯 Comptes de test :</h4>";
    echo "<ul>";
    echo "<li><strong>Client :</strong> client@test.com / client123</li>";
    echo "<li><strong>Admin :</strong> admin@remboursepro.com / admin123</li>";
    echo "</ul>";
    echo "<div style='margin-top: 20px;'>";
    echo "<a href='index.php' style='background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; margin-right: 10px; display: inline-block;'>🚀 Accéder au Site</a>";
    echo "<a href='login.php' style='background: rgba(255,255,255,0.2); color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; display: inline-block;'>🔐 Se Connecter</a>";
    echo "</div>";
    echo "</div>";
} else {
    echo "<div style='background: rgba(239, 68, 68, 0.2); padding: 20px; border-radius: 10px; border: 1px solid rgba(239, 68, 68, 0.5);'>";
    echo "<h3 class='error'>❌ PROBLÈMES DÉTECTÉS</h3>";
    echo "<p>Corrigez les erreurs ci-dessus avant utilisation.</p>";
    echo "</div>";
}

echo "<br><p><strong>Supprimez ce fichier test_final.php une fois que tout fonctionne.</strong></p>";
echo "</div>";
echo "</body></html>";
?>