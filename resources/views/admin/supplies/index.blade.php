<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplies Inventory - DepEd AMS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <style>
        /* Lock body scroll */
        body { 
            background-color: #f4f6f9; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            overflow: hidden; 
            height: 100vh;
            margin: 0;
        }

        /* Flexbox Layout to utilize 100vh properly */
        .main-content { 
            margin-left: 250px; 
            padding: 20px; 
            padding-top: 80px !important; 
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
        .table thead th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 1;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .status-available { background-color: #d1e7dd; color: #0f5132; }
        .status-low { background-color: #fff3cd; color: #856404; }
        .status-out { background-color: #f8d7da; color: #842029; }
        
        .clickable-row { cursor: pointer; transition: background-color 0.2s; }
        .clickable-row:hover { background-color: #f8f9fa !important; }

        /* --- Advanced Scrollable Pagination (Sticky Arrows) --- */
        #scrollablePagination nav > div:not(:last-child),
        #scrollablePagination p { display: none !important; }

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

    @include('layouts.admin_header')
    @include('layouts.admin_sidebar')

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="fw-bold text-dark mb-0" style="color: #003366 !important;">
                    <i class="fas fa-box-open text-primary me-2"></i>Supplies Inventory (Consumables)
                </h3>
            </div>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addSupplyModal">
                <i class="fas fa-plus me-2"></i> Add New Supply
            </button>
        </div>

        @if(session('msg'))
            <div class="alert alert-success alert-dismissible fade show py-2 border-0 shadow-sm">
                <i class="fas fa-check-circle me-2"></i> {{ session('msg') }} 
                <button type="button" class="btn-close btn-sm pt-3" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-container">
            
            <form action="{{ url('/admin/supplies') }}" method="GET" id="filterForm" class="d-flex justify-content-between align-items-center mb-3 pe-2 gap-2">
                <div class="d-flex gap-2 flex-grow-1">
                    <div class="input-group shadow-sm" style="max-width: 350px;">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" id="supplySearchInput" class="form-control border-start-0 ps-0" placeholder="Search Stock No., Article, or Desc..." value="{{ request('search') }}">
                    </div>
                    
                    <select name="status_filter" class="form-select shadow-sm" style="max-width: 180px;" onchange="document.getElementById('filterForm').submit();">
                        <option value="All" {{ request('status_filter') == 'All' ? 'selected' : '' }}>All Statuses</option>
                        <option value="Available" {{ request('status_filter') == 'Available' ? 'selected' : '' }}>Available</option>
                        <option value="Low Stock" {{ request('status_filter') == 'Low Stock' ? 'selected' : '' }}>Low Stock</option>
                        <option value="Out of Stock" {{ request('status_filter') == 'Out of Stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>

                @if(request('search') || request('status_filter') && request('status_filter') !== 'All')
                    <a href="{{ url('/admin/supplies') }}" class="btn btn-outline-danger btn-sm fw-bold shadow-sm"><i class="fas fa-times me-1"></i> Clear Filters</a>
                @endif
            </form>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Stock No.</th>
                            <th>Article / Item</th>
                            <th>Description</th>
                            <th>Unit</th>
                            <th>Value</th>
                            <th>Qty</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($supplies as $row)
                            @php
                                $threshold = $row->low_stock_threshold ?? 10;
                                $status_class = 'status-available';
                                $status_text = 'Available';
                                if($row->quantity == 0) { $status_class = 'status-out'; $status_text = 'Out of Stock'; }
                                elseif($row->quantity <= $threshold) { $status_class = 'status-low'; $status_text = 'Low Stock'; }
                            @endphp
                            <tr class="clickable-row" data-id="{{ $row->id }}">
                                <td class="fw-bold text-primary font-monospace">{{ $row->barcode_id ?: 'N/A' }}</td>
                                <td class="fw-bold">{{ $row->article }}</td>
                                <td><small class="text-muted">{{ Str::limit($row->description, 40) }}</small></td>
                                <td>{{ $row->unit_measure }}</td>
                                <td>₱{{ number_format($row->unit_value, 2) }}</td>
                                <td class="fw-bold fs-5">{{ $row->quantity }}</td>
                                <td><span class="badge rounded-pill {{ $status_class }} px-2 py-1">{{ $status_text }}</span></td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <button class="btn btn-sm btn-light border text-primary view-btn" 
                                                title="View" 
                                                data-id="{{ $row->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <button class="btn btn-sm btn-light border text-success edit-btn" 
                                                title="Edit"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editSupplyModal"
                                                data-id="{{ $row->id }}"
                                                data-article="{{ $row->article }}"
                                                data-stock="{{ $row->barcode_id }}"
                                                data-desc="{{ $row->description }}"
                                                data-unit="{{ $row->unit_measure }}"
                                                data-value="{{ $row->unit_value }}"
                                                data-qty="{{ $row->quantity }}"
                                                data-threshold="{{ $row->low_stock_threshold ?? 10 }}"
                                                data-image="{{ $row->image }}"
                                                data-supplier="{{ $row->supplier }}">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <button class="btn btn-sm btn-light border text-danger delete-btn" 
                                                title="Delete"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteSupplyModal"
                                                data-id="{{ $row->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted border-bottom-0">
                                    <i class="fas fa-box-open fa-3x mb-3 opacity-25 d-block"></i>
                                    No supplies match your search.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-2">
                <div class="text-muted small">
                    Showing {{ $supplies->firstItem() ?? 0 }} to {{ $supplies->lastItem() ?? 0 }} of {{ $supplies->total() }} results
                </div>

                <div class="d-flex align-items-center">
                    <span class="text-muted small me-2">Per page</span>
                    <form action="{{ url('/admin/supplies') }}" method="GET" id="perPageForm">
                        @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                        @if(request('status_filter')) <input type="hidden" name="status_filter" value="{{ request('status_filter') }}"> @endif
                        <select name="per_page" class="form-select form-select-sm shadow-none" style="width: 70px; border-color: #101954; color: #101954; font-weight: 500;" onchange="document.getElementById('perPageForm').submit();">
                            <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        </select>
                    </form>
                </div>

                <div class="custom-pagination-wrapper" id="scrollablePagination">
                    {{ $supplies->onEachSide(1)->appends(request()->query())->links() }}
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="addSupplyModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Add New Supply</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="addSupplyForm" action="{{ url('/admin/supplies') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-3 text-center border-end pe-4">
                                <label class="form-label fw-bold d-block text-start">Supply Image</label>
                                <div class="border rounded bg-light d-flex justify-content-center align-items-center mx-auto mb-3 overflow-hidden shadow-sm" 
                                     style="width: 100%; aspect-ratio: 1/1; position: relative;">
                                    <img id="imagePreviewAdd" src="" alt="Preview" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                                    <i id="imagePlaceholderAdd" class="fas fa-image fa-4x text-muted opacity-50"></i>
                                </div>
                                <input type="file" name="image" id="imageInputAdd" class="form-control form-control-sm" accept="image/*">
                                <small class="text-muted text-start d-block mt-2">Recommended: Square format (JPG, PNG)</small>
                            </div>
                            
                            <div class="col-md-9 ps-4">
                                <div class="mb-4 bg-light p-3 rounded border">
                                    <label class="form-label text-primary fw-bold mb-2"><i class="fas fa-magic me-1"></i> Auto-Fill from Delivered P.O. (Optional)</label>
                                    <select id="po_autofill_select" class="form-select border-primary shadow-sm" onchange="autoFillSupplyForm(this)">
                                        <option value="">Select a delivered item to auto-fill the form...</option>
                                        @if(isset($deliveredPoItems) && count($deliveredPoItems) > 0)
                                            @php
                                                $groupedItems = $deliveredPoItems->groupBy(function($item) {
                                                    return $item->purchaseOrder->po_no ?? 'Unknown PO';
                                                });
                                            @endphp
                                            @foreach($groupedItems as $poNo => $items)
                                                <optgroup label="P.O. {{ $poNo }}">
                                                    @foreach($items as $item)
                                                        <option value="{{ $item->id }}" 
                                                                data-desc="{{ $item->description }}"
                                                                data-supplier="{{ $item->purchaseOrder->supplier_name ?? '' }}"
                                                                data-unit="{{ $item->unit }}"
                                                                data-val="{{ $item->unit_cost }}"
                                                                data-qty="{{ $item->qty }}">
                                                            {{ Str::limit($item->description, 45) }} (Qty: {{ $item->qty }})
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Article (Name) <span class="text-danger">*</span></label>
                                        <input type="text" name="article" id="add_article" class="form-control" required placeholder="e.g. Bond Paper">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Supplier</label>
                                        <input type="text" name="supplier" id="add_supplier" class="form-control" placeholder="e.g. Pandayan">
                                    </div>
                                    
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Description</label>
                                        <textarea name="description" id="add_desc" class="form-control" rows="2" placeholder="e.g. A4 Size, 70gsm, White"></textarea>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Stock No. (Barcode)</label>
                                        <input type="text" class="form-control bg-light text-muted" placeholder="Auto-generated" disabled>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Unit Measure <span class="text-danger">*</span></label>
                                        <select name="unit_measure" id="add_unit" class="form-select" required>
                                            <option value="" selected disabled>Select Unit</option>
                                            <optgroup label="Individual Pieces">
                                                <option value="Piece(s)">Piece(s) (pc/s)</option>
                                                <option value="Unit">Unit</option>
                                                <option value="Set">Set</option>
                                                <option value="Pair">Pair</option>
                                            </optgroup>
                                            <optgroup label="Paper Products">
                                                <option value="Ream">Ream</option>
                                                <option value="Pad">Pad</option>
                                                <option value="Book">Book</option>
                                                <option value="Sheet">Sheet</option>
                                            </optgroup>
                                            <optgroup label="Bulk/Packaging">
                                                <option value="Box">Box</option>
                                                <option value="Carton">Carton (ctn)</option>
                                                <option value="Pack">Pack (pk)</option>
                                                <option value="Bundle">Bundle</option>
                                                <option value="Case">Case</option>
                                            </optgroup>
                                            <optgroup label="Liquids/Chemicals">
                                                <option value="Bottle">Bottle (btl)</option>
                                                <option value="Can">Can</option>
                                                <option value="Gallon">Gallon (gal)</option>
                                                <option value="Liter">Liter (L)</option>
                                                <option value="Milliliter">Milliliter (mL)</option>
                                            </optgroup>
                                            <optgroup label="Length/Volume">
                                                <option value="Roll">Roll</option>
                                                <option value="Meter">Meter (m)</option>
                                                <option value="Tube">Tube</option>
                                                <option value="Jar">Jar</option>
                                            </optgroup>
                                            <optgroup label="Weight">
                                                <option value="Kilogram">Kilogram (kg)</option>
                                                <option value="Gram">Gram (g)</option>
                                                <option value="Bag">Bag</option>
                                            </optgroup>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Unit Value (₱) <span class="text-danger">*</span></label>
                                        <input type="number" name="unit_value" id="add_val" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-success">Initial Quantity <span class="text-danger">*</span></label>
                                        <input type="number" name="initial_quantity" id="add_qty" class="form-control border-success border-2" min="0" placeholder="0" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-warning">Low Stock Alert Threshold <span class="text-danger">*</span></label>
                                        <input type="number" name="low_stock_threshold" class="form-control border-warning border-2 bg-warning bg-opacity-10" min="1" value="10" required>
                                        <small class="text-muted d-block mt-1">System warns you when stock hits this number.</small>
                                    </div>
                                </div>
                                <input type="hidden" name="status" value="Available">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">Save Supply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editSupplyModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Supply</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-3 text-center border-end pe-4">
                                <label class="form-label fw-bold d-block text-start">Update Image (Optional)</label>
                                <div class="border rounded bg-light d-flex justify-content-center align-items-center mx-auto mb-3 overflow-hidden shadow-sm" 
                                     style="width: 100%; aspect-ratio: 1/1; position: relative;">
                                    <img id="imagePreviewEdit" src="" alt="Preview" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                                    <i id="imagePlaceholderEdit" class="fas fa-image fa-4x text-muted opacity-50"></i>
                                </div>
                                <input type="file" name="image" id="imageInputEdit" class="form-control form-control-sm" accept="image/*">
                                <small class="text-muted text-start d-block mt-2">Leave blank to keep current image.</small>
                            </div>
                            
                            <div class="col-md-9 ps-4">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="alert alert-light border-success border-start border-4 py-2 px-3 mb-1 d-flex align-items-center justify-content-between">
                                            <span><i class="fas fa-barcode text-success me-2"></i>Stock No. (Barcode)</span>
                                            <input type="text" name="barcode_id" id="edit_stock" class="form-control form-control-sm bg-white fw-bold w-50" readonly required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Article (Name) <span class="text-danger">*</span></label>
                                        <input type="text" name="article" id="edit_article" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Supplier</label>
                                        <input type="text" name="supplier" id="edit_supplier" class="form-control">
                                    </div>
                                    
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Description</label>
                                        <textarea name="description" id="edit_desc" class="form-control" rows="2"></textarea>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Unit Measure <span class="text-danger">*</span></label>
                                        <select name="unit_measure" id="edit_unit" class="form-select" required>
                                            <option value="" disabled>Select Unit</option>
                                            <optgroup label="Individual Pieces">
                                                <option value="Piece(s)">Piece(s) (pc/s)</option>
                                                <option value="Unit">Unit</option>
                                                <option value="Set">Set</option>
                                                <option value="Pair">Pair</option>
                                            </optgroup>
                                            <optgroup label="Paper Products">
                                                <option value="Ream">Ream</option>
                                                <option value="Pad">Pad</option>
                                                <option value="Book">Book</option>
                                                <option value="Sheet">Sheet</option>
                                            </optgroup>
                                            <optgroup label="Bulk/Packaging">
                                                <option value="Box">Box</option>
                                                <option value="Carton">Carton (ctn)</option>
                                                <option value="Pack">Pack (pk)</option>
                                                <option value="Bundle">Bundle</option>
                                                <option value="Case">Case</option>
                                            </optgroup>
                                            <optgroup label="Liquids/Chemicals">
                                                <option value="Bottle">Bottle (btl)</option>
                                                <option value="Can">Can</option>
                                                <option value="Gallon">Gallon (gal)</option>
                                                <option value="Liter">Liter (L)</option>
                                                <option value="Milliliter">Milliliter (mL)</option>
                                            </optgroup>
                                            <optgroup label="Length/Volume">
                                                <option value="Roll">Roll</option>
                                                <option value="Meter">Meter (m)</option>
                                                <option value="Tube">Tube</option>
                                                <option value="Jar">Jar</option>
                                            </optgroup>
                                            <optgroup label="Weight">
                                                <option value="Kilogram">Kilogram (kg)</option>
                                                <option value="Gram">Gram (g)</option>
                                                <option value="Bag">Bag</option>
                                            </optgroup>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Unit Value (₱) <span class="text-danger">*</span></label>
                                        <input type="number" name="unit_value" id="edit_value" class="form-control" step="0.01" min="0" required>
                                    </div>
                                    
                                    <div class="col-md-12 mt-4"><hr class="m-0"></div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-success">Total Stock Quantity <span class="text-danger">*</span></label>
                                        <input type="number" name="quantity" id="edit_qty" class="form-control border-success border-2 bg-success bg-opacity-10" min="0" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-warning">Low Stock Alert Threshold <span class="text-danger">*</span></label>
                                        <input type="number" name="low_stock_threshold" id="edit_threshold" class="form-control border-warning border-2 bg-warning bg-opacity-10" min="1" required>
                                        <small class="text-muted d-block mt-1">System warns you when stock hits this number.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success px-4 fw-bold">Update Supply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="deleteSupplyModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body text-center py-4">
                        <p class="fs-5 mb-0">Are you sure you want to delete this supply?</p>
                        <small class="text-danger">This will also delete related transactions.</small>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewSupplyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0" id="view_details_content" style="border-radius: 10px;">
                </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // --- DUPLICATE ITEM CHECK INTERCEPTOR ---
        document.getElementById('addSupplyForm').addEventListener('submit', function(e) {
            // If the form has the hidden force_save flag, submit normally
            if (this.querySelector('input[name="force_save"]')) {
                return; 
            }
            
            e.preventDefault(); // Stop standard form submission
            const form = this;
            const formData = new FormData(form);

            // Change button to loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            submitBtn.disabled = true;

            // Perform the AJAX fetch check
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'duplicate') {
                    // Reset button
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    Swal.fire({
                        title: 'Item Already Exists!',
                        text: 'An item with these exact details is already in the inventory. Would you like to add this quantity to the existing stock instead of creating a duplicate?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#10b981', // Success green for "Add to existing"
                        cancelButtonColor: '#6c757d',  // Gray for "Create as new"
                        confirmButtonText: '<i class="fas fa-plus me-1"></i> Yes, add to existing stock',
                        cancelButtonText: 'No, create as a new item',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // User wants to add to existing stock (Triggers stockTransaction endpoint)
                            const qty = document.getElementById('add_qty').value;
                            const supplier = document.getElementById('add_supplier').value;
                            const csrf = document.querySelector('meta[name="csrf-token"]').content;
                            
                            // Create a temporary hidden form to post to the transaction endpoint (ADMIN ROUTE)
                            const tempForm = document.createElement('form');
                            tempForm.method = 'POST';
                            tempForm.action = `/admin/supplies/${data.existing_id}/transaction`;
                            
                            // Get today's date formatted as YYYY-MM-DD
                            const today = new Date().toISOString().split('T')[0];
                            
                            tempForm.innerHTML = `
                                <input type="hidden" name="_token" value="${csrf}">
                                <input type="hidden" name="transaction_type" value="IN">
                                <input type="hidden" name="qty" value="${qty}">
                                <input type="hidden" name="supplier" value="${supplier}">
                                <input type="hidden" name="transaction_date" value="${today}">
                                <input type="hidden" name="remarks" value="Added from duplicate check">
                            `;
                            document.body.appendChild(tempForm);
                            tempForm.submit(); // Submits and naturally redirects back with success message

                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            // User actively rejected the duplicate catch, force save it anyway!
                            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
                            submitBtn.disabled = true;
                            
                            const forceInput = document.createElement('input');
                            forceInput.type = 'hidden';
                            forceInput.name = 'force_save';
                            forceInput.value = '1';
                            form.appendChild(forceInput);
                            
                            form.submit(); // Standard submit to trigger page reload + session message
                        }
                    });
                } else if (data.status === 'success') {
                    // It saved perfectly fine on the backend without duplicates
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });

        // Auto-search logic (Debounce)
        document.addEventListener("DOMContentLoaded", function() {
            const searchInput = document.getElementById('supplySearchInput');
            const filterForm = document.getElementById('filterForm');
            let typingTimer;

            if(searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(typingTimer);
                    typingTimer = setTimeout(() => {
                        filterForm.submit();
                    }, 600); // Waits 600ms after user stops typing
                });

                // Keep cursor focused and at the end of the text after reload
                if (searchInput.value.length > 0) {
                    searchInput.focus();
                    const val = searchInput.value;
                    searchInput.value = '';
                    searchInput.value = val;
                }
            }
        });

        function autoFillSupplyForm(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            
            if (!selectedOption.value) {
                document.getElementById('add_article').value = '';
                document.getElementById('add_desc').value = '';
                document.getElementById('add_supplier').value = '';
                document.getElementById('add_unit').selectedIndex = 0;
                document.getElementById('add_val').value = '';
                document.getElementById('add_qty').value = '';
                return;
            }

            document.getElementById('add_article').value = selectedOption.getAttribute('data-desc').split(' ')[0]; 
            document.getElementById('add_desc').value = selectedOption.getAttribute('data-desc');
            document.getElementById('add_supplier').value = selectedOption.getAttribute('data-supplier');
            
            // Auto-select the dropdown logic
            let rawUnit = (selectedOption.getAttribute('data-unit') || "").toLowerCase().trim();
            let unitSelect = document.getElementById('add_unit');
            let matchFound = false;
            
            for (let i = 0; i < unitSelect.options.length; i++) {
                let optVal = unitSelect.options[i].value.toLowerCase();
                if (optVal === rawUnit || (rawUnit.length > 1 && optVal.includes(rawUnit))) {
                    unitSelect.selectedIndex = i;
                    matchFound = true;
                    break;
                }
            }
            
            if (!matchFound) {
                if (rawUnit === 'pc' || rawUnit === 'pcs' || rawUnit === 'piece') unitSelect.value = 'Piece(s)';
                else if (rawUnit === 'box' || rawUnit === 'bx') unitSelect.value = 'Box';
                else if (rawUnit === 'ream' || rawUnit === 'rm') unitSelect.value = 'Ream';
                else if (rawUnit === 'pack' || rawUnit === 'pk') unitSelect.value = 'Pack';
                else if (rawUnit === 'kg' || rawUnit === 'kilo') unitSelect.value = 'Kilogram';
                else if (rawUnit === 'm' || rawUnit === 'meter') unitSelect.value = 'Meter';
                else unitSelect.selectedIndex = 0; 
            }
            
            document.getElementById('add_val').value = selectedOption.getAttribute('data-val');
            document.getElementById('add_qty').value = selectedOption.getAttribute('data-qty');
        }

        function loadViewModal(id) {
            const contentArea = document.getElementById('view_details_content');
            
            new bootstrap.Modal(document.getElementById('viewSupplyModal')).show();
            contentArea.innerHTML = '<div class="p-5 text-center"><div class="spinner-border text-primary"></div><p class="mt-2 mb-0">Loading...</p></div>';

            fetch(`/admin/supplies/${id}/details`)
                .then(response => response.text())
                .then(data => { 
                    contentArea.innerHTML = data; 
                    
                    // Render Barcode
                    const barcodeEl = contentArea.querySelector('.barcode-render-modal');
                    if (barcodeEl && barcodeEl.getAttribute('data-value') !== 'N/A') {
                        JsBarcode(barcodeEl, barcodeEl.getAttribute('data-value'), {
                            format: "CODE128",
                            width: 1.5,
                            height: 40,
                            displayValue: true,
                            fontSize: 14,
                            margin: 0,
                            textMargin: 4
                        });
                    }
                });
        }

        document.querySelectorAll('.clickable-row').forEach(row => {
            row.addEventListener('click', function(e) {
                if(e.target.closest('button') || e.target.closest('a')) { return; }
                const id = this.getAttribute('data-id');
                loadViewModal(id);
            });
        });

        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation(); 
                const id = this.getAttribute('data-id');
                loadViewModal(id);
            });
        });

        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                document.getElementById('editForm').action = `/admin/supplies/${id}`;
                
                document.getElementById('edit_article').value = this.getAttribute('data-article');
                document.getElementById('edit_stock').value = this.getAttribute('data-stock');
                document.getElementById('edit_desc').value = this.getAttribute('data-desc');
                
                // Set the dropdown value safely
                let unitVal = this.getAttribute('data-unit');
                let unitSelect = document.getElementById('edit_unit');
                let optionExists = Array.from(unitSelect.options).some(opt => opt.value === unitVal);
                if(optionExists) {
                    unitSelect.value = unitVal;
                } else {
                    unitSelect.selectedIndex = 0; 
                }

                document.getElementById('edit_value').value = this.getAttribute('data-value');
                document.getElementById('edit_supplier').value = this.getAttribute('data-supplier');
                document.getElementById('edit_qty').value = this.getAttribute('data-qty');
                document.getElementById('edit_threshold').value = this.getAttribute('data-threshold');

                const currentImage = this.getAttribute('data-image');
                const preview = document.getElementById('imagePreviewEdit');
                const placeholder = document.getElementById('imagePlaceholderEdit');
                
                if (currentImage && currentImage !== '') {
                    preview.src = `/storage/supplies/${currentImage}`;
                    preview.style.display = 'block';
                    placeholder.style.display = 'none';
                } else {
                    preview.src = '';
                    preview.style.display = 'none';
                    placeholder.style.display = 'block';
                }
                document.getElementById('imageInputEdit').value = '';
            });
        });

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                document.getElementById('deleteForm').action = `/admin/supplies/${id}`;
            });
        });

        document.getElementById('imageInputAdd').addEventListener('change', function(event) {
            const preview = document.getElementById('imagePreviewAdd');
            const placeholder = document.getElementById('imagePlaceholderAdd');
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    placeholder.style.display = 'none';
                }
                reader.readAsDataURL(file);
            } else {
                preview.src = '';
                preview.style.display = 'none';
                placeholder.style.display = 'block';
            }
        });

        document.getElementById('imageInputEdit').addEventListener('change', function(event) {
            const preview = document.getElementById('imagePreviewEdit');
            const placeholder = document.getElementById('imagePlaceholderEdit');
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    placeholder.style.display = 'none';
                }
                reader.readAsDataURL(file);
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