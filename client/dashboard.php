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
                            <a class="sidebar-item active" href="dashboard.php">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="sidebar-item" href="profil.php">
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
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>