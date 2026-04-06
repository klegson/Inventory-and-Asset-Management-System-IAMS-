<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Defective Assets Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; background: white !important; }
            .container { max-width: 100% !important; width: 100% !important; box-shadow: none !important; margin: 0 !important; padding: 0 !important;}
            .table { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        body { background-color: #f8f9fa; }
        .report-header { text-align: center; margin-bottom: 30px; }
    </style>
</head>
<body class="p-4">
    <div class="container bg-white p-5 shadow-sm">
        <div class="no-print mb-4 d-flex justify-content-between">
            <a href="{{ url('/admin/reports') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
            <button onclick="window.print()" class="btn btn-danger"><i class="fas fa-print me-1"></i> Print Report</button>
        </div>

        <div class="report-header">
            <img src="{{ asset('assets/images/DepEdseal.png') }}" width="80" alt="Logo">
            <h4 class="mt-2 text-uppercase fw-bold">Department of Education</h4>
            <h5 class="text-danger">Unserviceable / Defective Assets Report</h5>
            <p class="text-muted">Generated on {{ date('F d, Y') }}</p>
        </div>

        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr class="text-center">
                    <th>Property No.</th>
                    <th>Article</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Unit Value</th>
                    <th>Total Value</th>
                </tr>
            </thead>
            <tbody>
                @php $grand_total = 0; @endphp
                @forelse($assets as $row)
                    @php 
                        $total = $row->unit_value * $row->quantity;
                        $grand_total += $total;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $row->barcode_id }}</td>
                        <td class="fw-bold">{{ $row->article }}</td>
                        <td>{{ $row->description }}</td>
                        <td class="text-center text-danger fw-bold">{{ $row->quantity }}</td>
                        <td class="text-end">₱{{ number_format($row->unit_value, 2) }}</td>
                        <td class="text-end">₱{{ number_format($total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-4">Great news! No defective/unserviceable assets found.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="table-danger fw-bold">
                    <td colspan="5" class="text-end text-danger">Total Loss Value:</td>
                    <td class="text-end text-danger">₱{{ number_format($grand_total, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>
</html>