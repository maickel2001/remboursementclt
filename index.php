<?php
// Gestion des erreurs pour Hostinger
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Vérifier que les fichiers existent avant de les inclure
if (!file_exists(__DIR__ . '/config/auth.php')) {
    die('Erreur: Fichier config/auth.php manquant');
}

require_once __DIR__ . '/config/auth.php';

// Récupérer l'utilisateur actuel s'il est connecté
$currentUser = null;
if (isLoggedIn()) {
    $currentUser = getCurrentUser();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RemboursePRO - Plateforme de Gestion des Remboursements</title>
    
    <!-- Bootstrap 5.3.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-glass fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-shield-check me-2"></i>RemboursePRO
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#calculator">Calculateur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#contact">Contact</a>
                    </li>
                    <?php if ($currentUser): ?>
                        <li class="nav-item">
                            <a class="nav-link btn btn-glass ms-2" href="<?= $currentUser['role'] === 'admin' ? 'admin/dashboard.php' : 'client/dashboard.php' ?>">
                                <i class="bi bi-speedometer2 me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white ms-2" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link btn btn-glass ms-2" href="login.php">Connexion</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-gradient ms-2" href="register.php">Inscription</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="hero-content">
                        <h1 class="hero-title">Remboursements Sécurisés et Rapides</h1>
                        <p class="hero-subtitle">
                            Gérez vos remboursements en toute sécurité avec notre plateforme professionnelle. 
                            Transparence, rapidité et fiabilité garanties.
                        </p>
                        <div class="d-flex gap-3 flex-wrap">
                            <?php if ($currentUser): ?>
                                <a href="<?= $currentUser['role'] === 'admin' ? 'admin/dashboard.php' : 'client/dashboard.php' ?>" class="btn btn-gradient">
                                    <i class="bi bi-speedometer2 me-2"></i>Mon Dashboard
                                </a>
                            <?php else: ?>
                                <a href="register.php" class="btn btn-gradient">
                                    <i class="bi bi-person-plus me-2"></i>Commencer maintenant
                                </a>
                                <a href="login.php" class="btn btn-glass">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="text-center">
                        <div class="hero-image">
                            <i class="bi bi-credit-card" style="font-size: 15rem; color: rgba(255,255,255,0.8);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12" data-aos="fade-up">
                    <h2 class="display-4 fw-bold text-white mb-4">Nos Services</h2>
                    <p class="lead text-white-50">Une plateforme complète pour tous vos besoins de remboursement</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4 class="text-white mb-3">Sécurité Maximale</h4>
                        <p class="text-white-50">
                            Vos données sont protégées par un système de chiffrement de niveau bancaire. 
                            Toutes les transactions sont sécurisées.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-lightning-charge"></i>
                        </div>
                        <h4 class="text-white mb-3">Rapidité</h4>
                        <p class="text-white-50">
                            Traitements ultra-rapides de vos demandes. La plupart des remboursements 
                            sont traités en moins de 24h.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-headset"></i>
                        </div>
                        <h4 class="text-white mb-3">Support 24/7</h4>
                        <p class="text-white-50">
                            Notre équipe de support est disponible 24h/24 et 7j/7 pour vous accompagner 
                            dans toutes vos démarches.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Calculator Section -->
    <section id="calculator" class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8" data-aos="fade-up">
                    <div class="calculator-card">
                        <h3 class="text-center text-white mb-4">
                            <i class="bi bi-calculator me-2"></i>Calculateur de Remboursement
                        </h3>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="totalAmount" class="form-label text-white">Montant à rembourser (€)</label>
                                <input type="number" class="form-control form-control-glass" id="totalAmount" 
                                       placeholder="0.00" min="0" step="0.01">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="reimbursementAmount" class="form-label text-white">Remboursement à effectuer (€)</label>
                                <input type="number" class="form-control form-control-glass" id="reimbursementAmount" 
                                       placeholder="0.00" min="0" step="0.01">
                            </div>
                        </div>
                        
                        <div class="calculator-result mt-4" id="calculatorResult" style="display: none;">
                            <h4 class="mb-3">Résultat du calcul</h4>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <div class="h5 mb-1" id="displayTotal">0.00 €</div>
                                        <small>Total à rembourser</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <div class="h5 mb-1" id="displayReimbursement">0.00 €</div>
                                        <small>Remboursement</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <div class="h5 mb-1" id="displayRemaining">0.00 €</div>
                                        <small>Reste à rembourser</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($currentUser): ?>
                            <div class="text-center mt-4">
                                <a href="client/remboursement.php" class="btn btn-gradient">
                                    <i class="bi bi-credit-card me-2"></i>Effectuer un remboursement
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="text-center mt-4">
                                <p class="text-white-50 mb-3">Connectez-vous pour effectuer un remboursement</p>
                                <a href="register.php" class="btn btn-gradient me-2">S'inscrire</a>
                                <a href="login.php" class="btn btn-glass">Se connecter</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8" data-aos="fade-up">
                    <div class="text-center mb-5">
                        <h2 class="display-4 fw-bold text-white mb-4">Contactez-nous</h2>
                        <p class="lead text-white-50">Notre équipe est là pour vous aider</p>
                    </div>
                    
                    <div class="row g-4">
                        <div class="col-md-4 text-center" data-aos="fade-up" data-aos-delay="100">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="bi bi-envelope"></i>
                                </div>
                                <h5 class="text-white">Email</h5>
                                <p class="text-white-50">contact@remboursepro.com</p>
                            </div>
                        </div>
                        
                        <div class="col-md-4 text-center" data-aos="fade-up" data-aos-delay="200">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="bi bi-telephone"></i>
                                </div>
                                <h5 class="text-white">Téléphone</h5>
                                <p class="text-white-50">+33 1 23 45 67 89</p>
                            </div>
                        </div>
                        
                        <div class="col-md-4 text-center" data-aos="fade-up" data-aos-delay="300">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="bi bi-chat-dots"></i>
                                </div>
                                <h5 class="text-white">Chat Live</h5>
                                <p class="text-white-50">Disponible 24h/24</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="text-center">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <p class="mb-0">&copy; 2024 RemboursePRO. Tous droits réservés.</p>
                    <p class="small mb-0">Plateforme sécurisée de gestion des remboursements</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true
        });

        // Calculator functionality
        function updateCalculator() {
            const totalAmount = parseFloat(document.getElementById('totalAmount').value) || 0;
            const reimbursementAmount = parseFloat(document.getElementById('reimbursementAmount').value) || 0;
            const remainingAmount = totalAmount - reimbursementAmount;
            
            if (totalAmount > 0 && reimbursementAmount >= 0) {
                document.getElementById('displayTotal').textContent = totalAmount.toFixed(2) + ' €';
                document.getElementById('displayReimbursement').textContent = reimbursementAmount.toFixed(2) + ' €';
                document.getElementById('displayRemaining').textContent = Math.max(0, remainingAmount).toFixed(2) + ' €';
                document.getElementById('calculatorResult').style.display = 'block';
            } else {
                document.getElementById('calculatorResult').style.display = 'none';
            }
        }

        document.getElementById('totalAmount').addEventListener('input', updateCalculator);
        document.getElementById('reimbursementAmount').addEventListener('input', updateCalculator);
        
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>