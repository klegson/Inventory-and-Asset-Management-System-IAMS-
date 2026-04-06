@php
    $statusColor = ($asset->status == 'Serviceable') ? '#198754' : '#dc3545';
@endphp

<div class="modal-header border-0 d-flex flex-column align-items-center py-3" style="background-color: #101954; color: white; border-radius: 10px 10px 0 0;">
    <h5 class="modal-title fw-bold mb-0">Asset Details</h5>
</div>
<div class="modal-body p-0">
    <table class="table mb-0">
        <tbody>
            <tr style="border-bottom: 1px solid #f0f0f0;">
                <td class="ps-4 py-3 text-muted">Article:</td>
                <td class="pe-4 py-3 text-end fw-bold">{{ $asset->article }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #f0f0f0;">
                <td class="ps-4 py-3 text-muted">Description:</td>
                <td class="pe-4 py-3 text-end fw-bold">{{ $asset->description ?: '-' }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #f0f0f0;">
                <td class="ps-4 py-3 text-muted">Quantity:</td>
                <td class="pe-4 py-3 text-end fw-bold">{{ $asset->quantity }} {{ $asset->unit_measure }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #f0f0f0;">
                <td class="ps-4 py-3 text-muted">Unit Value:</td>
                <td class="pe-4 py-3 text-end fw-bold">₱{{ number_format($asset->unit_value, 2) }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #f0f0f0;">
                <td class="ps-4 py-3 text-muted">Total Value:</td>
                <td class="pe-4 py-3 text-end fw-bold">₱{{ number_format($asset->unit_value * $asset->quantity, 2) }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #f0f0f0;">
                <td class="ps-4 py-3 text-muted">Supplier:</td>
                <td class="pe-4 py-3 text-end fw-bold">{{ $asset->supplier ?: 'N/A' }}</td>
            </tr>
            <tr>
                <td class="ps-4 py-3 text-muted">Status:</td>
                <td class="pe-4 py-3 text-end">
                    <span class="badge" style="background-color: {{ $statusColor }};">
                        {{ $asset->status }}
                    </span>
                </td>
            </tr>
        </tbody>
    </table>
    <div class="bg-light text-center py-3 border-top">
        <small class="text-muted d-block mb-1">PROPERTY / BARCODE ID:</small>
        <span class="fw-bold text-dark fs-5 font-monospace">{{ $asset->barcode_id ?: 'N/A' }}</span>
    </div>
</div>
<div class="modal-footer border-0 p-3">
    <button type="button" class="btn btn-outline-primary w-100 py-2" data-bs-dismiss="modal">Close</button>
</div>