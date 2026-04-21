<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RIS Approval Queue - Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* Lock body scroll */
        body { 
            background-color: #f4f6f9; 
            font-family: 'Segoe UI', sans-serif; 
            overflow: hidden; /* Prevents entire page from scrolling */
            height: 100vh;
            margin: 0;
        }

        /* Flexbox Layout to utilize 100vh properly */
        .main-content { 
            margin-left: 250px; 
            padding: 20px; 
            padding-top: 80px !important; /* Spacing for fixed header */
            transition: all 0.3s; 
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Table Card matches flex height */
        .table-container { 
            background: white; 
            padding: 20px 20px 10px 20px; 
            border-radius: 8px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); 
            border-top: 4px solid #101954; 
            flex-grow: 1; /* Takes up remaining height */
            display: flex;
            flex-direction: column;
            min-height: 0; /* Important for flex children scrolling */
        }

        /* Scrollable table body */
        .table-responsive {
            flex-grow: 1;
            overflow-y: auto; /* Scroll only inside the table */
            margin-bottom: 10px;
        }

        /* Sticky Table Headers */
        .table thead th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 1;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        /* Status Badges */
        .status-pending { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .status-forwarded { background-color: #cff4fc; color: #055160; border: 1px solid #b6effb; }
        .status-approved { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .status-cancelled { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; } 

        /* Advanced Scrollable Pagination */
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
            .table-container { min-height: 500px; }
        }
    </style>
</head>
<body>

    @include('layouts.header')
    @include('layouts.sidebar')

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="fw-bold text-dark mb-0"><i class="fas fa-clipboard-check text-primary me-2"></i>RIS Review Queue</h3>
                <small class="text-muted">Review incoming requests and verify stock availability before forwarding to Admin.</small>
            </div>
        </div>

        @if(session('msg'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm py-2">
                <i class="fas fa-check-circle me-2"></i>
                @if(session('msg') == 'updated') RIS successfully updated. @endif
                @if(session('msg') == 'forwarded') RIS successfully forwarded to Admin. @endif
                @if(session('msg') == 'returned') Approved RIS has been returned for corrections. @endif
                <button type="button" class="btn-close btn-sm pt-3" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-container">
            
            <form action="{{ url('/ris') }}" method="GET" id="filterForm" class="d-flex justify-content-between align-items-center mb-3 pe-2">
                <div class="input-group" style="width: 300px;">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" id="risSearchInput" class="form-control border-start-0 ps-0" placeholder="Search RIS No. or Requester..." value="{{ request('search') }}">
                </div>

                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                        <i class="fas fa-filter me-1"></i> Filter & Sort
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-3 shadow-lg border-0" style="width: 250px;">
                        
                        <h6 class="dropdown-header px-0 text-dark fw-bold"><i class="fas fa-tasks me-2"></i>Status</h6>
                        <select name="status_filter" class="form-select form-select-sm mb-3 cursor-pointer" onchange="document.getElementById('filterForm').submit();">
                            <option value="All" {{ request('status_filter') == 'All' ? 'selected' : '' }}>All Statuses</option>
                            <option value="Pending Staff Review" {{ request('status_filter') == 'Pending Staff Review' ? 'selected' : '' }}>Pending Staff Review</option>
                            <option value="Forwarded to Admin" {{ request('status_filter') == 'Forwarded to Admin' ? 'selected' : '' }}>Forwarded to Admin</option>
                            <option value="Approved" {{ request('status_filter') == 'Approved' ? 'selected' : '' }}>Approved</option>
                            <option value="Declined" {{ request('status_filter') == 'Declined' ? 'selected' : '' }}>Declined</option>
                            <option value="Cancelled" {{ request('status_filter') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>

                        <h6 class="dropdown-header px-0 text-dark fw-bold"><i class="fas fa-sort-amount-down me-2"></i>Sort By</h6>
                        <select name="sort" class="form-select form-select-sm mb-3 cursor-pointer" onchange="document.getElementById('filterForm').submit();">
                            <option value="latest" {{ request('sort', 'latest') == 'latest' ? 'selected' : '' }}>Date (Newest First)</option>
                            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Date (Oldest First)</option>
                            <option value="priority" {{ request('sort') == 'priority' ? 'selected' : '' }}>Needs Action (Priority)</option>
                        </select>

                        <a href="{{ url('/ris') }}" class="btn btn-sm btn-light w-100 border text-danger fw-bold">Clear Filters</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
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
                                
                                if($row->status == 'Approved') {
                                    $status_class = 'status-approved';
                                } elseif($row->status == 'Forwarded to Admin') {
                                    $status_class = 'status-forwarded';
                                } elseif(in_array($row->status, ['Cancelled', 'Declined', 'Rejected'])) {
                                    $status_class = 'status-cancelled'; 
                                }
                            @endphp
                            <tr>
                                <td class="fw-bold text-primary">{{ $row->ris_no }}</td>
                                <td class="fw-bold">{{ $row->sig_requested_by }}</td>
                                <td>{{ $row->division }} <br> <span class="badge text-white" style="background-color: #101954;">{{ $row->office }}</span></td>
                                <td>{{ \Carbon\Carbon::parse($row->created_at)->format('M d, Y') }}</td>
                                <td><span class="badge rounded-pill {{ $status_class }} px-3 py-2">{{ $row->status }}</span></td>
                                <td class="text-center">
                                    <a href="{{ url('/ris/'.$row->id.'/review') }}" class="btn btn-primary btn-sm fw-bold shadow-sm">
                                        <i class="fas fa-search me-1"></i> Verify & Action
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted border-bottom-0">
                                    <i class="fas fa-clipboard-list fa-3x mb-3 opacity-25 d-block"></i>
                                    No pending RIS requests match your search.
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
                    <form action="{{ url('/ris') }}" method="GET" id="perPageForm">
                        @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                        @if(request('status_filter')) <input type="hidden" name="status_filter" value="{{ request('status_filter') }}"> @endif
                        @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
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

                // Keep cursor focused and at the end of the text after reload
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