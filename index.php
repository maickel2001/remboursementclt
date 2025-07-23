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
    
    <!-- CSS INTÉGRÉ DIRECTEMENT -->
    <style>
        /* FORCE L'ARRIÈRE-PLAN SOMBRE PARTOUT */
        * {
            box-sizing: border-box;
        }
        
        html, body {
            background: #0f172a !important;
            background-color: #0f172a !important;
            color: #ffffff !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
            min-height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Tous les conteneurs */
        .container, .container-fluid, .row, .col, [class*="col-"] {
            background: transparent !important;
            color: #ffffff !important;
        }

        /* Navigation */
        .navbar {
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

        /* Hero section */
        .hero-section {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%) !important;
            min-height: 100vh !important;
            display: flex !important;
            align-items: center !important;
            padding: 80px 0 !important;
        }

        .hero-title {
            font-size: 3.5rem !important;
            font-weight: 700 !important;
            color: #ffffff !important;
            margin-bottom: 1.5rem !important;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3) !important;
        }

        .hero-subtitle {
            color: rgba(255, 255, 255, 0.9) !important;
            font-size: 1.3rem !important;
            margin-bottom: 2rem !important;
            line-height: 1.6 !important;
        }

        /* Cards glassmorphism */
        .glass, .feature-card, .calculator-card {
            background: rgba(15, 23, 42, 0.8) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 16px !important;
            padding: 2rem !important;
            backdrop-filter: blur(20px) !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3) !important;
            color: #ffffff !important;
            margin-bottom: 2rem !important;
        }

        /* Titres */
        h1, h2, h3, h4, h5, h6 {
            color: #ffffff !important;
            font-weight: 600 !important;
            margin-bottom: 1rem !important;
        }

        /* Textes */
        p, span, div, label, small {
            color: #ffffff !important;
        }

        .text-white-50 {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        /* Boutons */
        .btn {
            border-radius: 12px !important;
            padding: 12px 24px !important;
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
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.6) !important;
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
            transform: translateY(-2px) !important;
        }

        /* Formulaires */
        .form-control, .form-select, input, textarea, select {
            background: rgba(15, 23, 42, 0.8) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: #ffffff !important;
            border-radius: 8px !important;
            padding: 12px 16px !important;
        }

        .form-control:focus, .form-select:focus, input:focus, textarea:focus {
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

        /* Feature icons */
        .feature-icon {
            font-size: 4rem !important;
            color: #3b82f6 !important;
            margin-bottom: 1.5rem !important;
            text-align: center !important;
        }

        /* Alertes */
        .alert {
            border-radius: 12px !important;
            border: none !important;
            padding: 1rem 1.5rem !important;
            margin-bottom: 1rem !important;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.2) !important;
            color: #ffffff !important;
            border: 1px solid rgba(34, 197, 94, 0.4) !important;
        }

        .alert-info {
            background: rgba(59, 130, 246, 0.2) !important;
            color: #ffffff !important;
            border: 1px solid rgba(59, 130, 246, 0.4) !important;
        }

        /* Footer */
        footer {
            background: rgba(15, 23, 42, 0.9) !important;
            color: #ffffff !important;
            padding: 3rem 0 !important;
            margin-top: 5rem !important;
            border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        /* Calculateur */
        .calculator-result {
            background: rgba(34, 197, 94, 0.2) !important;
            border: 1px solid rgba(34, 197, 94, 0.4) !important;
            border-radius: 12px !important;
            padding: 1.5rem !important;
            margin-top: 1.5rem !important;
            color: #ffffff !important;
        }

        /* Liens */
        a {
            color: #60a5fa !important;
            text-decoration: none !important;
        }

        a:hover {
            color: #93c5fd !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem !important;
            }
            
            .hero-subtitle {
                font-size: 1.1rem !important;
            }
            
            .feature-card, .calculator-card {
                padding: 1.5rem !important;
            }
            
            body {
                padding-top: 70px !important;
            }
        }

        /* Navbar fixed */
        .navbar.fixed-top {
            position: fixed !important;
            top: 0 !important;
            width: 100% !important;
            z-index: 1030 !important;
        }

        body {
            padding-top: 80px !important;
        }

        /* Hero image */
        .hero-image i {
            opacity: 0.8 !important;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3)) !important;
        }

        /* Sections */
        section {
            padding: 5rem 0 !important;
        }

        /* Navbar toggler */
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
    <nav class="navbar navbar-expand-lg fixed-top">
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
                        <a class="nav-link" href="#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#calculator">Calculateur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <?php if ($currentUser): ?>
                        <li class="nav-item">
                            <a class="nav-link btn btn-glass ms-2" href="<?= $currentUser['role'] === 'admin' ? 'admin/dashboard.php' : 'client/dashboard.php' ?>">
                                <i class="bi bi-speedometer2 me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link ms-2" href="logout.php">
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
                            <i class="bi bi-credit-card" style="font-size: 12rem; color: rgba(59, 130, 246, 0.8);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12" data-aos="fade-up">
                    <h2 class="display-4 fw-bold mb-4">Nos Services</h2>
                    <p class="lead text-white-50">Une plateforme complète pour tous vos besoins de remboursement</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4 class="mb-3">Sécurité Maximale</h4>
                        <p class="text-white-50">
                            Vos données sont protégées par un système de chiffrement de niveau bancaire. 
                            Toutes les transactions sont sécurisées.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="bi bi-lightning-charge"></i>
                        </div>
                        <h4 class="mb-3">Rapidité</h4>
                        <p class="text-white-50">
                            Traitements ultra-rapides de vos demandes. La plupart des remboursements 
                            sont traités en moins de 24h.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="bi bi-headset"></i>
                        </div>
                        <h4 class="mb-3">Support 24/7</h4>
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
    <section id="calculator">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8" data-aos="fade-up">
                    <div class="calculator-card">
                        <h3 class="text-center mb-4">
                            <i class="bi bi-calculator me-2"></i>Calculateur de Remboursement
                        </h3>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="totalAmount" class="form-label">Montant à rembourser (€)</label>
                                <input type="number" class="form-control" id="totalAmount" 
                                       placeholder="0.00" min="0" step="0.01">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="reimbursementAmount" class="form-label">Remboursement à effectuer (€)</label>
                                <input type="number" class="form-control" id="reimbursementAmount" 
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
    <section id="contact">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8" data-aos="fade-up">
                    <div class="text-center mb-5">
                        <h2 class="display-4 fw-bold mb-4">Contactez-nous</h2>
                        <p class="lead text-white-50">Notre équipe est là pour vous aider</p>
                    </div>
                    
                    <div class="row g-4">
                        <div class="col-md-4 text-center" data-aos="fade-up" data-aos-delay="100">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="bi bi-envelope"></i>
                                </div>
                                <h5>Email</h5>
                                <p class="text-white-50">contact@remboursepro.com</p>
                            </div>
                        </div>
                        
                        <div class="col-md-4 text-center" data-aos="fade-up" data-aos-delay="200">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="bi bi-telephone"></i>
                                </div>
                                <h5>Téléphone</h5>
                                <p class="text-white-50">+33 1 23 45 67 89</p>
                            </div>
                        </div>
                        
                        <div class="col-md-4 text-center" data-aos="fade-up" data-aos-delay="300">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="bi bi-chat-dots"></i>
                                </div>
                                <h5>Chat Live</h5>
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

        // Effet de particules en arrière-plan
        function createParticles() {
            const particlesContainer = document.createElement('div');
            particlesContainer.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                pointer-events: none;
                z-index: -1;
                overflow: hidden;
            `;
            document.body.appendChild(particlesContainer);

            for (let i = 0; i < 50; i++) {
                const particle = document.createElement('div');
                particle.style.cssText = `
                    position: absolute;
                    width: 2px;
                    height: 2px;
                    background: rgba(59, 130, 246, 0.3);
                    border-radius: 50%;
                    animation: float ${Math.random() * 10 + 10}s infinite linear;
                    left: ${Math.random() * 100}%;
                    top: ${Math.random() * 100}%;
                `;
                particlesContainer.appendChild(particle);
            }

            // CSS pour l'animation des particules
            const style = document.createElement('style');
            style.textContent = `
                @keyframes float {
                    0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
                    10% { opacity: 1; }
                    90% { opacity: 1; }
                    100% { transform: translateY(-100vh) rotate(360deg); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }

        // Effet de curseur personnalisé
        function createCustomCursor() {
            const cursor = document.createElement('div');
            cursor.style.cssText = `
                position: fixed;
                width: 20px;
                height: 20px;
                background: radial-gradient(circle, rgba(59, 130, 246, 0.8) 0%, transparent 70%);
                border-radius: 50%;
                pointer-events: none;
                z-index: 9999;
                transition: transform 0.1s ease;
            `;
            document.body.appendChild(cursor);

            document.addEventListener('mousemove', (e) => {
                cursor.style.left = e.clientX - 10 + 'px';
                cursor.style.top = e.clientY - 10 + 'px';
            });

            // Effet au survol des boutons
            document.querySelectorAll('.btn').forEach(btn => {
                btn.addEventListener('mouseenter', () => {
                    cursor.style.transform = 'scale(2)';
                    cursor.style.background = 'radial-gradient(circle, rgba(139, 92, 246, 0.8) 0%, transparent 70%)';
                });
                btn.addEventListener('mouseleave', () => {
                    cursor.style.transform = 'scale(1)';
                    cursor.style.background = 'radial-gradient(circle, rgba(59, 130, 246, 0.8) 0%, transparent 70%)';
                });
            });
        }

        // Effet de typing pour le titre
        function typeWriter(element, text, speed = 100) {
            let i = 0;
            element.innerHTML = '';
            function type() {
                if (i < text.length) {
                    element.innerHTML += text.charAt(i);
                    i++;
                    setTimeout(type, speed);
                }
            }
            type();
        }

        // Initialiser les effets
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            createCustomCursor();
            
            // Effet typing sur le titre principal
            const heroTitle = document.querySelector('.hero-title');
            if (heroTitle) {
                const originalText = heroTitle.textContent;
                setTimeout(() => {
                    typeWriter(heroTitle, originalText, 80);
                }, 1000);
            }
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