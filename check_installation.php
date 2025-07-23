<?php
// Vérification complète de l'installation RemboursePRO
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html><head><title>Vérification Installation RemboursePRO</title>";
echo "<style>body{font-family:Arial;margin:20px;} .ok{color:green;} .error{color:red;} .warning{color:orange;}</style>";
echo "</head><body>";
echo "<h1>🔍 Vérification Installation RemboursePRO</h1>";

$allOk = true;

// Test 1: Dossiers
echo "<h2>1. Vérification des Dossiers</h2>";
$directories = ['uploads', 'uploads/profiles', 'logs'];
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "<span class='ok'>✅ $dir : Existe et accessible en écriture</span><br>";
        } else {
            echo "<span class='warning'>⚠️ $dir : Existe mais pas d'écriture (chmod 755 requis)</span><br>";
            $allOk = false;
        }
    } else {
        echo "<span class='error'>❌ $dir : Manquant</span><br>";
        $allOk = false;
    }
}

// Test 2: Fichiers de configuration
echo "<h2>2. Fichiers de Configuration</h2>";
$configFiles = [
    'config/database.php' => 'Configuration base de données',
    'config/auth.php' => 'Système d\'authentification',
    'assets/css/style.css' => 'Styles CSS'
];

foreach ($configFiles as $file => $description) {
    if (file_exists($file)) {
        echo "<span class='ok'>✅ $file : $description OK</span><br>";
    } else {
        echo "<span class='error'>❌ $file : $description MANQUANT</span><br>";
        $allOk = false;
    }
}

// Test 3: Base de données
echo "<h2>3. Test Base de Données</h2>";
try {
    if (file_exists('config/database.php')) {
        require_once 'config/database.php';
        $database = new Database();
        $conn = $database->getConnection();
        
        if ($conn) {
            echo "<span class='ok'>✅ Connexion base de données : RÉUSSIE</span><br>";
            
            // Test des tables
            $tables = ['users', 'reimbursements', 'site_settings'];
            foreach ($tables as $table) {
                try {
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM $table");
                    $stmt->execute();
                    $count = $stmt->fetchColumn();
                    echo "<span class='ok'>✅ Table $table : $count enregistrement(s)</span><br>";
                } catch (Exception $e) {
                    echo "<span class='error'>❌ Table $table : MANQUANTE (exécutez database_complete.sql)</span><br>";
                    $allOk = false;
                }
            }
        } else {
            echo "<span class='error'>❌ Connexion base de données : ÉCHEC</span><br>";
            $allOk = false;
        }
    } else {
        echo "<span class='error'>❌ Fichier database.php manquant</span><br>";
        $allOk = false;
    }
} catch (Exception $e) {
    echo "<span class='error'>❌ Erreur BDD : " . $e->getMessage() . "</span><br>";
    $allOk = false;
    
    // Test des mots de passe
    echo "<h3>Test des Mots de Passe</h3>";
    require_once 'config/auth.php';
    echo testPasswords() . "<br>";
}

// Test 4: Sessions
echo "<h2>4. Test Sessions</h2>";
try {
    if (session_start()) {
        echo "<span class='ok'>✅ Sessions : Fonctionnelles</span><br>";
        $_SESSION['test'] = 'ok';
        if (isset($_SESSION['test'])) {
            echo "<span class='ok'>✅ Écriture session : OK</span><br>";
            unset($_SESSION['test']);
        }
    }
} catch (Exception $e) {
    echo "<span class='error'>❌ Erreur sessions : " . $e->getMessage() . "</span><br>";
    $allOk = false;
}

// Test 5: Pages principales
echo "<h2>5. Test Pages Principales</h2>";
$pages = [
    'index.php' => 'Page d\'accueil',
    'login.php' => 'Page de connexion',
    'register.php' => 'Page d\'inscription'
];

foreach ($pages as $page => $description) {
    if (file_exists($page)) {
        echo "<span class='ok'>✅ $page : $description OK</span><br>";
    } else {
        echo "<span class='error'>❌ $page : $description MANQUANT</span><br>";
        $allOk = false;
    }
}

// Résultat final
echo "<h2>📋 Résultat Final</h2>";
if ($allOk) {
    echo "<div style='background:#d4edda;padding:15px;border-radius:5px;'>";
    echo "<span class='ok'><strong>🎉 INSTALLATION RÉUSSIE !</strong></span><br>";
    echo "Votre plateforme RemboursePRO est prête à être utilisée.<br><br>";
    echo "<strong>Comptes de test :</strong><br>";
    echo "• Client : client@test.com / client123<br>";
    echo "• Admin : admin@remboursepro.com / admin123<br><br>";
    echo "<a href='index.php' style='background:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>🚀 Accéder à la plateforme</a>";
    echo "</div>";
} else {
    echo "<div style='background:#f8d7da;padding:15px;border-radius:5px;'>";
    echo "<span class='error'><strong>❌ PROBLÈMES DÉTECTÉS</strong></span><br>";
    echo "Corrigez les erreurs ci-dessus avant d'utiliser la plateforme.<br><br>";
    echo "<strong>Actions recommandées :</strong><br>";
    echo "1. Exécutez database_complete.sql dans phpMyAdmin<br>";
    echo "2. Vérifiez les permissions des dossiers (chmod 755)<br>";
    echo "3. Relancez ce test<br>";
    echo "</div>";
}

echo "<br><p><strong>Une fois que tout fonctionne, supprimez ce fichier check_installation.php pour la sécurité.</strong></p>";
echo "</body></html>";
?>