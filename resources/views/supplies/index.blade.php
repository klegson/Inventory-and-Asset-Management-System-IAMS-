<!DOCTYPE html>
<html lang="en">
<head>
    <title>Supplies Inventory - Personnel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; margin: 0; }
        .main-content { margin-left: 250px; padding: 20px; transition: all 0.3s; min-height: 100vh; }
        .table-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .status-available { background-color: #d1e7dd; color: #0f5132; }
        .status-low { background-color: #fff3cd; color: #856404; }
        .status-out { background-color: #f8d7da; color: #842029; }
        
        .clickable-row { cursor: pointer; }
        .clickable-row td { transition: background-color 0.2s ease-in-out; }
        .clickable-row:hover td { background-color: #dde2e6 !important; }

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
    </style>
</head>
<body>

    @include('layouts.header')
    @include('layouts.sidebar')

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-0"><i class="fas fa-box-open text-primary me-2"></i>Supplies Inventory</h3>
                <small class="text-muted">Manage consumable items, stock levels, and details.</small>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-outline-dark fw-bold" onclick="openScanner('IN', 'supplies')">
                    <i class="fas fa-barcode me-1"></i> Stock IN (Scanner)
                </button>

                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplyModal">
                    <i class="fas fa-plus me-2"></i> Add New Supply
                </button>
            </div>
        </div>

        @if(session('msg'))
            <div class="alert alert-success alert-dismissible fade show">
                Action Successful! <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-container">
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
                            $stockNo = !empty($row->barcode_id) ? $row->barcode_id : '<span class="text-muted small">No Barcode ID</span>';
                            
                            $status_class = 'status-available';
                            $status_text = 'Available';
                            if($row->quantity == 0) { $status_class = 'status-out'; $status_text = 'Out of Stock'; }
                            elseif($row->quantity <= 10) { $status_class = 'status-low'; $status_text = 'Low Stock'; }
                        @endphp
                        <tr class="clickable-row" data-id="{{ $row->id }}">
                            <td class="fw-bold text-primary font-monospace">{!! $stockNo !!}</td>
                            <td class="fw-bold">{{ $row->article }}</td>
                            <td>{{ $row->description }}</td>
                            <td>{{ $row->unit_measure }}</td>
                            <td>₱{{ number_format($row->unit_value, 2) }}</td>
                            <td class="fw-bold fs-5 text-dark">{{ $row->quantity }}</td>
                            <td><span class="badge rounded-pill {{ $status_class }}">{{ $status_text }}</span></td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button class="btn btn-primary btn-sm view-btn" data-id="{{ $row->id }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <button class="btn btn-success btn-sm edit-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editSupplyModal"
                                            data-id="{{ $row->id }}"
                                            data-article="{{ $row->article }}"
                                            data-stock="{{ $row->barcode_id }}" 
                                            data-desc="{{ $row->description }}"
                                            data-supplier="{{ $row->supplier }}"
                                            data-unit="{{ $row->unit_measure }}"
                                            data-value="{{ $row->unit_value }}"
                                            data-qty="{{ $row->quantity }}"
                                            data-image="{{ $row->image }}"> <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <button class="btn btn-danger btn-sm delete-btn" 
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
                            <td colspan="8" class="text-center py-4">No supplies found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-2">
                
                <div class="text-muted small">
                    Showing {{ $supplies->firstItem() ?? 0 }} to {{ $supplies->lastItem() ?? 0 }} of {{ $supplies->total() }} results
                </div>

                <div class="d-flex align-items-center">
                    <span class="text-muted small me-2">Per page</span>
                    <form action="{{ url('/supplies') }}" method="GET" id="perPageForm">
                        <select name="per_page" class="form-select form-select-sm shadow-none" style="width: 70px; border-color: #101954; color: #101954; font-weight: 500;" onchange="document.getElementById('perPageForm').submit();">
                            <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        </select>
                    </form>
                </div>

                <div class="custom-pagination-wrapper" id="styled-pagination">
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
                <form action="{{ url('/supplies') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-4 text-center">
                            <label class="form-label fw-bold d-block text-start">Supply Image</label>
                            <div class="border rounded bg-light d-flex justify-content-center align-items-center mx-auto mb-3 overflow-hidden shadow-sm" 
                                 style="width: 150px; height: 150px; position: relative;">
                                <img id="imagePreviewAdd" src="" alt="Preview" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                                <i id="imagePlaceholderAdd" class="fas fa-image fa-3x text-muted"></i>
                            </div>
                            <input type="file" name="image" id="imageInputAdd" class="form-control" accept="image/*">
                            <small class="text-muted text-start d-block mt-1">Recommended size: 500x500px (JPG, PNG)</small>
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
                                <input type="number" name="initial_quantity" class="form-control" min="0" placeholder="0" required>
                            </div>
                        </div>
                        <input type="hidden" name="status" value="Available">
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
                            <div class="border rounded bg-light d-flex justify-content-center align-items-center mx-auto mb-3 overflow-hidden shadow-sm" 
                                 style="width: 150px; height: 150px; position: relative;">
                                <img id="imagePreviewEdit" src="" alt="Preview" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                                <i id="imagePlaceholderEdit" class="fas fa-image fa-3x text-muted"></i>
                            </div>
                            <input type="file" name="image" id="imageInputEdit" class="form-control" accept="image/*">
                            <small class="text-muted text-start d-block mt-1">Leave blank to keep current image.</small>
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
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Unit Value (₱) <span class="text-danger">*</span></label>
                                <input type="number" name="unit_value" id="edit_value" class="form-control" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-success">Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" id="edit_qty" class="form-control" min="0" required>
                            </div>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @include('layouts.modal_scanner')

    <script>
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
                
                document.getElementById('edit_article').value = this.getAttribute('data-article');
                document.getElementById('edit_stock').value = this.getAttribute('data-stock'); 
                document.getElementById('edit_desc').value = this.getAttribute('data-desc');
                document.getElementById('edit_supplier').value = this.getAttribute('data-supplier');
                document.getElementById('edit_unit').value = this.getAttribute('data-unit');
                document.getElementById('edit_value').value = this.getAttribute('data-value');
                document.getElementById('edit_qty').value = this.getAttribute('data-qty');

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

        // PERFECTED PAGINATION LOGIC (Auto-Center + Mouse Wheel)
        window.addEventListener('load', function() {
            const paginationUl = document.querySelector('.custom-pagination-wrapper ul.pagination');
            
            if (paginationUl) {
                // 1. Smooth Mouse Wheel Scrolling
                paginationUl.addEventListener('wheel', function(e) {
                    if (e.deltaY !== 0) {
                        e.preventDefault();
                        this.scrollLeft += (e.deltaY * 1.5);
                    }
                }, { passive: false });

                // 2. Exact Auto-Center Logic
                setTimeout(() => {
                    const activeLi = paginationUl.querySelector('.page-item.active');
                    if (activeLi) {
                        const ulRect = paginationUl.getBoundingClientRect();
                        const liRect = activeLi.getBoundingClientRect();
                        
                        // Current scroll + (Distance of item from left edge) - (Half container width) + (Half item width)
                        const scrollPos = paginationUl.scrollLeft + (liRect.left - ulRect.left) - (ulRect.width / 2) + (liRect.width / 2);
                        
                        paginationUl.scrollLeft = scrollPos;
                        
                        // Turn smooth scrolling back on via JS *after* the initial jump
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