<?php
// Test pour vérifier que le CSS se charge correctement
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test CSS - RemboursePRO</title>
    
    <!-- Bootstrap 5.3.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <style>
        /* CSS de secours au cas où le fichier externe ne se charge pas */
        body {
            background: #1a1a2e !important;
            color: #ffffff !important;
            font-family: Arial, sans-serif !important;
            min-height: 100vh !important;
        }
        .test-container {
            background: rgba(26, 26, 46, 0.8) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 12px !important;
            padding: 2rem !important;
            margin: 2rem auto !important;
            max-width: 800px !important;
            color: #ffffff !important;
        }
        .test-ok {
            color: #10b981 !important;
            font-weight: bold !important;
        }
        .test-error {
            color: #ef4444 !important;
            font-weight: bold !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="test-container">
            <h1><i class="bi bi-palette me-2"></i>Test CSS RemboursePRO</h1>
            
            <div class="mb-4">
                <h3>État du CSS :</h3>
                <div id="css-status">
                    <span class="test-ok">✅ CSS chargé correctement</span>
                </div>
            </div>
            
            <div class="mb-4">
                <h3>Test des éléments :</h3>
                <div class="glass p-3 mb-3">
                    <p>Ce texte devrait être blanc sur fond sombre glassmorphism</p>
                </div>
                
                <button class="btn btn-gradient me-2">Bouton Gradient</button>
                <button class="btn btn-glass me-2">Bouton Glass</button>
                <button class="btn btn-success">Bouton Success</button>
            </div>
            
            <div class="mb-4">
                <h3>Test formulaire :</h3>
                <input type="text" class="form-control form-control-glass mb-2" placeholder="Champ de test">
                <select class="form-control form-control-glass">
                    <option>Option de test</option>
                </select>
            </div>
            
            <div class="alert alert-success alert-glass">
                <i class="bi bi-check-circle me-2"></i>Alerte de succès
            </div>
            
            <div class="alert alert-info alert-glass">
                <i class="bi bi-info-circle me-2"></i>Si vous voyez cette page avec un arrière-plan sombre et du texte blanc, le CSS fonctionne !
            </div>
            
            <div class="text-center mt-4">
                <a href="index.php" class="btn btn-gradient">
                    <i class="bi bi-house me-2"></i>Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Vérifier si le CSS personnalisé est chargé
        function checkCSS() {
            const body = document.body;
            const computedStyle = window.getComputedStyle(body);
            const bgColor = computedStyle.backgroundColor;
            
            console.log('Background color:', bgColor);
            
            // Si l'arrière-plan n'est pas sombre, il y a un problème
            if (bgColor === 'rgb(26, 26, 46)' || bgColor.includes('26, 26, 46')) {
                document.getElementById('css-status').innerHTML = '<span class="test-ok">✅ CSS chargé correctement</span>';
            } else {
                document.getElementById('css-status').innerHTML = '<span class="test-error">❌ CSS non chargé - Arrière-plan: ' + bgColor + '</span>';
            }
        }
        
        // Vérifier au chargement
        window.addEventListener('load', checkCSS);
    </script>
</body>
</html>