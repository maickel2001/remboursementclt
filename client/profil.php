<?php
require_once '../config/auth.php';
require_once '../config/database.php';

checkClient();
$currentUser = getCurrentUser();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitizeInput($_POST['firstName'] ?? '');
    $lastName = sanitizeInput($_POST['lastName'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Token de sécurité invalide.';
    } elseif (empty($firstName) || empty($lastName) || empty($email)) {
        $error = 'Les champs prénom, nom et email sont obligatoires.';
    } elseif (!validateEmail($email)) {
        $error = 'Email invalide.';
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Vérifier si l'email est déjà utilisé par un autre utilisateur
            $checkQuery = "SELECT id FROM users WHERE email = :email AND id != :user_id";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':email', $email);
            $checkStmt->bindParam(':user_id', $currentUser['id']);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                $error = 'Cette adresse email est déjà utilisée par un autre utilisateur.';
            } else {
                // Gestion de l'upload de photo
                $profilePicture = $currentUser['profile_picture'];
                
                if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../uploads/profiles/';
                    
                    // Créer le dossier s'il n'existe pas
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    $fileType = $_FILES['profile_picture']['type'];
                    $fileSize = $_FILES['profile_picture']['size'];
                    
                    if (!in_array($fileType, $allowedTypes)) {
                        $error = 'Type de fichier non autorisé. Utilisez JPG, PNG ou GIF.';
                    } elseif ($fileSize > 5 * 1024 * 1024) { // 5MB max
                        $error = 'Le fichier est trop volumineux (5MB maximum).';
                    } else {
                        $extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
                        $newFileName = 'user_' . $currentUser['id'] . '_' . time() . '.' . $extension;
                        $uploadPath = $uploadDir . $newFileName;
                        
                        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadPath)) {
                            // Supprimer l'ancienne photo si elle existe
                            if ($profilePicture && file_exists('../uploads/profiles/' . $profilePicture)) {
                                unlink('../uploads/profiles/' . $profilePicture);
                            }
                            $profilePicture = $newFileName;
                        } else {
                            $error = 'Erreur lors de l\'upload de la photo.';
                        }
                    }
                }
                
                if (!$error) {
                    // Mettre à jour les informations
                    $query = "UPDATE users SET firstName = :firstName, lastName = :lastName, email = :email, 
                             phone = :phone, address = :address, profile_picture = :profile_picture 
                             WHERE id = :user_id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':firstName', $firstName);
                    $stmt->bindParam(':lastName', $lastName);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':phone', $phone);
                    $stmt->bindParam(':address', $address);
                    $stmt->bindParam(':profile_picture', $profilePicture);
                    $stmt->bindParam(':user_id', $currentUser['id']);
                    
                    if ($stmt->execute()) {
                        $success = 'Profil mis à jour avec succès !';
                        
                        // Recharger les données utilisateur
                        $currentUser = getCurrentUser();
                    } else {
                        $error = 'Erreur lors de la mise à jour du profil.';
                    }
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
    <title>Mon Profil - RemboursePRO</title>
    
    <!-- Bootstrap 5.3.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-glass">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">
                <i class="bi bi-shield-check me-2"></i>RemboursePRO
            </a>
            
            <div class="d-flex align-items-center">
                <span class="text-white me-3">
                    <i class="bi bi-person-circle me-2"></i><?= htmlspecialchars($currentUser['firstName'] . ' ' . $currentUser['lastName']) ?>
                </span>
                <a href="../logout.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block dashboard-sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="sidebar-item" href="dashboard.php">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="sidebar-item active" href="profil.php">
                                <i class="bi bi-person me-2"></i>Mon Profil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="sidebar-item" href="remboursement.php">
                                <i class="bi bi-credit-card me-2"></i>Nouveau Remboursement
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="sidebar-item" href="historique.php">
                                <i class="bi bi-clock-history me-2"></i>Historique
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="sidebar-item" href="calculateur.php">
                                <i class="bi bi-calculator me-2"></i>Calculateur
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="py-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="text-white">
                            <i class="bi bi-person me-3"></i>Mon Profil
                        </h1>
                        <a href="dashboard.php" class="btn btn-glass">
                            <i class="bi bi-arrow-left me-2"></i>Retour
                        </a>
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

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="form-glass">
                                <h3 class="text-white mb-4">
                                    <i class="bi bi-person-gear me-2"></i>Informations Personnelles
                                </h3>
                                
                                <form method="POST" action="" enctype="multipart/form-data">
                                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                    
                                    <!-- Photo de profil -->
                                    <div class="text-center mb-4">
                                        <div class="profile-pic-container">
                                            <?php if ($currentUser['profile_picture']): ?>
                                                <img src="../uploads/profiles/<?= htmlspecialchars($currentUser['profile_picture']) ?>" 
                                                     alt="Photo de profil" class="profile-pic" id="profilePreview">
                                            <?php else: ?>
                                                <div class="profile-pic d-flex align-items-center justify-content-center bg-secondary" id="profilePreview">
                                                    <i class="bi bi-person" style="font-size: 4rem; color: white;"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="profile-pic-overlay" onclick="document.getElementById('profilePictureInput').click()">
                                                <i class="bi bi-camera text-white" style="font-size: 2rem;"></i>
                                            </div>
                                        </div>
                                        <input type="file" id="profilePictureInput" name="profile_picture" 
                                               accept="image/jpeg,image/png,image/gif" style="display: none;">
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="firstName" class="form-label text-white">
                                                <i class="bi bi-person me-2"></i>Prénom *
                                            </label>
                                            <input type="text" class="form-control form-control-glass" id="firstName" 
                                                   name="firstName" required value="<?= htmlspecialchars($currentUser['firstName']) ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="lastName" class="form-label text-white">
                                                <i class="bi bi-person me-2"></i>Nom *
                                            </label>
                                            <input type="text" class="form-control form-control-glass" id="lastName" 
                                                   name="lastName" required value="<?= htmlspecialchars($currentUser['lastName']) ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label text-white">
                                            <i class="bi bi-envelope me-2"></i>Adresse email *
                                        </label>
                                        <input type="email" class="form-control form-control-glass" id="email" 
                                               name="email" required value="<?= htmlspecialchars($currentUser['email']) ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="phone" class="form-label text-white">
                                            <i class="bi bi-telephone me-2"></i>Téléphone
                                        </label>
                                        <input type="tel" class="form-control form-control-glass" id="phone" 
                                               name="phone" value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="address" class="form-label text-white">
                                            <i class="bi bi-geo-alt me-2"></i>Adresse
                                        </label>
                                        <textarea class="form-control form-control-glass" id="address" name="address" 
                                                  rows="3"><?= htmlspecialchars($currentUser['address'] ?? '') ?></textarea>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-gradient">
                                            <i class="bi bi-check-circle me-2"></i>Sauvegarder les modifications
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="glass p-4 rounded-3 mb-4">
                                <h4 class="text-white mb-3">
                                    <i class="bi bi-info-circle me-2"></i>Informations du compte
                                </h4>
                                <div class="mb-3">
                                    <small class="text-white-50">Type de compte</small>
                                    <div class="text-white">
                                        <span class="badge bg-primary">Client</span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <small class="text-white-50">Membre depuis</small>
                                    <div class="text-white">
                                        <?= date('d/m/Y', strtotime($currentUser['created_at'])) ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <small class="text-white-50">Dernière mise à jour</small>
                                    <div class="text-white">
                                        <?= date('d/m/Y H:i', strtotime($currentUser['updated_at'])) ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="glass p-4 rounded-3">
                                <h4 class="text-white mb-3">
                                    <i class="bi bi-shield-check me-2"></i>Sécurité
                                </h4>
                                <p class="text-white-50 small mb-3">
                                    Vos données sont protégées par un chiffrement de niveau bancaire.
                                </p>
                                <button class="btn btn-glass w-100">
                                    <i class="bi bi-key me-2"></i>Changer le mot de passe
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Prévisualisation de l'image
        document.getElementById('profilePictureInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('profilePreview');
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Aperçu" class="profile-pic">';
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>