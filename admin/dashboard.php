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
                            <a class="sidebar-item active" href="dashboard.php">
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
                            <a class="sidebar-item" href="settings.php">
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
            </main>
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
                    borderColor: 'rgba(102, 126, 234, 1)',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Montant (€)',
                    data: chartData.map(item => item.amount),
                    borderColor: 'rgba(118, 75, 162, 1)',
                    backgroundColor: 'rgba(118, 75, 162, 0.1)',
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
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(220, 53, 69, 0.8)'
                    ],
                    borderColor: [
                        'rgba(255, 193, 7, 1)',
                        'rgba(40, 167, 69, 1)',
                        'rgba(220, 53, 69, 1)'
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