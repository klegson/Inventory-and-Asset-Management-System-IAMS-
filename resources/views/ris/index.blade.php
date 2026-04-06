<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RIS Approval Queue - Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .main-content { margin-left: 250px; padding: 20px; transition: all 0.3s; }
        .table-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-top: 4px solid #101954; }
        
        /* Status Badges */
        .status-pending { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .status-forwarded { background-color: #cff4fc; color: #055160; border: 1px solid #b6effb; }
        .status-approved { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .status-cancelled { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; } /* Added Red Styling */
        
        @media (max-width: 768px) { .main-content { margin-left: 0; } }
    </style>
</head>
<body>

    @include('layouts.header')
    @include('layouts.sidebar')

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-0"><i class="fas fa-clipboard-check text-primary me-2"></i>RIS Review Queue</h3>
                <small class="text-muted">Review incoming requests and verify stock availability before forwarding to Admin.</small>
            </div>
        </div>

        @if(session('msg'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">
                <i class="fas fa-check-circle me-2"></i>
                @if(session('msg') == 'updated') RIS successfully updated. @endif
                @if(session('msg') == 'forwarded') RIS successfully forwarded to Admin. @endif
                @if(session('msg') == 'returned') Approved RIS has been returned for corrections. @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-container">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>RIS No.</th>
                        <th>Requested By</th>
                        <th>Division / Office</th>
                        <th>Date Submitted</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $row)
                        @php
                            $status_class = 'status-pending';
                            
                            // Updated logic to catch all states perfectly
                            if($row->status == 'Approved') {
                                $status_class = 'status-approved';
                            } elseif($row->status == 'Forwarded to Admin') {
                                $status_class = 'status-forwarded';
                            } elseif(in_array($row->status, ['Cancelled', 'Declined', 'Rejected'])) {
                                $status_class = 'status-cancelled'; // Triggers the red badge
                            }
                        @endphp
                        <tr>
                            <td class="fw-bold text-primary">{{ $row->ris_no }}</td>
                            <td class="fw-bold">{{ $row->sig_requested_by }}</td>
                            <td>{{ $row->division }} / {{ $row->office }}</td>
                            <td>{{ \Carbon\Carbon::parse($row->created_at)->format('M d, Y') }}</td>
                            <td><span class="badge rounded-pill {{ $status_class }} px-3 py-2">{{ $row->status }}</span></td>
                            <td class="text-center">
                                <a href="{{ url('/ris/'.$row->id.'/review') }}" class="btn btn-primary btn-sm fw-bold">
                                    <i class="fas fa-search me-1"></i> Verify & Action
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No pending RIS requests at the moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>