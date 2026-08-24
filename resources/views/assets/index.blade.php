<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Assets Inventory - Staff</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
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

            .custody-history-side {
                width: min(420px, calc(50vw - 1.5rem));
                max-width: 420px;
                margin: 0;
                position: fixed;
                left: calc(50% + 0.5rem);
                top: 50%;
            }

            .modal.show .custody-history-side {
                transform: translateY(-50%);
            }

            @media (max-width: 992px) {
                .custody-history-side {
                    width: auto;
                    max-width: 500px;
                    position: relative;
                    left: auto;
                    top: auto;
                    margin-left: auto;
                    margin-right: auto;
                }
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

        .status-serviceable { background-color: #198754; color: #fff; } /* Green Available */
        .status-assigned { background-color: #101954; color: #fff; } /* Blue Assigned */
        .status-defective { background-color: #dc3545; color: #fff; } /* Red Defective */
        
        .clickable-row { cursor: pointer; }
        .clickable-row td { transition: background-color 0.2s ease-in-out; }
        .clickable-row:hover td { background-color: #dde2e6 !important; }

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
        .custom-pagination-wrapper ul.pagination > li:last-child .page-link {
            background-color: white !important;
            box-shadow: 0 0 5px rgba(0,0,0,0.15); 
        }

        .page-item.active .page-link { background-color: #f4f6f9; color: #101954; font-weight: 700; border-color: #dee2e6; }
        .page-link { color: #6c757d; }
        .page-link:hover { color: #101954; background-color: #f4f6f9; }

        .modal { z-index: 1060 !important; }
        .modal-backdrop { z-index: 1055 !important; }

        /* Forms wrap modal-body/modal-footer, so they must be flex children too or modal-dialog-scrollable can't compute a scrollable height */
        .modal-dialog-scrollable .modal-content > form {
            display: flex;
            flex-direction: column;
            min-height: 0;
            overflow: hidden;
        }
        
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
                <h3 class="fw-bold text-dark mb-0" style="color: #003366 !important;"><i class="fas fa-laptop text-primary me-2"></i>Assets Inventory</h3>
                <small class="text-muted">Manage equipment, status tracking, and details.</small>
            </div>
            <div class="col-12 col-md-6 d-flex flex-column flex-md-row gap-2 justify-content-md-end">
                <button class="btn btn-outline-dark fw-bold shadow-sm mobile-stack" onclick="scanAssetStatus()">
                    <i class="fas fa-qrcode me-1"></i> Scan Asset QR for Custody
                </button>
                <button class="btn btn-primary shadow-sm mobile-stack" data-bs-toggle="modal" data-bs-target="#addAssetModal">
                    <i class="fas fa-plus me-2"></i> Add New Asset
                </button>
            </div>
        </div>

        @if(session('msg') == 'saved')
            <div class="alert alert-success alert-dismissible fade show py-2 border-0 shadow-sm">
                <i class="fas fa-check-circle me-2"></i> Asset successfully saved/updated! 
                <button type="button" class="btn-close btn-sm pt-3" data-bs-dismiss="alert"></button>
            </div>
        @elseif(session('msg') == 'deleted')
            <div class="alert alert-success alert-dismissible fade show py-2 border-0 shadow-sm">
                <i class="fas fa-trash-alt me-2"></i> Asset successfully deleted! 
                <button type="button" class="btn-close btn-sm pt-3" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-container">
            
            <form action="{{ url('/asset-list') }}" method="GET" id="filterForm" class="row g-2 mb-3 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group shadow-sm mobile-stack">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" id="assetSearchInput" class="form-control border-start-0 ps-0" placeholder="Search Property No., Article, or Desc..." value="{{ request('search') }}">
                    </div>
                </div>
                
                <div class="col-12 col-md-3">
                    <select name="status_filter" class="form-select shadow-sm mobile-stack" onchange="document.getElementById('filterForm').submit();">
                        <option value="All" {{ request('status_filter') == 'All' ? 'selected' : '' }}>All Statuses</option>
                        <option value="Serviceable" {{ request('status_filter') == 'Serviceable' ? 'selected' : '' }}>Serviceable</option>
                        <option value="Unserviceable" {{ request('status_filter') == 'Unserviceable' ? 'selected' : '' }}>Unserviceable / Defective</option>
                    </select>
                </div>

                <div class="col-12 col-md-4 text-md-end">
                    @if(request('search') || request('status_filter') && request('status_filter') !== 'All')
                        <a href="{{ url('/asset-list') }}" class="btn btn-outline-danger btn-sm fw-bold shadow-sm mobile-stack">
                            <i class="fas fa-times me-1"></i> Clear Filters
                        </a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-nowrap">Property No.</th>
                            <th class="text-nowrap">Article / Item</th>
                            <th class="text-nowrap" style="min-width: 200px;">Description</th>
                            <th>Unit</th>
                            <th>Value</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $row)
                            @php
                                $stockNo = !empty($row->barcode_id) ? $row->barcode_id : '<span class="text-muted small">No Property No.</span>';
                                
                                // SMART STATUS BADGE LOGIC
                                if ($row->status != 'Serviceable') {
                                    $statusHtml = '<span class="badge rounded-pill status-defective px-3 py-1">'.$row->status.'</span>';
                                } elseif ($row->assigned_to) {
                                    $statusHtml = '<span class="badge rounded-pill status-assigned shadow-sm px-3 py-1" title="Issued to: '.$row->assigned_to.'"><i class="fas fa-user-check me-1"></i> Assigned</span>';
                                } else {
                                    $statusHtml = '<span class="badge rounded-pill status-serviceable shadow-sm px-3 py-1"><i class="fas fa-check-circle me-1"></i> Available</span>';
                                }
                            @endphp
                            <tr class="clickable-row" data-id="{{ $row->id }}">
                                <td class="fw-bold text-primary font-monospace">{!! $stockNo !!}</td>
                                <td class="fw-bold text-nowrap">{{ $row->article }}</td>
                                <td>{{ Str::limit($row->description, 40) }}</td>
                                <td>{{ $row->unit_measure }}</td>
                                <td class="text-nowrap">₱{{ number_format($row->unit_value, 2) }}</td>
                                <td>{!! $statusHtml !!}</td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <button class="btn btn-sm btn-light border text-primary view-btn" title="View" data-id="{{ $row->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <button class="btn btn-sm btn-light border text-success edit-btn" title="Edit"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editAssetModal"
                                                data-id="{{ $row->id }}"
                                                data-article="{{ $row->article }}"
                                                data-stock="{{ $row->barcode_id }}" 
                                                data-desc="{{ $row->description }}"
                                                data-model="{{ $row->model }}"
                                                data-serial-number="{{ $row->serial_number }}"
                                                data-acquisition-date="{{ optional($row->acquisition_date)->format('Y-m-d') }}"
                                                data-supplier="{{ $row->supplier }}"
                                                data-unit="{{ $row->unit_measure }}"
                                                data-value="{{ $row->unit_value }}"
                                                data-person-accountable="{{ $row->person_accountable }}"
                                                data-validation-signatory="{{ $row->validation_signatory }}"
                                                data-status="{{ $row->status }}"
                                                data-image="{{ $row->image }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <button class="btn btn-sm btn-light border text-danger delete-btn" title="Delete"
                                                data-id="{{ $row->id }}"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteAssetModal">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted border-bottom-0">
                                    <i class="fas fa-laptop fa-3x mb-3 opacity-25 d-block"></i>
                                    No assets match your search filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center border-top pt-3 mt-2 gap-3">
                <div class="text-muted small text-center text-md-start">
                    Showing {{ $assets->firstItem() ?? 0 }} to {{ $assets->lastItem() ?? 0 }} of {{ $assets->total() }} results
                </div>

                <div class="d-flex align-items-center justify-content-center">
                    <span class="text-muted small me-2">Per page</span>
                    <form action="{{ url('/asset-list') }}" method="GET" id="perPageForm">
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
                    {{ $assets->onEachSide(1)->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div> <div class="modal fade" id="addAssetModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Add New Asset</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="addAssetForm" action="{{ url('/asset-list') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-3 text-center border-end pe-md-4 mb-4 mb-md-0">
                                <label class="form-label fw-bold d-block text-start">Asset Image</label>
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
                                        <select id="po_autofill_select" class="form-select border-primary shadow-sm" onchange="autoFillAssetForm(this)">
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
                                                                    data-val="{{ $item->unit_cost }}">
                                                                {{ Str::limit($item->description, 45) }}
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                        </select>
                                    </div>
                                @endif

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Date of Inventory</label>
                                        <input type="date" name="inventory_date" class="form-control" value="{{ now()->toDateString() }}" readonly>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Article / Name <span class="text-danger">*</span></label>
                                        <input type="text" name="article" id="add_article" class="form-control" required placeholder="e.g. Dell Laptop">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Description</label>
                                        <textarea name="description" id="add_desc" class="form-control" rows="2" placeholder="Describe the property or equipment"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Model</label>
                                        <input type="text" name="model" id="add_model" class="form-control" placeholder="Optional">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Serial Number <span class="text-danger">*</span></label>
                                        <input type="text" name="serial_number" id="add_serial_number" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Acquisition Date <span class="text-danger">*</span></label>
                                        <input type="date" name="acquisition_date" id="add_acquisition_date" class="form-control" value="{{ now()->toDateString() }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">PPE Sub-Major Group <span class="text-danger">*</span></label>
                                        <input type="text" name="ppe_sub_major_account_group" class="form-control" maxlength="2" pattern="\d{2}" inputmode="numeric" placeholder="00" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">General Ledger Account <span class="text-danger">*</span></label>
                                        <input type="text" name="general_ledger_account" class="form-control" maxlength="2" pattern="\d{2}" inputmode="numeric" placeholder="00" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Location / Office <span class="text-danger">*</span></label>
                                        <input type="text" name="location_office" class="form-control" maxlength="2" pattern="\d{2}" inputmode="numeric" placeholder="00" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Unit Value (₱) <span class="text-danger">*</span></label>
                                        <input type="number" name="unit_value" id="add_val" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Unit Measure <span class="text-danger">*</span></label>
                                        <select name="unit_measure" id="add_unit" class="form-select" required onchange="toggleAssetSetFields(this, 'assetSetItems')">
                                            <option value="" selected disabled>Select Unit or Set</option>
                                            <option value="Unit">Unit</option>
                                            <option value="Set">Set</option>
                                        </select>
                                        <button type="button" id="openSetItemsButton" class="btn btn-outline-primary btn-sm mt-2 d-none" data-bs-toggle="modal" data-bs-target="#setItemsModal"><i class="fas fa-layer-group me-1"></i> Add Set Items</button>
                                        <div id="setItemsSummary" class="small text-muted mt-2"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Person Accountable</label>
                                        <input type="text" name="person_accountable" id="add_person_accountable" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Validation Signatory of Inventory Committees</label>
                                        <textarea name="validation_signatory" id="add_validation_signatory" class="form-control" rows="3" placeholder="List one signatory per line"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Supplier</label>
                                        <input type="text" name="supplier" id="add_supplier" class="form-control" placeholder="e.g. PC Express">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-primary">Property No. / QR Code</label>
                                        <input type="text" class="form-control border-primary border-2 bg-light" value="Generated after saving" readonly>
                                    </div>
                                    <div class="col-12 d-none" id="assetSetItems">
                                        <div class="border border-primary rounded p-3 bg-light">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <label class="form-label fw-bold text-primary mb-0">Set Components</label>
                                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addAssetSetItem('assetSetItemRows')"><i class="fas fa-plus me-1"></i>Add Component</button>
                                            </div>
                                            <div id="assetSetItemRows"></div>
                                            <small class="text-muted">The main asset is sequence 01. Additional components receive 02, 03, and so on.</small>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="status" value="Serviceable">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">Save Asset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="setItemsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-layer-group me-2"></i>Set Components</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Add the peripherals or component assets. The main asset receives sequence <strong>01</strong>; components continue with <strong>02, 03, ...</strong>.</p>
                    <div id="setItemsContainer"></div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addSetItemButton"><i class="fas fa-plus me-1"></i> Add Component</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveSetItemsButton">Use These Components</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editAssetModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Asset</h5>
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
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-primary">Property No. / QR Code</label>
                                        <input type="text" id="edit_stock" class="form-control border-primary border-2 bg-light" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                        <select name="status" id="edit_status" class="form-select border-secondary border-2" required>
                                            <option value="Serviceable">Serviceable</option>
                                            <option value="Unserviceable">Unserviceable / Defective</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Article (Name) <span class="text-danger">*</span></label>
                                        <input type="text" name="article" id="edit_article" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Supplier</label>
                                        <input type="text" name="supplier" id="edit_supplier" class="form-control">
                                    </div>
                                    
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Description</label>
                                        <textarea name="description" id="edit_desc" class="form-control" rows="2"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Model</label>
                                        <input type="text" name="model" id="edit_model" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Serial Number <span class="text-danger">*</span></label>
                                        <input type="text" name="serial_number" id="edit_serial_number" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Acquisition Date <span class="text-danger">*</span></label>
                                        <input type="date" name="acquisition_date" id="edit_acquisition_date" class="form-control" required>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Unit Measure <span class="text-danger">*</span></label>
                                        <input type="text" name="unit_measure" id="edit_unit" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Unit Value (₱) <span class="text-danger">*</span></label>
                                        <input type="number" name="unit_value" id="edit_value" class="form-control" step="0.01" min="0" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Person Accountable</label>
                                        <input type="text" name="person_accountable" id="edit_person_accountable" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Validation Signatory of Inventory Committees</label>
                                        <textarea name="validation_signatory" id="edit_validation_signatory" class="form-control" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success px-4 fw-bold">Update Asset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="deleteAssetModal" tabindex="-1" aria-hidden="true">
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
                        <p class="fs-5 mb-0">Are you sure you want to delete this asset?</p>
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

    <div class="modal fade" id="viewAssetModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0" id="view_details_content" style="border-radius: 10px;">
                </div>
        </div>
    </div>

    <div class="modal fade" id="custodyHistoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg custody-history-side">
            <div class="modal-content border-0 shadow" id="custody_history_content">
                <div class="modal-body text-center p-5"><div class="spinner-border text-primary"></div></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="assetCustodyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-qrcode me-2"></i>Asset Custody QR Scanner</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="assetCustodyForm">
                    <div class="modal-body p-4">
                        <div id="custodyLookupSection">
                            <label for="custodyBarcode" class="form-label fw-bold">Scan Asset QR Code</label>
                            <div class="input-group">
                                <input type="text" id="custodyBarcode" class="form-control form-control-lg" autocomplete="off" placeholder="Scan or enter the QR value">
                                <button type="button" id="startQrCamera" class="btn btn-outline-primary" title="Use camera to scan QR code"><i class="fas fa-camera"></i></button>
                                <button type="button" id="lookupAssetButton" class="btn btn-primary"><i class="fas fa-search"></i></button>
                            </div>
                            <div id="qrReader" class="mt-3 d-none"></div>
                            <small class="text-muted d-block mt-2">Camera scanning requires HTTPS or localhost and browser camera permission.</small>
                        </div>

                        <div id="custodyDetailsSection" class="d-none">
                            <div class="border rounded p-3 bg-light mb-3">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div id="custodyAssetArticle" class="fw-bold fs-5"></div>
                                        <div id="custodyAssetDescription" class="text-muted small"></div>
                                        <div id="custodyAssetBarcode" class="font-monospace text-primary fw-semibold mt-1"></div>
                                    </div>
                                    <span id="custodyStateBadge" class="badge bg-success">Available</span>
                                </div>
                                <div id="currentHolder" class="small text-muted mt-2 d-none"></div>
                            </div>

                            <input type="hidden" id="custodyAssetId">
                            <input type="hidden" id="activeCustodyId">
                            <div id="issueTransferFields">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Action</label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" class="btn-check" name="custody_action" id="borrowAction" value="Borrowed" checked>
                                        <label class="btn btn-outline-primary" for="borrowAction"><i class="fas fa-handshake me-1"></i>Borrow / Issue</label>
                                        <input type="radio" class="btn-check" name="custody_action" id="transferAction" value="Transferred">
                                        <label class="btn btn-outline-primary" for="transferAction"><i class="fas fa-right-left me-1"></i>Transfer</label>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Borrower / Accountable Person</label>
                                        <input type="text" id="holderName" class="form-control" maxlength="255">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Position</label>
                                        <input type="text" id="holderPosition" class="form-control" maxlength="255">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Department</label>
                                        <input type="text" id="holderDepartment" class="form-control" maxlength="255" placeholder="e.g. Administrative Division">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Unit / Office</label>
                                        <input type="text" id="holderUnit" class="form-control" maxlength="255" placeholder="e.g. General Services Unit">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Issue Date</label>
                                        <input type="date" id="issuedAt" class="form-control" value="{{ now()->toDateString() }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Expected Return Date <span class="text-muted fw-normal">(optional)</span></label>
                                        <input type="date" id="dueAt" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div id="returnFields" class="d-none">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Action</label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" class="btn-check" name="active_custody_action" id="returnAction" value="return" checked>
                                        <label class="btn btn-outline-success" for="returnAction"><i class="fas fa-rotate-left me-1"></i>Return to Inventory</label>
                                        <input type="radio" class="btn-check" name="active_custody_action" id="activeTransferAction" value="transfer">
                                        <label class="btn btn-outline-primary" for="activeTransferAction"><i class="fas fa-right-left me-1"></i>Transfer</label>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Return Date</label>
                                        <input type="date" id="returnedAt" class="form-control" value="{{ now()->toDateString() }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Condition on Return</label>
                                        <select id="returnCondition" class="form-select">
                                            <option value="Serviceable">Serviceable</option>
                                            <option value="Unserviceable">Unserviceable / Defective</option>
                                        </select>
                                    </div>
                                </div>
                                <div id="transferRecipientFields" class="row g-3 mt-0 d-none">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">New Accountable Person</label>
                                        <input type="text" id="transferHolderName" class="form-control" maxlength="255">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Position</label>
                                        <input type="text" id="transferHolderPosition" class="form-control" maxlength="255">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Department</label>
                                        <input type="text" id="transferDepartment" class="form-control" maxlength="255">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Unit / Office</label>
                                        <input type="text" id="transferUnit" class="form-control" maxlength="255">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Transfer Date</label>
                                        <input type="date" id="transferIssuedAt" class="form-control" value="{{ now()->toDateString() }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Expected Return Date <span class="text-muted fw-normal">(optional)</span></label>
                                        <input type="date" id="transferDueAt" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="form-label fw-bold">Remarks <span class="text-muted fw-normal">(optional)</span></label>
                                <textarea id="custodyRemarks" class="form-control" rows="2" maxlength="2000"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="saveCustodyButton" class="btn btn-primary d-none">Save Custody Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // --- DUPLICATE ITEM CHECK INTERCEPTOR FOR ASSETS ---
        function toggleAssetSetFields(select, sectionId) {
            const section = document.getElementById(sectionId);
            const rows = document.getElementById('assetSetItemRows');
            const isSet = select.value === 'Set';
            section.classList.toggle('d-none', !isSet);
            if (!isSet) rows.innerHTML = '';
            if (isSet && rows.children.length === 0) addAssetSetItem('assetSetItemRows');
        }

        function addAssetSetItem(containerId) {
            const container = document.getElementById(containerId);
            const index = container.children.length;
            const row = document.createElement('div');
            row.className = 'row g-2 align-items-end mb-2 set-item-row';
            row.innerHTML = `
                <div class="col-md-3"><label class="form-label small">Component</label><input name="set_items[${index}][article]" class="form-control" required></div>
                <div class="col-md-2"><label class="form-label small">Model</label><input name="set_items[${index}][model]" class="form-control"></div>
                <div class="col-md-2"><label class="form-label small">Serial No.</label><input name="set_items[${index}][serial_number]" class="form-control" required></div>
                <div class="col-md-2"><label class="form-label small">Value</label><input name="set_items[${index}][unit_value]" type="number" min="0" step="0.01" class="form-control" required></div>
                <div class="col-md-2"><label class="form-label small">Description</label><input name="set_items[${index}][description]" class="form-control"></div>
                <div class="col-md-1"><button type="button" class="btn btn-outline-danger" onclick="this.closest('.set-item-row').remove()" title="Remove component"><i class="fas fa-trash"></i></button></div>`;
            container.appendChild(row);
        }

        function attachDuplicateCheck(formId) {
            const formEl = document.getElementById(formId);
            if(!formEl) return;

            formEl.addEventListener('submit', function(e) {
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
                            title: 'Duplicate Asset Found!',
                            text: 'This Property No. (QR value) already exists in the inventory. Individual assets must have unique Property Numbers. Please change it to proceed.',
                            icon: 'warning',
                            confirmButtonText: 'Okay, let me change it',
                            confirmButtonColor: '#101954'
                        });
                    } else if (data.status === 'success') {
                        window.location.reload();
                    } else {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                        Swal.fire('Error', data.message || 'An error occurred while saving the asset.', 'error');
                    }
                })
                .catch(error => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    Swal.fire('Error', 'An error occurred while saving the asset.', 'error');
                });
            });
        }

        // Attach to both add and edit forms
        attachDuplicateCheck('addAssetForm');
        attachDuplicateCheck('editForm');


        const custodyModalElement = document.getElementById('assetCustodyModal');
        const custodyModal = new bootstrap.Modal(custodyModalElement);
        const custodyDetailsSection = document.getElementById('custodyDetailsSection');
        const issueTransferFields = document.getElementById('issueTransferFields');
        const returnFields = document.getElementById('returnFields');
        const saveCustodyButton = document.getElementById('saveCustodyButton');
        const qrReaderElement = document.getElementById('qrReader');
        let qrScanner = null;

        function scanAssetStatus() {
            document.getElementById('assetCustodyForm').reset();
            document.getElementById('custodyBarcode').value = '';
            stopQrCamera();
            qrReaderElement.classList.add('d-none');
            custodyDetailsSection.classList.add('d-none');
            saveCustodyButton.classList.add('d-none');
            custodyModal.show();
            custodyModalElement.addEventListener('shown.bs.modal', () => document.getElementById('custodyBarcode').focus(), { once: true });
        }

        function stopQrCamera() {
            if (!qrScanner) return;
            qrScanner.stop().catch(() => {}).finally(() => {
                qrScanner.clear();
                qrScanner = null;
            });
        }

        document.getElementById('startQrCamera').addEventListener('click', async () => {
            if (qrScanner) {
                stopQrCamera();
                qrReaderElement.classList.add('d-none');
                return;
            }

            qrReaderElement.classList.remove('d-none');
            qrScanner = new Html5Qrcode('qrReader');

            try {
                await qrScanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 220, height: 220 } },
                    decodedText => {
                        document.getElementById('custodyBarcode').value = decodedText.trim();
                        stopQrCamera();
                        qrReaderElement.classList.add('d-none');
                        lookupAssetForCustody().catch(error => Swal.fire('Asset not found', error.message, 'error'));
                    },
                    () => {}
                );
            } catch (error) {
                stopQrCamera();
                qrReaderElement.classList.add('d-none');
                Swal.fire('Camera unavailable', 'Allow camera access or enter the QR value manually.', 'warning');
            }
        });

        custodyModalElement.addEventListener('hidden.bs.modal', stopQrCamera);

        function setCustodyMode(activeCustody) {
            const isReturned = Boolean(activeCustody);
            issueTransferFields.classList.toggle('d-none', isReturned);
            returnFields.classList.toggle('d-none', !isReturned);
            document.getElementById('currentHolder').classList.toggle('d-none', !isReturned);
            saveCustodyButton.textContent = isReturned ? 'Return Asset to Inventory' : 'Save Custody Record';
        }

        document.querySelectorAll('input[name="active_custody_action"]').forEach(input => {
            input.addEventListener('change', () => {
                const transferring = document.getElementById('activeTransferAction').checked;
                document.getElementById('transferRecipientFields').classList.toggle('d-none', !transferring);
                saveCustodyButton.textContent = transferring ? 'Save Transfer Record' : 'Return Asset to Inventory';
            });
        });

        async function lookupAssetForCustody() {
            const qrCode = document.getElementById('custodyBarcode').value.trim();
            if (!qrCode) return;

            const normalizedQrCode = qrCode.toUpperCase();
            const response = await fetch(`{{ url('/asset-custody/scan') }}?qr_code=${encodeURIComponent(normalizedQrCode)}`);
            const responseText = await response.text();
            let data;

            try {
                data = JSON.parse(responseText);
            } catch (error) {
                throw new Error('The scan response was invalid. Please check the QR code and try again.');
            }

            if (!response.ok) throw new Error(data.message || 'Unable to find this asset.');

            const activeCustody = data.active_custody;
            document.getElementById('custodyAssetId').value = data.asset.id;
            document.getElementById('activeCustodyId').value = activeCustody ? activeCustody.id : '';
            document.getElementById('custodyAssetArticle').textContent = data.asset.article;
            document.getElementById('custodyAssetDescription').textContent = data.asset.description || 'No description';
            document.getElementById('custodyAssetBarcode').textContent = data.asset.barcode_id;
            const badge = document.getElementById('custodyStateBadge');
            badge.textContent = activeCustody ? `${activeCustody.transaction_type} / Out` : data.asset.status === 'Serviceable' ? 'Available' : data.asset.status;
            badge.className = `badge ${activeCustody ? 'bg-primary' : data.asset.status === 'Serviceable' ? 'bg-success' : 'bg-danger'}`;
            document.getElementById('currentHolder').textContent = activeCustody ? `Currently with ${activeCustody.holder_name} | ${activeCustody.department}${activeCustody.unit ? `, ${activeCustody.unit}` : ''}` : '';
            setCustodyMode(activeCustody);
            custodyDetailsSection.classList.remove('d-none');
            saveCustodyButton.classList.remove('d-none');
        }

        document.getElementById('lookupAssetButton').addEventListener('click', () => {
            lookupAssetForCustody().catch(error => Swal.fire('Asset not found', error.message, 'error'));
        });

        let custodyScanTimer;
        let custodyLastKeyTime = 0;
        let custodyRapidKeyCount = 0;
        let custodyLookupInProgress = false;

        function autoLookupScannedAsset() {
            clearTimeout(custodyScanTimer);
            custodyScanTimer = setTimeout(() => {
                const barcode = document.getElementById('custodyBarcode').value.trim();
                if (!custodyLookupInProgress && barcode.length >= 4 && custodyRapidKeyCount >= 4) {
                    custodyLookupInProgress = true;
                    lookupAssetForCustody()
                        .catch(error => Swal.fire('Asset not found', error.message, 'error'))
                        .finally(() => {
                            custodyLookupInProgress = false;
                            custodyRapidKeyCount = 0;
                        });
                }
            }, 120);
        }

        document.getElementById('custodyBarcode').addEventListener('input', event => {
            const now = performance.now();
            custodyRapidKeyCount = now - custodyLastKeyTime <= 80 ? custodyRapidKeyCount + 1 : 1;
            custodyLastKeyTime = now;
            autoLookupScannedAsset();
        });

        document.getElementById('custodyBarcode').addEventListener('keydown', event => {
            if (event.key === 'Enter') {
                event.preventDefault();
                if (!custodyLookupInProgress) {
                    custodyLookupInProgress = true;
                    lookupAssetForCustody()
                        .catch(error => Swal.fire('Asset not found', error.message, 'error'))
                        .finally(() => {
                            custodyLookupInProgress = false;
                            custodyRapidKeyCount = 0;
                        });
                }
            }
        });

        let pageScanBuffer = '';
        let pageScanTimer;
        let pageScanLastKeyTime = 0;

        document.addEventListener('keydown', event => {
            if (custodyModalElement.classList.contains('show')) return;
            if (event.key === 'Shift' || event.key === 'Control' || event.key === 'Alt' || event.key === 'Tab') return;

            const now = performance.now();
            if (now - pageScanLastKeyTime > 80) pageScanBuffer = '';
            pageScanLastKeyTime = now;

            if (event.key === 'Enter') {
                const scannedBarcode = pageScanBuffer.trim();
                pageScanBuffer = '';
                if (scannedBarcode.length >= 4) {
                    event.preventDefault();
                    scanAssetStatus();
                    document.getElementById('custodyBarcode').value = scannedBarcode;
                    custodyModalElement.addEventListener('shown.bs.modal', () => {
                        lookupAssetForCustody().catch(error => Swal.fire('Asset not found', error.message, 'error'));
                    }, { once: true });
                }
                return;
            }

            if (event.key.length === 1) {
                pageScanBuffer += event.key;
                clearTimeout(pageScanTimer);
                pageScanTimer = setTimeout(() => { pageScanBuffer = ''; }, 150);
            }
        });

        document.getElementById('assetCustodyForm').addEventListener('submit', async event => {
            event.preventDefault();
            const activeCustodyId = document.getElementById('activeCustodyId').value;
            const isTransfer = activeCustodyId && document.getElementById('activeTransferAction').checked;
            const payload = activeCustodyId && !isTransfer
                ? { returned_at: document.getElementById('returnedAt').value, condition_on_return: document.getElementById('returnCondition').value, remarks: document.getElementById('custodyRemarks').value }
                : isTransfer
                    ? {
                        holder_name: document.getElementById('transferHolderName').value,
                        holder_position: document.getElementById('transferHolderPosition').value,
                        department: document.getElementById('transferDepartment').value,
                        unit: document.getElementById('transferUnit').value,
                        issued_at: document.getElementById('transferIssuedAt').value,
                        due_at: document.getElementById('transferDueAt').value,
                        remarks: document.getElementById('custodyRemarks').value,
                    }
                : {
                    asset_id: document.getElementById('custodyAssetId').value,
                    transaction_type: document.querySelector('input[name="custody_action"]:checked').value,
                    holder_name: document.getElementById('holderName').value,
                    holder_position: document.getElementById('holderPosition').value,
                    department: document.getElementById('holderDepartment').value,
                    unit: document.getElementById('holderUnit').value,
                    issued_at: document.getElementById('issuedAt').value,
                    due_at: document.getElementById('dueAt').value,
                    condition_on_issue: 'Serviceable',
                    remarks: document.getElementById('custodyRemarks').value,
                };
            const url = activeCustodyId ? `{{ url('/asset-custody') }}/${activeCustodyId}/${isTransfer ? 'transfer' : 'return'}` : '{{ url('/asset-custody') }}';
            saveCustodyButton.disabled = true;
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(payload),
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Unable to save the custody record.');
                custodyModal.hide();
                Swal.fire('Saved', data.message, 'success').then(() => window.location.reload());
            } catch (error) {
                Swal.fire('Unable to save', error.message, 'error');
            } finally {
                saveCustodyButton.disabled = false;
            }
        });

        document.addEventListener("DOMContentLoaded", function() {
            const searchInput = document.getElementById('assetSearchInput');
            const filterForm = document.getElementById('filterForm');
            let typingTimer;

            if(searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(typingTimer);
                    typingTimer = setTimeout(() => { filterForm.submit(); }, 600); 
                });

                if (searchInput.value.length > 0) {
                    searchInput.focus();
                    const val = searchInput.value;
                    searchInput.value = '';
                    searchInput.value = val;
                }
            }
        });

        function autoFillAssetForm(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            
            if (!selectedOption.value) {
                document.getElementById('add_article').value = '';
                document.getElementById('add_desc').value = '';
                document.getElementById('add_supplier').value = '';
                document.getElementById('add_unit').value = '';
                document.getElementById('add_val').value = '';
                return;
            }

            document.getElementById('add_article').value = selectedOption.getAttribute('data-desc').split(' ')[0]; 
            document.getElementById('add_desc').value = selectedOption.getAttribute('data-desc');
            document.getElementById('add_supplier').value = selectedOption.getAttribute('data-supplier');
            document.getElementById('add_unit').value = selectedOption.getAttribute('data-unit') || 'Unit';
            document.getElementById('add_val').value = selectedOption.getAttribute('data-val');
        }

        function loadViewModal(id) {
            const contentArea = document.getElementById('view_details_content');
            contentArea.innerHTML = '<div class="p-5 text-center"><div class="spinner-border text-primary"></div><p class="mt-2 mb-0">Loading...</p></div>';
            
            var myModal = new bootstrap.Modal(document.getElementById('viewAssetModal'));
            myModal.show();

            fetch(`/asset-list/${id}/details`)
                .then(response => response.text())
                .then(data => { contentArea.innerHTML = data; });
        }

            function openCustodyHistory(id) {
                const contentArea = document.getElementById('custody_history_content');
                contentArea.innerHTML = '<div class="modal-body text-center p-5"><div class="spinner-border text-primary"></div><p class="mt-2 mb-0">Loading history...</p></div>';
                new bootstrap.Modal(document.getElementById('custodyHistoryModal')).show();
                fetch(`/asset-list/${id}/custody-history`)
                .then(response => response.text())
                .then(data => { contentArea.innerHTML = '<div class="modal-header bg-primary text-white"><h5 class="modal-title"><i class="fas fa-clock-rotate-left me-2"></i>Borrowing & Transfer History</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body p-4">' + data + '</div>'; });
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
                document.getElementById('editForm').action = `/asset-list/${id}`;
                
                document.getElementById('edit_article').value = this.getAttribute('data-article');
                document.getElementById('edit_stock').value = this.getAttribute('data-stock'); 
                document.getElementById('edit_desc').value = this.getAttribute('data-desc');
                document.getElementById('edit_model').value = this.getAttribute('data-model');
                document.getElementById('edit_serial_number').value = this.getAttribute('data-serial-number');
                document.getElementById('edit_acquisition_date').value = this.getAttribute('data-acquisition-date');
                document.getElementById('edit_supplier').value = this.getAttribute('data-supplier');
                document.getElementById('edit_unit').value = this.getAttribute('data-unit');
                document.getElementById('edit_value').value = this.getAttribute('data-value');
                document.getElementById('edit_person_accountable').value = this.getAttribute('data-person-accountable');
                document.getElementById('edit_validation_signatory').value = this.getAttribute('data-validation-signatory');
                document.getElementById('edit_status').value = this.getAttribute('data-status');

                const currentImage = this.getAttribute('data-image');
                const preview = document.getElementById('imagePreviewEdit');
                const placeholder = document.getElementById('imagePlaceholderEdit');
                
                if (currentImage && currentImage !== '') {
                    preview.src = `/storage/assets/${currentImage}`;
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
                document.getElementById('deleteForm').action = `/asset-list/${id}`;
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