<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Supply System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { 
            background-color: #f4f6f9; 
            font-family: 'Segoe UI', sans-serif; 
            overflow-x: hidden;
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
            margin-bottom: 30px;
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
        
        .stat-card:hover { 
            transform: translateY(-5px);
        }

        .stat-card:hover .stat-icon {
            background-color: rgba(255, 255, 255, 0.2) !important;
        }
        
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

        .action-btn {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            color: #101954;
            text-decoration: none;
            transition: all 0.3s;
            display: block;
            height: 100%;
        }

        .action-btn i { 
            font-size: 2rem; 
            margin-bottom: 10px; 
            display: block; 
            transition: all 0.3s;
        }

        .action-btn:hover {
            background-color: #101954;
            color: white !important;
            border-color: #101954;
            box-shadow: 0 5px 15px rgba(16, 25, 84, 0.2);
        }

        .action-btn:hover i {
            color: white !important;
        }
        
        .table-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
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

    @include('layouts.user_sidebar')

    <div class="main-content">
        
        <div class="welcome-banner d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">Welcome back, {{ $user->firstname }} {{ $user->lastname }}!</h2>
                <p class="mb-0 opacity-75">Here is what's happening with your requests today.</p>
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
            <div class="col-lg-4">
                <h5 class="fw-bold text-dark mb-3">Stock Overview</h5>
                <div class="row g-3">
                    <div class="col-12">
                        <a href="#" class="action-btn" data-bs-toggle="modal" data-bs-target="#stockModal">
                            <i class="fa-solid fa-folder-open text-warning"></i>
                            Live Stocks Folder
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <h5 class="fw-bold text-dark mb-3">Recent RIS Activity</h5>
                <div class="table-card">
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
                                        if ($ris->status == 'Approved') {
                                            $badgeClass = 'status-approved';
                                        } elseif (in_array($ris->status, ['Cancelled', 'Declined', 'Rejected'])) {
                                            $badgeClass = 'status-declined'; 
                                        } elseif ($ris->status == 'Forwarded to Admin') {
                                            $badgeClass = 'status-forwarded';
                                        }
                                    @endphp
                                    <tr>
                                        <td class="fw-bold text-primary">{{ $ris->ris_no }}</td>
                                        <td>{{ \Carbon\Carbon::parse($ris->created_at)->format('M d, Y') }}</td>
                                        <td><span class="badge rounded-pill {{ $badgeClass }} px-3">{{ $ris->status }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            <i class="fas fa-file-alt fs-3 mb-2 opacity-50"></i><br>
                                            No recent RIS activity found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="modal fade" id="stockModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Select Folder Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-4 pb-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="{{ url('/user/assets') }}" class="action-btn">
                                <i class="fa-solid fa-desktop text-primary"></i>
                                Assets Stock
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ url('/user/supplies') }}" class="action-btn">
                                <i class="fa-solid fa-box-open text-success"></i>
                                Supply Stock
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update Time
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