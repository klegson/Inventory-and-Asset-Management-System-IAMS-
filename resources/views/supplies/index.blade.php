<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Supplies Inventory - Personnel</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <style>
        body { 
            background-color: #f4f6f9; 
            font-family: 'Segoe UI', sans-serif; 
            overflow: hidden; 
            height: 100vh;
            margin: 0;
        }

        .main-content { 
            margin-left: 250px; 
            padding: 20px; 
            padding-top: 80px !important; 
            transition: all 0.3s; 
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .table-container { 
            background: white; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); 
            flex-grow: 1; 
            display: flex;
            flex-direction: column;
            min-height: 0; 
        }

        .table-responsive {
            flex-grow: 1;
            overflow-y: auto; 
            margin-bottom: 10px;
        }

        .table thead th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 1;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .status-available { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc;}
        .status-low { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba;}
        .status-out { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7;}
        
        .clickable-row { cursor: pointer; }
        .clickable-row td { transition: background-color 0.2s ease-in-out; }
        .clickable-row:hover td { background-color: #dde2e6 !important; }

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

        .modal { z-index: 1060 !important; }
        
        @media (max-width: 768px) { 
            body { overflow: visible; height: auto; }
            .main-content { 
                margin-left: 0; 
                height: auto; 
                overflow: visible; 
                display: block; 
                padding: 15px; 
                padding-top: 80px !important; 
            } 
            .table-container { display: block; min-height: auto; padding: 15px; }
            .mobile-stack { width: 100% !important; max-width: 100% !important; }
        }
    </style>
</head>
<body>

    @include('layouts.header')
    @include('layouts.sidebar')

    <div class="main-content">
        
        <div class="row align-items-center mb-3 g-3">
            <div class="col-12 col-md-6 text-center text-md-start">
                <h3 class="fw-bold text-dark mb-0"><i class="fas fa-box-open text-primary me-2"></i>Supplies Inventory</h3>
                <small class="text-muted">Manage consumable items, stock levels, and details.</small>
            </div>
            <div class="col-12 col-md-6 d-flex flex-column flex-md-row gap-2 justify-content-md-end">
                <button class="btn btn-primary shadow-sm mobile-stack" data-bs-toggle="modal" data-bs-target="#addSupplyModal">
                    <i class="fas fa-plus me-2"></i> Add New Supply
                </button>
            </div>
        </div>

        @if(session('msg'))
            <div class="alert alert-success alert-dismissible fade show py-2 border-0 shadow-sm">
                <i class="fas fa-check-circle me-2"></i> Action Successful! 
                <button type="button" class="btn-close btn-sm pt-3" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-container">
            
            <form action="{{ url('/supplies') }}" method="GET" id="filterForm" class="row g-2 mb-3 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group shadow-sm mobile-stack">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" id="supplySearchInput" class="form-control border-start-0 ps-0" placeholder="Search Article, Brand, Model, or Description..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-12 col-md-2">
                    <select name="brand_filter" class="form-select shadow-sm mobile-stack" onchange="document.getElementById('filterForm').submit();">
                        <option value="All" {{ request('brand_filter', 'All') == 'All' ? 'selected' : '' }}>All Brands</option>
                        @foreach($brandOptions ?? [] as $brandOption)
                            <option value="{{ $brandOption }}" {{ request('brand_filter') == $brandOption ? 'selected' : '' }}>{{ $brandOption }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-12 col-md-3">
                    <select name="status_filter" class="form-select shadow-sm mobile-stack" onchange="document.getElementById('filterForm').submit();">
                        <option value="All" {{ request('status_filter') == 'All' ? 'selected' : '' }}>All Statuses</option>
                        <option value="Available" {{ request('status_filter') == 'Available' ? 'selected' : '' }}>Available</option>
                        <option value="Low Stock" {{ request('status_filter') == 'Low Stock' ? 'selected' : '' }}>Low Stock</option>
                        <option value="Out of Stock" {{ request('status_filter') == 'Out of Stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>

                <div class="col-12 col-md-3 text-md-end">
                    @if(request('search') || (request('brand_filter') && request('brand_filter') !== 'All') || (request('status_filter') && request('status_filter') !== 'All'))
                        <a href="{{ url('/supplies') }}" class="btn btn-outline-danger btn-sm fw-bold shadow-sm mobile-stack">
                            <i class="fas fa-times me-1"></i> Clear Filters
                        </a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-nowrap">Article / Item</th>
                            <th class="text-nowrap">Brand / Model</th>
                            <th class="text-nowrap" style="min-width: 200px;">Description</th>
                            <th>Unit Value</th>
                            <th class="text-center">Remaining Stock</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($supplies as $row)
                            @php
                                $threshold = $row->low_stock_threshold ?? 10; 
                                
                                $status_class = 'status-available';
                                $status_text = 'Available';
                                $qtyColor = 'text-dark';

                                if($row->quantity == 0) { 
                                    $status_class = 'status-out'; 
                                    $status_text = 'Out of Stock'; 
                                    $qtyColor = 'text-danger';
                                } elseif($row->quantity <= $threshold) { 
                                    $status_class = 'status-low'; 
                                    $status_text = 'Low Stock'; 
                                    $qtyColor = 'text-warning text-dark';
                                }

                                // Fetch the dynamic total input from the subquery passed by the controller
                                $totalInventory = max((int)$row->total_input, (int)$row->quantity);
                            @endphp
                            <tr class="clickable-row" data-id="{{ $row->id }}">
                                <td class="fw-bold text-nowrap">
                                    {{ $row->article }}
                                    @if($row->classification)
                                        <small class="d-block text-muted fw-normal">{{ $row->classification }}</small>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    @if($row->brand || $row->model)
                                        {{ $row->brand }}{{ $row->brand && $row->model ? ' / ' : '' }}{{ $row->model }}
                                    @else
                                        <span class="text-muted">&mdash;</span>
                                    @endif
                                </td>
                                <td>{{ Str::limit($row->description, 40) }}</td>
                                <td class="text-nowrap">₱{{ number_format($row->unit_value, 2) }}</td>
                                
                                <td class="text-center" style="min-width: 120px;">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <span class="fw-bold fs-5 {{ $qtyColor }}">{{ $row->quantity }}</span>
                                        <span class="text-muted small">/ {{ $totalInventory }}</span>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.7rem;">({{ $row->unit_measure }})</div>
                                </td>
                                
                                <td class="text-center"><span class="badge rounded-pill {{ $status_class }} px-2 py-1">{{ $status_text }}</span></td>
                                
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <button class="btn btn-sm btn-light border text-primary view-btn" title="View" data-id="{{ $row->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a class="btn btn-sm btn-light border text-dark" title="Stock Card" href="{{ url('/supplies/'.$row->id.'/stock-card') }}" target="_blank">
                                            <i class="fas fa-clipboard-list"></i>
                                        </a>
                                        
                                        <button class="btn btn-sm btn-light border text-success edit-btn" title="Edit"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editSupplyModal"
                                                data-id="{{ $row->id }}"
                                                data-article="{{ $row->article }}"
                                                data-desc="{{ $row->description }}"
                                                data-brand="{{ $row->brand }}"
                                                data-model="{{ $row->model }}"
                                                data-classification="{{ $row->classification }}"
                                                data-supplier="{{ $row->supplier }}"
                                                data-unit="{{ $row->unit_measure }}"
                                                data-value="{{ $row->unit_value }}"
                                                data-qty="{{ $row->quantity }}"
                                                data-threshold="{{ $row->low_stock_threshold ?? 10 }}"
                                                data-image="{{ $row->image }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <button class="btn btn-sm btn-light border text-danger delete-btn" title="Delete"
                                                data-id="{{ $row->id }}"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteSupplyModal">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted border-bottom-0">
                                    <i class="fas fa-box-open fa-3x mb-3 opacity-25 d-block"></i>
                                    No supplies match your search filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center border-top pt-3 mt-2 gap-3">
                
                <div class="text-muted small text-center text-md-start">
                    Showing {{ $supplies->firstItem() ?? 0 }} to {{ $supplies->lastItem() ?? 0 }} of {{ $supplies->total() }} results
                </div>

                <div class="d-flex align-items-center justify-content-center">
                    <span class="text-muted small me-2">Per page</span>
                    <form action="{{ url('/supplies') }}" method="GET" id="perPageForm">
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

                <div class="custom-pagination-wrapper d-flex justify-content-center" id="styled-pagination">
                    {{ $supplies->onEachSide(1)->appends(request()->query())->links() }}
                </div>
                
            </div>
        </div>
    </div>

    <div class="modal fade" id="addSupplyModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Add New Supply</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="addSupplyForm" action="{{ url('/supplies') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-3 text-center border-end pe-md-4 mb-4 mb-md-0">
                                <label class="form-label fw-bold d-block text-start">Supply Image</label>
                                <div class="border rounded bg-light d-flex justify-content-center align-items-center mx-auto mb-3 overflow-hidden shadow-sm" 
                                     style="width: 100%; max-width: 200px; aspect-ratio: 1/1; position: relative;">
                                    <img id="imagePreviewAdd" src="" alt="Preview" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                                    <i id="imagePlaceholderAdd" class="fas fa-image fa-4x text-muted opacity-50"></i>
                                </div>
                                <input type="file" name="image" id="imageInputAdd" class="form-control form-control-sm" accept="image/*">
                                <small class="text-muted text-start d-block mt-2">Recommended: Square format (JPG, PNG)</small>
                            </div>
                            
                            <div class="col-md-9 ps-md-4">
                                @if(isset($deliveredPoItems) && count($deliveredPoItems) > 0)
                                    <div class="mb-4 bg-light p-3 rounded border">
                                        <label class="form-label text-primary fw-bold mb-2"><i class="fas fa-magic me-1"></i> Auto-Fill from Delivered P.O. (Optional)</label>
                                        <select id="po_autofill_select" class="form-select border-primary shadow-sm" onchange="autoFillSupplyForm(this)">
                                            <option value="">Select a delivered item to auto-fill the form...</option>
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
                                        </select>
                                    </div>
                                @endif

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Supply Section <span class="text-danger">*</span></label>
                                        <select id="add_article_select" class="form-select" onchange="handleSectionChange(this, 'add')">
                                            <option value="">Select existing section...</option>
                                            @foreach($sections ?? [] as $sectionName => $classifications)
                                                <option value="{{ $sectionName }}">{{ $sectionName }}</option>
                                            @endforeach
                                            <option value="__new__">+ Add New Section...</option>
                                        </select>
                                        <input type="text" name="article" id="add_article" class="form-control mt-2 d-none" placeholder="e.g. Bond Paper">
                                        <small class="text-muted d-block mt-1">The general item group, e.g. "Bond Paper".</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Classification</label>
                                        <select id="add_classification_select" class="form-select" onchange="handleClassificationChange(this, 'add')" disabled>
                                            <option value="">Select a section first...</option>
                                        </select>
                                        <input type="text" name="classification" id="add_classification" class="form-control mt-2 d-none" placeholder="e.g. Size: A4">
                                        <small class="text-muted d-block mt-1">The specific kind/size within the section, e.g. "Size: A4".</small>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Supplier</label>
                                        <input type="text" name="supplier" id="add_supplier" class="form-control" placeholder="e.g. Pandayan">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Brand</label>
                                        <input type="text" name="brand" id="add_brand" class="form-control" placeholder="e.g. HP, Epson">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Model / Sub-type</label>
                                        <input type="text" name="model" id="add_model" class="form-control" placeholder="e.g. 680, C4400, Rev. B">
                                        <small class="text-muted d-block mt-1">Use this to tell apart items with the same name and brand.</small>
                                    </div>
                                    
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Description</label>
                                        <textarea name="description" id="add_desc" class="form-control" rows="2" placeholder="e.g. A4 Size, 70gsm, White"></textarea>
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
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
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
                            <div class="col-md-3 text-center border-end pe-md-4 mb-4 mb-md-0">
                                <label class="form-label fw-bold d-block text-start">Update Image (Optional)</label>
                                <div class="border rounded bg-light d-flex justify-content-center align-items-center mx-auto mb-3 overflow-hidden shadow-sm" 
                                     style="width: 100%; max-width: 200px; aspect-ratio: 1/1; position: relative;">
                                    <img id="imagePreviewEdit" src="" alt="Preview" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                                    <i id="imagePlaceholderEdit" class="fas fa-image fa-4x text-muted opacity-50"></i>
                                </div>
                                <input type="file" name="image" id="imageInputEdit" class="form-control form-control-sm" accept="image/*">
                                <small class="text-muted text-start d-block mt-2">Leave blank to keep current image.</small>
                            </div>
                            
                            <div class="col-md-9 ps-md-4">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="alert alert-light border-success border-start border-4 py-2 px-3 mb-1 d-flex flex-column flex-md-row align-items-md-start justify-content-between gap-2">
                                            <span><i class="fas fa-box-open text-success me-2"></i>Supply details</span>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Supply Section <span class="text-danger">*</span></label>
                                        <select id="edit_article_select" class="form-select" onchange="handleSectionChange(this, 'edit')">
                                            <option value="">Select existing section...</option>
                                            @foreach($sections ?? [] as $sectionName => $classifications)
                                                <option value="{{ $sectionName }}">{{ $sectionName }}</option>
                                            @endforeach
                                            <option value="__new__">+ Add New Section...</option>
                                        </select>
                                        <input type="text" name="article" id="edit_article" class="form-control mt-2 d-none" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Classification</label>
                                        <select id="edit_classification_select" class="form-select" onchange="handleClassificationChange(this, 'edit')" disabled>
                                            <option value="">Select a section first...</option>
                                        </select>
                                        <input type="text" name="classification" id="edit_classification" class="form-control mt-2 d-none" placeholder="e.g. Size: A4">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Supplier</label>
                                        <input type="text" name="supplier" id="edit_supplier" class="form-control">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Brand</label>
                                        <input type="text" name="brand" id="edit_brand" class="form-control" placeholder="e.g. HP, Epson">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Model / Sub-type</label>
                                        <input type="text" name="model" id="edit_model" class="form-control" placeholder="e.g. 680, C4400, Rev. B">
                                        <small class="text-muted d-block mt-1">Use this to tell apart items with the same name and brand.</small>
                                    </div>
                                    
                                    <div class="col-12">
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
                                    
                                    <div class="col-12 mt-4"><hr class="m-0"></div>
                                    
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
        // --- MULTI-LEVEL SUPPLY SECTION / CLASSIFICATION DROPDOWN ---
        const supplySections = @json($sections ?? []);

        function populateClassifications(prefix, sectionName) {
            const select = document.getElementById(prefix + '_classification_select');
            const hiddenInput = document.getElementById(prefix + '_classification');

            if (!sectionName || sectionName === '__new__') {
                select.disabled = true;
                select.innerHTML = '<option value="">Select a section first...</option>';
                hiddenInput.classList.add('d-none');
                hiddenInput.value = '';
                return;
            }

            const classifications = supplySections[sectionName] || [];
            let html = '<option value="">General / No specific classification</option>';
            classifications.forEach(function (classification) {
                html += `<option value="${classification}">${classification}</option>`;
            });
            html += '<option value="__new__">+ Add New Classification...</option>';

            select.disabled = false;
            select.innerHTML = html;
            hiddenInput.classList.add('d-none');
            hiddenInput.value = '';
        }

        function handleSectionChange(select, prefix) {
            const value = select.value;
            const textInput = document.getElementById(prefix + '_article');

            if (value === '__new__') {
                textInput.classList.remove('d-none');
                textInput.value = '';
                textInput.focus();
                populateClassifications(prefix, null);
            } else {
                textInput.classList.add('d-none');
                textInput.value = value;
                populateClassifications(prefix, value);
            }
        }

        function handleClassificationChange(select, prefix) {
            const value = select.value;
            const textInput = document.getElementById(prefix + '_classification');

            if (value === '__new__') {
                textInput.classList.remove('d-none');
                textInput.value = '';
                textInput.focus();
            } else {
                textInput.classList.add('d-none');
                textInput.value = value;
            }
        }

        // Reset the multi-level dropdown whenever the Add Supply modal is opened
        document.getElementById('addSupplyModal').addEventListener('show.bs.modal', function () {
            const sectionSelect = document.getElementById('add_article_select');
            sectionSelect.value = '';
            handleSectionChange(sectionSelect, 'add');
        });

        // --- DUPLICATE ITEM CHECK INTERCEPTOR ---
        document.getElementById('addSupplyForm').addEventListener('submit', function(e) {
            if (this.querySelector('input[name="force_save"]')) return; 
            
            e.preventDefault(); 
            const form = this;
            const formData = new FormData(form);

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            submitBtn.disabled = true;

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'duplicate') {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    Swal.fire({
                        title: 'Item Already Exists!',
                        text: 'An item with these exact details is already in the inventory. Would you like to add this quantity to the existing stock instead of creating a duplicate?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#10b981', 
                        cancelButtonColor: '#6c757d', 
                        confirmButtonText: '<i class="fas fa-plus me-1"></i> Yes, add to existing stock',
                        cancelButtonText: 'No, create as a new item',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const qty = document.getElementById('add_qty').value;
                            const supplier = document.getElementById('add_supplier').value;
                            const csrf = document.querySelector('meta[name="csrf-token"]').content;
                            
                            const tempForm = document.createElement('form');
                            tempForm.method = 'POST';
                            tempForm.action = `/supplies/${data.existing_id}/transaction`;
                            
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
                            tempForm.submit();

                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
                            submitBtn.disabled = true;
                            
                            const forceInput = document.createElement('input');
                            forceInput.type = 'hidden';
                            forceInput.name = 'force_save';
                            forceInput.value = '1';
                            form.appendChild(forceInput);
                            
                            form.submit(); 
                        }
                    });
                } else if (data.status === 'success') {
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
                    }, 600); 
                });

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
                document.getElementById('add_article_select').value = '';
                handleSectionChange(document.getElementById('add_article_select'), 'add');
                document.getElementById('add_desc').value = '';
                document.getElementById('add_supplier').value = '';
                document.getElementById('add_unit').selectedIndex = 0;
                document.getElementById('add_val').value = '';
                document.getElementById('add_qty').value = '';
                return;
            }

            const articleSelect = document.getElementById('add_article_select');
            const articleName = selectedOption.getAttribute('data-desc').split(' ')[0];
            const matchingOption = Array.from(articleSelect.options).find(opt => opt.value === articleName);
            articleSelect.value = matchingOption ? articleName : '__new__';
            handleSectionChange(articleSelect, 'add');
            if (!matchingOption) {
                document.getElementById('add_article').value = articleName;
            }

            document.getElementById('add_desc').value = selectedOption.getAttribute('data-desc');
            document.getElementById('add_supplier').value = selectedOption.getAttribute('data-supplier');
            
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
            contentArea.innerHTML = '<div class="p-5 text-center"><div class="spinner-border text-primary"></div><p class="mt-2 mb-0">Loading...</p></div>';
            
            var myModal = new bootstrap.Modal(document.getElementById('viewSupplyModal'));
            myModal.show();

            fetch(`/supplies/${id}/details`)
                .then(response => response.text())
                .then(data => { contentArea.innerHTML = data; });
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
                document.getElementById('editForm').action = `/supplies/${id}`;
                
                const editArticleSelect = document.getElementById('edit_article_select');
                const articleValue = this.getAttribute('data-article');
                const articleOptionExists = Array.from(editArticleSelect.options).some(opt => opt.value === articleValue);
                editArticleSelect.value = articleOptionExists ? articleValue : '__new__';
                handleSectionChange(editArticleSelect, 'edit');
                document.getElementById('edit_article').value = articleValue;
                if (!articleOptionExists) {
                    document.getElementById('edit_article').classList.remove('d-none');
                }

                const editClassificationSelect = document.getElementById('edit_classification_select');
                const classificationValue = this.getAttribute('data-classification');
                const classificationOptionExists = Array.from(editClassificationSelect.options).some(opt => opt.value === classificationValue);
                if (classificationValue) {
                    editClassificationSelect.value = classificationOptionExists ? classificationValue : '__new__';
                    handleClassificationChange(editClassificationSelect, 'edit');
                    if (!classificationOptionExists) {
                        document.getElementById('edit_classification').classList.remove('d-none');
                    }
                }
                document.getElementById('edit_classification').value = classificationValue;

                document.getElementById('edit_desc').value = this.getAttribute('data-desc');
                document.getElementById('edit_brand').value = this.getAttribute('data-brand');
                document.getElementById('edit_model').value = this.getAttribute('data-model');
                document.getElementById('edit_supplier').value = this.getAttribute('data-supplier');
                
                let unitVal = this.getAttribute('data-unit');
                let unitSelect = document.getElementById('edit_unit');
                let optionExists = Array.from(unitSelect.options).some(opt => opt.value === unitVal);
                if(optionExists) {
                    unitSelect.value = unitVal;
                } else {
                    unitSelect.selectedIndex = 0;
                }

                document.getElementById('edit_value').value = this.getAttribute('data-value');
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
                document.getElementById('deleteForm').action = `/supplies/${id}`;
            });
        });

        // ADD MODAL IMAGE PREVIEW
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

        // EDIT MODAL IMAGE PREVIEW
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

        // PERFECTED PAGINATION LOGIC
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