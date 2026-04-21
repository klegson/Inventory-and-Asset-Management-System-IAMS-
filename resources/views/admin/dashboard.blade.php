<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - DepEd AMS</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body::-webkit-scrollbar {
            display: none;
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
            transition: all 0.3s;
            padding-top: 90px !important;
        }

        .welcome-banner {
            background: linear-gradient(135deg, #101954 0%, #0a4d9c 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(16, 25, 84, 0.2);
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.2s;
            position: relative;
            overflow: hidden;
            height: 100%;
            border-left: 5px solid #101954;
        }

        .stat-card:hover { transform: translateY(-5px); }
        .stat-icon { position: absolute; right: 20px; top: 20px; font-size: 2.5rem; opacity: 0.15; color: #101954; }
        .stat-title { font-size: 0.85rem; color: #6c757d; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;}
        .stat-value { font-size: 2.2rem; font-weight: 800; color: #101954; margin: 10px 0 5px 0; }
        .stat-desc { font-size: 0.8rem; display: flex; align-items: center; gap: 5px; }

        .chart-card, .data-card {
            background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); height: 100%;
        }
        
        .card-header-flex {
            font-weight: 700; color: #101954; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 10px;
        }

        .table thead th { background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057; }
        .badge-pending { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .badge-approved { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .badge-acquired { background-color: #cfe2ff; color: #084298; border: 1px solid #b6d4fe; }
        .badge-cancelled { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
        .badge-forwarded { background-color: #cff4fc; color: #055160; border: 1px solid #b6effb; }

        @media (max-width: 768px) { .main-content { margin-left: 0; } }
    </style>
</head>
<body>

    @include('layouts.admin_header')
    @include('layouts.admin_sidebar')

    <div class="main-content">
        <div class="welcome-banner d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">Welcome back, {{ $user_name }}</h2>
                <p class="mb-0 opacity-75">Here is your complete administrative overview.</p>
            </div>
            <div class="text-end d-none d-md-block">
                <h4 class="fw-bold mb-0" id="clock">00:00:00 AM</h4>
                <small id="date">Loading date...</small>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card border-primary">
                    <div class="stat-title">Total Assets</div>
                    <div class="stat-value">{{ number_format($total_assets) }}</div>
                    <div class="stat-desc text-primary"><i class="fas fa-box-open"></i> Inventory Count</div>
                    <i class="fas fa-laptop stat-icon text-primary"></i>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card" style="border-left-color: #28a745;">
                    <div class="stat-title">Supplies Stock</div>
                    <div class="stat-value">{{ number_format($total_supplies) }}</div>
                    <div class="stat-desc text-success"><i class="fas fa-boxes"></i> Available Items</div>
                    <i class="fas fa-boxes stat-icon text-success"></i>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card" style="border-left-color: #ffc107;">
                    <div class="stat-title">Approved Requests</div>
                    <div class="stat-value">{{ number_format($approved_requests) }}</div>
                    <div class="stat-desc text-warning"><i class="fas fa-check-circle"></i> Ready for Release</div>
                    <i class="fas fa-file-signature stat-icon text-warning"></i>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card" style="border-left-color: #17a2b8;">
                    <div class="stat-title">Registered Users</div>
                    <div class="stat-value">{{ number_format($total_users) }}</div>
                    <div class="stat-desc text-info"><i class="fas fa-user-check"></i> System Users</div>
                    <i class="fas fa-users stat-icon text-info"></i>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            
            <div class="col-lg-7">
                <div class="data-card">
                    <div class="card-header-flex">
                        <span class="fs-6"><i class="fas fa-list-alt me-2 text-primary"></i> Live Requisition Queue</span>
                        <a href="{{ url('/admin/ris') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="table-responsive" style="max-height: 300px;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th>RIS No.</th>
                                    <th>Requested By</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_requests as $row)
                                    @php
                                        $badgeClass = 'badge-pending';
                                        $statusStr = strtolower($row->status);
                                        if (str_contains($statusStr, 'approv')) $badgeClass = 'badge-approved';
                                        elseif (str_contains($statusStr, 'forward')) $badgeClass = 'badge-forwarded';
                                        elseif (str_contains($statusStr, 'acquir') || str_contains($statusStr, 'issu')) $badgeClass = 'badge-acquired';
                                        elseif (str_contains($statusStr, 'cancel') || str_contains($statusStr, 'declin') || str_contains($statusStr, 'reject')) $badgeClass = 'badge-cancelled';
                                    @endphp
                                    <tr>
                                        <td class="fw-bold text-primary">{{ $row->ris_no }}</td>
                                        <td>
                                            <div class="fw-bold">{{ $row->sig_requested_by ?: 'No Name Provided' }}</div>
                                            <div class="small text-muted">{{ $row->division }}</div>
                                        </td>
                                        <td><span class="badge rounded-pill px-3 py-2 {{ $badgeClass }}">{{ $row->status }}</span></td>
                                        <td class="text-center">
                                            <a href="{{ url('/admin/ris/'.$row->id.'/review') }}" class="btn btn-sm btn-light border text-primary"><i class="fas fa-eye"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-5 text-muted">No recent requests found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="data-card">
                    <div class="card-header-flex">
                        <span class="fs-6 text-danger"><i class="fas fa-exclamation-circle me-2"></i> Critical Procurement Alerts</span>
                        <a href="{{ url('/admin/supplies') }}" class="btn btn-sm btn-outline-danger">Manage</a>
                    </div>
                    <div class="table-responsive" style="max-height: 300px;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th>Item Description</th>
                                    <th class="text-center">Qty / Threshold</th>
                                    <th class="text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lowStockItems ?? [] as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-truncate" style="max-width: 180px;">{{ $item->article }}</div>
                                            <div class="small text-muted">{{ $item->barcode_id ?? 'N/A' }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-danger fw-bold fs-5">{{ $item->quantity }}</span> 
                                            <span class="text-muted small fw-bold">/ {{ $item->low_stock_threshold ?? 10 }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge {{ $item->quantity == 0 ? 'bg-danger' : 'bg-warning text-dark' }} rounded-pill px-2 py-1">
                                                {{ $item->quantity == 0 ? 'Empty' : 'Low' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-5 text-muted"><i class="fas fa-check-circle text-success fs-3 mb-2"></i><br>Stock levels are healthy.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="chart-card">
                    <div class="card-header-flex"><span><i class="fas fa-pie-chart me-2"></i> Global RIS Status</span></div>
                    <div style="height: 220px; display:flex; justify-content:center;"><canvas id="risChart"></canvas></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-card">
                    <div class="card-header-flex"><span><i class="fas fa-database me-2"></i> Inventory Composition</span></div>
                    <div style="height: 220px; display:flex; justify-content:center;"><canvas id="inventoryChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="chart-card">
                    <div class="card-header-flex">
                        <span><i class="fas fa-chart-area me-2"></i> System Stock Movement Analytics</span>
                        <div class="d-flex gap-2">
                            <select id="trendRange" class="form-select form-select-sm" style="width: 140px; border-color: #0d6efd; color: #101954; font-weight: 600;">
                                <option value="7days">Last 7 Days</option>
                                <option value="30days">Last 30 Days</option>
                                <option value="this_year">This Year</option>
                                <option value="last_year">Last Year</option>
                            </select>
                            <a href="{{ url('/admin/transactions') }}" class="btn btn-sm btn-outline-primary">View Log</a>
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
            Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
            Chart.defaults.color = '#6c757d';

            // 1. Trend Chart
            let trendChartInstance = null;
            function renderTrendChart(labels, inData, outData) {
                const trendCtx = document.getElementById('trendChart').getContext('2d');
                if (trendChartInstance) trendChartInstance.destroy();

                trendChartInstance = new Chart(trendCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            { label: 'Procured / Stock In', data: inData, backgroundColor: 'rgba(25, 135, 84, 0.85)', borderRadius: 4, barPercentage: 0.6 },
                            { label: 'Issued / Stock Out', data: outData, backgroundColor: 'rgba(220, 53, 69, 0.85)', borderRadius: 4, barPercentage: 0.6 }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { position: 'top', align: 'end' } },
                        scales: {
                            y: { beginAtZero: true, grid: { borderDash: [2, 4], color: '#e0e0e0' } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            renderTrendChart({!! json_encode($dates) !!}, {!! json_encode($stockInData) !!}, {!! json_encode($stockOutData) !!});

            document.getElementById('trendRange').addEventListener('change', function() {
                const range = this.value;
                fetch(`{{ url('/admin/dashboard/chart-data') }}?range=${range}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.json())
                .then(data => { renderTrendChart(data.labels, data.stockIn, data.stockOut); })
                .catch(err => console.error('Error fetching admin chart data:', err));
            });

            // 2. Inventory Breakdown (Doughnut)
            new Chart(document.getElementById('inventoryChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Assets (Equipment)', 'Supplies (Consumables)'],
                    datasets: [{
                        data: [{{ $assetItemCount }}, {{ $supplyItemCount }}],
                        backgroundColor: ['#101954', '#0d6efd'],
                        borderWidth: 2, borderColor: '#fff', hoverOffset: 4
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'right' } } }
            });

            // 3. RIS Status Chart (Pie) - UPDATED WITH 5 COLORS
            new Chart(document.getElementById('risChart').getContext('2d'), {
                type: 'pie',
                data: {
                    labels: {!! json_encode($risStatusLabels) !!},
                    datasets: [{
                        data: {!! json_encode($risStatusData) !!},
                        backgroundColor: [
                            '#ffc107', // Pending Staff Review (Yellow)
                            '#fd7e14', // Pending Admin Approval (Orange)
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