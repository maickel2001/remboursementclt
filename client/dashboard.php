<?php
require_once '../config/auth.php';
require_once '../config/database.php';

checkClient();

$currentUser = getCurrentUser();
$stats = [
    'total_reimbursements' => 0,
    'total_amount' => 0,
    'total_reimbursed' => 0,
    'validated_count' => 0,
    'pending_count' => 0
];
$recentReimbursements = [];

// Récupérer les statistiques du client
try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Statistiques
    $statsQuery = "SELECT 
                    COUNT(*) as total_reimbursements,
                    COALESCE(SUM(amount_to_reimburse), 0) as total_amount,
                    COALESCE(SUM(reimbursement_amount), 0) as total_reimbursed,
                    COUNT(CASE WHEN status = 'validé' THEN 1 END) as validated_count,
                    COUNT(CASE WHEN status = 'en_attente' THEN 1 END) as pending_count
                   FROM reimbursements WHERE user_id = :user_id";
    $statsStmt = $db->prepare($statsQuery);
    $statsStmt->bindParam(':user_id', $currentUser['id']);
    $statsStmt->execute();
    $stats = $statsStmt->fetch();
    
    // Derniers remboursements
    $recentQuery = "SELECT * FROM reimbursements WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5";
    $recentStmt = $db->prepare($recentQuery);
    $recentStmt->bindParam(':user_id', $currentUser['id']);
    $recentStmt->execute();
    $recentReimbursements = $recentStmt->fetchAll();
    
} catch (Exception $e) {
    // En cas d'erreur, garder les valeurs par défaut
    error_log('Erreur dashboard client: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Client - RemboursePRO</title>
    
    <!-- Bootstrap 5.3.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
        .glass, .stats-card, .form-glass {
            background: rgba(15, 23, 42, 0.8) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 16px !important;
            padding: 1.5rem !important;
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

        /* Stats cards */
        .stats-value {
            font-size: 2rem !important;
            font-weight: 700 !important;
            color: #ffffff !important;
            margin-bottom: 0.5rem !important;
        }

        .stats-label {
            color: rgba(255, 255, 255, 0.8) !important;
            font-size: 0.9rem !important;
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

        /* Tables */
        .table {
            background: transparent !important;
            color: #ffffff !important;
        }

        .table th {
            background: rgba(15, 23, 42, 0.8) !important;
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            font-weight: 600 !important;
        }

        .table td {
            background: transparent !important;
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        .table-glass {
            background: rgba(15, 23, 42, 0.6) !important;
            border-radius: 12px !important;
            overflow: hidden !important;
        }

        /* Status badges */
        .status-badge {
            padding: 4px 12px !important;
            border-radius: 20px !important;
            font-size: 0.8rem !important;
            font-weight: 600 !important;
        }

        .status-en_attente, .status-en-attente {
            background: rgba(245, 158, 11, 0.2) !important;
            color: #fbbf24 !important;
            border: 1px solid rgba(245, 158, 11, 0.4) !important;
        }

        .status-validé {
            background: rgba(16, 185, 129, 0.2) !important;
            color: #34d399 !important;
            border: 1px solid rgba(16, 185, 129, 0.4) !important;
        }

        .status-refusé {
            background: rgba(239, 68, 68, 0.2) !important;
            color: #f87171 !important;
            border: 1px solid rgba(239, 68, 68, 0.4) !important;
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
            .stats-value {
                font-size: 1.5rem !important;
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
                </span>
                <a href="../logout.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid" style="padding-top: 80px;">
            <div class="py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="text-white">
                        <i class="bi bi-speedometer2 me-3"></i>Dashboard Client
                    </h1>
                    <div class="text-white-50">
                        <i class="bi bi-calendar3 me-2"></i><?= date('d/m/Y H:i') ?>
                    </div>
                </div>

                <!-- Welcome message -->
                <div class="glass p-4 rounded-3 mb-4">
                    <h3 class="text-white mb-3">
                        Bienvenue, <?= htmlspecialchars($currentUser['firstName']) ?> ! 👋
                    </h3>
                    <p class="text-white-50 mb-0">
                        Gérez vos remboursements en toute simplicité depuis votre espace personnel.
                    </p>
                </div>

                <!-- Statistics Cards -->
                <div class="row g-4 mb-5">
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-value"><?= $stats['total_reimbursements'] ?></div>
                            <div class="stats-label">
                                <i class="bi bi-list-check me-2"></i>Total Demandes
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-value"><?= number_format($stats['total_amount'], 2) ?>€</div>
                            <div class="stats-label">
                                <i class="bi bi-currency-euro me-2"></i>Montant Total
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-value"><?= number_format($stats['total_reimbursed'], 2) ?>€</div>
                            <div class="stats-label">
                                <i class="bi bi-check-circle me-2"></i>Remboursé
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-value"><?= $stats['pending_count'] ?></div>
                            <div class="stats-label">
                                <i class="bi bi-clock me-2"></i>En Attente
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="glass p-4 rounded-3 mb-5">
                    <h4 class="text-white mb-4">
                        <i class="bi bi-lightning-charge me-2"></i>Actions Rapides
                    </h4>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="remboursement.php" class="btn btn-gradient w-100">
                                <i class="bi bi-credit-card me-2"></i>Nouveau Remboursement
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="calculateur.php" class="btn btn-glass w-100">
                                <i class="bi bi-calculator me-2"></i>Calculateur
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="historique.php" class="btn btn-glass w-100">
                                <i class="bi bi-clock-history me-2"></i>Historique
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="profil.php" class="btn btn-glass w-100">
                                <i class="bi bi-person me-2"></i>Mon Profil
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Reimbursements -->
                <div class="glass p-4 rounded-3">
                    <h4 class="text-white mb-4">
                        <i class="bi bi-clock-history me-2"></i>Derniers Remboursements
                    </h4>
                    
                    <?php if (empty($recentReimbursements)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 4rem; color: rgba(255,255,255,0.3);"></i>
                            <p class="text-white-50 mt-3">Aucun remboursement pour le moment</p>
                            <a href="remboursement.php" class="btn btn-gradient">
                                <i class="bi bi-plus-circle me-2"></i>Créer votre première demande
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-glass">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Montant</th>
                                        <th>Remboursement</th>
                                        <th>Moyen de paiement</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentReimbursements as $reimbursement): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($reimbursement['created_at'])) ?></td>
                                            <td><?= number_format($reimbursement['amount_to_reimburse'], 2) ?>€</td>
                                            <td><?= number_format($reimbursement['reimbursement_amount'], 2) ?>€</td>
                                            <td>
                                                <?php
                                                $paymentMethods = [
                                                    'carte_recharge' => 'Carte de recharge',
                                                    'code_rechargement' => 'Code de rechargement',
                                                    'carte_bancaire' => 'Carte bancaire'
                                                ];
                                                echo $paymentMethods[$reimbursement['payment_method']] ?? $reimbursement['payment_method'];
                                                ?>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?= $reimbursement['status'] ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $reimbursement['status'])) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="historique.php" class="btn btn-glass">
                                <i class="bi bi-eye me-2"></i>Voir tout l'historique
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Widget météo -->
    <div id="weather-widget" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;">
        <div class="glass p-3 rounded-3" style="min-width: 200px;">
            <h6 class="text-white mb-2"><i class="bi bi-cloud-sun me-2"></i>Météo</h6>
            <div id="weather-info" class="text-white-50 small">
                <div>Paris, France</div>
                <div>🌤️ 22°C - Partiellement nuageux</div>
            </div>
        </div>
    </div>

    <!-- Raccourcis clavier -->
    <div id="shortcuts-help" style="position: fixed; bottom: 20px; left: 20px; z-index: 1000; display: none;">
        <div class="glass p-3 rounded-3">
            <h6 class="text-white mb-2"><i class="bi bi-keyboard me-2"></i>Raccourcis</h6>
            <div class="text-white-50 small">
                <div><kbd>Ctrl + N</kbd> Nouveau remboursement</div>
                <div><kbd>Ctrl + H</kbd> Historique</div>
                <div><kbd>Ctrl + P</kbd> Profil</div>
                <div><kbd>?</kbd> Aide</div>
            </div>
        </div>
    </div>
 
    <div class="container-fluid" style="padding-top: 80px;">
            <div class="py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="text-white">
                        <i class="bi bi-speedometer2 me-3"></i>Dashboard Client
                    </h1>
                    <div class="text-white-50">
                        <i class="bi bi-calendar3 me-2"></i><?= date('d/m/Y H:i') ?>
                    </div>
                </div>

                <!-- Welcome message -->
                <div class="glass p-4 rounded-3 mb-4">
                    <h3 class="text-white mb-3">
                        Bienvenue, <?= htmlspecialchars($currentUser['firstName']) ?> ! 👋
                    </h3>
                    <p class="text-white-50 mb-0">
                        Gérez vos remboursements en toute simplicité depuis votre espace personnel.
                    </p>
                </div>

                <!-- Statistics Cards -->
                <div class="row g-4 mb-5">
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-value"><?= $stats['total_reimbursements'] ?></div>
                            <div class="stats-label">
                                <i class="bi bi-list-check me-2"></i>Total Demandes
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-value"><?= number_format($stats['total_amount'], 2) ?>€</div>
                            <div class="stats-label">
                                <i class="bi bi-currency-euro me-2"></i>Montant Total
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-value"><?= number_format($stats['total_reimbursed'], 2) ?>€</div>
                            <div class="stats-label">
                                <i class="bi bi-check-circle me-2"></i>Remboursé
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-value"><?= $stats['pending_count'] ?></div>
                            <div class="stats-label">
                                <i class="bi bi-clock me-2"></i>En Attente
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="glass p-4 rounded-3 mb-5">
                    <h4 class="text-white mb-4">
                        <i class="bi bi-lightning-charge me-2"></i>Actions Rapides
                    </h4>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="remboursement.php" class="btn btn-gradient w-100">
                                <i class="bi bi-credit-card me-2"></i>Nouveau Remboursement
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="calculateur.php" class="btn btn-glass w-100">
                                <i class="bi bi-calculator me-2"></i>Calculateur
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="historique.php" class="btn btn-glass w-100">
                                <i class="bi bi-clock-history me-2"></i>Historique
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="profil.php" class="btn btn-glass w-100">
                                <i class="bi bi-person me-2"></i>Mon Profil
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Reimbursements -->
                <div class="glass p-4 rounded-3">
                    <h4 class="text-white mb-4">
                        <i class="bi bi-clock-history me-2"></i>Derniers Remboursements
                    </h4>
                    
                    <?php if (empty($recentReimbursements)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 4rem; color: rgba(255,255,255,0.3);"></i>
                            <p class="text-white-50 mt-3">Aucun remboursement pour le moment</p>
                            <a href="remboursement.php" class="btn btn-gradient">
                                <i class="bi bi-plus-circle me-2"></i>Créer votre première demande
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-glass">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Montant</th>
                                        <th>Remboursement</th>
                                        <th>Moyen de paiement</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentReimbursements as $reimbursement): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($reimbursement['created_at'])) ?></td>
                                            <td><?= number_format($reimbursement['amount_to_reimburse'], 2) ?>€</td>
                                            <td><?= number_format($reimbursement['reimbursement_amount'], 2) ?>€</td>
                                            <td>
                                                <?php
                                                $paymentMethods = [
                                                    'carte_recharge' => 'Carte de recharge',
                                                    'code_rechargement' => 'Code de rechargement',
                                                    'carte_bancaire' => 'Carte bancaire'
                                                ];
                                                echo $paymentMethods[$reimbursement['payment_method']] ?? $reimbursement['payment_method'];
                                                ?>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?= $reimbursement['status'] ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $reimbursement['status'])) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="historique.php" class="btn btn-glass">
                                <i class="bi bi-eye me-2"></i>Voir tout l'historique
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Widget météo -->
    <div id="weather-widget" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;">
        <div class="glass p-3 rounded-3" style="min-width: 200px;">
            <h6 class="text-white mb-2"><i class="bi bi-cloud-sun me-2"></i>Météo</h6>
            <div id="weather-info" class="text-white-50 small">
                <div>Paris, France</div>
                <div>🌤️ 22°C - Partiellement nuageux</div>
            </div>
        </div>
    </div>

    <!-- Raccourcis clavier -->
    <div id="shortcuts-help" style="position: fixed; bottom: 20px; left: 20px; z-index: 1000; display: none;">
        <div class="glass p-3 rounded-3">
            <h6 class="text-white mb-2"><i class="bi bi-keyboard me-2"></i>Raccourcis</h6>
            <div class="text-white-50 small">
                <div><kbd>Ctrl + N</kbd> Nouveau remboursement</div>
                <div><kbd>Ctrl + H</kbd> Historique</div>
                <div><kbd>Ctrl + P</kbd> Profil</div>
                <div><kbd>?</kbd> Aide</div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Raccourcis clavier
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey) {
                switch(e.key) {
                    case 'n':
                        e.preventDefault();
                        window.location.href = 'remboursement.php';
                        break;
                    case 'h':
                        e.preventDefault();
                        window.location.href = 'historique.php';
                        break;
                    case 'p':
                        e.preventDefault();
                        window.location.href = 'profil.php';
                        break;
                }
            } else if (e.key === '?') {
                const help = document.getElementById('shortcuts-help');
                help.style.display = help.style.display === 'none' ? 'block' : 'none';
            }
        });

        // Animation des statistiques au chargement
        function animateStats() {
            document.querySelectorAll('.stats-value').forEach(stat => {
                const finalValue = parseInt(stat.textContent);
                let currentValue = 0;
                const increment = finalValue / 50;
                
                const timer = setInterval(() => {
                    currentValue += increment;
                    if (currentValue >= finalValue) {
                        stat.textContent = finalValue;
                        clearInterval(timer);
                    } else {
                        stat.textContent = Math.floor(currentValue);
                    }
                }, 30);
            });
        }

        // Effet de pulsation sur les boutons d'action
        function addPulseEffect() {
            const style = document.createElement('style');
            style.textContent = `
                @keyframes pulse {
                    0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
                    70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
                    100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
                }
                
                .btn-gradient:hover {
                    animation: pulse 2s infinite !important;
                }
            `;
            document.head.appendChild(style);
        }

        // Initialiser les effets
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(animateStats, 500);
            addPulseEffect();
        });
    </script>
</body>
</html>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Raccourcis clavier
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey) {
                switch(e.key) {
                    case 'n':
                        e.preventDefault();
                        window.location.href = 'remboursement.php';
                        break;
                    case 'h':
                        e.preventDefault();
                        window.location.href = 'historique.php';
                        break;
                    case 'p':
                        e.preventDefault();
                        window.location.href = 'profil.php';
                        break;
                }
            } else if (e.key === '?') {
                const help = document.getElementById('shortcuts-help');
                help.style.display = help.style.display === 'none' ? 'block' : 'none';
            }
        });

        // Animation des statistiques au chargement
        function animateStats() {
            document.querySelectorAll('.stats-value').forEach(stat => {
                const finalValue = parseInt(stat.textContent);
                let currentValue = 0;
                const increment = finalValue / 50;
                
                const timer = setInterval(() => {
                    currentValue += increment;
                    if (currentValue >= finalValue) {
                        stat.textContent = finalValue;
                        clearInterval(timer);
                    } else {
                        stat.textContent = Math.floor(currentValue);
                    }
                }, 30);
            });
        }

        // Effet de pulsation sur les boutons d'action
        function addPulseEffect() {
            const style = document.createElement('style');
            style.textContent = `
                @keyframes pulse {
                    0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
                    70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
                    100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
                }
                
                .btn-gradient:hover {
                    animation: pulse 2s infinite !important;
                }
            `;
            document.head.appendChild(style);
        }

        // Initialiser les effets
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(animateStats, 500);
            addPulseEffect();
        });
    </script>
</body>
</html>