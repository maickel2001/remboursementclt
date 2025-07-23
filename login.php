<?php
// Gestion des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifier les fichiers requis
if (!file_exists(__DIR__ . '/config/auth.php')) {
    die('Erreur: Fichier config/auth.php manquant');
}
if (!file_exists(__DIR__ . '/config/database.php')) {
    die('Erreur: Fichier config/database.php manquant');
}

require_once 'config/auth.php';
require_once 'config/database.php';

// Redirection si déjà connecté
if (isLoggedIn()) {
    $user = getCurrentUser();
    header('Location: ' . ($user['role'] === 'admin' ? '/admin/dashboard.php' : '/client/dashboard.php'));
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Token de sécurité invalide.';
    } elseif (empty($email) || empty($password)) {
        $error = 'Tous les champs sont obligatoires.';
    } elseif (!validateEmail($email)) {
        $error = 'Email invalide.';
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "SELECT id, firstName, lastName, email, password, role FROM users WHERE email = :email";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch();
                
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_name'] = $user['firstName'] . ' ' . $user['lastName'];
                    $_SESSION['login_time'] = time();
                    
                    // Régénérer l'ID de session pour la sécurité
                    session_regenerate_id(true);
                    
                    $success = 'Connexion réussie ! Redirection...';
                    logLoginAttempt($email, true, $_SERVER['REMOTE_ADDR']);
                    
                    // Redirection après 2 secondes
                    $redirectUrl = $user['role'] === 'admin' ? '/admin/dashboard.php' : '/client/dashboard.php';
                    header("refresh:2;url=$redirectUrl");
                } else {
                    logLoginAttempt($email, false, $_SERVER['REMOTE_ADDR']);
                    $error = 'Email ou mot de passe incorrect.';
                }
            } else {
                logLoginAttempt($email, false, $_SERVER['REMOTE_ADDR']);
                $error = 'Email ou mot de passe incorrect.';
            }
        } catch (Exception $e) {
            $error = 'Erreur de connexion à la base de données.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - RemboursePRO</title>
    
    <!-- Bootstrap 5.3.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-lg-5 col-md-7">
                <div class="form-glass">
                    <div class="text-center mb-4">
                        <a href="index.php" class="text-decoration-none">
                            <h2 class="navbar-brand mb-0">
                                <i class="bi bi-shield-check me-2"></i>RemboursePRO
                            </h2>
                        </a>
                        <h3 class="text-white mt-3">Connexion</h3>
                        <p class="text-white-50">Accédez à votre espace personnel</p>
                    </div>
                    
                    <!-- Comptes de test -->
                    <div class="alert alert-info alert-glass mb-4" role="alert">
                        <h6 class="mb-2"><i class="bi bi-info-circle me-2"></i>Comptes de test disponibles :</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>👤 Client :</strong><br>
                                <small>Email: client@test.com</small><br>
                                <small>Mot de passe: client123</small>
                            </div>
                            <div class="col-md-6">
                                <strong>🔧 Admin :</strong><br>
                                <small>Email: admin@remboursepro.com</small><br>
                                <small>Mot de passe: admin123</small>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-glass" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-glass" role="alert">
                            <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                        
                        <div class="mb-3">
                            <label for="email" class="form-label text-white">
                                <i class="bi bi-envelope me-2"></i>Adresse email
                            </label>
                            <input type="email" class="form-control form-control-glass" id="email" name="email" 
                                   placeholder="votre@email.com" required value="<?= htmlspecialchars($email ?? '') ?>">
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label text-white">
                                <i class="bi bi-lock me-2"></i>Mot de passe
                            </label>
                            <input type="password" class="form-control form-control-glass" id="password" name="password" 
                                   placeholder="Votre mot de passe" required>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-gradient">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                            </button>
                        </div>
                        
                        <div class="text-center">
                            <p class="text-white-50 mb-0">
                                Pas encore de compte ?
                                <a href="register.php" class="text-white text-decoration-none fw-bold">
                                    S'inscrire
                                </a>
                            </p>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <a href="index.php" class="btn btn-glass">
                            <i class="bi bi-arrow-left me-2"></i>Retour à l'accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>