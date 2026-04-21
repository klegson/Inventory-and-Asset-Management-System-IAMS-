<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - DepEd AMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body { 
            background-color: #f4f6f9; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        body::-webkit-scrollbar {
            display: none;
        }

        .main-content { 
            margin-left: 250px; 
            padding: 30px; 
            transition: all 0.3s; 
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
            transition: all 0.3s;
            border-bottom: 4px solid transparent;
            height: 100%;
        }
        
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card.pending { border-color: #fd7e14; }
        .stat-card.approved { border-color: #198754; }
        .stat-card.declined { border-color: #dc3545; }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
            transition: all 0.3s;
        }

        .table-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            height: 100%;
        }

        .chart-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .status-pending { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .status-approved { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .status-declined { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
        .status-forwarded { background-color: #cff4fc; color: #055160; border: 1px solid #b6effb;}

        @media (max-width: 768px) { 
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; } 
        }
    </style>
</head>
<body>

    @include('layouts.user_header')
    @include('layouts.user_sidebar')

    <div class="main-content">
        
        <div class="welcome-banner d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">Welcome back, {{ $user->firstname }}</h2>
                <p class="mb-0 opacity-75">Here is an overview of your division's requisition activity.</p>
            </div>
            <div class="text-end d-none d-md-block">
                <h4 class="fw-bold mb-0" id="clock">00:00:00 AM</h4>
                <small id="date">Loading date...</small>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stat-card pending">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">Pending Requests</div>
                            <h2 class="fw-bold text-dark mt-2">{{ $pendingCount }}</h2>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="fa-solid fa-hourglass-half"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card approved">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">Approved RIS</div>
                            <h2 class="fw-bold text-dark mt-2">{{ $approvedCount }}</h2>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="fa-solid fa-check-double"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card declined">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">Declined Requests</div>
                            <h2 class="fw-bold text-danger mt-2">{{ $declinedCount }}</h2>
                        </div>
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            
            <div class="col-lg-7">
                <div class="table-card">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="fas fa-list-alt me-2 text-primary"></i>Recent RIS Activity</h6>
                        <a href="{{ url('/user/ris/history') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>RIS No.</th>
                                    <th>Date Requested</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentRis as $ris)
                                    @php
                                        $badgeClass = 'status-pending';
                                        if (str_contains(strtolower($ris->status), 'approv')) {
                                            $badgeClass = 'status-approved';
                                        } elseif (str_contains(strtolower($ris->status), 'declin') || str_contains(strtolower($ris->status), 'reject') || str_contains(strtolower($ris->status), 'cancel')) {
                                            $badgeClass = 'status-declined'; 
                                        } elseif (str_contains(strtolower($ris->status), 'forward')) {
                                            $badgeClass = 'status-forwarded';
                                        }
                                    @endphp
                                    <tr>
                                        <td class="fw-bold text-primary">{{ $ris->ris_no }}</td>
                                        <td>{{ \Carbon\Carbon::parse($ris->created_at)->format('M d, Y') }}</td>
                                        <td><span class="badge rounded-pill {{ $badgeClass }} px-3 py-2">{{ $ris->status }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted border-0">
                                            <i class="fas fa-folder-open fs-3 mb-3 d-block opacity-50"></i>
                                            No recent RIS activity found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="chart-card">
                    <h6 class="fw-bold text-dark mb-3 border-bottom pb-3"><i class="fas fa-chart-pie me-2 text-primary"></i>Request Analytics</h6>
                    
                    <div class="flex-grow-1 d-flex justify-content-center align-items-center" style="position: relative; min-height: 250px;">
                        @if($pendingCount == 0 && $approvedCount == 0 && $declinedCount == 0)
                            <div class="text-center text-muted">
                                <i class="fas fa-chart-bar fs-2 mb-2 opacity-25"></i>
                                <p class="small">Not enough data to display chart.</p>
                            </div>
                        @else
                            <canvas id="risStatusChart"></canvas>
                        @endif
                    </div>
                </div>
            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Live Clock
        function updateTime() {
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            document.getElementById('date').innerText = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }
        setInterval(updateTime, 1000);
        updateTime();

        // Chart.js Initialization
        document.addEventListener('DOMContentLoaded', function() {
            @if($pendingCount > 0 || $approvedCount > 0 || $declinedCount > 0)
                const ctx = document.getElementById('risStatusChart').getContext('2d');
                
                // Inherit the global font styling
                Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
                
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Pending', 'Approved', 'Declined'],
                        datasets: [{
                            data: [{{ $pendingCount }}, {{ $approvedCount }}, {{ $declinedCount }}],
                            backgroundColor: [
                                '#fd7e14', // Warning/Orange
                                '#198754', // Success/Green
                                '#dc3545'  // Danger/Red
                            ],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%', // Makes the doughnut thinner and more modern
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            }
                        }
                    }
                });
            @endif
        });
    </script>
</body>
</html>