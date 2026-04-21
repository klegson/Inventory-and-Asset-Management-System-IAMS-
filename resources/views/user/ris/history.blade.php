<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RIS History - DepEd ROV</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Lock body scroll */
        body { 
            background-color: #f4f6f9; 
            font-family: 'Segoe UI', sans-serif; 
            overflow: hidden; 
            height: 100vh;
            margin: 0;
        }

        /* Flexbox Layout */
        .main-content { 
            margin-left: 250px; 
            padding: 20px; 
            padding-top: 80px !important; 
            transition: all 0.3s; 
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .welcome-banner {
            background: linear-gradient(135deg, #101954 0%, #0a4d9c 100%);
            color: white;   
            padding: 25px 30px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(16, 25, 84, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        /* Table Card matches flex height */
        .table-card { 
            background: white; 
            padding: 20px 20px 10px 20px; 
            border-radius: 8px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); 
            flex-grow: 1; 
            display: flex;
            flex-direction: column;
            min-height: 0; 
        }

        /* Scrollable table body */
        .table-responsive {
            flex-grow: 1;
            overflow-y: auto; 
            margin-bottom: 10px;
        }

        /* Sticky Table Headers */
        .table-custom th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 1;
            color: #6c757d;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            border-bottom: 2px solid #e9ecef;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .table-custom td {
            vertical-align: middle;
            padding: 15px 10px;
            font-weight: 500;
            color: #333;
            border-bottom: 1px solid #f4f6f9;
        }

        /* Status Badges */
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
            padding: 8px 15px;
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
        .btn-view:hover { background-color: #101954; color: white; border-color: #101954; }

        /* Custom Pagination Styling */
        #styled-pagination nav > div:not(:last-child),
        #styled-pagination p { display: none !important; }

        .custom-pagination-wrapper ul.pagination {
            position: relative; 
            display: flex; 
            flex-wrap: nowrap;
            max-width: 250px; 
            overflow-x: auto; 
            overflow-y: hidden;
            scrollbar-width: thin; 
            scrollbar-color: #101954 #f4f6f9;
            padding-bottom: 4px;
            margin-bottom: 0;
        }
        
        .custom-pagination-wrapper ul.pagination::-webkit-scrollbar { height: 6px; }
        .custom-pagination-wrapper ul.pagination::-webkit-scrollbar-track { background: #f4f6f9; border-radius: 10px; }
        .custom-pagination-wrapper ul.pagination::-webkit-scrollbar-thumb { background: #101954; border-radius: 10px; }

        .custom-pagination-wrapper ul.pagination > li:first-child { position: sticky; left: 0; z-index: 5; }
        .custom-pagination-wrapper ul.pagination > li:last-child { position: sticky; right: 0; z-index: 5; }
        
        .custom-pagination-wrapper ul.pagination > li:first-child .page-link,
        .custom-pagination-wrapper ul.pagination > li:last-child .page-link {
            background-color: white !important;
            box-shadow: 0 0 5px rgba(0,0,0,0.15); 
        }

        .page-item.active .page-link { background-color: #f4f6f9; color: #101954; font-weight: 700; border-color: #dee2e6; }
        .page-link { color: #6c757d; }
        .page-link:hover { color: #101954; background-color: #f4f6f9; }

        @media (max-width: 768px) { 
            .main-content { margin-left: 0; height: auto; overflow: visible; } 
            body { overflow: visible; height: auto; }
            .table-card { min-height: 500px; }
        }
    </style>
</head>
<body>

    @include('layouts.user_header')
    @include('layouts.user_sidebar')

    <div class="main-content">
        
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
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm py-2" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('msg') }}
                <button type="button" class="btn-close pt-3" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ url('/user/ris/history') }}" method="GET" id="filterForm">
            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
            <div class="row g-3 mb-3">
                <div class="col-md-5">
                    <div class="input-group shadow-sm border-0">
                        <span class="input-group-text bg-white border-end-0 border-light"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" id="risSearchInput" value="{{ request('search') }}" class="form-control filter-input border-start-0 border-light" placeholder="Search RIS No...">
                    </div>
                </div>
                <div class="col-md-3">
                    <input type="date" name="date" value="{{ request('date') }}" class="form-control filter-input shadow-sm border-light" onchange="this.form.submit()">
                </div>
                <div class="col-md-4 text-end">
                    <select name="status" class="form-select filter-input d-inline-block w-auto pe-4 shadow-sm border-light" style="min-width: 140px;" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="declined" {{ request('status') == 'declined' ? 'selected' : '' }}>Declined</option>
                    </select>
                    <a href="{{ url('/user/ris/history') }}" class="btn btn-light border ms-2 shadow-sm" title="Clear Filters"><i class="fas fa-undo"></i></a>
                </div>
            </div>
        </form>

        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-custom table-hover align-middle mb-0">
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
                                    <a href="{{ url('/user/ris/' . $ris->id) }}" class="btn-view shadow-sm"><i class="fa-regular fa-eye"></i> View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted border-bottom-0">
                                    <i class="fas fa-file-invoice fs-1 mb-3 opacity-25 d-block"></i>
                                    No requests match your current filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-2">
                <div class="text-muted small">
                    Showing {{ $requests->firstItem() ?? 0 }} to {{ $requests->lastItem() ?? 0 }} of {{ $requests->total() }} entries
                </div>

                <div class="d-flex align-items-center">
                    <span class="text-muted small me-2">Per page</span>
                    <form action="{{ url('/user/ris/history') }}" method="GET" id="perPageForm">
                        @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                        @if(request('date')) <input type="hidden" name="date" value="{{ request('date') }}"> @endif
                        @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                        <select name="per_page" class="form-select form-select-sm shadow-none" style="width: 70px; border-color: #101954; color: #101954; font-weight: 500;" onchange="document.getElementById('perPageForm').submit();">
                            <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        </select>
                    </form>
                </div>

                <div class="custom-pagination-wrapper" id="styled-pagination">
                    {{ $requests->onEachSide(1)->appends(request()->query())->links() }}
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-search logic (Debounce)
        document.addEventListener("DOMContentLoaded", function() {
            const risSearchInput = document.getElementById('risSearchInput');
            const filterForm = document.getElementById('filterForm');
            let typingTimer;

            if(risSearchInput) {
                risSearchInput.addEventListener('input', function() {
                    clearTimeout(typingTimer);
                    typingTimer = setTimeout(() => {
                        filterForm.submit();
                    }, 600); // Waits 600ms after user stops typing
                });

                // Keep cursor focused
                if (risSearchInput.value.length > 0) {
                    risSearchInput.focus();
                    const val = risSearchInput.value;
                    risSearchInput.value = '';
                    risSearchInput.value = val;
                }
            }
        });

        // Advanced Pagination Scroll & Auto-Center
        window.addEventListener('load', function() {
            const paginationUl = document.querySelector('.custom-pagination-wrapper ul.pagination');
            
            if (paginationUl) {
                paginationUl.addEventListener('wheel', function(e) {
                    if (e.deltaY !== 0) {
                        e.preventDefault();
                        this.scrollLeft += (e.deltaY * 1.5);
                    }
                }, { passive: false });

                setTimeout(() => {
                    const activeLi = paginationUl.querySelector('.page-item.active');
                    if (activeLi) {
                        const ulRect = paginationUl.getBoundingClientRect();
                        const liRect = activeLi.getBoundingClientRect();
                        const scrollPos = paginationUl.scrollLeft + (liRect.left - ulRect.left) - (ulRect.width / 2) + (liRect.width / 2);
                        
                        paginationUl.scrollLeft = scrollPos;
                        setTimeout(() => { paginationUl.style.scrollBehavior = 'smooth'; }, 50);
                    }
                }, 150); 
            }
        });
    </script>
</body>
</html>