<?php
require_once '../config/auth.php';
require_once '../config/database.php';

checkAdmin();
$currentUser = getCurrentUser();

$error = '';
$success = '';

// Traitement des actions (changement de statut)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = sanitizeInput($_POST['action']);
    $reimbursementId = intval($_POST['reimbursement_id'] ?? 0);
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Token de sécurité invalide.';
    } elseif ($reimbursementId <= 0) {
        $error = 'ID de remboursement invalide.';
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $newStatus = '';
            switch ($action) {
                case 'validate':
                    $newStatus = 'validé';
                    break;
                case 'refuse':
                    $newStatus = 'refusé';
                    break;
                case 'reset':
                    $newStatus = 'en_attente';
                    break;
                default:
                    $error = 'Action invalide.';
            }
            
            if (!$error) {
                $query = "UPDATE reimbursements SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':status', $newStatus);
                $stmt->bindParam(':id', $reimbursementId);
                
                if ($stmt->execute()) {
                    $success = "Statut mis à jour avec succès : $newStatus";
                    
                    // Envoyer un email de notification au client
                    $clientQuery = "SELECT u.email, u.firstName, r.amount_to_reimburse, r.reimbursement_amount 
                                   FROM users u 
                                   JOIN reimbursements r ON u.id = r.user_id 
                                   WHERE r.id = :id";
                    $clientStmt = $db->prepare($clientQuery);
                    $clientStmt->bindParam(':id', $reimbursementId);
                    $clientStmt->execute();
                    $clientData = $clientStmt->fetch();
                    
                    if ($clientData) {
                        $subject = "Mise à jour de votre demande de remboursement - RemboursePRO";
                        $statusText = ucfirst(str_replace('_', ' ', $newStatus));
                        $message = "
                        <h2>Mise à jour de votre demande de remboursement</h2>
                        <p>Bonjour {$clientData['firstName']},</p>
                        <p>Le statut de votre demande de remboursement a été mis à jour.</p>
                        <p><strong>Nouveau statut : $statusText</strong></p>
                        <p><strong>Détails de la demande :</strong></p>
                        <ul>
                            <li>Montant total : " . number_format($clientData['amount_to_reimburse'], 2) . "€</li>
                            <li>Remboursement : " . number_format($clientData['reimbursement_amount'], 2) . "€</li>
                        </ul>
                        <p>Vous pouvez consulter le détail dans votre espace client.</p>
                        <p>Cordialement,<br>L'équipe RemboursePRO</p>
                        ";
                        
                        sendEmail($clientData['email'], $subject, $message);
                    }
                } else {
                    $error = 'Erreur lors de la mise à jour du statut.';
                }
            }
        } catch (Exception $e) {
            $error = 'Erreur de connexion à la base de données.';
        }
    }
}

// Pagination et filtres
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;
$statusFilter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$searchFilter = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

$reimbursements = [];
$totalRecords = 0;
$totalPages = 1;

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Construire la requête avec filtres
    $whereClause = "WHERE 1=1";
    $params = [];
    
    if ($statusFilter) {
        $whereClause .= " AND r.status = :status";
        $params[':status'] = $statusFilter;
    }
    
    if ($searchFilter) {
        $whereClause .= " AND (u.firstName LIKE :search OR u.lastName LIKE :search OR u.email LIKE :search)";
        $params[':search'] = "%$searchFilter%";
    }
    
    // Compter le total pour la pagination
    $countQuery = "SELECT COUNT(*) as total FROM reimbursements r JOIN users u ON r.user_id = u.id $whereClause";
    $countStmt = $db->prepare($countQuery);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalRecords = $countStmt->fetch()['total'];
    $totalPages = ceil($totalRecords / $limit);
    
    // Récupérer les remboursements
    $query = "SELECT r.*, u.firstName, u.lastName, u.email 
             FROM reimbursements r 
             JOIN users u ON r.user_id = u.id 
             $whereClause 
             ORDER BY r.created_at DESC 
             LIMIT :limit OFFSET :offset";
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
    error_log('Erreur remboursements: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Remboursements - RemboursePRO</title>
    
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
                            <a class="sidebar-item active" href="remboursements.php">
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
                            <i class="bi bi-credit-card me-3"></i>Gestion des Remboursements
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

                    <!-- Filtres -->
                    <div class="glass p-3 rounded-3 mb-4">
                        <form method="GET" action="" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="status" class="form-label text-white">
                                    <i class="bi bi-funnel me-2"></i>Statut
                                </label>
                                <select class="form-control form-control-glass" id="status" name="status">
                                    <option value="">Tous les statuts</option>
                                    <option value="en_attente" <?= $statusFilter === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                    <option value="validé" <?= $statusFilter === 'validé' ? 'selected' : '' ?>>Validé</option>
                                    <option value="refusé" <?= $statusFilter === 'refusé' ? 'selected' : '' ?>>Refusé</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="search" class="form-label text-white">
                                    <i class="bi bi-search me-2"></i>Rechercher un client
                                </label>
                                <input type="text" class="form-control form-control-glass" id="search" name="search" 
                                       placeholder="Nom, prénom ou email" value="<?= htmlspecialchars($searchFilter) ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-gradient w-100">
                                    <i class="bi bi-search me-2"></i>Filtrer
                                </button>
                            </div>
                            <?php if ($statusFilter || $searchFilter): ?>
                                <div class="col-md-2">
                                    <a href="remboursements.php" class="btn btn-glass w-100">
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
                                    <?= ($statusFilter || $searchFilter) ? 'Aucun remboursement ne correspond aux critères de recherche.' : 'Aucune demande de remboursement pour le moment.' ?>
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="text-white mb-0">
                                    <i class="bi bi-list-check me-2"></i>Liste des Remboursements
                                </h4>
                                <span class="text-white-50">
                                    <?= $totalRecords ?> résultat(s) trouvé(s)
                                </span>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-glass">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Client</th>
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
                                                <td>#<?= $reimbursement['id'] ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($reimbursement['firstName'] . ' ' . $reimbursement['lastName']) ?></strong>
                                                    <br><small class="text-white-50"><?= htmlspecialchars($reimbursement['email']) ?></small>
                                                </td>
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
                                                    <div class="btn-group" role="group">
                                                        <?php if ($reimbursement['status'] !== 'validé'): ?>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                                                <input type="hidden" name="action" value="validate">
                                                                <input type="hidden" name="reimbursement_id" value="<?= $reimbursement['id'] ?>">
                                                                <button type="submit" class="btn btn-success btn-sm" 
                                                                        onclick="return confirm('Valider ce remboursement ?')">
                                                                    <i class="bi bi-check"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($reimbursement['status'] !== 'refusé'): ?>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                                                <input type="hidden" name="action" value="refuse">
                                                                <input type="hidden" name="reimbursement_id" value="<?= $reimbursement['id'] ?>">
                                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                                        onclick="return confirm('Refuser ce remboursement ?')">
                                                                    <i class="bi bi-x"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($reimbursement['status'] !== 'en_attente'): ?>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                                                <input type="hidden" name="action" value="reset">
                                                                <input type="hidden" name="reimbursement_id" value="<?= $reimbursement['id'] ?>">
                                                                <button type="submit" class="btn btn-warning btn-sm" 
                                                                        onclick="return confirm('Remettre en attente ?')">
                                                                    <i class="bi bi-arrow-clockwise"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        
                                                        <button class="btn btn-glass btn-sm" onclick="showDetails(<?= $reimbursement['id'] ?>)">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                    </div>
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
                                                   href="?page=<?= $page - 1 ?><?= $statusFilter ? '&status=' . $statusFilter : '' ?><?= $searchFilter ? '&search=' . urlencode($searchFilter) : '' ?>">
                                                    <i class="bi bi-chevron-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                                <a class="page-link bg-transparent border-white text-white" 
                                                   href="?page=<?= $i ?><?= $statusFilter ? '&status=' . $statusFilter : '' ?><?= $searchFilter ? '&search=' . urlencode($searchFilter) : '' ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link bg-transparent border-white text-white" 
                                                   href="?page=<?= $page + 1 ?><?= $statusFilter ? '&status=' . $statusFilter : '' ?><?= $searchFilter ? '&search=' . urlencode($searchFilter) : '' ?>">
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

    <!-- Modal pour les détails du remboursement -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel">
                        <i class="bi bi-eye me-2"></i>Détails du Remboursement
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalContent">
                    <!-- Le contenu sera chargé ici -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-glass" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function showDetails(reimbursementId) {
            // Charger les détails via AJAX
            fetch('get_reimbursement_details.php?id=' + reimbursementId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const details = data.reimbursement;
                        const user = data.user;
                        
                        let paymentDetails = '';
                        if (details.payment_method === 'carte_recharge') {
                            paymentDetails = `
                                <strong>Type de carte:</strong> ${details.card_type ? details.card_type.charAt(0).toUpperCase() + details.card_type.slice(1) : 'Non spécifié'}<br>
                                <strong>Numéros de cartes:</strong><br>
                                ${details.card_numbers ? details.card_numbers.split(',').map((num, index) => `Carte ${index + 1}: ${num}`).join('<br>') : 'Aucun numéro'}
                            `;
                        } else if (details.payment_method === 'code_rechargement') {
                            paymentDetails = `<strong>Code de rechargement:</strong> ${details.recharge_code || 'Non spécifié'}`;
                        } else if (details.payment_method === 'carte_bancaire') {
                            paymentDetails = '<strong>Paiement par carte bancaire</strong>';
                        }
                        
                        const statusColor = details.status === 'validé' ? 'success' : 
                                          details.status === 'refusé' ? 'danger' : 'warning';
                        
                        document.getElementById('modalContent').innerHTML = `
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-white mb-3"><i class="bi bi-person me-2"></i>Informations Client</h6>
                                    <p class="mb-2"><strong>Nom:</strong> ${user.firstName} ${user.lastName}</p>
                                    <p class="mb-2"><strong>Email:</strong> ${user.email}</p>
                                    <p class="mb-2"><strong>Téléphone:</strong> ${user.phone || 'Non renseigné'}</p>
                                    <p class="mb-3"><strong>Adresse:</strong> ${user.address || 'Non renseignée'}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-white mb-3"><i class="bi bi-calendar me-2"></i>Informations Demande</h6>
                                    <p class="mb-2"><strong>Date de création:</strong> ${new Date(details.created_at).toLocaleString('fr-FR')}</p>
                                    <p class="mb-2"><strong>Dernière mise à jour:</strong> ${new Date(details.updated_at).toLocaleString('fr-FR')}</p>
                                    <p class="mb-3"><strong>Statut:</strong> <span class="badge bg-${statusColor}">${details.status.charAt(0).toUpperCase() + details.status.slice(1)}</span></p>
                                </div>
                            </div>
                            
                            <hr class="my-4" style="border-color: rgba(255,255,255,0.2);">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-white mb-3"><i class="bi bi-currency-euro me-2"></i>Détails Financiers</h6>
                                    <p class="mb-2"><strong>Montant à rembourser:</strong> <span class="text-info">${parseFloat(details.amount_to_reimburse).toFixed(2)}€</span></p>
                                    <p class="mb-2"><strong>Remboursement effectué:</strong> <span class="text-success">${parseFloat(details.reimbursement_amount).toFixed(2)}€</span></p>
                                    <p class="mb-3"><strong>Reste à rembourser:</strong> <span class="text-warning">${parseFloat(details.remaining_amount).toFixed(2)}€</span></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-white mb-3"><i class="bi bi-credit-card me-2"></i>Moyen de Paiement</h6>
                                    <p class="mb-2"><strong>Type:</strong> ${details.payment_method === 'carte_recharge' ? 'Carte de recharge' : 
                                                                           details.payment_method === 'code_rechargement' ? 'Code de rechargement' : 
                                                                           'Carte bancaire'}</p>
                                    <div class="mb-3">${paymentDetails}</div>
                                </div>
                            </div>
                        `;
                        
                        // Afficher la modal
                        const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
                        modal.show();
                    } else {
                        alert('Erreur lors du chargement des détails: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors du chargement des détails');
                });
        }
    </script>
</body>
</html>