<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RIS History - DepEd ROV</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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

        .sticky-header {
            position: sticky;
            top: 0;
            background-color: #f4f6f9;
            z-index: 90;
            padding: 30px 30px 10px 30px;
            margin: -30px -30px 20px -30px;
        }

        .welcome-banner {
            background: linear-gradient(135deg, #101954 0%, #0a4d9c 100%);
            color: white;   
            padding: 25px 30px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(16, 25, 84, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
        }

        .table-custom th {
            color: #6c757d;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 15px;
        }

        .table-custom td {
            vertical-align: middle;
            padding: 15px 10px;
            font-weight: 500;
            color: #333;
            border-bottom: 1px solid #f4f6f9;
        }

        /* Status Badges - Made consistent with Admin/Staff side */
        .status-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 700;
        }
        .status-approved { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .status-pending { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .status-declined { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
        .status-forwarded { background-color: #cff4fc; color: #055160; border: 1px solid #b6effb; }

        .filter-input {
            background-color: #f8f9fc;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px 15px;
            outline: none;
        }

        .btn-view {
            background-color: #f8f9fc;
            color: #101954;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 5px 15px;
            transition: 0.3s;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-block;
        }
        .btn-view:hover {
            background-color: #101954;
            color: white;
            border-color: #101954;
        }

        /* Custom Pagination Styling */
        .pagination { margin-bottom: 0; }
        .page-item.active .page-link {
            background-color: transparent;
            color: #198754;
            font-weight: 700;
            border-color: #dee2e6;
        }
        .page-link { color: #6c757d; }
        .page-link:hover { color: #198754; }

        @media (max-width: 768px) { 
            .main-content { margin-left: 0; } 
            .pagination-container { flex-direction: column; gap: 15px; }
        }
    </style>
</head>
<body>

    @include('layouts.user_sidebar')

    <div class="main-content">
        
        <div class="sticky-header">
            <div class="welcome-banner">
                <div>
                    <h2 class="fw-bold mb-1">Request History</h2>
                    <p class="mb-0 opacity-75">View and track all past Requisition and Issue Slips.</p>
                </div>
                <div class="d-none d-md-block">
                    <i class="fa-solid fa-clock-rotate-left" style="font-size: 3rem; opacity: 0.8;"></i>
                </div>
            </div>

            @if(session('msg'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('msg') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ url('/user/ris/history') }}" method="GET" id="filterForm">
                <input type="hidden" name="per_page" value="{{ request('per_page', 5) }}">
                <div class="row g-3 mb-2">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control filter-input border-start-0" placeholder="Search RIS No..." onchange="this.form.submit()">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="date" value="{{ request('date') }}" class="form-control filter-input" onchange="this.form.submit()">
                    </div>
                    <div class="col-md-4 text-end">
                        <select name="status" class="form-select filter-input d-inline-block w-auto pe-4" style="min-width: 140px;" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="declined" {{ request('status') == 'declined' ? 'selected' : '' }}>Declined</option>
                    </select>
                        <a href="{{ url('/user/ris/history') }}" class="btn btn-light border ms-2" title="Clear Filters"><i class="fas fa-undo"></i></a>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-custom table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>RIS No.</th>
                            <th>Requested By</th>
                            <th>Division</th>
                            <th>Items Summary</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $ris)
                            @php
                                // Exact logic to match Staff/Admin badges
                                $badgeClass = 'status-pending';
                                $icon = 'fa-regular fa-clock';
                                
                                if ($ris->status == 'Approved') {
                                    $badgeClass = 'status-approved';
                                    $icon = 'fa-solid fa-check';
                                } elseif (in_array($ris->status, ['Declined', 'Cancelled', 'Rejected'])) {
                                    $badgeClass = 'status-declined';
                                    $icon = 'fa-solid fa-xmark';
                                } elseif ($ris->status == 'Forwarded to Admin') {
                                    $badgeClass = 'status-forwarded';
                                    $icon = 'fa-solid fa-share';
                                }

                                $itemSummary = '';
                                if ($ris->items->count() > 0) {
                                    $summaryParts = [];
                                    foreach ($ris->items->take(2) as $item) {
                                        $summaryParts[] = $item->req_quantity . 'x ' . $item->description;
                                    }
                                    $itemSummary = implode(', ', $summaryParts);
                                    if ($ris->items->count() > 2) {
                                        $itemSummary .= '...';
                                    }
                                } else {
                                    $itemSummary = 'No items listed';
                                }
                            @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($ris->created_at)->format('M d, Y') }}</td>
                                <td class="text-primary fw-bold">{{ $ris->ris_no }}</td>
                                <td>{{ $ris->sig_requested_by ?: 'N/A' }}</td>
                                <td>{{ $ris->division}}</td>
                                <td class="text-muted small" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $itemSummary }}">
                                    {{ $itemSummary }}
                                </td>
                                <td>
                                    <span class="status-badge {{ $badgeClass }}"><i class="{{ $icon }} me-1"></i> {{ $ris->status }}</span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ url('/user/ris/' . $ris->id) }}" class="btn-view"><i class="fa-regular fa-eye"></i> View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-file-invoice fs-1 mb-3 opacity-25"></i><br>
                                    No requests match your current filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($requests->count() > 0)
                <div class="d-flex justify-content-between align-items-center mt-4 pagination-container border-top pt-3">
                    
                    <div class="d-flex align-items-center">
                        <span class="text-muted small me-3">Showing {{ $requests->firstItem() }} to {{ $requests->lastItem() }} of {{ $requests->total() }} records</span>
                        
                        <div class="d-flex align-items-center border rounded px-2 py-1 bg-light">
                            <span class="text-muted small me-2">Rows per page:</span>
                            <form action="{{ url('/user/ris/history') }}" method="GET" id="perPageForm" class="m-0">
                                <input type="hidden" name="search" value="{{ request('search') }}">
                                <input type="hidden" name="date" value="{{ request('date') }}">
                                <input type="hidden" name="status" value="{{ request('status') }}">
                                
                                <select name="per_page" class="form-select form-select-sm border-0 bg-transparent shadow-none" style="width: auto; cursor: pointer;" onchange="this.form.submit()">
                                    <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                                    <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                                </select>
                            </form>
                        </div>
                    </div>

                    <nav>
                        {{ $requests->appends(request()->query())->links() }}
                    </nav>
                </div>
            @endif

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>