<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supply Overview - Supply System</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Background */
        body { 
            background-color: #f4f6f9; 
            font-family: 'Segoe UI', sans-serif; 
            overflow-x: hidden;
        }

        /* Main area */
        .main-content { 
            margin-left: 250px; 
            padding: 30px; 
            transition: all 0.3s; 
        }

        /* Sticky top section */
        .sticky-header {
            position: sticky;
            top: 0;
            background-color: #f4f6f9;
            z-index: 90;
            padding: 30px 30px 10px 30px;
            margin: -30px -30px 20px -30px;
        }

        /* Top banner */
        .welcome-banner {
            background: linear-gradient(135deg, #101954 0%, #0a4d9c 100%);
            color: white;   
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(25, 135, 84, 0.2);
        }

        /* Clickable Stock Card */
        .item-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 25px 20px; 
            text-align: center;
            color: #101954;
            display: flex;
            flex-direction: column;
            height: 100%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
        }

        .item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(16, 25, 84, 0.15);
            border-color: #0d6efd;
        }

        .item-card i.main-icon { 
            font-size: 2.5rem; 
            margin-bottom: 10px; 
        }

        .item-title {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .item-subtitle {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 15px;
            min-height: 20px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Stock Status text */
        .stock-status {
            font-size: 1.2rem;
            font-weight: bold;
            color: #212529;
            margin-top: 10px;
        }

        /* Progress Bar styling */
        .progress {
            height: 10px;
            border-radius: 10px;
            background-color: #e9ecef;
            margin-top: auto;
        }

        .input-group-text {
            border-color: #101954;
        }

        .form-control {
            border-color: #101954;
        }

        .progress-bar.safe { background-color: #198754; }
        .progress-bar.warning { background-color: #ffc107; }
        .progress-bar.danger { background-color: #dc3545; }

        /* Mobile */
        @media (max-width: 768px) { 
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; } 
        }
    </style>
</head>
<body>

    @include('layouts.user_sidebar')

    <div class="main-content">
        
        <div class="sticky-header">
            
            <div class="welcome-banner d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold mb-1">Supply Overview</h2>
                    <p class="mb-0 opacity-75">Track all regular office supplies and paper stocks here.</p>
                </div>
                <div class="text-end d-none d-md-block">
                    <i class="fa-solid fa-box-open" style="font-size: 3rem; opacity: 0.8;"></i>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="searchInput" placeholder="Search for a supply item...">
                    </div>
                </div>
            </div>

        </div>

        <div class="row g-4 mb-5" id="supplyContainer">
            
            @forelse($supplies as $item)
                @php
                    // Determine Icon and Color dynamically based on Item Name
                    $nameLower = strtolower($item->article);
                    $iconClass = 'fa-box';
                    $iconColor = 'text-primary';

                    if (str_contains($nameLower, 'paper') || str_contains($nameLower, 'bond')) {
                        $iconClass = 'fa-file-lines';
                        $iconColor = 'text-secondary';
                    } elseif (str_contains($nameLower, 'pen') || str_contains($nameLower, 'marker')) {
                        $iconClass = 'fa-pen';
                        $iconColor = 'text-success';
                    } elseif (str_contains($nameLower, 'ink')) {
                        $iconClass = 'fa-fill-drip';
                        $iconColor = 'text-dark';
                    } elseif (str_contains($nameLower, 'note')) {
                        $iconClass = 'fa-note-sticky';
                        $iconColor = 'text-warning';
                    } elseif (str_contains($nameLower, 'tape')) {
                        $iconClass = 'fa-tape';
                        $iconColor = 'text-info';
                    }

                    // Calculate Stock Health (Assumes 500 is "Full" capacity for visual purposes)
                    $maxCapacity = 500; 
                    $qty = $item->quantity;
                    $percentage = min(100, ($qty / $maxCapacity) * 100);
                    
                    // Progress bar color
                    $barClass = 'safe';
                    if ($percentage <= 20) $barClass = 'danger';
                    elseif ($percentage <= 50) $barClass = 'warning';
                @endphp

                <div class="col-6 col-md-3 supply-card-wrapper" data-name="{{ strtolower($item->article) }}">
                    <a class="item-card" data-bs-toggle="modal" data-bs-target="#detailModal" 
                       onclick="showDetails('{{ $item->article }}', '{{ $item->description }}', '{{ $item->quantity }}', '{{ $item->unit_measure }}', '{{ $item->unit_value }}', '{{ $item->supplier }}')">
                        
                        <i class="fa-solid {{ $iconClass }} main-icon {{ $iconColor }}"></i>
                        <div class="item-title" title="{{ $item->article }}">{{ $item->article }}</div>
                        <div class="item-subtitle" title="{{ $item->description }}">{{ $item->description ?: 'No Description' }}</div>
                        
                        <div class="progress mt-3">
                            <div class="progress-bar {{ $barClass }}" style="width: {{ $percentage }}%;"></div>
                        </div>
                        
                        <div class="stock-status">{{ $qty }}</div>
                        <small class="text-muted">Current Stock ({{ $item->unit_measure }})</small>
                    </a>
                </div>
            @empty
                <div class="col-12 text-center py-5 text-muted">
                    <i class="fa-solid fa-box-open fa-3x mb-3 opacity-50"></i>
                    <h4>No Supplies Found</h4>
                </div>
            @endforelse

        </div>

    </div>

    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalTitle">Item Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-4 pb-4">
                    
                    <div class="mb-3 border-bottom pb-2">
                        <small class="text-muted d-block">Description</small>
                        <span class="fw-bold" id="mDesc"></span>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-6 border-end">
                            <small class="text-muted d-block">Current Quantity</small>
                            <span class="fw-bold fs-5 text-primary" id="mQty"></span> <span id="mUnit"></span>
                        </div>
                        <div class="col-6 ps-3">
                            <small class="text-muted d-block">Unit Value</small>
                            <span class="fw-bold text-success" id="mValue"></span>
                        </div>
                    </div>

                    <div class="bg-light p-3 rounded">
                        <small class="text-muted d-block">Supplier / Vendor</small>
                        <span class="fw-bold" id="mSupplier"></span>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Populate Dynamic Modal
        function showDetails(title, desc, qty, unit, value, supplier) {
            document.getElementById('modalTitle').innerText = title;
            document.getElementById('mDesc').innerText = desc || 'N/A';
            document.getElementById('mQty').innerText = qty;
            document.getElementById('mUnit').innerText = unit;
            document.getElementById('mValue').innerText = '₱' + parseFloat(value).toFixed(2);
            document.getElementById('mSupplier').innerText = supplier || 'Not specified';
        }

        // Live Search Filter
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let cards = document.querySelectorAll('.supply-card-wrapper');
            
            cards.forEach(card => {
                let name = card.getAttribute('data-name');
                if (name.includes(filter)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>