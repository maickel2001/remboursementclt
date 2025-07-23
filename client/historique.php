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
        .glass {
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

        /* Pagination */
        .page-link {
            background: rgba(15, 23, 42, 0.8) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: #ffffff !important;
        }

        .page-link:hover {
            background: rgba(59, 130, 246, 0.3) !important;
            color: #ffffff !important;
        }

        .page-item.active .page-link {
            background: #3b82f6 !important;
            border-color: #3b82f6 !important;
            color: #ffffff !important;
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
                        </span>
                        <div class="d-flex gap-2 flex-wrap justify-content-center">
                            <a href="dashboard.php" class="btn btn-glass btn-sm">
                                <i class="bi bi-speedometer2 me-1"></i>Dashboard
                            </a>
                            <a href="remboursement.php" class="btn btn-glass btn-sm">
                                <i class="bi bi-credit-card me-1"></i>Remboursement
                            </a>
                            <a href="calculateur.php" class="btn btn-glass btn-sm">
                                <i class="bi bi-calculator me-1"></i>Calculateur
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
                        <label for="status" class="form-label">
                            <i class="bi bi-funnel me-2"></i>Filtrer par statut
                        </label>
                        <select class="form-control" id="status" name="status">
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
                                        <a class="page-link" 
                                           href="?page=<?= $page - 1 ?><?= $statusFilter ? '&status=' . $statusFilter : '' ?>">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" 
                                           href="?page=<?= $i ?><?= $statusFilter ? '&status=' . $statusFilter : '' ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" 
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