<?php
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
    
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
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
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="firstName" class="form-label text-white">
                                    <i class="bi bi-person me-2"></i>Prénom
                                </label>
                                <input type="text" class="form-control form-control-glass" id="firstName" name="firstName" 
                                       placeholder="Votre prénom" required value="<?= htmlspecialchars($firstName ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="lastName" class="form-label text-white">
                                    <i class="bi bi-person me-2"></i>Nom
                                </label>
                                <input type="text" class="form-control form-control-glass" id="lastName" name="lastName" 
                                       placeholder="Votre nom" required value="<?= htmlspecialchars($lastName ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label text-white">
                                <i class="bi bi-envelope me-2"></i>Adresse email
                            </label>
                            <input type="email" class="form-control form-control-glass" id="email" name="email" 
                                   placeholder="votre@email.com" required value="<?= htmlspecialchars($email ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label text-white">
                                <i class="bi bi-lock me-2"></i>Mot de passe
                            </label>
                            <input type="password" class="form-control form-control-glass" id="password" name="password" 
                                   placeholder="8 caractères min, 1 majuscule, 1 chiffre" required minlength="8">
                            <small class="text-white-50">Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.</small>
                        </div>
                        
                        <div class="mb-4">
                            <label for="confirmPassword" class="form-label text-white">
                                <i class="bi bi-lock-fill me-2"></i>Confirmer le mot de passe
                            </label>
                            <input type="password" class="form-control form-control-glass" id="confirmPassword" name="confirmPassword" 
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
                                <a href="login.php" class="text-white text-decoration-none fw-bold">
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