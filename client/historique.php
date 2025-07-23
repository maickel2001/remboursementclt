<?php
require_once '../config/auth.php';
require_once '../config/database.php';

checkClient();
$currentUser = getCurrentUser();

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filtre par statut
$statusFilter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

$reimbursements = [];
$totalRecords = 0;
$totalPages = 1;

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Construire la requête avec filtre
    $whereClause = "WHERE user_id = :user_id";
    $params = [':user_id' => $currentUser['id']];
    
    if ($statusFilter) {
        $whereClause .= " AND status = :status";
        $params[':status'] = $statusFilter;
    }
    
    // Compter le total pour la pagination
    $countQuery = "SELECT COUNT(*) as total FROM reimbursements $whereClause";
    $countStmt = $db->prepare($countQuery);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalRecords = $countStmt->fetch()['total'];
    $totalPages = ceil($totalRecords / $limit);
    
    // Récupérer les remboursements
    $query = "SELECT * FROM reimbursements $whereClause ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $reimbursements = $stmt->fetchAll();
    
} catch (Exception $e) {
    // En cas d'erreur, garder les valeurs par défaut
    error_log('Erreur historique: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des Remboursements - RemboursePRO</title>
    
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
                            <a class="sidebar-item active" href="historique.php">
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
                            <i class="bi bi-clock-history me-3"></i>Historique des Remboursements
                        </h1>
                        <div class="d-flex gap-2">
                            <a href="remboursement.php" class="btn btn-gradient">
                                <i class="bi bi-plus-circle me-2"></i>Nouveau Remboursement
                            </a>
                            <a href="dashboard.php" class="btn btn-glass">
                                <i class="bi bi-arrow-left me-2"></i>Retour
                            </a>
                        </div>
                    </div>

                    <!-- Filtres -->
                    <div class="glass p-3 rounded-3 mb-4">
                        <form method="GET" action="" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label for="status" class="form-label text-white">
                                    <i class="bi bi-funnel me-2"></i>Filtrer par statut
                                </label>
                                <select class="form-control form-control-glass" id="status" name="status">
                                    <option value="">Tous les statuts</option>
                                    <option value="en_attente" <?= $statusFilter === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                    <option value="validé" <?= $statusFilter === 'validé' ? 'selected' : '' ?>>Validé</option>
                                    <option value="refusé" <?= $statusFilter === 'refusé' ? 'selected' : '' ?>>Refusé</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-gradient w-100">
                                    <i class="bi bi-search me-2"></i>Filtrer
                                </button>
                            </div>
                            <?php if ($statusFilter): ?>
                                <div class="col-md-2">
                                    <a href="historique.php" class="btn btn-glass w-100">
                                        <i class="bi bi-x-circle me-2"></i>Réinitialiser
                                    </a>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- Tableau des remboursements -->
                    <div class="glass p-4 rounded-3">
                        <?php if (empty($reimbursements)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 4rem; color: rgba(255,255,255,0.3);"></i>
                                <h4 class="text-white mt-3">Aucun remboursement trouvé</h4>
                                <p class="text-white-50">
                                    <?= $statusFilter ? 'Aucun remboursement avec ce statut.' : 'Vous n\'avez pas encore effectué de demande de remboursement.' ?>
                                </p>
                                <a href="remboursement.php" class="btn btn-gradient">
                                    <i class="bi bi-plus-circle me-2"></i>Créer votre première demande
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="text-white mb-0">
                                    <i class="bi bi-list-check me-2"></i>Mes Remboursements
                                </h4>
                                <span class="text-white-50">
                                    <?= $totalRecords ?> résultat(s) trouvé(s)
                                </span>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-glass">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Montant Total</th>
                                            <th>Remboursement</th>
                                            <th>Reste</th>
                                            <th>Moyen de Paiement</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reimbursements as $reimbursement): ?>
                                            <tr>
                                                <td><?= date('d/m/Y H:i', strtotime($reimbursement['created_at'])) ?></td>
                                                <td><?= number_format($reimbursement['amount_to_reimburse'], 2) ?>€</td>
                                                <td><?= number_format($reimbursement['reimbursement_amount'], 2) ?>€</td>
                                                <td><?= number_format($reimbursement['remaining_amount'], 2) ?>€</td>
                                                <td>
                                                    <?php
                                                    $paymentMethods = [
                                                        'carte_recharge' => 'Carte de recharge',
                                                        'code_rechargement' => 'Code de rechargement',
                                                        'carte_bancaire' => 'Carte bancaire'
                                                    ];
                                                    echo $paymentMethods[$reimbursement['payment_method']] ?? $reimbursement['payment_method'];
                                                    
                                                    if ($reimbursement['card_type']) {
                                                        echo '<br><small class="text-white-50">(' . ucfirst($reimbursement['card_type']) . ')</small>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <span class="status-badge status-<?= $reimbursement['status'] ?>">
                                                        <?= ucfirst(str_replace('_', ' ', $reimbursement['status'])) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-glass btn-sm" onclick="showDetails(<?= $reimbursement['id'] ?>)">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                                <nav aria-label="Pagination" class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link bg-transparent border-white text-white" 
                                                   href="?page=<?= $page - 1 ?><?= $statusFilter ? '&status=' . $statusFilter : '' ?>">
                                                    <i class="bi bi-chevron-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                                <a class="page-link bg-transparent border-white text-white" 
                                                   href="?page=<?= $i ?><?= $statusFilter ? '&status=' . $statusFilter : '' ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link bg-transparent border-white text-white" 
                                                   href="?page=<?= $page + 1 ?><?= $statusFilter ? '&status=' . $statusFilter : '' ?>">
                                                    <i class="bi bi-chevron-right"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function showDetails(id) {
            // Ici vous pouvez implémenter une modal avec les détails complets
            alert('Fonctionnalité de détails à implémenter pour le remboursement ID: ' + id);
        }
    </script>
</body>
</html>