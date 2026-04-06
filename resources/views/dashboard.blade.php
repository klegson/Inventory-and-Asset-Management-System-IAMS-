<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - DepEd AMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .main-content { margin-left: 250px; padding: 30px; transition: all 0.3s; }
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
            transition: transform 0.2s;
            border-bottom: 4px solid transparent;
            height: 100%;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card.assets { border-color: #0d6efd; }
        .stat-card.supplies { border-color: #198754; }
        .stat-card.critical { border-color: #dc3545; }
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
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
        .action-btn:hover {
            background-color: #101954;
            color: white;
            border-color: #101954;
            box-shadow: 0 5px 15px rgba(16, 25, 84, 0.2);
        }
        .action-btn i { font-size: 2rem; margin-bottom: 10px; display: block; }
        .table-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
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
        
        <div class="welcome-banner d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">Welcome back, {{ $firstname ?? 'User' }} {{ $lastname ?? '' }}!</h2>
                <p class="mb-0 opacity-75">Here is what's happening in your inventory today.</p>
            </div>
            <div class="text-end d-none d-md-block">
                <h4 class="fw-bold mb-0" id="clock">00:00:00 AM</h4>
                <small id="date">Loading date...</small>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stat-card assets">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">Total Assets</div>
                            <h2 class="fw-bold text-dark mt-2">{{ number_format($totalAssets ?? 0) }}</h2>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-laptop"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card supplies">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">Total Supplies</div>
                            <h2 class="fw-bold text-dark mt-2">{{ number_format($totalSupplies ?? 0) }}</h2>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-boxes"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card critical">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">Low Stock Supplies</div>
                            <h2 class="fw-bold text-danger mt-2">{{ $lowStockCount ?? 0 }}</h2>
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
                <h5 class="fw-bold text-dark mb-3">Quick Actions</h5>
                <div class="row g-3">
                    <div class="col-6">
                        <a href="{{ url('/asset-list') }}" class="action-btn">
                            <i class="fas fa-plus-circle"></i>
                            Manage Assets
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="/supplies" class="action-btn">
                            <i class="fas fa-box-open"></i>
                            Manage Supplies
                        </a>
                    </div>
                    <div class="col-12">
                        <a href="/barcodes" class="action-btn d-flex align-items-center justify-content-center gap-3">
                            <i class="fas fa-barcode mb-0"></i>
                            <span>Generate & Print Barcodes</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <h5 class="fw-bold text-dark mb-3">Needs Attention (Low Stock Supplies)</h5>
                <div class="table-card">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Type</th>
                                    <th>Article / Item</th>
                                    <th>Code / Prop No.</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lowStockItems ?? [] as $item)
                                    @php
                                        $statusClass = 'status-available';
                                        $statusText = 'Available';
                                        if($item->quantity == 0) { 
                                            $statusClass = 'status-out'; 
                                            $statusText = 'Out of Stock'; 
                                        } elseif($item->quantity <= 10) { 
                                            $statusClass = 'status-low'; 
                                            $statusText = 'Low Stock'; 
                                        } 
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="badge bg-success bg-opacity-10 text-success">Supply</span>
                                        </td>
                                        <td class="fw-bold">{{ $item->article }}</td>
                                        <td class="text-muted small">{{ $item->barcode_id }}</td>
                                        <td class="text-danger fw-bold">{{ $item->quantity }}</td>
                                        <td><span class="badge rounded-pill {{ $statusClass }}">{{ $statusText }}</span></td>
                                        <td>
                                            <a href="{{ url('/supplies') }}" class="btn btn-sm btn-outline-primary">View Stock</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="fas fa-check-circle text-success mb-2 fs-4"></i><br>
                                            All supplies are well stocked!
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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