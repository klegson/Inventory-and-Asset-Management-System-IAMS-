<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>RIS Master List</title>
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
            <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print me-1"></i> Print Report</button>
        </div>

        <div class="report-header">
            <img src="{{ asset('assets/images/DepEdseal.png') }}" width="80" alt="Logo">
            <h4 class="mt-2 text-uppercase fw-bold">Department of Education</h4>
            <h5>Master List of Requisition and Issue Slips (RIS)</h5>
            <p class="text-muted">Generated on {{ date('F d, Y') }}</p>
        </div>

        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr class="text-center">
                    <th>Date</th>
                    <th>RIS Number</th>
                    <th>Requested By</th>
                    <th>Division / Office</th>
                    <th>Purpose</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                    <tr>
                        <td class="text-center">{{ \Carbon\Carbon::parse($req->created_at)->format('M d, Y') }}</td>
                        <td class="fw-bold text-center">{{ $req->ris_no }}</td>
                        <td>{{ $req->sig_requested_by ?: 'N/A' }}</td>
                        <td>{{ $req->division }} / {{ $req->office }}</td>
                        <td><small>{{ $req->purpose }}</small></td>
                        <td class="text-center fw-semibold">{{ $req->status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-4">No RIS data available.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>