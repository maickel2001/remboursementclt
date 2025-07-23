/*
  # Base de données complète RemboursePRO pour Hostinger

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

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
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
CREATE TABLE IF NOT EXISTS reimbursements (
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
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_name VARCHAR(100) DEFAULT 'RemboursePRO',
    contact_email VARCHAR(100) DEFAULT 'contact@remboursepro.com',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Suppression des anciens comptes de test s'ils existent
DELETE FROM users WHERE email IN ('admin@remboursepro.com', 'client@test.com');

-- Insertion des paramètres par défaut
INSERT INTO site_settings (site_name, contact_email) 
VALUES ('RemboursePRO', 'contact@remboursepro.com')
ON DUPLICATE KEY UPDATE site_name = site_name;

-- Admin par défaut (mot de passe: admin123)
INSERT INTO users (firstName, lastName, email, password, role) 
VALUES ('Admin', 'Principal', 'admin@remboursepro.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHuFEDcyBp/H6Jkd/cJbr0.JEv/vKDvJ5u', 'admin');

-- Client de test (mot de passe: client123)
INSERT INTO users (firstName, lastName, email, password, phone, address, role) 
VALUES ('Jean', 'Dupont', 'client@test.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHuFEDcyBp/H6Jkd/cJbr0.JEv/vKDvJ5u', '+33 6 12 34 56 78', '123 Rue de la Paix, 75001 Paris', 'client');

-- Index pour optimiser les performances
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_reimbursements_user ON reimbursements(user_id);
CREATE INDEX IF NOT EXISTS idx_reimbursements_status ON reimbursements(status);
CREATE INDEX IF NOT EXISTS idx_reimbursements_created ON reimbursements(created_at);

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
AND NOT EXISTS (SELECT 1 FROM reimbursements WHERE user_id = u.id)
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
AND (SELECT COUNT(*) FROM reimbursements WHERE user_id = u.id) < 2
LIMIT 1;