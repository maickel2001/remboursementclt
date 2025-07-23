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
                                    <label for="totalAmount" class="form-label text-white">
                                        <i class="bi bi-currency-euro me-2"></i>Montant total à rembourser (€)
                                    </label>
                                    <input type="number" class="form-control form-control-glass" id="totalAmount" 
                                           placeholder="0.00" min="0" step="0.01">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="reimbursementAmount" class="form-label text-white">
                                        <i class="bi bi-cash me-2"></i>Remboursement à effectuer (€)
                                    </label>
                                    <input type="number" class="form-control form-control-glass" id="reimbursementAmount" 
                                           placeholder="0.00" min="0" step="0.01">
                                </div>
                                
                                <div class="mb-4">
                                    <label for="remainingAmount" class="form-label text-white">
                                        <i class="bi bi-calculator me-2"></i>Reste à rembourser (€)
                                    </label>
                                    <input type="number" class="form-control form-control-glass" id="remainingAmount" 
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
                            <button class="btn btn-glass btn-sm" onclick="clearHistory()">
                                <i class="bi bi-trash me-2"></i>Vider l'historique
                            </button>
                        </div>
                        
                        <div id="simulationHistory">
                            <div class="text-center py-4">
                                <i class="bi bi-inbox" style="font-size: 3rem; color: rgba(255,255,255,0.3);"></i>
                                <p class="text-white-50 mt-2">Aucune simulation sauvegardée</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let chart = null;
        
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
            } else {
                document.getElementById('calculatorResult').style.display = 'none';
                if (chart) {
                    chart.destroy();
                    chart = null;
                }
            }
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
                            'rgba(102, 126, 234, 0.8)',
                            'rgba(118, 75, 162, 0.8)'
                        ],
                        borderColor: [
                            'rgba(102, 126, 234, 1)',
                            'rgba(118, 75, 162, 1)'
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