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
    header('Location: ' . ($user['role'] === 'admin' ? 'admin/dashboard.php' : 'client/dashboard.php'));
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitizeInput($_POST['firstName'] ?? '');
    $lastName = sanitizeInput($_POST['lastName'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Token de sécurité invalide.';
    } elseif (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Tous les champs sont obligatoires.';
    } elseif (!validateEmail($email)) {
        $error = 'Email invalide.';
    } elseif (!validatePassword($password)) {
        $error = 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Vérifier si l'email existe déjà
            $checkQuery = "SELECT id FROM users WHERE email = :email";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':email', $email);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                $error = 'Cette adresse email est déjà utilisée.';
            } else {
                // Créer le compte
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                $query = "INSERT INTO users (firstName, lastName, email, password) VALUES (:firstName, :lastName, :email, :password)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':firstName', $firstName);
                $stmt->bindParam(':lastName', $lastName);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashedPassword);
                
                if ($stmt->execute()) {
                    $success = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.';
                    
                    // Vider les champs après succès
                    $firstName = $lastName = $email = '';
                    
                    // Redirection vers login après 3 secondes
                    header("refresh:3;url=login.php");
                } else {
                    $error = 'Erreur lors de la création du compte.';
                }
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
    <title>Inscription - RemboursePRO</title>
    
    <!-- Bootstrap 5.3.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- CSS INTÉGRÉ -->
    <style>
        html, body {
            background: #0f172a !important;
            background-color: #0f172a !important;
            color: #ffffff !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
            min-height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .container {
            background: transparent !important;
            color: #ffffff !important;
        }

        .form-glass {
            background: rgba(15, 23, 42, 0.8) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 20px !important;
            padding: 3rem !important;
            backdrop-filter: blur(20px) !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3) !important;
            color: #ffffff !important;
            max-width: 600px !important;
            margin: 0 auto !important;
        }

        .navbar-brand {
            color: #ffffff !important;
            font-size: 2rem !important;
            font-weight: 700 !important;
            text-decoration: none !important;
        }

        .navbar-brand:hover {
            color: #60a5fa !important;
        }

        h1, h2, h3, h4, h5, h6 {
            color: #ffffff !important;
            font-weight: 600 !important;
        }

        p, span, div, label, small {
            color: #ffffff !important;
        }

        .text-white-50 {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        .form-control {
            background: rgba(15, 23, 42, 0.8) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: #ffffff !important;
            border-radius: 10px !important;
            padding: 12px 16px !important;
        }

        .form-control:focus {
            background: rgba(15, 23, 42, 0.9) !important;
            border-color: #3b82f6 !important;
            color: #ffffff !important;
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25) !important;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6) !important;
        }

        .form-label {
            color: #ffffff !important;
            font-weight: 500 !important;
            margin-bottom: 0.5rem !important;
        }

        .btn {
            border-radius: 10px !important;
            padding: 12px 24px !important;
            font-weight: 600 !important;
            transition: all 0.3s ease !important;
            border: none !important;
        }

        .btn-gradient {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4) !important;
        }

        .btn-gradient:hover {
            background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%) !important;
            color: #ffffff !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.6) !important;
        }

        .btn-glass {
            background: rgba(255, 255, 255, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: #ffffff !important;
            backdrop-filter: blur(10px) !important;
        }

        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.2) !important;
            color: #ffffff !important;
            transform: translateY(-2px) !important;
        }

        .alert {
            border-radius: 10px !important;
            border: none !important;
            padding: 1rem 1.5rem !important;
            margin-bottom: 1rem !important;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.2) !important;
            color: #ffffff !important;
            border: 1px solid rgba(34, 197, 94, 0.4) !important;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.2) !important;
            color: #ffffff !important;
            border: 1px solid rgba(239, 68, 68, 0.4) !important;
        }

        a {
            color: #60a5fa !important;
            text-decoration: none !important;
        }

        a:hover {
            color: #93c5fd !important;
        }

        .min-vh-100 {
            min-height: 100vh !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .row {
            background: transparent !important;
        }

        .col-lg-6, .col-md-8 {
            background: transparent !important;
        }

        @media (max-width: 768px) {
            .form-glass {
                padding: 2rem !important;
                margin: 1rem !important;
            }
        }
        
        /* Navbar mobile improvements */
        .navbar-toggler {
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            padding: 4px 8px !important;
        }
        
        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25) !important;
        }
        
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.8%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='m4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e") !important;
            width: 1.2em !important;
            height: 1.2em !important;
        }
        
        .navbar-collapse {
            background: rgba(15, 23, 42, 0.95) !important;
            border-radius: 8px !important;
            margin-top: 10px !important;
            padding: 15px !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }
        
        @media (max-width: 991.98px) {
            .navbar-nav {
                text-align: center !important;
            }
            
            .navbar-nav .nav-item {
                margin: 5px 0 !important;
            }
            
            .navbar-brand {
                font-size: 1.5rem !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-5">
            <div class="col-lg-6 col-md-8">
                <div class="form-glass">
                    <div class="text-center mb-4">
                        <a href="index.php" class="text-decoration-none">
                            <h2 class="navbar-brand mb-0">
                                <i class="bi bi-shield-check me-2"></i>RemboursePRO
                            </h2>
                        </a>
                        <h3 class="text-white mt-3">Inscription</h3>
                        <p class="text-white-50">Créez votre compte pour commencer</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="firstName" class="form-label">
                                    <i class="bi bi-person me-2"></i>Prénom
                                </label>
                                <input type="text" class="form-control" id="firstName" name="firstName" 
                                       placeholder="Votre prénom" required value="<?= htmlspecialchars($firstName ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="lastName" class="form-label">
                                    <i class="bi bi-person me-2"></i>Nom
                                </label>
                                <input type="text" class="form-control" id="lastName" name="lastName" 
                                       placeholder="Votre nom" required value="<?= htmlspecialchars($lastName ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope me-2"></i>Adresse email
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="votre@email.com" required value="<?= htmlspecialchars($email ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock me-2"></i>Mot de passe
                            </label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="8 caractères min, 1 majuscule, 1 chiffre" required minlength="8">
                            <small class="text-white-50">Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.</small>
                        </div>
                        
                        <div class="mb-4">
                            <label for="confirmPassword" class="form-label">
                                <i class="bi bi-lock-fill me-2"></i>Confirmer le mot de passe
                            </label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" 
                                   placeholder="Confirmez votre mot de passe" required minlength="8">
                        </div>
                        
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-gradient">
                                <i class="bi bi-person-plus me-2"></i>Créer mon compte
                            </button>
                        </div>
                        
                        <div class="text-center">
                            <p class="text-white-50 mb-0">
                                Déjà un compte ?
                                <a href="login.php" class="text-white fw-bold">
                                    Se connecter
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
    
    <script>
        // Validation en temps réel du mot de passe
        document.getElementById('confirmPassword').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Les mots de passe ne correspondent pas');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>