<?php
// Gestion des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Démarrer la session de manière sécurisée
if (session_status() === PHP_SESSION_NONE) {
    if (!session_start()) {
        die('Erreur: Impossible de démarrer la session');
    }
}

// Fonctions d'authentification et de sécurité
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit();
    }
    return true;
}

function checkAdmin() {
    checkLogin();
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        header('Location: /error_pages/403.php');
        exit();
    }
    return true;
}

function checkClient() {
    checkLogin();
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'client') {
        header('Location: /error_pages/403.php');
        exit();
    }
    return true;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    try {
        if (!file_exists(__DIR__ . '/database.php')) {
            return null;
        }
        require_once __DIR__ . '/database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        if (!$db) {
            return null;
        }
        
        $query = "SELECT * FROM users WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $_SESSION['user_id']);
        $stmt->execute();
        
        $user = $stmt->fetch();
        return $user;
    } catch (Exception $e) {
        return null;
    }
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password) {
    // Au moins 8 caractères, une majuscule, une minuscule, un chiffre
    return strlen($password) >= 8 && 
           preg_match('/[A-Z]/', $password) && 
           preg_match('/[a-z]/', $password) && 
           preg_match('/[0-9]/', $password);
}

function logLoginAttempt($email, $success, $ip) {
    // Log simple pour éviter les erreurs
    $logEntry = date('Y-m-d H:i:s') . " - Login attempt: $email - " . ($success ? 'SUCCESS' : 'FAILED') . " - IP: $ip\n";
    @file_put_contents(__DIR__ . '/../logs/login.log', $logEntry, FILE_APPEND | LOCK_EX);
}

function sendEmail($to, $subject, $message) {
    // Configuration email simple
    $headers = "From: RemboursePRO <noreply@remboursepro.com>\r\n";
    $headers .= "Reply-To: contact@remboursepro.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return @mail($to, $subject, $message, $headers);
}

// Fonction de test pour vérifier les mots de passe
function testPasswords() {
    // Test avec les vrais hashs utilisés dans la base
    $testPasswords = [
        'admin123' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'client123' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
    ];
    
    $results = [];
    foreach ($testPasswords as $password => $hash) {
        if (password_verify($password, $hash)) {
            $results[] = "✅ Hash valide pour '$password'";
        } else {
            $results[] = "❌ Hash invalide pour '$password'";
        }
    }
    
    return implode('<br>', $results);
}
?>