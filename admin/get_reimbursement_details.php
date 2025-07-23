<?php
require_once '../config/auth.php';
require_once '../config/database.php';

// Vérifier que c'est un admin
checkAdmin();

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID invalide']);
    exit;
}

$reimbursementId = intval($_GET['id']);

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Récupérer les détails du remboursement avec les informations utilisateur
    $query = "SELECT r.*, u.firstName, u.lastName, u.email, u.phone, u.address 
              FROM reimbursements r 
              JOIN users u ON r.user_id = u.id 
              WHERE r.id = :id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $reimbursementId);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $result = $stmt->fetch();
        
        // Séparer les données utilisateur et remboursement
        $reimbursement = [
            'id' => $result['id'],
            'user_id' => $result['user_id'],
            'amount_to_reimburse' => $result['amount_to_reimburse'],
            'reimbursement_amount' => $result['reimbursement_amount'],
            'remaining_amount' => $result['remaining_amount'],
            'payment_method' => $result['payment_method'],
            'card_type' => $result['card_type'],
            'card_numbers' => $result['card_numbers'],
            'recharge_code' => $result['recharge_code'],
            'status' => $result['status'],
            'created_at' => $result['created_at'],
            'updated_at' => $result['updated_at']
        ];
        
        $user = [
            'firstName' => $result['firstName'],
            'lastName' => $result['lastName'],
            'email' => $result['email'],
            'phone' => $result['phone'],
            'address' => $result['address']
        ];
        
        echo json_encode([
            'success' => true,
            'reimbursement' => $reimbursement,
            'user' => $user
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Remboursement non trouvé']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
}
?>