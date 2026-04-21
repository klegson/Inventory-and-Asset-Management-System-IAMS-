<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - DepEd AMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }

        body::-webkit-scrollbar {
            display: none;
        }

        .main-content { margin-left: 250px; padding: 30px; transition: all 0.3s; padding-top: 90px !important; }
        
        .welcome-banner {
            background: linear-gradient(135deg, #101954 0%, #0a4d9c 100%);
            color: white; padding: 30px; border-radius: 15px; margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(16, 25, 84, 0.2);
        }
        
        .stat-card {
            background: white; border-radius: 12px; padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: transform 0.2s;
            border-bottom: 4px solid transparent; height: 100%; position: relative; overflow: hidden;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card.assets { border-color: #0d6efd; }
        .stat-card.supplies { border-color: #198754; }
        .stat-card.ris { border-color: #ffc107; }
        .stat-card.critical { border-color: #dc3545; }
        
        .stat-icon {
            width: 50px; height: 50px; border-radius: 10px; display: flex;
            align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 15px;
        }

        .chart-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); height: 100%; }
        .chart-header { font-weight: 700; color: #101954; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 10px;}
        
        .table-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); height: 100%;}
        .status-available { background-color: #d1e7dd; color: #0f5132; }
        .status-low { background-color: #fff3cd; color: #856404; }
        .status-out { background-color: #f8d7da; color: #842029; }
        
        @media (max-width: 768px) { .main-content { margin-left: 0; } }
    </style>
</head>
<body>

    @include('layouts.header')
    @include('layouts.sidebar')

    <div class="main-content">
        
        @php
            // SMART FILTER: Enforce custom threshold dynamically 
            // This guarantees items above their custom threshold disappear from the alert list!
            $lowStockItems = collect($lowStockItems ?? [])->filter(function($item) {
                return $item->quantity <= ($item->low_stock_threshold ?? 10);
            });
            // Auto-update the top red widget count to match the filtered list
            $lowStockCount = $lowStockItems->count();
        @endphp

        <div class="welcome-banner d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">Welcome back, {{ $firstname ?? 'User' }} {{ $lastname ?? '' }}</h2>
                <p class="mb-0 opacity-75">Here is a complete overview of your inventory analytics.</p>
            </div>
            <div class="text-end d-none d-md-block">
                <h4 class="fw-bold mb-0" id="clock">00:00:00 AM</h4>
                <small id="date">Loading date...</small>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card assets">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">Total Assets Vol.</div>
                            <h2 class="fw-bold text-dark mt-2 mb-0">{{ number_format($totalAssets ?? 0) }}</h2>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-laptop"></i></div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card supplies">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">Total Supplies Vol.</div>
                            <h2 class="fw-bold text-dark mt-2 mb-0">{{ number_format($totalSupplies ?? 0) }}</h2>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="fas fa-boxes"></i></div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card ris">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">Pending RIS</div>
                            <h2 class="fw-bold text-dark mt-2 mb-0">{{ $pendingRisCount ?? 0 }}</h2>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-file-signature"></i></div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card critical">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">Low Stock Alerts</div>
                            <h2 class="fw-bold text-danger mt-2 mb-0">{{ $lowStockCount ?? 0 }}</h2>
                        </div>
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="fas fa-exclamation-triangle"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            
            <div class="col-lg-7">
                <div class="table-card">
                    <div class="chart-header">
                        <span class="text-danger"><i class="fas fa-exclamation-circle me-2"></i> Critical Stocks (Action Required)</span>
                        <a href="{{ url('/supplies') }}" class="btn btn-sm btn-outline-danger">Manage Supplies</a>
                    </div>
                    <div class="table-responsive" style="max-height: 350px;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th>Item Article</th>
                                    <th>Barcode / Code</th>
                                    <th class="text-center">Qty / Threshold</th>
                                    <th class="text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lowStockItems as $item)
                                    @php
                                        $statusClass = $item->quantity == 0 ? 'bg-danger' : 'bg-warning-subtle text-dark';
                                        $statusText = $item->quantity == 0 ? 'Out of Stock' : 'Low Stock';
                                    @endphp
                                    <tr>
                                        <td class="fw-bold text-truncate" style="max-width: 200px;">{{ $item->article }}</td>
                                        <td class="text-muted small"><i class="fas fa-barcode me-1"></i> {{ $item->barcode_id ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            <span class="text-danger fw-bold fs-5">{{ $item->quantity }}</span> 
                                            <span class="text-muted small fw-bold">/ {{ $item->low_stock_threshold ?? 10 }}</span>
                                        </td>
                                        <td class="text-end"><span class="badge rounded-pill {{ $statusClass }} px-3 py-2">{{ $statusText }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <div class="mb-3"><i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i></div>
                                            <h5>All clear!</h5>
                                            <p class="mb-0">All supplies in the inventory are above their custom low stock thresholds.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 d-flex flex-column gap-4">
                
                <div class="chart-card flex-grow-1">
                    <div class="chart-header">
                        <span><i class="fas fa-clipboard-list me-2"></i> RIS Status Breakdown</span>
                    </div>
                    <div style="height: 180px; position: relative; display:flex; justify-content:center;">
                        <canvas id="risChart"></canvas>
                    </div>
                </div>

                <div class="chart-card flex-grow-1">
                    <div class="chart-header">
                        <span><i class="fas fa-chart-pie me-2"></i> System Item Categories</span>
                    </div>
                    <div style="height: 180px; position: relative; display:flex; justify-content:center;">
                        <canvas id="inventoryChart"></canvas>
                    </div>
                </div>

            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="chart-card mb-4"> <div class="chart-header">
                        <span><i class="fas fa-chart-line me-2"></i> Stock Movement Activity</span>
                        <div class="d-flex gap-2">
                            <select id="trendRange" class="form-select form-select-sm" style="width: 140px; border-color: #0d6efd; color: #101954; font-weight: 600;">
                                <option value="7days">Last 7 Days</option>
                                <option value="30days">Last 30 Days</option>
                                <option value="this_year">This Year</option>
                                <option value="last_year">Last Year</option>
                            </select>
                            <a href="{{ url('/transactions') }}" class="btn btn-sm btn-outline-primary">View Transactions</a>
                        </div>
                    </div>
                    <div style="height: 300px; position: relative; width: 100%;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            Chart.defaults.font.family = "'Segoe UI', sans-serif";
            Chart.defaults.color = '#6c757d';

            // --- 1. DYNAMIC TREND CHART LOGIC ---
            let trendChartInstance = null;

            function renderTrendChart(labels, inData, outData) {
                const trendCtx = document.getElementById('trendChart').getContext('2d');
                
                // Destroy the old chart before drawing the new one
                if (trendChartInstance) {
                    trendChartInstance.destroy();
                }

                trendChartInstance = new Chart(trendCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Items Added (Stock IN)',
                                data: inData,
                                backgroundColor: 'rgba(25, 135, 84, 0.85)', // Success Green
                                borderRadius: 4,
                                barPercentage: 0.6
                            },
                            {
                                label: 'Items Deducted (Released/Issued)',
                                data: outData,
                                backgroundColor: 'rgba(220, 53, 69, 0.85)', // Danger Red
                                borderRadius: 4,
                                barPercentage: 0.6
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'top', align: 'end' } },
                        scales: {
                            y: { beginAtZero: true, grid: { borderDash: [2, 4], color: '#e0e0e0' } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // Render Initial 7-Day Chart from PHP payload
            renderTrendChart({!! json_encode($dates ?? []) !!}, {!! json_encode($stockInData ?? []) !!}, {!! json_encode($stockOutData ?? []) !!});

            // Listen for Dropdown Changes and fetch new data
            document.getElementById('trendRange').addEventListener('change', function() {
                const range = this.value;
                
                fetch(`{{ url('/dashboard/chart-data') }}?range=${range}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    renderTrendChart(data.labels, data.stockIn, data.stockOut);
                })
                .catch(error => console.error('Error fetching chart data:', error));
            });


            // --- 2. Inventory Breakdown (Doughnut) ---
            const invCtx = document.getElementById('inventoryChart').getContext('2d');
            new Chart(invCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Assets (Equipment)', 'Supplies (Consumables)'],
                    datasets: [{
                        data: [{{ $assetItemCount ?? 0 }}, {{ $supplyItemCount ?? 0 }}],
                        backgroundColor: ['#101954', '#0d6efd'], // DepEd Navy, Primary Blue
                        borderWidth: 2, borderColor: '#fff', hoverOffset: 4
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'right' } } }
            });

            // --- 3. RIS Status Chart (Pie) ---
            const risCtx = document.getElementById('risChart').getContext('2d');
            new Chart(risCtx, {
                type: 'pie',
                data: {
                    labels: {!! json_encode($risStatusLabels ?? []) !!},
                    datasets: [{
                        data: {!! json_encode($risStatusData ?? []) !!},
                        backgroundColor: [
                            '#ffc107', // Pending Staff Review (Yellow)
                            '#0d6efd', // Approved (Blue)
                            '#198754', // Issued (Green)
                            '#dc3545'  // Declined (Red)
                        ],
                        borderWidth: 2, borderColor: '#fff', hoverOffset: 4
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
            });

        });

        // Realtime Clock
        function updateTime() {
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            document.getElementById('date').innerText = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }
        setInterval(updateTime, 1000);
        updateTime();
    </script>
</body>
</html>