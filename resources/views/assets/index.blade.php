<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Assets Inventory - Staff</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .main-content { margin-left: 250px; padding: 20px; transition: all 0.3s; }
        .table-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .clickable-row { cursor: pointer; transition: background-color 0.2s; }
        .clickable-row:hover { background-color: #f8f9fa !important; }
        @media (max-width: 768px) { .main-content { margin-left: 0; } }
    </style>
</head>
<body>

    @include('layouts.header')
    @include('layouts.sidebar')

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-0"><i class="fas fa-laptop text-primary me-2"></i>Assets Inventory</h3>
                <small class="text-muted">Manage equipment, stock in/out, and details.</small>
            </div>
            
            <div class="d-flex gap-2">
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-barcode"></i> Scan Barcode
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item text-success fw-bold" href="javascript:void(0);" onclick="openScanner('IN', 'assets')"><i class="fas fa-plus-circle me-2"></i> Stock IN</a></li>
                        <li><a class="dropdown-item text-danger fw-bold" href="javascript:void(0);" onclick="openScanner('OUT', 'assets')"><i class="fas fa-minus-circle me-2"></i> Stock OUT</a></li>
                    </ul>
                </div>

                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAssetModal">
                    <i class="fas fa-plus me-2"></i> Add New Asset
                </button>
            </div>
        </div>

        @if(session('msg'))
            <div class="alert alert-success alert-dismissible fade show">
                Action Successful! <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>P.O. No. (Barcode ID)</th>
                            <th>Article</th>
                            <th>Description</th>
                            <th>Supplier</th> 
                            <th>Status</th>
                            <th>Stock</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $row)
                        <tr>
                            <td class="fw-bold font-monospace text-primary">
                                {!! !empty($row->barcode_id) ? $row->barcode_id : '<span class="text-muted small">No Barcode ID</span>' !!}
                            </td>
                            <td class="fw-bold">{{ $row->article }}</td>
                            <td><small class="text-muted">{{ $row->description }}</small></td>
                            <td><small>{{ $row->supplier }}</small></td>
                            <td>
                                <span class="badge bg-{{ $row->status == 'Serviceable' ? 'success' : 'danger' }}">
                                    {{ $row->status }}
                                </span>
                            </td>
                            <td class="fw-bold fs-5">{{ $row->quantity }}</td>
                            <td class="text-center">
                                <div class="btn-group me-2">
                                    <button class="btn btn-outline-success btn-sm stock-btn" 
                                            data-id="{{ $row->id }}"
                                            data-name="{{ $row->article }}"
                                            data-type="IN"
                                            title="Stock IN">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm stock-btn" 
                                            data-id="{{ $row->id }}"
                                            data-name="{{ $row->article }}"
                                            data-type="OUT"
                                            title="Stock OUT">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>

                                <div class="btn-group">
                                    <button class="btn btn-secondary btn-sm edit-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editAssetModal"
                                            data-id="{{ $row->id }}"
                                            data-article="{{ $row->article }}"
                                            data-desc="{{ $row->description }}"
                                            data-supplier="{{ $row->supplier }}"
                                            data-unit="{{ $row->unit_measure }}"
                                            data-value="{{ $row->unit_value }}"
                                            data-barcode="{{ $row->barcode_id }}"
                                            data-status="{{ $row->status }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm delete-btn" 
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
                            <td colspan="7" class="text-center py-4 text-muted">No assets found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addAssetModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Add New Asset</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ url('/asset-list') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Article (Name) <span class="text-danger">*</span></label>
                            <input type="text" name="article" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Supplier</label>
                            <input type="text" name="supplier" class="form-control">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">P.O. No. (Barcode ID) <span class="text-danger">*</span></label>
                                <input type="text" name="barcode_id" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Unit Measure</label>
                                <input type="text" name="unit_measure" class="form-control" placeholder="e.g. Unit, Set">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Unit Value (₱)</label>
                                <input type="number" name="unit_value" class="form-control" step="0.01" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-success">Initial Qty <span class="text-danger">*</span></label>
                                <input type="number" name="initial_quantity" class="form-control" min="0" value="0" required>
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
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Asset</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Article (Name) <span class="text-danger">*</span></label>
                            <input type="text" name="article" id="edit_article" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="edit_desc" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Supplier</label>
                            <input type="text" name="supplier" id="edit_supplier" class="form-control">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">P.O. No. (Barcode ID)</label>
                                <input type="text" name="barcode_id" id="edit_barcode" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Unit Measure</label>
                                <input type="text" name="unit_measure" id="edit_unit" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Unit Value (₱)</label>
                                <input type="number" name="unit_value" id="edit_value" class="form-control" step="0.01" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
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
                        <button type="submit" class="btn btn-success">Update Asset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteAssetModal" tabindex="-1">
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

    <div class="modal fade" id="stockModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" id="stockModalHeader">
                    <h5 class="modal-title" id="stockModalTitle">Stock Transaction</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="stockForm" method="POST">
                    @csrf
                    <input type="hidden" name="transaction_type" id="stock_type">
                    <div class="modal-body p-4">
                        <div class="text-center mb-4">
                            <h5 id="stock_asset_name" class="fw-bold text-dark mb-0"></h5>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Quantity</label>
                            <input type="number" name="qty" class="form-control form-control-lg text-center" min="1" value="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" id="stock_supplier_label">Reference / Note</label>
                            <input type="text" name="remarks" class="form-control" placeholder="e.g. RIS-2026-001">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Transaction Date</label>
                            <input type="date" name="transaction_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 justify-content-center">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4" id="stockSubmitBtn">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @include('layouts.modal_scanner')

    <script>
        // Edit Modal Population (Updated action URL)
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                document.getElementById('editForm').action = `/asset-list/${id}`;
                
                document.getElementById('edit_article').value = this.getAttribute('data-article');
                document.getElementById('edit_desc').value = this.getAttribute('data-desc');
                document.getElementById('edit_supplier').value = this.getAttribute('data-supplier');
                document.getElementById('edit_barcode').value = this.getAttribute('data-barcode');
                document.getElementById('edit_unit').value = this.getAttribute('data-unit');
                document.getElementById('edit_value').value = this.getAttribute('data-value');
                document.getElementById('edit_status').value = this.getAttribute('data-status');
            });
        });

        // Delete Modal Population (Updated action URL)
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                document.getElementById('deleteForm').action = `/asset-list/${id}`;
            });
        });

        // Manual Stock IN/OUT Modal Logic (Updated action URL)
        document.querySelectorAll('.stock-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const type = this.getAttribute('data-type');
                
                document.getElementById('stockForm').action = `/asset-list/${id}/transaction`;
                document.getElementById('stock_asset_name').innerText = name;
                document.getElementById('stock_type').value = type;

                const header = document.getElementById('stockModalHeader');
                const title = document.getElementById('stockModalTitle');
                const submitBtn = document.getElementById('stockSubmitBtn');
                const label = document.getElementById('stock_supplier_label');

                if (type === 'IN') {
                    header.className = 'modal-header bg-success text-white';
                    title.innerHTML = '<i class="fas fa-arrow-circle-down me-2"></i>Stock IN (Receive)';
                    submitBtn.className = 'btn btn-success px-4';
                    submitBtn.innerText = 'Confirm Receive';
                    label.innerText = 'Supplier / Source';
                } else {
                    header.className = 'modal-header bg-danger text-white';
                    title.innerHTML = '<i class="fas fa-arrow-circle-up me-2"></i>Stock OUT (Release)';
                    submitBtn.className = 'btn btn-danger px-4';
                    submitBtn.innerText = 'Confirm Release';
                    label.innerText = 'RIS / Purpose';
                }

                new bootstrap.Modal(document.getElementById('stockModal')).show();
            });
        });
    </script>
</body>
</html>