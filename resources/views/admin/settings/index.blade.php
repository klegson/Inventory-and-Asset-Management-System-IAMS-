<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .main-content { margin-left: 250px; padding: 30px; transition: all 0.3s; padding-top: 90px !important; }
        .custom-card { background: #fff; border: 1px solid #e0e0e0; border-radius: 12px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 25px;}
        .form-label { font-weight: 600; color: #475569; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .format-badge { font-family: monospace; background-color: #f1f5f9; padding: 4px 8px; border-radius: 6px; color: #101954; font-size: 0.85rem; font-weight: bold; border: 1px solid #cbd5e1;}
        @media (max-width: 768px) { .main-content { margin-left: 0; } }
    </style>
</head>
<body>

    @include('layouts.admin_header')
    @include('layouts.admin_sidebar')

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-4" style="border-color: #003366 !important;">
            <div>
                <h2 style="color: #003366; margin: 0;">
                    <i class="fas fa-cogs me-2"></i> System Settings
                </h2>
                <p class="text-muted small mb-0">Manage Document Sequence Numbers and Barcode Starters</p>
            </div>
        </div>

        @if(session('msg'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm py-3">
                <i class="fas fa-check-circle me-2"></i> {{ session('msg') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-6">
                <div class="custom-card border-top border-4 border-primary">
                    <h5 class="fw-bold mb-4 text-dark"><i class="fas fa-box-open text-primary me-2"></i>Inventory & Requests</h5>
                    
                    <form action="{{ url('/admin/settings/update') }}" method="POST" class="mb-4">
                        @csrf
                        <label class="form-label">Next Supplies Stock No. (Barcode)</label>
                        <div class="input-group mb-2 shadow-sm">
                            <span class="input-group-text bg-light text-muted border-end-0">SUP-{{ date('Y-m') }}-</span>
                            <input type="number" name="seq_stock_no" class="form-control fw-bold border-start-0 ps-0" value="{{ $settings['seq_stock_no'] ?? '1' }}" min="1" required>
                            <button type="submit" class="btn btn-primary fw-bold"><i class="fas fa-save me-1"></i> Update</button>
                        </div>
                        <small class="text-muted d-block">Format will be: <span class="format-badge">SUP-{{ date('Y-m') }}-{{ str_pad($settings['seq_stock_no'] ?? '1', 4, '0', STR_PAD_LEFT) }}</span></small>
                    </form>

                    <hr class="my-4 text-muted opacity-25">

                    <form action="{{ url('/admin/settings/update') }}" method="POST" class="mb-3">
                        @csrf
                        <label class="form-label">Next RIS Number</label>
                        <div class="input-group mb-2 shadow-sm">
                            <span class="input-group-text bg-light text-muted border-end-0">RIS-{{ date('Y-m') }}-</span>
                            <input type="number" name="seq_ris_no" class="form-control fw-bold border-start-0 ps-0" value="{{ $settings['seq_ris_no'] ?? '1' }}" min="1" required>
                            <button type="submit" class="btn btn-primary fw-bold"><i class="fas fa-save me-1"></i> Update</button>
                        </div>
                        <small class="text-muted d-block">Format will be: <span class="format-badge">RIS-{{ date('Y-m') }}-{{ str_pad($settings['seq_ris_no'] ?? '1', 4, '0', STR_PAD_LEFT) }}</span></small>
                    </form>
                </div>
            </div>

            <div class="col-md-6">
                <div class="custom-card border-top border-4 border-success">
                    <h5 class="fw-bold mb-4 text-dark"><i class="fas fa-desktop text-success me-2"></i>Equipment & Assets (ICS)</h5>
                    
                    <form action="{{ url('/admin/settings/update') }}" method="POST" class="mb-4">
                        @csrf
                        <label class="form-label">Next PAR Number (PPE)</label>
                        <div class="input-group mb-2 shadow-sm">
                            <span class="input-group-text bg-light text-muted border-end-0">PAR-{{ date('Y-m') }}-</span>
                            <input type="number" name="seq_par_no" class="form-control fw-bold border-start-0 ps-0" value="{{ $settings['seq_par_no'] ?? '1' }}" min="1" required>
                            <button type="submit" class="btn btn-success fw-bold"><i class="fas fa-save me-1"></i> Update</button>
                        </div>
                        <small class="text-muted d-block">Format will be: <span class="format-badge">PAR-{{ date('Y-m') }}-{{ str_pad($settings['seq_par_no'] ?? '1', 4, '0', STR_PAD_LEFT) }}</span></small>
                    </form>

                    <hr class="my-4 text-muted opacity-25">

                    <form action="{{ url('/admin/settings/update') }}" method="POST" class="mb-4">
                        @csrf
                        <label class="form-label">Next SPHV Number (High Value)</label>
                        <div class="input-group mb-2 shadow-sm">
                            <span class="input-group-text bg-light text-muted border-end-0">SPHV-{{ date('Y-m') }}-</span>
                            <input type="number" name="seq_sphv_no" class="form-control fw-bold border-start-0 ps-0" value="{{ $settings['seq_sphv_no'] ?? '1' }}" min="1" required>
                            <button type="submit" class="btn btn-success fw-bold"><i class="fas fa-save me-1"></i> Update</button>
                        </div>
                        <small class="text-muted d-block">Format will be: <span class="format-badge">SPHV-{{ date('Y-m') }}-{{ str_pad($settings['seq_sphv_no'] ?? '1', 4, '0', STR_PAD_LEFT) }}</span></small>
                    </form>

                    <hr class="my-4 text-muted opacity-25">

                    <form action="{{ url('/admin/settings/update') }}" method="POST" class="mb-3">
                        @csrf
                        <label class="form-label">Next SPLV Number (Low Value)</label>
                        <div class="input-group mb-2 shadow-sm">
                            <span class="input-group-text bg-light text-muted border-end-0">SPLV-{{ date('Y-m') }}-</span>
                            <input type="number" name="seq_splv_no" class="form-control fw-bold border-start-0 ps-0" value="{{ $settings['seq_splv_no'] ?? '1' }}" min="1" required>
                            <button type="submit" class="btn btn-success fw-bold"><i class="fas fa-save me-1"></i> Update</button>
                        </div>
                        <small class="text-muted d-block">Format will be: <span class="format-badge">SPLV-{{ date('Y-m') }}-{{ str_pad($settings['seq_splv_no'] ?? '1', 4, '0', STR_PAD_LEFT) }}</span></small>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>