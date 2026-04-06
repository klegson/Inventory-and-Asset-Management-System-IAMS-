<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports Dashboard - DepEd AMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .main-content { margin-left: 250px; padding: 20px; transition: all 0.3s; }
        
        .report-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid #e0e0e0;
            cursor: pointer;
            height: 100%;
            text-decoration: none;
            display: block;
        }
        
        .report-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            border-color: #101954;
        }

        .folder-icon {
            font-size: 3.5rem;
            margin-bottom: 15px;
            display: block;
        }

        .report-title {
            color: #101954;
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 5px;
        }

        .report-desc {
            color: #6c757d;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) { .main-content { margin-left: 0; } }
    </style>
</head>
<body>

    @include('layouts.admin_header')
    @include('layouts.admin_sidebar')

    <div class="main-content">
        <div class="mb-4 border-bottom pb-1 mb-3" style="border-color: #003366 !important;">
            <h2 style="color: #003366; font-weight: 700;">
                <i class="fa-solid fa-chart-line me-2"></i> Inventory Reports
            </h2>
            <p class="text-muted">Generate and view system-wide inventory and request summaries.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-xl-3">
                <a href="{{ url('/admin/reports/assets') }}" class="report-card">
                    <span class="folder-icon text-primary"><i class="fas fa-folder" style="color: #004891;"></i></span>
                    <div class="report-title">Asset Stocks</div>
                    <div class="report-desc">Equipment inventory and valuation summary.</div>
                </a>
            </div>

            <div class="col-md-6 col-xl-3">
                <a href="{{ url('/admin/reports/supplies') }}" class="report-card">
                    <span class="folder-icon text-success"><i class="fas fa-folder"></i></span>
                    <div class="report-title">Supply Stocks</div>
                    <div class="report-desc">Consumable items and remaining balances.</div>
                </a>
            </div>

            <div class="col-md-6 col-xl-3">
                <a href="{{ url('/admin/reports/ris') }}" class="report-card">
                    <span class="folder-icon text-warning"><i class="fas fa-folder"></i></span>
                    <div class="report-title">RIS Reports</div>
                    <div class="report-desc">List of all Requisition and Issue Slips.</div>
                </a>
            </div>

            <div class="col-md-6 col-xl-3">
                <a href="{{ url('/admin/reports/defective') }}" class="report-card">
                    <span class="folder-icon text-danger"><i class="fas fa-folder"></i></span>
                    <div class="report-title">Defective Assets</div>
                    <div class="report-desc">Summary of unserviceable/returned assets.</div>
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>