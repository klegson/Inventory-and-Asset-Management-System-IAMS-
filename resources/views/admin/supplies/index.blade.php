<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplies Inventory - DepEd AMS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .main-content { margin-left: 250px; padding: 20px; transition: all 0.3s; }
        .table-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
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

        /* Sticky Arrows */
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
        
        @media (max-width: 768px) { .main-content { margin-left: 0; } }
    </style>
</head>
<body>

    @include('layouts.admin_header')
    @include('layouts.admin_sidebar')

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3" style="border-color: #003366 !important;">
            <div>
                <h2 style="color: #003366; margin: 0;">
                    <i class="fas fa-box-open"></i> Supplies Inventory (Consumables)
                </h2>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplyModal">
                <i class="fas fa-plus me-2"></i> Add New Supply
            </button>
        </div>

        @if(session('msg'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('msg') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-container">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Stock No.</th>
                        <th>Article / Item</th>
                        <th>Description</th>
                        <th>Unit</th>
                        <th>Value</th>
                        <th>Qty</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($supplies as $row)
                        @php
                            $status_class = 'status-available';
                            $status_text = 'Available';
                            if($row->quantity == 0) { $status_class = 'status-out'; $status_text = 'Out of Stock'; }
                            elseif($row->quantity <= 10) { $status_class = 'status-low'; $status_text = 'Low Stock'; }
                        @endphp
                        <tr class="clickable-row" data-id="{{ $row->id }}">
                            <td class="fw-bold text-primary font-monospace">{{ $row->barcode_id ?: 'N/A' }}</td>
                            <td class="fw-bold">{{ $row->article }}</td>
                            <td><small class="text-muted">{{ $row->description }}</small></td>
                            <td>{{ $row->unit_measure }}</td>
                            <td>₱{{ number_format($row->unit_value, 2) }}</td>
                            <td class="fw-bold fs-5">{{ $row->quantity }}</td>
                            <td><span class="badge rounded-pill {{ $status_class }}">{{ $status_text }}</span></td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-primary text-white view-btn" 
                                            data-id="{{ $row->id }}">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <button class="btn btn-sm btn-success text-white edit-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editSupplyModal"
                                            data-id="{{ $row->id }}"
                                            data-article="{{ $row->article }}"
                                            data-stock="{{ $row->barcode_id }}"
                                            data-desc="{{ $row->description }}"
                                            data-unit="{{ $row->unit_measure }}"
                                            data-value="{{ $row->unit_value }}"
                                            data-image="{{ $row->image }}"
                                            data-supplier="{{ $row->supplier }}">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button class="btn btn-sm btn-danger delete-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteSupplyModal"
                                            data-id="{{ $row->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-4 text-muted">No supplies found.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-2">
                <div class="text-muted small">
                    Showing {{ $supplies->firstItem() ?? 0 }} to {{ $supplies->lastItem() ?? 0 }} of {{ $supplies->total() }} results
                </div>

                <div class="d-flex align-items-center">
                    <span class="text-muted small me-2">Per page</span>
                    <form action="{{ url('/admin/supplies') }}" method="GET" id="perPageForm">
                        <select name="per_page" class="form-select form-select-sm shadow-none" style="width: 70px; border-color: #101954; color: #101954; font-weight: 500;" onchange="document.getElementById('perPageForm').submit();">
                            <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        </select>
                    </form>
                </div>

                <div class="custom-pagination-wrapper" id="scrollablePagination">
                    {{ $supplies->onEachSide(999)->appends(['per_page' => $perPage])->links() }}
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="addSupplyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Add New Supply</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ url('/admin/supplies') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-4 text-center">
                            <label class="form-label fw-bold d-block text-start">Supply Image</label>
                            <div class="border rounded bg-light d-flex justify-content-center align-items-center mx-auto mb-3 overflow-hidden shadow-sm" style="width: 150px; height: 150px; position: relative;">
                                <img id="imagePreviewAdd" src="" alt="Preview" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                                <i id="imagePlaceholderAdd" class="fas fa-image fa-3x text-muted"></i>
                            </div>
                            <input type="file" name="image" id="imageInputAdd" class="form-control" accept="image/*">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Article (Name) <span class="text-danger">*</span></label>
                            <input type="text" name="article" class="form-control" required placeholder="e.g. Bond Paper">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="e.g. A4 Size, 70gsm"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Supplier</label>
                            <input type="text" name="supplier" class="form-control" placeholder="e.g. Pandayan">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Stock No. (Barcode)</label>
                                <input type="text" class="form-control bg-light text-muted" placeholder="Auto-generated by system" disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Unit Measure <span class="text-danger">*</span></label>
                                <select name="unit_measure" class="form-select" required>
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
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Unit Value (₱) <span class="text-danger">*</span></label>
                                <input type="number" name="unit_value" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-success">Initial Qty <span class="text-danger">*</span></label>
                                <input type="number" name="initial_quantity" class="form-control" min="0" value="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Supply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editSupplyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Supply</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        
                        <div class="mb-4 text-center">
                            <label class="form-label fw-bold d-block text-start">Update Image (Optional)</label>
                            <div class="border rounded bg-light d-flex justify-content-center align-items-center mx-auto mb-3 overflow-hidden shadow-sm" style="width: 150px; height: 150px; position: relative;">
                                <img id="imagePreviewEdit" src="" alt="Preview" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                                <i id="imagePlaceholderEdit" class="fas fa-image fa-3x text-muted"></i>
                            </div>
                            <input type="file" name="image" id="imageInputEdit" class="form-control" accept="image/*">
                        </div>

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
                                <label class="form-label">Stock No. (Barcode) <span class="text-danger">*</span></label>
                                <input type="text" name="barcode_id" id="edit_stock" class="form-control bg-light text-muted" readonly required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Unit Measure <span class="text-danger">*</span></label>
                                <select name="unit_measure" id="edit_unit" class="form-select" required>
                                    <option value="" disabled>Select Unit</option>
                                    <option value="Piece(s)">Piece(s) (pc/s)</option>
                                    <option value="Unit">Unit</option>
                                    <option value="Set">Set</option>
                                    <option value="Pair">Pair</option>
                                    <option value="Ream">Ream</option>
                                    <option value="Pad">Pad</option>
                                    <option value="Book">Book</option>
                                    <option value="Sheet">Sheet</option>
                                    <option value="Box">Box</option>
                                    <option value="Carton">Carton (ctn)</option>
                                    <option value="Pack">Pack (pk)</option>
                                    <option value="Bundle">Bundle</option>
                                    <option value="Case">Case</option>
                                    <option value="Bottle">Bottle (btl)</option>
                                    <option value="Can">Can</option>
                                    <option value="Gallon">Gallon (gal)</option>
                                    <option value="Liter">Liter (L)</option>
                                    <option value="Milliliter">Milliliter (mL)</option>
                                    <option value="Roll">Roll</option>
                                    <option value="Meter">Meter (m)</option>
                                    <option value="Tube">Tube</option>
                                    <option value="Jar">Jar</option>
                                    <option value="Kilogram">Kilogram (kg)</option>
                                    <option value="Gram">Gram (g)</option>
                                    <option value="Bag">Bag</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Unit Value (₱) <span class="text-danger">*</span></label>
                            <input type="number" name="unit_value" id="edit_value" class="form-control" step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Update Supply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteSupplyModal" tabindex="-1">
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
                        <p class="fs-5 mb-1">Are you sure you want to delete this item?</p>
                        <p class="text-muted small mb-0">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger px-4">Delete Item</button>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function loadViewModal(id) {
            const contentArea = document.getElementById('view_details_content');
            
            new bootstrap.Modal(document.getElementById('viewSupplyModal')).show();
            contentArea.innerHTML = '<div class="p-5 text-center"><div class="spinner-border text-primary"></div><p class="mt-2 mb-0">Loading...</p></div>';

            fetch(`/admin/supplies/${id}/details`)
                .then(response => response.text())
                .then(data => { 
                    contentArea.innerHTML = data; 
                    
                    // Render Barcode exactly like the screenshot
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
                document.getElementById('edit_unit').value = this.getAttribute('data-unit');
                document.getElementById('edit_value').value = this.getAttribute('data-value');
                document.getElementById('edit_supplier').value = this.getAttribute('data-supplier');

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