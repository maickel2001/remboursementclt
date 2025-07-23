<?php
require_once '../config/auth.php';
checkClient();
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculateur Avancé - RemboursePRO</title>
    
    <!-- Bootstrap 5.3.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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

        /* Sidebar */
        .dashboard-sidebar {
            background: rgba(15, 23, 42, 0.9) !important;
            border-right: 1px solid rgba(255, 255, 255, 0.1) !important;
            min-height: 100vh !important;
        }

        .sidebar-item {
            color: #ffffff !important;
            background: transparent !important;
            padding: 12px 20px !important;
            margin: 5px 15px !important;
            border-radius: 8px !important;
            text-decoration: none !important;
            display: block !important;
            transition: all 0.3s ease !important;
        }

        .sidebar-item:hover, .sidebar-item.active {
            background: rgba(59, 130, 246, 0.2) !important;
            color: #ffffff !important;
        }

        /* Cards glassmorphism */
        .glass, .calculator-card {
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

        .btn-outline-danger {
            background: transparent !important;
            border: 1px solid #ef4444 !important;
            color: #ef4444 !important;
        }

        .btn-outline-danger:hover {
            background: #ef4444 !important;
            color: #ffffff !important;
        }

        /* Calculateur result */
        .calculator-result {
            background: rgba(34, 197, 94, 0.2) !important;
            border: 1px solid rgba(34, 197, 94, 0.4) !important;
            border-radius: 12px !important;
            padding: 1.5rem !important;
            margin-top: 1.5rem !important;
            color: #ffffff !important;
        }

        /* Chart container */
        .chart-container {
            background: rgba(15, 23, 42, 0.6) !important;
            border-radius: 8px !important;
            padding: 1rem !important;
            height: 400px !important;
        }

        /* Tables */
        .table {
            background: transparent !important;
            color: #ffffff !important;
        }

        .table th {
            background: rgba(15, 23, 42, 0.8) !important;
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            font-weight: 600 !important;
        }

        .table td {
            background: transparent !important;
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        .table-glass {
            background: rgba(15, 23, 42, 0.6) !important;
            border-radius: 12px !important;
            overflow: hidden !important;
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
            .calculator-card {
                padding: 1.5rem !important;
            }
            
            .container, .container-fluid {
                padding-left: 15px !important;
                padding-right: 15px !important;
            }
        }
    </style>
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
                            <a class="sidebar-item" href="profil.php">
                                <i class="bi bi-person me-2"></i>Mon Profil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="sidebar-item" href="remboursement.php">
                                <i class="bi bi-credit-card me-2"></i>Nouveau Remboursement
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="sidebar-item" href="historique.php">
                                <i class="bi bi-clock-history me-2"></i>Historique
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="sidebar-item active" href="calculateur.php">
                                <i class="bi bi-calculator me-2"></i>Calculateur
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
                            <i class="bi bi-calculator me-3"></i>Calculateur Avancé
                        </h1>
                        <a href="dashboard.php" class="btn btn-glass">
                            <i class="bi bi-arrow-left me-2"></i>Retour
                        </a>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="calculator-card">
                                <h3 class="text-center text-white mb-4">
                                    <i class="bi bi-calculator me-2"></i>Simulation de Remboursement
                                </h3>
                                
                                <div class="mb-3">
                                    <label for="totalAmount" class="form-label">
                                        <i class="bi bi-currency-euro me-2"></i>Montant total à rembourser (€)
                                    </label>
                                    <input type="number" class="form-control" id="totalAmount" 
                                           placeholder="0.00" min="0" step="0.01">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="reimbursementAmount" class="form-label">
                                        <i class="bi bi-cash me-2"></i>Remboursement à effectuer (€)
                                    </label>
                                    <input type="number" class="form-control" id="reimbursementAmount" 
                                           placeholder="0.00" min="0" step="0.01">
                                </div>
                                
                                <div class="mb-4">
                                    <label for="remainingAmount" class="form-label">
                                        <i class="bi bi-calculator me-2"></i>Reste à rembourser (€)
                                    </label>
                                    <input type="number" class="form-control" id="remainingAmount" 
                                           placeholder="0.00" readonly>
                                </div>
                                
                                <div class="calculator-result" id="calculatorResult" style="display: none;">
                                    <h4 class="mb-3">Résultat de la simulation</h4>
                                    <div class="row g-3">
                                        <div class="col-4">
                                            <div class="text-center">
                                                <div class="h5 mb-1" id="displayTotal">0.00 €</div>
                                                <small>Total</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-center">
                                                <div class="h5 mb-1" id="displayReimbursement">0.00 €</div>
                                                <small>Remboursement</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-center">
                                                <div class="h5 mb-1" id="displayRemaining">0.00 €</div>
                                                <small>Reste</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 mt-4">
                                    <button class="btn btn-gradient" onclick="saveSimulation()">
                                        <i class="bi bi-bookmark me-2"></i>Sauvegarder la simulation
                                    </button>
                                    <a href="remboursement.php" class="btn btn-glass">
                                        <i class="bi bi-credit-card me-2"></i>Effectuer ce remboursement
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-6">
                            <div class="glass p-4 rounded-3 mb-4">
                                <h4 class="text-white mb-3">
                                    <i class="bi bi-pie-chart me-2"></i>Répartition Visuelle
                                </h4>
                                <div class="chart-container">
                                    <canvas id="reimbursementChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Historique des simulations -->
                    <div class="glass p-4 rounded-3 mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="text-white mb-0">
                                <i class="bi bi-clock-history me-2"></i>Historique des Simulations
                            </h4>
                            <div>
                                <button class="btn btn-glass btn-sm me-2" onclick="exportHistory()">
                                    <i class="bi bi-download me-2"></i>Exporter
                                </button>
                                <button class="btn btn-glass btn-sm" onclick="clearHistory()">
                                <i class="bi bi-trash me-2"></i>Vider l'historique
                                </button>
                            </div>
                        </div>
                        
                        <div id="simulationHistory">
                            <div class="text-center py-4">
                                <i class="bi bi-inbox" style="font-size: 3rem; color: rgba(255,255,255,0.3);"></i>
                                <p class="text-white-50 mt-2">Aucune simulation sauvegardée</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Conseils intelligents -->
                    <div class="glass p-4 rounded-3 mt-4" id="smartTips">
                        <h4 class="text-white mb-3"><i class="bi bi-lightbulb me-2"></i>Conseil Intelligent</h4>
                        <p class="text-white-50" id="tipContent">Calculez différents scénarios pour optimiser vos remboursements.</p>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let chart = null;
        let tipIndex = 0;
        
        // Calcul automatique et mise à jour du graphique
        function updateCalculator() {
            const totalAmount = parseFloat(document.getElementById('totalAmount').value) || 0;
            const reimbursementAmount = parseFloat(document.getElementById('reimbursementAmount').value) || 0;
            const remainingAmount = Math.max(0, totalAmount - reimbursementAmount);
            
            document.getElementById('remainingAmount').value = remainingAmount.toFixed(2);
            
            if (totalAmount > 0) {
                document.getElementById('displayTotal').textContent = totalAmount.toFixed(2) + ' €';
                document.getElementById('displayReimbursement').textContent = reimbursementAmount.toFixed(2) + ' €';
                document.getElementById('displayRemaining').textContent = remainingAmount.toFixed(2) + ' €';
                document.getElementById('calculatorResult').style.display = 'block';
                
                updateChart(reimbursementAmount, remainingAmount);
                showSmartTip(totalAmount, reimbursementAmount, remainingAmount);
            } else {
                document.getElementById('calculatorResult').style.display = 'none';
                if (chart) {
                    chart.destroy();
                    chart = null;
                }
            }
        }

        // Conseils intelligents basés sur les calculs
        function showSmartTip(total, reimbursement, remaining) {
            const tips = [
                remaining === 0 ? "✅ Excellent ! Remboursement complet effectué." : 
                remaining < total * 0.3 ? "👍 Bon remboursement ! Il reste moins de 30% à rembourser." :
                remaining > total * 0.7 ? "⚠️ Remboursement partiel. Considérez augmenter le montant." :
                "💡 Remboursement équilibré. Vous êtes sur la bonne voie.",
                
                total > 500 ? "💰 Montant élevé détecté. Vérifiez les conditions de remboursement." :
                total < 50 ? "📝 Petit montant. Le traitement sera rapide." :
                "💼 Montant standard. Traitement habituel prévu.",
                
                reimbursement === total ? "🎯 Remboursement intégral ! Parfait." :
                reimbursement > total * 0.8 ? "📈 Remboursement quasi-complet. Très bien !" :
                "📊 Remboursement partiel. Planifiez le reste si nécessaire."
            ];
            
            const randomTip = tips[Math.floor(Math.random() * tips.length)];
            document.getElementById('tipContent').textContent = randomTip;
            
            // Animation du conseil
            const tipElement = document.getElementById('smartTips');
            tipElement.style.animation = 'none';
            setTimeout(() => tipElement.style.animation = 'pulse 2s ease-in-out', 100);
        }

        // Mise à jour du graphique
        function updateChart(reimbursement, remaining) {
            const ctx = document.getElementById('reimbursementChart').getContext('2d');
            
            if (chart) {
                chart.destroy();
            }
            
            chart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Remboursement effectué', 'Reste à rembourser'],
                    datasets: [{
                        data: [reimbursement, remaining],
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(139, 92, 246, 0.8)'
                        ],
                        borderColor: [
                            'rgba(59, 130, 246, 1)',
                            'rgba(139, 92, 246, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: 'white'
                            }
                        }
                    }
                }
            });
        }

        // Sauvegarder une simulation
        function saveSimulation() {
            const totalAmount = parseFloat(document.getElementById('totalAmount').value) || 0;
            const reimbursementAmount = parseFloat(document.getElementById('reimbursementAmount').value) || 0;
            const remainingAmount = parseFloat(document.getElementById('remainingAmount').value) || 0;
            
            if (totalAmount <= 0) {
                alert('Veuillez saisir un montant valide');
                return;
            }
            
            const simulation = {
                id: Date.now(),
                date: new Date().toLocaleString('fr-FR'),
                totalAmount: totalAmount,
                reimbursementAmount: reimbursementAmount,
                remainingAmount: remainingAmount
            };
            
            let history = JSON.parse(localStorage.getItem('simulationHistory') || '[]');
            history.unshift(simulation);
            
            // Garder seulement les 10 dernières simulations
            if (history.length > 10) {
                history = history.slice(0, 10);
            }
            
            localStorage.setItem('simulationHistory', JSON.stringify(history));
            loadHistory();
            
            alert('Simulation sauvegardée avec succès !');
        }

        // Charger l'historique des simulations
        function loadHistory() {
            const history = JSON.parse(localStorage.getItem('simulationHistory') || '[]');
            const container = document.getElementById('simulationHistory');
            
            if (history.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-4">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: rgba(255,255,255,0.3);"></i>
                        <p class="text-white-50 mt-2">Aucune simulation sauvegardée</p>
                    </div>
                `;
                return;
            }
            
            let html = '<div class="table-responsive"><table class="table table-glass"><thead><tr>';
            html += '<th>Date</th><th>Total</th><th>Remboursement</th><th>Reste</th><th>Actions</th>';
            html += '</tr></thead><tbody>';
            
            history.forEach(sim => {
                html += `
                    <tr>
                        <td>${sim.date}</td>
                        <td>${sim.totalAmount.toFixed(2)}€</td>
                        <td>${sim.reimbursementAmount.toFixed(2)}€</td>
                        <td>${sim.remainingAmount.toFixed(2)}€</td>
                        <td>
                            <button class="btn btn-glass btn-sm" onclick="loadSimulation(${sim.id})">
                                <i class="bi bi-arrow-up-circle"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-sm ms-1" onclick="deleteSimulation(${sim.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            html += '</tbody></table></div>';
            container.innerHTML = html;
        }

        // Charger une simulation
        function loadSimulation(id) {
            const history = JSON.parse(localStorage.getItem('simulationHistory') || '[]');
            const simulation = history.find(sim => sim.id === id);
            
            if (simulation) {
                document.getElementById('totalAmount').value = simulation.totalAmount;
                document.getElementById('reimbursementAmount').value = simulation.reimbursementAmount;
                updateCalculator();
            }
        }

        // Supprimer une simulation
        function deleteSimulation(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette simulation ?')) {
                let history = JSON.parse(localStorage.getItem('simulationHistory') || '[]');
                history = history.filter(sim => sim.id !== id);
                localStorage.setItem('simulationHistory', JSON.stringify(history));
                loadHistory();
            }
        }

        // Exporter l'historique
        function exportHistory() {
            const history = JSON.parse(localStorage.getItem('simulationHistory') || '[]');
            if (history.length === 0) {
                alert('Aucune simulation à exporter');
                return;
            }
            
            const csvContent = "data:text/csv;charset=utf-8," 
                + "Date,Total,Remboursement,Reste\n"
                + history.map(sim => `${sim.date},${sim.totalAmount},${sim.reimbursementAmount},${sim.remainingAmount}`).join('\n');
            
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement('a');
            link.setAttribute('href', encodedUri);
            link.setAttribute('download', 'historique_simulations.csv');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Vider l'historique
        function clearHistory() {
            if (confirm('Êtes-vous sûr de vouloir vider tout l\'historique ?')) {
                localStorage.removeItem('simulationHistory');
                loadHistory();
            }
        }

        // Event listeners
        document.getElementById('totalAmount').addEventListener('input', updateCalculator);
        document.getElementById('reimbursementAmount').addEventListener('input', updateCalculator);

        // Charger l'historique au chargement de la page
        document.addEventListener('DOMContentLoaded', loadHistory);
    </script>
</body>
</html>