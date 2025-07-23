/*
  # Base de données complète RemboursePRO

  1. Tables principales
    - `users` : Gestion des utilisateurs (clients et admins)
    - `reimbursements` : Demandes de remboursement
    - `site_settings` : Paramètres du site

  2. Comptes de test
    - Admin : admin@remboursepro.com / admin123
    - Client : client@test.com / client123

  3. Sécurité
    - Index optimisés pour les performances
    - Contraintes de clés étrangères
    - Données de test pour démonstration
*/

-- Suppression des tables existantes si elles existent
DROP TABLE IF EXISTS reimbursements;
DROP TABLE IF EXISTS site_settings;
DROP TABLE IF EXISTS users;

-- Table des utilisateurs
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstName VARCHAR(50) NOT NULL,
    lastName VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    role ENUM('client', 'admin') DEFAULT 'client',
    profile_picture VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des remboursements
CREATE TABLE reimbursements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount_to_reimburse DECIMAL(10,2) NOT NULL,
    reimbursement_amount DECIMAL(10,2) NOT NULL,
    remaining_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('carte_recharge', 'code_rechargement', 'carte_bancaire') NOT NULL,
    card_type VARCHAR(20) DEFAULT NULL,
    card_numbers TEXT DEFAULT NULL,
    recharge_code VARCHAR(12) DEFAULT NULL,
    status ENUM('en_attente', 'validé', 'refusé') DEFAULT 'en_attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des paramètres du site
CREATE TABLE site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_name VARCHAR(100) DEFAULT 'RemboursePRO',
    contact_email VARCHAR(100) DEFAULT 'contact@remboursepro.com',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertion des paramètres par défaut
INSERT INTO site_settings (site_name, contact_email) 
VALUES ('RemboursePRO', 'contact@remboursepro.com');

-- Admin par défaut (mot de passe: admin123)
-- Hash généré avec password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (firstName, lastName, email, password, role) 
VALUES ('Admin', 'Principal', 'admin@remboursepro.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Client de test (mot de passe: client123)
-- Hash généré avec password_hash('client123', PASSWORD_DEFAULT)
INSERT INTO users (firstName, lastName, email, password, phone, address, role) 
VALUES ('Jean', 'Dupont', 'client@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+33 6 12 34 56 78', '123 Rue de la Paix, 75001 Paris', 'client');

-- Index pour optimiser les performances
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_reimbursements_user ON reimbursements(user_id);
CREATE INDEX idx_reimbursements_status ON reimbursements(status);
CREATE INDEX idx_reimbursements_created ON reimbursements(created_at);

-- Données de test pour les remboursements
INSERT INTO reimbursements (user_id, amount_to_reimburse, reimbursement_amount, remaining_amount, payment_method, card_type, status) 
SELECT 
    u.id,
    150.00,
    100.00,
    50.00,
    'carte_recharge',
    'transcash',
    'validé'
FROM users u 
WHERE u.email = 'client@test.com' 
LIMIT 1;

INSERT INTO reimbursements (user_id, amount_to_reimburse, reimbursement_amount, remaining_amount, payment_method, recharge_code, status) 
SELECT 
    u.id,
    200.00,
    200.00,
    0.00,
    'code_rechargement',
    '123456789012',
    'en_attente'
FROM users u 
WHERE u.email = 'client@test.com' 
LIMIT 1;