<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>RIS Verification Queue - DepEd AMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .main-content { margin-left: 250px; padding: 20px; transition: all 0.3s; }
        .table-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .badge-pending { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .badge-approved { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .badge-acquired { background-color: #cfe2ff; color: #084298; border: 1px solid #b6d4fe; }
        .badge-cancelled { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
        .badge-forwarded { background-color: #cff4fc; color: #055160; border: 1px solid #b6effb;}

        /* --- Advanced Scrollable Pagination (Sticky Arrows) --- */
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
        .custom-pagination-wrapper ul.pagination > li:last-child .page-link { background-color: white !important; box-shadow: 0 0 5px rgba(0,0,0,0.15); }

        .page-item.active .page-link { background-color: #f4f6f9; color: #101954; font-weight: 700; border-color: #dee2e6; }
        .page-link { color: #6c757d; }
        .page-link:hover { color: #101954; background-color: #f4f6f9; }

        @media (max-width: 768px) { .main-content { margin-left: 0; } }
    </style>
</head>
<body>

    @include('layouts.admin_header')
    @include('layouts.admin_sidebar')

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3" style="border-color: #003366 !important;">
            <div>
                <h2 style="color: #003366; margin: 0;"><i class="fas fa-clipboard-check"></i> RIS Approval Queue</h2>
                <small class="text-muted">Review staff submissions and verify stock availability before approval.</small>
            </div>
        </div>

        @if(session('msg'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-1"></i> {{ session('msg') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-container">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>RIS No.</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Date Submitted</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $row)
                        @php
                            $badgeClass = 'badge-pending';
                            if ($row->status == 'Approved') {
                                $badgeClass = 'badge-approved';
                            } elseif ($row->status == 'Forwarded to Admin') {
                                $badgeClass = 'badge-forwarded';
                            } elseif (in_array($row->status, ['Cancelled', 'Declined', 'Rejected'])) {
                                $badgeClass = 'badge-cancelled'; 
                            }
                        @endphp
                        <tr>
                            <td class="fw-bold">{{ $row->ris_no }}</td>
                            <td>{{ $row->sig_requested_by ?: 'No Name Provided' }}</td>
                            <td>{{ $row->division }} <br>  <span class="badge text-white" style="background-color: #101954;">{{ $row->office }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($row->created_at)->format('M d, Y') }}</td>
                            <td>
                                <span class="badge rounded-pill {{ $badgeClass }}">
                                    {{ $row->status }}
                                </span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-primary verify-ris-btn" data-id="{{ $row->id }}">
                                    <i class="fas fa-search"></i> Verify & Action
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">No pending requests in the queue.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
                <div class="text-muted small">
                    Showing {{ $requests->firstItem() ?? 0 }} to {{ $requests->lastItem() ?? 0 }} of {{ $requests->total() }} results
                </div>

                <div class="d-flex align-items-center">
                    <span class="text-muted small me-2">Per page</span>
                    <form action="{{ url('/admin/requests') }}" method="GET" id="perPageForm">
                        <select name="per_page" class="form-select form-select-sm shadow-none" style="width: 70px; border-color: #101954; color: #101954; font-weight: 500;" onchange="document.getElementById('perPageForm').submit();">
                            <option value="7" {{ $perPage == 7 ? 'selected' : '' }}>7</option>
                            <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        </select>
                    </form>
                </div>

                <div class="custom-pagination-wrapper" id="styled-pagination">
                    {{ $requests->onEachSide(999)->appends(['per_page' => $perPage])->links() }}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="verifyRisModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered"> 
            <div class="modal-content shadow-lg border-0" id="verify_ris_content" style="border-radius: 10px;">
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.verify-ris-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const contentArea = document.getElementById('verify_ris_content');
                
                new bootstrap.Modal(document.getElementById('verifyRisModal')).show();
                contentArea.innerHTML = '<div class="p-5 text-center"><div class="spinner-border text-primary"></div><p class="mt-2 mb-0">Loading...</p></div>';

                fetch(`/admin/requests/${id}/verify`)
                    .then(response => response.text())
                    .then(data => { contentArea.innerHTML = data; });
            });
        });

        // Pagination Scroll Logic
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