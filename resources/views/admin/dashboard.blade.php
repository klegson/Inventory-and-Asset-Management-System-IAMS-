<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - DepEd AMS</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
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

        .date-display {
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.2s;
            position: relative;
            overflow: hidden;
            height: 100%;
            border-left: 5px solid #101954;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 2.5rem;
            opacity: 0.2;
            color: #101954;
        }

        .stat-title {
            font-size: 0.9rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: #101954;
            margin: 10px 0 5px 0;
        }

        .stat-desc {
            font-size: 0.8rem;
            color: #28a745; 
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .recent-table-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
        }

        .badge-pending { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .badge-approved { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .badge-acquired { background-color: #cfe2ff; color: #084298; border: 1px solid #b6d4fe; }
        .badge-cancelled { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
        .badge-forwarded { background-color: #cff4fc; color: #055160; border: 1px solid #b6effb; }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

    @include('layouts.admin_header')
    @include('layouts.admin_sidebar')

    <div class="main-content">
        <div class="welcome-banner d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">Welcome back, {{ $user_name }}!</h2>
                <p class="mb-0 opacity-75">Here is what's happening in your inventory today.</p>
            </div>
            <div class="text-end d-none d-md-block">
                <h4 class="fw-bold mb-0" id="clock">00:00:00 AM</h4>
                <small id="date">Loading date...</small>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card border-primary">
                    <div class="stat-title">Total Assets</div>
                    <div class="stat-value">{{ number_format($total_assets) }}</div>
                    <div class="stat-desc">
                        <i class="fas fa-box-open"></i> Inventory Count
                    </div>
                    <i class="fas fa-laptop stat-icon"></i>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card" style="border-left-color: #28a745;">
                    <div class="stat-title">Supplies Stock</div>
                    <div class="stat-value">{{ number_format($total_supplies) }}</div>
                    <div class="stat-desc text-success">
                        <i class="fas fa-boxes"></i> Available Items
                    </div>
                    <i class="fas fa-boxes stat-icon" style="color: #28a745;"></i>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card" style="border-left-color: #ffc107;">
                    <div class="stat-title">Approved Requests</div>
                    <div class="stat-value">{{ number_format($approved_requests) }}</div>
                    <div class="stat-desc text-success">
                        <i class="fas fa-check-circle"></i> Ready for Release
                    </div>
                    <i class="fas fa-file-signature stat-icon" style="color: #ffc107;"></i>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card" style="border-left-color: #17a2b8;">
                    <div class="stat-title">Registered Users</div>
                    <div class="stat-value">{{ number_format($total_users) }}</div>
                    <div class="stat-desc">
                        <i class="fas fa-user-check"></i> System Users
                    </div>
                    <i class="fas fa-users stat-icon" style="color: #17a2b8;"></i>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="recent-table-card">
                    <div class="table-header">
                        <h5 class="fw-bold mb-0">Recent Requisition Requests</h5>
                        <a href="{{ url('/admin/requests') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>RIS No.</th>
                                    <th>Requested By</th>
                                    <th>Date</th>
                                    <th>Purpose</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_requests as $row)
                                    @php
                                        $badgeClass = 'badge-pending';
                                        if ($row->status == 'Approved') {
                                            $badgeClass = 'badge-approved';
                                        } elseif ($row->status == 'Forwarded to Admin') {
                                            $badgeClass = 'badge-forwarded';
                                        } elseif (in_array($row->status, ['Cancelled', 'Declined', 'Rejected'])) {
                                            $badgeClass = 'badge-cancelled'; // Included Rejected so it turns red
                                        }
                                    @endphp
                                    <tr>
                                        <td class="fw-bold">#{{ $row->ris_no }}</td>
                                        <td>
                                            <div class="fw-bold">{{ $row->sig_requested_by ?: 'No Name Provided' }}</div>
                                            <div class="small text-muted">{{$row->division }}</div>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($row->created_at)->format('M d, Y') }}</td>
                                        <td class="text-truncate" style="max-width: 200px;">
                                            {{ $row->purpose ?: 'N/A' }}
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill {{ $badgeClass }}">
                                                {{ $row->status }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ url('/admin/requests') }}" class="btn btn-sm btn-primary text-white">View 
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No recent requests found.</td>
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
