<?php
require_once '../config/auth.php';
require_once '../config/database.php';

checkAdmin();

$currentUser = getCurrentUser();
$stats = [
    'total_clients' => 0,
    'total_reimbursements' => 0,
    'total_amount' => 0,
    'total_reimbursed' => 0,
    'pending_count' => 0,
    'validated_count' => 0,
    'refused_count' => 0
];
$recentReimbursements = [];
$chartData = [];

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Statistiques globales
    $statsQuery = "SELECT 
                    (SELECT COUNT(*) FROM users WHERE role = 'client') as total_clients,
                    (SELECT COUNT(*) FROM reimbursements) as total_reimbursements,
                    (SELECT COALESCE(SUM(amount_to_reimburse), 0) FROM reimbursements) as total_amount,
                    (SELECT COALESCE(SUM(reimbursement_amount), 0) FROM reimbursements WHERE status = 'validé') as total_reimbursed,
                    (SELECT COUNT(*) FROM reimbursements WHERE status = 'en_attente') as pending_count,
                    (SELECT COUNT(*) FROM reimbursements WHERE status = 'validé') as validated_count,
                    (SELECT COUNT(*) FROM reimbursements WHERE status = 'refusé') as refused_count";
    $statsStmt = $db->prepare($statsQuery);
    $statsStmt->execute();
    $stats = $statsStmt->fetch();
    
    // Derniers remboursements
    $recentQuery = "SELECT r.*, u.firstName, u.lastName, u.email 
                   FROM reimbursements r 
                   JOIN users u ON r.user_id = u.id 
                   ORDER BY r.created_at DESC LIMIT 5";
    $recentStmt = $db->prepare($recentQuery);
    $recentStmt->execute();
    $recentReimbursements = $recentStmt->fetchAll();
    
    // Données pour le graphique (remboursements par mois)
    $chartQuery = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as count,
                    SUM(reimbursement_amount) as amount
                   FROM reimbursements 
                   WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                   GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                   ORDER BY month";
    $chartStmt = $db->prepare($chartQuery);
    $chartStmt->execute();
    $chartData = $chartStmt->fetchAll();
    
} catch (Exception $e) {
        // En cas d'erreur, garder les valeurs par défaut
        error_log('Erreur dashboard admin: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - RemboursePRO</title>
    
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

        .btn-success {
            background: #10b981 !important;
            color: #ffffff !important;
        }

        .btn-danger {
            background: #ef4444 !important;
            color: #ffffff !important;
        }

        .btn-warning {
            background: #f59e0b !important;
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

        .bg-primary {
            background: #3b82f6 !important;
        }

        .bg-danger {
            background: #ef4444 !important;
        }

        .bg-success {
            background: #10b981 !important;
        }

        /* Chart container */
        .chart-container {
            background: rgba(15, 23, 42, 0.6) !important;
            border-radius: 8px !important;
            padding: 1rem !important;
            height: 400px !important;
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
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-glass fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">
                <i class="bi bi-shield-check me-2"></i>RemboursePRO
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto">
                    <div class="d-flex align-items-center flex-column flex-lg-row">
                        <span class="text-white me-lg-3 mb-2 mb-lg-0">
                            <i class="bi bi-person-circle me-2"></i><?= htmlspecialchars($currentUser['firstName'] . ' ' . $currentUser['lastName']) ?>
                            <span class="badge bg-danger ms-2">Admin</span>
                        </span>
                        <div class="d-flex gap-2 flex-wrap justify-content-center">
                            <a href="remboursements.php" class="btn btn-glass btn-sm">
                                <i class="bi bi-credit-card me-1"></i>Remboursements
                            </a>
                            <a href="utilisateurs.php" class="btn btn-glass btn-sm">
                                <i class="bi bi-people me-1"></i>Utilisateurs
                            </a>
                            <a href="settings.php" class="btn btn-glass btn-sm">
                                <i class="bi bi-gear me-1"></i>Paramètres
                            </a>
                            <a href="../logout.php" class="btn btn-outline-light btn-sm">
                                <i class="bi bi-box-arrow-right me-1"></i>Déconnexion
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid" style="padding-top: 80px;">
        <div class="py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-white">
                    <i class="bi bi-speedometer2 me-3"></i>Dashboard Administrateur
                </h1>
                <div class="text-white-50">
                    <i class="bi bi-calendar3 me-2"></i><?= date('d/m/Y H:i') ?>
                </div>
            </div>

            <!-- Welcome message -->
            <div class="glass p-4 rounded-3 mb-4">
                <h3 class="text-white mb-3">
                    Bienvenue dans l'espace administrateur, <?= htmlspecialchars($currentUser['firstName']) ?> ! 🔧
                </h3>
                <p class="text-white-50 mb-0">
                    Gérez l'ensemble de la plateforme RemboursePRO depuis ce tableau de bord centralisé.
                </p>
            </div>

            <!-- Statistics Cards -->
            <div class="row g-4 mb-5">
                <div class="col-lg-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-value"><?= $stats['total_clients'] ?></div>
                        <div class="stats-label">
                            <i class="bi bi-people me-2"></i>Clients Inscrits
                        </div>
                    </div>
                </div>
                
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
                        <div class="stats-value"><?= number_format($stats['total_amount'], 0) ?>€</div>
                        <div class="stats-label">
                            <i class="bi bi-currency-euro me-2"></i>Montant Total
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-value"><?= number_format($stats['total_reimbursed'], 0) ?>€</div>
                        <div class="stats-label">
                            <i class="bi bi-check-circle me-2"></i>Remboursé
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Overview -->
            <div class="row g-4 mb-5">
                <div class="col-lg-4 col-md-6">
                    <div class="stats-card">
                        <div class="stats-value text-warning"><?= $stats['pending_count'] ?></div>
                        <div class="stats-label">
                            <i class="bi bi-clock me-2"></i>En Attente
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="stats-card">
                        <div class="stats-value text-success"><?= $stats['validated_count'] ?></div>
                        <div class="stats-label">
                            <i class="bi bi-check-circle me-2"></i>Validés
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="stats-card">
                        <div class="stats-value text-danger"><?= $stats['refused_count'] ?></div>
                        <div class="stats-label">
                            <i class="bi bi-x-circle me-2"></i>Refusés
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
                        <a href="remboursements.php" class="btn btn-gradient w-100">
                            <i class="bi bi-credit-card me-2"></i>Gérer Remboursements
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="utilisateurs.php" class="btn btn-glass w-100">
                            <i class="bi bi-people me-2"></i>Gérer Utilisateurs
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="settings.php" class="btn btn-glass w-100">
                            <i class="bi bi-gear me-2"></i>Paramètres
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="../index.php" class="btn btn-glass w-100">
                            <i class="bi bi-house me-2"></i>Voir le Site
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Graphique des remboursements -->
                <div class="col-lg-8">
                    <div class="glass p-4 rounded-3 mb-4">
                        <h4 class="text-white mb-4">
                            <i class="bi bi-bar-chart me-2"></i>Évolution des Remboursements (12 derniers mois)
                        </h4>
                        <div class="chart-container">
                            <canvas id="reimbursementChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Répartition par statut -->
                <div class="col-lg-4">
                    <div class="glass p-4 rounded-3 mb-4">
                        <h4 class="text-white mb-4">
                            <i class="bi bi-pie-chart me-2"></i>Répartition par Statut
                        </h4>
                        <div class="chart-container">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Reimbursements -->
            <div class="glass p-4 rounded-3">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="text-white mb-0">
                        <i class="bi bi-clock-history me-2"></i>Derniers Remboursements
                    </h4>
                    <a href="remboursements.php" class="btn btn-glass">
                        <i class="bi bi-eye me-2"></i>Voir tout
                    </a>
                </div>
                
                <?php if (empty($recentReimbursements)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 4rem; color: rgba(255,255,255,0.3);"></i>
                        <p class="text-white-50 mt-3">Aucun remboursement pour le moment</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-glass">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Date</th>
                                    <th>Montant</th>
                                    <th>Remboursement</th>
                                    <th>Moyen de paiement</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentReimbursements as $reimbursement): ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($reimbursement['firstName'] . ' ' . $reimbursement['lastName']) ?>
                                            <br><small class="text-white-50"><?= htmlspecialchars($reimbursement['email']) ?></small>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($reimbursement['created_at'])) ?></td>
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
                                        <td>
                                            <a href="remboursements.php?id=<?= $reimbursement['id'] ?>" class="btn btn-glass btn-sm">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Données pour les graphiques
        const chartData = <?= json_encode($chartData) ?>;
        const stats = <?= json_encode($stats) ?>;
        
        // Graphique d'évolution des remboursements
        const ctx1 = document.getElementById('reimbursementChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: chartData.map(item => {
                    const date = new Date(item.month + '-01');
                    return date.toLocaleDateString('fr-FR', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Nombre de remboursements',
                    data: chartData.map(item => item.count),
                    borderColor: 'rgba(59, 130, 246, 1)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Montant (€)',
                    data: chartData.map(item => item.amount),
                    borderColor: 'rgba(139, 92, 246, 1)',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: 'white'
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: { color: 'white' },
                        grid: { color: 'rgba(255,255,255,0.1)' }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        ticks: { color: 'white' },
                        grid: { color: 'rgba(255,255,255,0.1)' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        ticks: { color: 'white' },
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });
        
        // Graphique de répartition par statut
        const ctx2 = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['En attente', 'Validés', 'Refusés'],
                datasets: [{
                    data: [stats.pending_count, stats.validated_count, stats.refused_count],
                    backgroundColor: [
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ],
                    borderColor: [
                        'rgba(245, 158, 11, 1)',
                        'rgba(16, 185, 129, 1)',
                        'rgba(239, 68, 68, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: 'white'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>