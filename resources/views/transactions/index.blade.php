<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Transaction History - Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .main-content { margin-left: 250px; padding: 20px; transition: all 0.3s; }
        
        .table-responsive {
            max-height: 65vh;
            overflow-y: auto;
            border-bottom: 1px solid #dee2e6;
        }

        .table thead th {
            position: sticky;
            top: 0;
            background-color: #e9ecef;
            color: #495057;
            z-index: 10;
            box-shadow: 0 2px 2px -1px rgba(0,0,0,0.1);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            padding-top: 12px;
            padding-bottom: 12px;
        }

        .history-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: none;
            overflow: hidden;
            padding: 20px;
        }
        
        .badge-in { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .badge-added { background-color: #cfe2ff; color: #084298; border: 1px solid #b6d4fe; } 
        .badge-out { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
        
        .text-small { font-size: 0.9rem; }

        /* --- Advanced Scrollable Pagination (Sticky Arrows) --- */
        #styled-pagination nav > div:not(:last-child),
        #styled-pagination p { display: none !important; } /* Hide Laravel duplicate text */

        .custom-pagination-wrapper ul.pagination {
            position: relative; 
            display: flex; 
            flex-wrap: nowrap;
            max-width: 250px; /* Adjust to fit roughly 5 numbers + arrows */
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

        /* MAGIC TRICK: Sticky Arrows */
        .custom-pagination-wrapper ul.pagination > li:first-child {
            position: sticky;
            left: 0;
            z-index: 5;
        }
        .custom-pagination-wrapper ul.pagination > li:last-child {
            position: sticky;
            right: 0;
            z-index: 5;
        }
        /* Solid background so numbers hide when they slide underneath */
        .custom-pagination-wrapper ul.pagination > li:first-child .page-link,
        .custom-pagination-wrapper ul.pagination > li:last-child .page-link {
            background-color: white !important;
            box-shadow: 0 0 5px rgba(0,0,0,0.15); /* Adds a soft drop shadow separator */
        }

        /* General page link styling */
        .page-item.active .page-link { background-color: #f4f6f9; color: #101954; font-weight: 700; border-color: #dee2e6; }
        .page-link { color: #6c757d; }
        .page-link:hover { color: #101954; background-color: #f4f6f9; }

        @media (max-width: 768px) { .main-content { margin-left: 0; } }

        @media print {
            .table-responsive { max-height: none; overflow: visible; }
            .main-content { margin: 0; padding: 0; }
            .btn, .sidebar, .search-box, .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    @include('layouts.header')
    @include('layouts.sidebar')

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <div>
                <h3 class="fw-bold text-dark mb-0"><i class="fas fa-history text-primary me-2"></i>Transaction History</h3>
                <small class="text-muted">Track all inventory movements (In, Out, and New Items).</small>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-dark shadow-sm" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print Log
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3 search-box no-print">
            <div class="card-body p-3">
                <div class="row g-2">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Search by item name, barcode, or remarks...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select id="typeFilter" class="form-select border-primary">
                            <option value="all">All Transaction Types</option>
                            <option value="IN">Stock IN</option>
                            <option value="OUT">Stock OUT</option>
                            <option value="ADDED">Newly Added</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card history-card">
            <div class="card-body p-0">
                <div class="table-responsive mb-0">
                    <table class="table table-hover align-middle mb-0" id="transactionTable">
                        <thead>
                            <tr>
                                <th class="ps-4">Date & Time</th>
                                <th class="text-center">Type</th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Supplier / Recipient</th>
                                <th class="text-center">Qty</th>
                                <th>Remarks</th>
                                <th class="text-center no-print">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $row)
                                @php
                                    $badgeClass = 'badge-in';
                                    $icon = 'fa-arrow-down';
                                    $typeUpper = strtoupper($row->transaction_type);
                                    
                                    if ($typeUpper == 'OUT') {
                                        $badgeClass = 'badge-out';
                                        $icon = 'fa-arrow-up';
                                    } elseif ($typeUpper == 'ADDED') {
                                        $badgeClass = 'badge-added';
                                        $icon = 'fa-plus';
                                    }
                                    
                                    $dateObj = \Carbon\Carbon::parse($row->date_time);
                                @endphp
                                <tr class="tx-row" data-type="{{ $typeUpper }}">
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $dateObj->format('M d, Y') }}</div>
                                        <small class="text-muted">{{ $dateObj->format('h:i A') }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill {{ $badgeClass }} px-3 py-2">
                                            <i class="fas {{ $icon }} me-1"></i> {{ $typeUpper }}
                                        </span>
                                    </td>
                                    <td class="searchable">
                                        <span class="fw-bold d-block text-dark">
                                            {{ $row->item_name ?? 'Unknown Item' }}
                                        </span>
                                        <small class="text-muted font-monospace">
                                            {{ $row->item_code ?? '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        @if(strtolower($row->item_type) == 'supplies')
                                            <span class="badge bg-success border-0 px-2 py-1">SUPPLY</span>
                                        @else
                                            <span class="badge bg-primary border-0 px-2 py-1">ASSET</span>
                                        @endif
                                    </td>
                                    <td class="text-muted searchable">
                                        {{ !empty($row->supplier) ? $row->supplier : '-' }}
                                    </td>
                                    <td class="text-center fw-bold fs-6">
                                        {{ $row->quantity }}
                                    </td>
                                    <td class="text-small text-secondary searchable" style="max-width: 200px;">
                                        {{ $row->remarks }}
                                    </td>
                                    <td class="text-center no-print">
                                        <button class="btn btn-sm btn-outline-primary view-btn" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewTxModal"
                                                data-date="{{ $dateObj->format('F d, Y h:i A') }}"
                                                data-type="{{ $typeUpper }}"
                                                data-item="{{ $row->item_name ?? 'Unknown Item' }}"
                                                data-code="{{ $row->item_code ?? '-' }}"
                                                data-qty="{{ $row->quantity }}"
                                                data-remarks="{{ $row->remarks }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-clipboard-list fa-2x mb-3 text-secondary opacity-50"></i>
                                        <p>No transaction history found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3 no-print">
                    <div class="text-muted small">
                        Showing {{ $transactions->firstItem() ?? 0 }} to {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} results
                    </div>

                    <div class="d-flex align-items-center">
                        <span class="text-muted small me-2">Per page</span>
                        <form action="{{ url('/transactions') }}" method="GET" id="perPageForm">
                            <select name="per_page" class="form-select form-select-sm shadow-none" style="width: 70px; border-color: #101954; color: #101954; font-weight: 500;" onchange="document.getElementById('perPageForm').submit();">
                                <option value="6" {{ $perPage == 6 ? 'selected' : '' }}>6</option>
                                <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
                                <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                            </select>
                        </form>
                    </div>

                    <div class="custom-pagination-wrapper" id="styled-pagination">
                        {{ $transactions->onEachSide(999)->appends(['per_page' => $perPage])->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewTxModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="fas fa-file-invoice me-2"></i> Transaction Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <h4 id="m_item" class="fw-bold text-dark mb-0"></h4>
                        <div id="m_code" class="text-muted font-monospace small"></div>
                    </div>
                    
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Transaction Type</span>
                            <span id="m_type" class="fw-bold"></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Quantity</span>
                            <span id="m_qty" class="fw-bold fs-5"></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Date & Time</span>
                            <span id="m_date" class="fw-bold"></span>
                        </li>
                        <li class="list-group-item px-0 pt-3 border-bottom-0">
                            <span class="text-muted d-block mb-1">Remarks / Reference</span>
                            <div id="m_remarks" class="p-3 bg-light rounded border text-dark"></div>
                        </li>
                    </ul>
                </div>
                <div class="modal-footer border-0 pb-4 justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // JS Filter and Search Logic
            function filterTable() {
                let search = $('#searchInput').val().toLowerCase();
                let type = $('#typeFilter').val();

                $('.tx-row').each(function() {
                    let rowType = $(this).data('type');
                    let text = $(this).find('.searchable').text().toLowerCase();
                    
                    let matchSearch = text.indexOf(search) > -1;
                    let matchType = (type === 'all' || rowType === type);

                    if (matchSearch && matchType) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }

            $('#searchInput').on('keyup', filterTable);
            $('#typeFilter').on('change', filterTable);

            // Populate Modal Data
            $('.view-btn').on('click', function() {
                $('#m_item').text($(this).data('item'));
                $('#m_code').text($(this).data('code'));
                $('#m_qty').text($(this).data('qty'));
                $('#m_date').text($(this).data('date'));
                $('#m_remarks').text($(this).data('remarks') || 'No remarks provided.');
                
                let type = $(this).data('type');
                let typeClass = type === 'IN' ? 'text-success' : (type === 'OUT' ? 'text-danger' : 'text-primary');
                $('#m_type').html(`<span class="${typeClass}">${type}</span>`);
            });
        });

        // Improved Pagination Logic (Mouse Wheel + Bulletproof Auto-Center Active Item)
        document.addEventListener("DOMContentLoaded", function() {
            const paginationUl = document.querySelector('.custom-pagination-wrapper ul.pagination');
            
            if (paginationUl) {
                // 1. Smooth Mouse Wheel Scrolling
                paginationUl.addEventListener('wheel', function(e) {
                    if (e.deltaY !== 0) {
                        e.preventDefault();
                        this.scrollLeft += (e.deltaY * 1.5);
                    }
                }, { passive: false });

                // 2. Bulletproof Auto-scroll using bounding rectangles
                setTimeout(() => {
                    const activeLi = paginationUl.querySelector('.page-item.active');
                    if (activeLi) {
                        const ulRect = paginationUl.getBoundingClientRect();
                        const liRect = activeLi.getBoundingClientRect();
                        
                        const scrollPos = paginationUl.scrollLeft + (liRect.left - ulRect.left) - (ulRect.width / 2) + (liRect.width / 2);
                        
                        paginationUl.scrollLeft = scrollPos;
                        
                        setTimeout(() => {
                            paginationUl.style.scrollBehavior = 'smooth';
                        }, 50);
                    }
                }, 150); 
            }
        });
    </script>
</body>
</html> 