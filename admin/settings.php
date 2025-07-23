<?php
require_once '../config/auth.php';
require_once '../config/database.php';

checkAdmin();
$currentUser = getCurrentUser();

$error = '';
$success = '';

$settings = [];

// Récupérer les paramètres actuels
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM site_settings ORDER BY id DESC LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $settings = $stmt->fetch();
    
    if (!$settings) {
        // Créer des paramètres par défaut si aucun n'existe
        $insertQuery = "INSERT INTO site_settings (site_name, contact_email) VALUES ('RemboursePRO', 'contact@remboursepro.com')";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->execute();
        
        $stmt->execute();
        $settings = $stmt->fetch();
    }
} catch (Exception $e) {
    // En cas d'erreur, garder les valeurs par défaut
    error_log('Erreur settings: ' . $e->getMessage());
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siteName = sanitizeInput($_POST['site_name'] ?? '');
    $contactEmail = sanitizeInput($_POST['contact_email'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Token de sécurité invalide.';
    } elseif (empty($siteName) || empty($contactEmail)) {
        $error = 'Tous les champs sont obligatoires.';
    } elseif (!validateEmail($contactEmail)) {
        $error = 'Email de contact invalide.';
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "UPDATE site_settings SET site_name = :site_name, contact_email = :contact_email WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':site_name', $siteName);
            $stmt->bindParam(':contact_email', $contactEmail);
            $stmt->bindParam(':id', $settings['id']);
            
            if ($stmt->execute()) {
                $success = 'Paramètres mis à jour avec succès !';
                
                // Recharger les paramètres
                $selectQuery = "SELECT * FROM site_settings WHERE id = :id";
                $selectStmt = $db->prepare($selectQuery);
                $selectStmt->bindParam(':id', $settings['id']);
                $selectStmt->execute();
                $settings = $selectStmt->fetch();
            } else {
                $error = 'Erreur lors de la mise à jour des paramètres.';
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
    <title>Paramètres du Site - RemboursePRO</title>
    
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

        * {
            color: #ffffff !important;
        }

        .container, .container-fluid, .row, .col, [class*="col-"] {
            background: transparent !important;
            color: #ffffff !important;
        }

        /* Navigation */
        .navbar, .navbar-glass {
            background: rgba(15, 23, 42, 0.95) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
            backdrop-filter: blur(10px) !important;
        }

        .navbar-brand, .nav-link {
            color: #ffffff !important;
        }

        .navbar-brand:hover, .nav-link:hover {
            color: #60a5fa !important;
        }

        /* Sidebar */
        .dashboard-sidebar {
            background: rgba(15, 23, 42, 0.9) !important;
            border-right: 1px solid rgba(255, 255, 255, 0.1) !important;
            min-height: 100vh !important;
        }

        .sidebar-item {
            color: #ffffff !important;
            background: transparent !important;
            padding: 12px 20px !important;
            margin: 5px 15px !important;
            border-radius: 8px !important;
            text-decoration: none !important;
            display: block !important;
            transition: all 0.3s ease !important;
        }

        .sidebar-item:hover, .sidebar-item.active {
            background: rgba(59, 130, 246, 0.2) !important;
            color: #ffffff !important;
        }

        /* Cards glassmorphism */
        .glass, .form-glass {
            background: rgba(15, 23, 42, 0.8) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 16px !important;
            padding: 2rem !important;
            backdrop-filter: blur(20px) !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3) !important;
            color: #ffffff !important;
            margin-bottom: 1rem !important;
        }

        /* Titres */
        h1, h2, h3, h4, h5, h6 {
            color: #ffffff !important;
            font-weight: 600 !important;
        }

        /* Textes */
        p, span, div, label, small {
            color: #ffffff !important;
        }

        .text-white-50 {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        /* Formulaires */
        .form-control, .form-select, input, textarea, select {
            background: rgba(15, 23, 42, 0.8) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: #ffffff !important;
            border-radius: 10px !important;
            padding: 12px 16px !important;
        }

        .form-control:focus, .form-select:focus, input:focus, textarea:focus, select:focus {
            background: rgba(15, 23, 42, 0.9) !important;
            border-color: #3b82f6 !important;
            color: #ffffff !important;
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25) !important;
        }

        .form-control::placeholder, input::placeholder, textarea::placeholder {
            color: rgba(255, 255, 255, 0.6) !important;
        }

        .form-label {
            color: #ffffff !important;
            font-weight: 500 !important;
            margin-bottom: 0.5rem !important;
        }

        /* Boutons */
        .btn {
            border-radius: 10px !important;
            padding: 10px 20px !important;
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
        }

        .btn-warning {
            background: #f59e0b !important;
            color: #ffffff !important;
        }

        .btn-info {
            background: #06b6d4 !important;
            color: #ffffff !important;
        }

        .btn-secondary {
            background: #6b7280 !important;
            color: #ffffff !important;
        }

        .btn-outline-light {
            background: transparent !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            color: #ffffff !important;
        }

        .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.1) !important;
            color: #ffffff !important;
        }

        /* Alertes */
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

        /* Badges */
        .badge {
            color: #ffffff !important;
        }

        .bg-danger {
            background: #ef4444 !important;
        }

        /* Liens */
        a {
            color: #60a5fa !important;
        }

        a:hover {
            color: #93c5fd !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-glass {
                padding: 1.5rem !important;
            }
            
            .container, .container-fluid {
                padding-left: 15px !important;
                padding-right: 15px !important;
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
            
            .d-flex.align-items-center span {
                font-size: 0.9rem !important;
            }
            
            .btn-outline-light.btn-sm {
                padding: 6px 12px !important;
            }
        }
    </style>
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
                    <span class="badge bg-danger ms-2">Admin</span>
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
                            <a class="sidebar-item" href="remboursements.php">
                                <i class="bi bi-credit-card me-2"></i>Remboursements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="sidebar-item" href="utilisateurs.php">
                                <i class="bi bi-people me-2"></i>Utilisateurs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="sidebar-item active" href="settings.php">
                                <i class="bi bi-gear me-2"></i>Paramètres
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
                            <i class="bi bi-gear me-3"></i>Paramètres du Site
                        </h1>
                        <a href="dashboard.php" class="btn btn-glass">
                            <i class="bi bi-arrow-left me-2"></i>Retour
                        </a>
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

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="form-glass">
                                <h3 class="text-white mb-4">
                                    <i class="bi bi-sliders me-2"></i>Configuration Générale
                                </h3>
                                
                                <form method="POST" action="">
                                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                    
                                    <div class="mb-3">
                                        <label for="site_name" class="form-label text-white">
                                            <i class="bi bi-globe me-2"></i>Nom du site *
                                        </label>
                                        <input type="text" class="form-control" id="site_name" 
                                               name="site_name" required value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>">
                                        <small class="text-white-50">Ce nom apparaîtra dans la navigation et les emails.</small>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="contact_email" class="form-label text-white">
                                            <i class="bi bi-envelope me-2"></i>Email de contact *
                                        </label>
                                        <input type="email" class="form-control" id="contact_email" 
                                               name="contact_email" required value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>">
                                        <small class="text-white-50">Adresse email utilisée pour les notifications et le support.</small>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-gradient">
                                            <i class="bi bi-check-circle me-2"></i>Sauvegarder les paramètres
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="glass p-4 rounded-3 mb-4">
                                <h4 class="text-white mb-3">
                                    <i class="bi bi-info-circle me-2"></i>Informations Système
                                </h4>
                                <div class="mb-3">
                                    <small class="text-white-50">Version PHP</small>
                                    <div class="text-white"><?= phpversion() ?></div>
                                </div>
                                <div class="mb-3">
                                    <small class="text-white-50">Serveur Web</small>
                                    <div class="text-white"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Non disponible' ?></div>
                                </div>
                                <div class="mb-3">
                                    <small class="text-white-50">Dernière mise à jour</small>
                                    <div class="text-white">
                                        <?= $settings ? date('d/m/Y H:i', strtotime($settings['updated_at'])) : 'Jamais' ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="glass p-4 rounded-3 mb-4">
                                <h4 class="text-white mb-3">
                                    <i class="bi bi-shield-check me-2"></i>Sécurité
                                </h4>
                                <ul class="text-white-50 small">
                                    <li class="mb-2">✅ Hachage des mots de passe activé</li>
                                    <li class="mb-2">✅ Protection CSRF activée</li>
                                    <li class="mb-2">✅ Validation des entrées activée</li>
                                    <li class="mb-2">✅ Sessions sécurisées</li>
                                </ul>
                            </div>
                            
                            <div class="glass p-4 rounded-3">
                                <h4 class="text-white mb-3">
                                    <i class="bi bi-tools me-2"></i>Actions Avancées
                                </h4>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-warning" onclick="clearCache()">
                                        <i class="bi bi-arrow-clockwise me-2"></i>Vider le cache
                                    </button>
                                    <button class="btn btn-info" onclick="exportData()">
                                        <i class="bi bi-download me-2"></i>Exporter les données
                                    </button>
                                    <button class="btn btn-secondary" onclick="viewLogs()">
                                        <i class="bi bi-file-text me-2"></i>Voir les logs
                                    </button>
                                </div>
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
        function clearCache() {
            if (confirm('Êtes-vous sûr de vouloir vider le cache ?')) {
                alert('Fonctionnalité de cache à implémenter selon vos besoins.');
            }
        }
        
        function exportData() {
            if (confirm('Exporter toutes les données de la plateforme ?')) {
                alert('Fonctionnalité d\'export à implémenter (CSV, JSON, etc.).');
            }
        }
        
        function viewLogs() {
            alert('Fonctionnalité de visualisation des logs à implémenter.');
        }
    </script>
</body>
</html>