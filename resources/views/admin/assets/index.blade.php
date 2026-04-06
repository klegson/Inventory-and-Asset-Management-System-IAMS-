<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Inventory - DepEd AMS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .main-content { margin-left: 250px; padding: 20px; transition: all 0.3s; }
        .table-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .status-serviceable { background-color: #d1e7dd; color: #0f5132; }
        .status-unserviceable { background-color: #f8d7da; color: #842029; }
        
        .clickable-row { cursor: pointer; transition: background-color 0.2s; }
        .clickable-row:hover { background-color: #f8f9fa !important; }
        
        @media (max-width: 768px) { .main-content { margin-left: 0; } }
    </style>
</head>
<body>

    @include('layouts.admin_header')
    @include('layouts.admin_sidebar')

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3" style="border-color: #003366 !important;">
            <h2 style="color: #003366; margin: 0;">
                <i class="fas fa-laptop me-2"></i> Asset Inventory (Equipment)
            </h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAssetModal">
                <i class="fas fa-plus me-2"></i> Add New Asset
            </button>
        </div>

        @if(session('msg'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('msg') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-container">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Property No.</th>
                        <th>Article / Item</th>
                        <th>Description</th>
                        <th>Supplier</th>
                        <th>Unit Value</th>
                        <th>Qty</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $row)
                        <tr class="clickable-row" data-id="{{ $row->id }}">
                            <td class="fw-bold text-primary font-monospace">{{ $row->barcode_id ?: 'N/A' }}</td>
                            <td class="fw-bold">{{ $row->article }}</td>
                            <td><small class="text-muted">{{ $row->description }}</small></td>
                            <td><small>{{ $row->supplier }}</small></td>
                            <td class="fw-semibold">₱{{ number_format($row->unit_value, 2) }}</td>
                            <td class="fw-bold fs-5">{{ $row->quantity }}</td>
                            <td>
                                <span class="status-badge {{ $row->status == 'Serviceable' ? 'status-serviceable' : 'status-unserviceable' }}">
                                    {{ $row->status }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-primary text-white view-btn" data-id="{{ $row->id }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <button class="btn btn-sm btn-success text-white edit-btn" 
                                            data-id="{{ $row->id }}" 
                                            data-article="{{ $row->article }}" 
                                            data-property="{{ $row->barcode_id }}" 
                                            data-description="{{ $row->description }}" 
                                            data-unit="{{ $row->unit_measure }}" 
                                            data-value="{{ $row->unit_value }}" 
                                            data-supplier="{{ $row->supplier }}" 
                                            data-status="{{ $row->status }}" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editAssetModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <button class="btn btn-sm btn-danger delete-btn" 
                                            data-id="{{ $row->id }}" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteAssetModal">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-4 text-muted">No assets found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="addAssetModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Add New Asset</h5>  
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ url('/admin/assets') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Article / Item Name <span class="text-danger">*</span></label>
                                <input type="text" name="article" class="form-control" required placeholder="e.g. Epson Printer">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Property Number <span class="text-danger">*</span></label>
                                <input type="text" name="barcode_id" class="form-control" required placeholder="e.g. DEPED-2026-001">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="e.g. L3210 3-in-1 Color Printer"></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Unit of Measure</label>
                                <select name="unit_measure" class="form-select">
                                    <option value="Unit">Unit</option>
                                    <option value="Pc">Pc</option>
                                    <option value="Set">Set</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Unit Value (₱)</label>
                                <input type="number" step="0.01" min="0" name="unit_value" class="form-control" placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-success">Initial Qty <span class="text-danger">*</span></label>
                                <input type="number" name="initial_quantity" class="form-control" required min="0" value="0">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Supplier (Optional)</label>
                                <input type="text" name="supplier" class="form-control" placeholder="Supplier Name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="Serviceable">Serviceable</option>
                                    <option value="Unserviceable">Unserviceable</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Asset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editAssetModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Asset</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Article <span class="text-danger">*</span></label>
                                <input type="text" name="article" id="edit_article" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Property No. <span class="text-danger">*</span></label>
                                <input type="text" name="barcode_id" id="edit_property" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Unit</label>
                                <select name="unit_measure" id="edit_unit" class="form-select">
                                    <option value="Unit">Unit</option>
                                    <option value="Pc">Pc</option>
                                    <option value="Set">Set</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Value (₱)</label>
                                <input type="number" step="0.01" min="0" name="unit_value" id="edit_value" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Supplier</label>
                                <input type="text" name="supplier" id="edit_supplier" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" id="edit_status" class="form-select">
                                    <option value="Serviceable">Serviceable</option>
                                    <option value="Unserviceable">Unserviceable</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success text-white">Update Asset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteAssetModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-trash-alt me-2"></i> Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body text-center py-4">
                        <p class="fs-5 mb-1">Are you sure you want to delete this asset?</p>
                        <p class="text-muted small mb-0">This will permanently remove the record from the inventory.</p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger px-4">Delete Asset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewAssetModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0" id="view_asset_content" style="border-radius: 10px;">
                </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Centralized function to load View Modal
        function loadViewModal(id) {
            const contentArea = document.getElementById('view_asset_content');
            
            new bootstrap.Modal(document.getElementById('viewAssetModal')).show();
            contentArea.innerHTML = '<div class="p-5 text-center"><div class="spinner-border text-primary"></div><p class="mt-2 mb-0">Loading...</p></div>';

            fetch(`/admin/assets/${id}/details`)
                .then(response => response.text())
                .then(data => { contentArea.innerHTML = data; });
        }

        // --- Handle Row Clicks ---
        document.querySelectorAll('.clickable-row').forEach(row => {
            row.addEventListener('click', function(e) {
                if(e.target.closest('button') || e.target.closest('a')) { return; }
                const id = this.getAttribute('data-id');
                loadViewModal(id);
            });
        });

        // --- Handle View Button Clicks ---
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation(); 
                const id = this.getAttribute('data-id');
                loadViewModal(id);
            });
        });

        // --- Populate Edit Modal ---
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                document.getElementById('editForm').action = `/admin/assets/${id}`;
                
                document.getElementById('edit_article').value = this.getAttribute('data-article');
                document.getElementById('edit_property').value = this.getAttribute('data-property');
                document.getElementById('edit_description').value = this.getAttribute('data-description');
                document.getElementById('edit_unit').value = this.getAttribute('data-unit');
                document.getElementById('edit_value').value = this.getAttribute('data-value');
                document.getElementById('edit_supplier').value = this.getAttribute('data-supplier');
                document.getElementById('edit_status').value = this.getAttribute('data-status');
            });
        });

        // --- Populate Delete Modal ---
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                document.getElementById('deleteForm').action = `/admin/assets/${id}`;
            });
        });
    </script>
</body>
</html>