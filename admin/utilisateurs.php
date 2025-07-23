<?php
require_once '../config/auth.php';
require_once '../config/database.php';

checkAdmin();
$currentUser = getCurrentUser();

$error = '';
$success = '';

// Traitement des actions (promouvoir/rétrograder)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = sanitizeInput($_POST['action']);
    $userId = intval($_POST['user_id'] ?? 0);
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Token de sécurité invalide.';
    } elseif ($userId <= 0) {
        $error = 'ID utilisateur invalide.';
    } elseif ($userId == $currentUser['id']) {
        $error = 'Vous ne pouvez pas modifier votre propre rôle.';
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $newRole = '';
            switch ($action) {
                case 'promote':
                    $newRole = 'admin';
                    break;
                case 'demote':
                    $newRole = 'client';
                    break;
                default:
                    $error = 'Action invalide.';
            }
            
            if (!$error) {
                $query = "UPDATE users SET role = :role WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':role', $newRole);
                $stmt->bindParam(':id', $userId);
                
                if ($stmt->execute()) {
                    $success = "Rôle utilisateur mis à jour avec succès : $newRole";
                } else {
                    $error = 'Erreur lors de la mise à jour du rôle.';
                }
            }
        } catch (Exception $e) {
            $error = 'Erreur de connexion à la base de données.';
        }
    }
}

// Pagination et filtres
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;
$roleFilter = isset($_GET['role']) ? sanitizeInput($_GET['role']) : '';
$searchFilter = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

$users = [];
$stats = [
    'total_users' => 0,
    'total_clients' => 0,
    'total_admins' => 0
];
$totalRecords = 0;
$totalPages = 1;

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Construire la requête avec filtres
    $whereClause = "WHERE 1=1";
    $params = [];
    
    if ($roleFilter) {
        $whereClause .= " AND role = :role";
        $params[':role'] = $roleFilter;
    }
    
    if ($searchFilter) {
        $whereClause .= " AND (firstName LIKE :search OR lastName LIKE :search OR email LIKE :search)";
        $params[':search'] = "%$searchFilter%";
    }
    
    // Compter le total pour la pagination
    $countQuery = "SELECT COUNT(*) as total FROM users $whereClause";
    $countStmt = $db->prepare($countQuery);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalRecords = $countStmt->fetch()['total'];
    $totalPages = ceil($totalRecords / $limit);
    
    // Récupérer les utilisateurs avec statistiques
    $query = "SELECT u.*, 
                     (SELECT COUNT(*) FROM reimbursements WHERE user_id = u.id) as total_reimbursements,
                     (SELECT COALESCE(SUM(amount_to_reimburse), 0) FROM reimbursements WHERE user_id = u.id) as total_amount
             FROM users u 
             $whereClause 
             ORDER BY u.created_at DESC 
             LIMIT :limit OFFSET :offset";
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    // Statistiques globales
    $statsQuery = "SELECT 
                    COUNT(*) as total_users,
                    COUNT(CASE WHEN role = 'client' THEN 1 END) as total_clients,
                    COUNT(CASE WHEN role = 'admin' THEN 1 END) as total_admins";
    $statsStmt = $db->prepare($statsQuery);
    $statsStmt->execute();
    $stats = $statsStmt->fetch();
    
} catch (Exception $e) {
    // En cas d'erreur, garder les valeurs par défaut
    error_log('Erreur utilisateurs: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - RemboursePRO</title>
    
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
                            <a class="sidebar-item" href="remboursements.php">
                                <i class="bi bi-credit-card me-2"></i>Remboursements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="sidebar-item active" href="utilisateurs.php">
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
                            <i class="bi bi-people me-3"></i>Gestion des Utilisateurs
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

                    <!-- Statistics Cards -->
                    <div class="row g-4 mb-4">
                        <div class="col-lg-4 col-md-6">
                            <div class="stats-card">
                                <div class="stats-value"><?= $stats['total_users'] ?></div>
                                <div class="stats-label">
                                    <i class="bi bi-people me-2"></i>Total Utilisateurs
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-md-6">
                            <div class="stats-card">
                                <div class="stats-value"><?= $stats['total_clients'] ?></div>
                                <div class="stats-label">
                                    <i class="bi bi-person me-2"></i>Clients
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-md-6">
                            <div class="stats-card">
                                <div class="stats-value"><?= $stats['total_admins'] ?></div>
                                <div class="stats-label">
                                    <i class="bi bi-person-gear me-2"></i>Administrateurs
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtres -->
                    <div class="glass p-3 rounded-3 mb-4">
                        <form method="GET" action="" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="role" class="form-label text-white">
                                    <i class="bi bi-funnel me-2"></i>Rôle
                                </label>
                                <select class="form-control form-control-glass" id="role" name="role">
                                    <option value="">Tous les rôles</option>
                                    <option value="client" <?= $roleFilter === 'client' ? 'selected' : '' ?>>Client</option>
                                    <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="search" class="form-label text-white">
                                    <i class="bi bi-search me-2"></i>Rechercher
                                </label>
                                <input type="text" class="form-control form-control-glass" id="search" name="search" 
                                       placeholder="Nom, prénom ou email" value="<?= htmlspecialchars($searchFilter) ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-gradient w-100">
                                    <i class="bi bi-search me-2"></i>Filtrer
                                </button>
                            </div>
                            <?php if ($roleFilter || $searchFilter): ?>
                                <div class="col-md-2">
                                    <a href="utilisateurs.php" class="btn btn-glass w-100">
                                        <i class="bi bi-x-circle me-2"></i>Réinitialiser
                                    </a>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- Tableau des utilisateurs -->
                    <div class="glass p-4 rounded-3">
                        <?php if (empty($users)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-people" style="font-size: 4rem; color: rgba(255,255,255,0.3);"></i>
                                <h4 class="text-white mt-3">Aucun utilisateur trouvé</h4>
                                <p class="text-white-50">
                                    <?= ($roleFilter || $searchFilter) ? 'Aucun utilisateur ne correspond aux critères de recherche.' : 'Aucun utilisateur inscrit pour le moment.' ?>
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="text-white mb-0">
                                    <i class="bi bi-list-check me-2"></i>Liste des Utilisateurs
                                </h4>
                                <span class="text-white-50">
                                    <?= $totalRecords ?> utilisateur(s) trouvé(s)
                                </span>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-glass">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Utilisateur</th>
                                            <th>Email</th>
                                            <th>Téléphone</th>
                                            <th>Rôle</th>
                                            <th>Inscription</th>
                                            <th>Statistiques</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td>#<?= $user['id'] ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($user['profile_picture']): ?>
                                                            <img src="../uploads/profiles/<?= htmlspecialchars($user['profile_picture']) ?>" 
                                                                 alt="Photo" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                        <?php else: ?>
                                                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-2" 
                                                                 style="width: 40px; height: 40px;">
                                                                <i class="bi bi-person text-white"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <strong><?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?></strong>
                                                            <?php if ($user['id'] == $currentUser['id']): ?>
                                                                <span class="badge bg-info ms-1">Vous</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($user['email']) ?></td>
                                                <td><?= htmlspecialchars($user['phone'] ?? 'Non renseigné') ?></td>
                                                <td>
                                                    <span class="badge <?= $user['role'] === 'admin' ? 'bg-danger' : 'bg-primary' ?>">
                                                        <?= ucfirst($user['role']) ?>
                                                    </span>
                                                </td>
                                                <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                                <td>
                                                    <small class="text-white-50">
                                                        <?= $user['total_reimbursements'] ?> demande(s)<br>
                                                        <?= number_format($user['total_amount'], 0) ?>€ total
                                                    </small>
                                                </td>
                                                <td>
                                                    <?php if ($user['id'] != $currentUser['id']): ?>
                                                        <div class="btn-group" role="group">
                                                            <?php if ($user['role'] === 'client'): ?>
                                                                <form method="POST" style="display: inline;">
                                                                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                                                    <input type="hidden" name="action" value="promote">
                                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                                    <button type="submit" class="btn btn-warning btn-sm" 
                                                                            onclick="return confirm('Promouvoir cet utilisateur en administrateur ?')"
                                                                            title="Promouvoir en admin">
                                                                        <i class="bi bi-arrow-up-circle"></i>
                                                                    </button>
                                                                </form>
                                                            <?php else: ?>
                                                                <form method="POST" style="display: inline;">
                                                                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                                                    <input type="hidden" name="action" value="demote">
                                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                                    <button type="submit" class="btn btn-secondary btn-sm" 
                                                                            onclick="return confirm('Rétrograder cet administrateur en client ?')"
                                                                            title="Rétrograder en client">
                                                                        <i class="bi bi-arrow-down-circle"></i>
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>
                                                            
                                                            <button class="btn btn-glass btn-sm" onclick="showUserDetails(<?= $user['id'] ?>)"
                                                                    title="Voir les détails">
                                                                <i class="bi bi-eye"></i>
                                                            </button>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-white-50 small">Votre compte</span>
                                                    <?php endif; ?>
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
                                                   href="?page=<?= $page - 1 ?><?= $roleFilter ? '&role=' . $roleFilter : '' ?><?= $searchFilter ? '&search=' . urlencode($searchFilter) : '' ?>">
                                                    <i class="bi bi-chevron-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                                <a class="page-link bg-transparent border-white text-white" 
                                                   href="?page=<?= $i ?><?= $roleFilter ? '&role=' . $roleFilter : '' ?><?= $searchFilter ? '&search=' . urlencode($searchFilter) : '' ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link bg-transparent border-white text-white" 
                                                   href="?page=<?= $page + 1 ?><?= $roleFilter ? '&role=' . $roleFilter : '' ?><?= $searchFilter ? '&search=' . urlencode($searchFilter) : '' ?>">
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
        function showUserDetails(id) {
            // Ici vous pouvez implémenter une modal avec tous les détails de l'utilisateur
            alert('Détails complets de l\'utilisateur ID: ' + id + '\n\nFonctionnalité à implémenter avec modal Bootstrap.');
        }
    </script>
</body>
</html>