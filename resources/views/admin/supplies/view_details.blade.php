@php
    $stockNo = !empty($supply->barcode_id) ? $supply->barcode_id : 'N/A';
    $qty = $supply->quantity;
    $unitValue = $supply->unit_value;
    $totalValue = $qty * $unitValue;
    
    $status_class = 'status-available';
    $status_text = 'Available';
    if($qty == 0) { 
        $status_class = 'status-out text-danger'; 
        $status_text = 'Out of Stock'; 
    } elseif($qty <= 10) { 
        $status_class = 'status-low text-warning'; 
        $status_text = 'Low Stock'; 
    }
@endphp

<div class="modal-header d-block text-center border-0 p-3" style="background-color: #0b1c3f; border-top-left-radius: 10px; border-top-right-radius: 10px;">
    <h5 class="modal-title text-white fw-bold mb-0">Supply Details</h5>
</div>

<div class="modal-body px-4 pt-4 pb-0">
    <div class="d-flex align-items-center mb-4">
        <div class="me-4 border rounded d-flex justify-content-center align-items-center bg-light shadow-sm overflow-hidden" style="width: 100px; height: 100px; flex-shrink: 0;">
            @if(!empty($supply->image))
                <img src="{{ asset('storage/supplies/' . $supply->image) }}" alt="Supply Image" style="width: 100%; height: 100%; object-fit: cover;">
            @else
                <i class="fas fa-image fa-2x text-muted"></i>
            @endif
        </div>
        <div class="flex-grow-1">
            <div class="text-muted small text-uppercase tracking-wide mb-1" style="font-size: 0.75rem;">STOCK ID:</div>
            @if($stockNo !== 'N/A')
                <svg class="barcode-render-modal" data-value="{{ $stockNo }}"></svg>
            @else
                <div class="fs-5 fw-bold text-dark">N/A</div>
            @endif
        </div>
    </div>

    <div class="d-flex justify-content-between border-bottom py-2 mb-2">
        <span class="text-muted">Article:</span>
        <span class="fw-bold text-dark">{{ $supply->article }}</span>
    </div>
    
    <div class="d-flex justify-content-between border-bottom py-2 mb-2">
        <span class="text-muted">Description:</span>
        <span class="fw-bold text-dark">{{ $supply->description }}</span>
    </div>
    
    <div class="d-flex justify-content-between border-bottom py-2 mb-2">
        <span class="text-muted">Quantity:</span>
        <span class="fw-bold text-dark">{{ $qty }} {{ $supply->unit_measure }}</span>
    </div>
    
    <div class="d-flex justify-content-between border-bottom py-2 mb-2">
        <span class="text-muted">Unit Value:</span>
        <span class="fw-bold text-dark">₱{{ number_format($unitValue, 2) }}</span>
    </div>
    
    <div class="d-flex justify-content-between border-bottom py-2 mb-2">
        <span class="text-muted">Total Value:</span>
        <span class="fw-bold text-dark">₱{{ number_format($totalValue, 2) }}</span>
    </div>
    
    <div class="d-flex justify-content-between border-bottom py-2 mb-2">
        <span class="text-muted">Supplier:</span>
        <span class="fw-bold text-dark">{{ !empty($supply->supplier) ? $supply->supplier : 'N/A' }}</span>
    </div>
    
    <div class="d-flex justify-content-between border-bottom py-3 mb-4">
        <span class="text-muted mt-1">Status:</span>
        <span class="badge {{ $status_class }} rounded-pill px-3 py-2 border">{{ $status_text }}</span>
    </div>
</div>

<div class="modal-footer border-0 pt-0 pb-4 px-4 justify-content-center">
    <button type="button" class="btn btn-outline-primary w-100 py-2 rounded-3" data-bs-dismiss="modal">Close</button>
</div>