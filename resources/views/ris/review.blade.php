<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review RIS - DepEd ROV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --deped-blue: #1a237e;
            --deped-gold: #fbc02d;
            --light-bg: #f8f9fa;
        }

        body { 
            background-color: #f0f2f5; 
            font-family: 'Inter', sans-serif; 
            color: #444; 
            margin: 0;
        }

        .main-content {
            margin-left: 260px;
            padding: 20px;
            min-height: 100vh;
        }

        .top-bar { 
            background: linear-gradient(90deg, var(--deped-blue) 0%, #283593 100%);
            color: white; 
            padding: 12px 25px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .section-box { 
            background: white; 
            padding: 25px; 
            margin-bottom: 25px; 
            border-radius: 12px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .section-title { 
            color: var(--deped-blue); 
            font-weight: 700; 
            margin-bottom: 20px; 
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
            display: flex;
            align-items: center;
            font-size: 1rem;
        }
        
        .section-title i { margin-right: 10px; color: var(--deped-gold); }

        label { 
            font-weight: 600; 
            font-size: 0.85rem; 
            color: #555; 
            margin-bottom: 6px;
        }

        .form-control, .form-select { 
            border-radius: 6px; 
            border: 1px solid #ced4da;
            font-size: 0.9rem;
        }
        
        .form-control:read-only { background-color: #f8f9fa; }

        .table-custom th {
            background-color: #f8f9fa;
            color: #555;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .table-custom td {
            vertical-align: middle;
        }

        .sig-line {
            border: none;
            border-bottom: 2px solid #333;
            border-radius: 0;
            font-weight: bold;
            background: transparent;
            text-align: center;
            padding: 0;
        }

        .desig-input {
            border: none;
            border-bottom: 1px dashed #aaa;
            border-radius: 0;
            background: transparent;
            text-align: center;
            font-size: 0.85rem;
            color: #666;
            padding: 2px;
            width: 80%;
            margin: 0 auto;
        }

        /* --- PRINT IMPROVEMENTS --- */
        #print-area { display: none; }

        @media print {
            @page { size: A4 portrait; margin: 10mm; }

            body * { visibility: hidden; }
            .sidebar, .main-content { display: none !important; margin: 0 !important; padding: 0 !important; }

            #print-area, #print-area * {
                visibility: visible;
            }

            #print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                color: #000;
                display: block;
                font-family: 'Times New Roman', Times, serif;
                font-size: 10pt;
            }

            #print-area table { 
                display: table !important; 
                width: 100% !important; 
                border-collapse: collapse !important; 
                table-layout: fixed !important; 
            }
            #print-area thead { display: table-header-group !important; }
            #print-area tbody { display: table-row-group !important; }
            #print-area tr { display: table-row !important; page-break-inside: avoid; }
            #print-area th, #print-area td { 
                display: table-cell !important; 
                float: none !important; 
            }
        }

        @media (max-width: 992px) { .main-content { margin-left: 0; } }
    </style>
</head>
<body>

    @include('layouts.header')
    @include('layouts.sidebar')

<div class="main-content">
    <div class="top-bar">
        <div><i class="fa-solid fa-building-shield me-2"></i> <strong>ASSET MANAGEMENT / SUPPLY SECTION</strong></div>
        <div id="clock"><i class="fa-regular fa-clock me-2"></i> Loading time...</div>
    </div>

    <form action="{{ url('/ris/'.$req->id.'/update') }}" method="POST" id="reviewForm">
        @csrf
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="{{ url('/ris') }}" class="btn btn-outline-secondary fw-bold shadow-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Queue
                </a>
                <h3 class="fw-bold m-0" style="color: var(--deped-blue);">REVIEW RIS: <span id="v_ris_no">{{ $req->ris_no }}</span></h3>
                <span class="badge bg-warning text-dark mt-1">{{ $req->status }}</span>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary fw-bold shadow-sm" onclick="prepareAndPrint()">
                    <i class="fas fa-print me-1"></i> Print PDF
                </button>
                <button type="submit" name="action" value="save" class="btn btn-outline-primary fw-bold shadow-sm">
                    <i class="fas fa-save me-1"></i> Save Draft
                </button>
                
                @if($req->status == 'Approved')
                    <button type="submit" name="action" value="return" class="btn btn-warning text-dark fw-bold shadow-sm" onclick="return confirm('Are you sure you want to reopen this RIS for corrections? This will revoke the current approval.')">
                        <i class="fas fa-folder-open me-1"></i> Re-open for Corrections
                    </button>
                @elseif(!in_array($req->status, ['Rejected', 'Cancelled', 'Declined']))
                    <button type="submit" name="action" value="forward" class="btn btn-success fw-bold shadow-sm" onclick="return confirm('Forward this to Admin for final approval?')">
                        <i class="fas fa-share me-1"></i> Forward to Admin
                    </button>
                @endif
            </div>
        </div>

        <div class="section-box">
            <h6 class="section-title"><i class="fa-solid fa-circle-info"></i> General Information</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label>Entity Name</label>
                    <input type="text" name="entity_name" id="v_entity" class="form-control" value="{{ $req->entity_name }}">
                </div>
                <div class="col-md-4">
                    <label>Unit / Section (Division)</label>
                    <input type="text" name="division" id="v_division" class="form-control fw-bold" value="{{ $req->division }}">
                </div>
                <div class="col-md-4">
                    <label>Office</label>
                    <input type="text" name="office" id="v_office" class="form-control" value="{{ $req->office }}">
                </div>
                <div class="col-md-4">
                    <label>Fund Cluster</label>
                    <input type="text" name="fund_cluster" id="v_fund" class="form-control" value="{{ $req->fund_cluster }}">
                </div>
                <div class="col-md-4">
                    <label>Responsible Center Code (RCC)</label>
                    <input type="text" name="rcc" id="v_center" class="form-control" value="{{ $req->rcc }}">
                </div>
                <div class="col-md-4">
                    <label>Purpose</label>
                    <input type="text" name="purpose" id="v_purpose" class="form-control" value="{{ $req->purpose }}">
                </div>
            </div>
        </div>

        <div class="section-box">
            <h6 class="section-title"><i class="fa-solid fa-list-check"></i> Stock Verification & Issuance</h6>
            
            <div class="table-responsive">
                <table class="table table-bordered table-custom">
                    <thead>
                        <tr>
                            <th style="width: 15%">Stock No.</th>
                            <th style="width: 7%">Unit</th>
                            <th style="width: 25%">Item Description</th>
                            <th style="width: 7%">Req Qty</th>
                            <th style="width: 12%" class="bg-warning bg-opacity-10 text-dark">Avail?</th>
                            <th style="width: 12%" class="bg-success bg-opacity-10 text-success">Issue Qty</th>
                            <th style="width: 20%">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($req->items as $i => $item)
                            @php 
                                $availVal = strtolower($item->stock_avail ?? 'n/a');
                                
                                $stockColor = 'danger';
                                if($item->current_stock >= $item->req_quantity) $stockColor = 'success';
                                elseif($item->current_stock > 0) $stockColor = 'warning text-dark';
                                
                                // Calculate initial remaining stock for display
                                $currentRemaining = $item->current_stock - ($item->issue_quantity ?: 0);
                            @endphp
                            <tr class="v-item-row">
                                <input type="hidden" name="item_id[{{ $i }}]" value="{{ $item->id }}">
                                
                                <td>
                                    <input type="text" name="stock_no[{{ $i }}]" class="form-control form-control-sm v-stock" value="{{ $item->stock_no }}" readonly>
                                </td>
                                <td>
                                    <input type="text" name="unit[{{ $i }}]" class="form-control form-control-sm v-unit" value="{{ $item->unit }}" readonly>
                                </td>
                                <td>
                                    <input type="text" name="description[{{ $i }}]" class="form-control form-control-sm v-desc fw-bold" value="{{ $item->description }}" readonly>
                                </td>
                                <td>
                                    <input type="number" name="req_quantity[{{ $i }}]" class="form-control form-control-sm text-center v-req" value="{{ $item->req_quantity }}" readonly>
                                </td>
                                <td class="bg-warning bg-opacity-10">
                                    <select name="stock_avail_{{ $i }}" class="form-select form-select-sm border-warning v-avail">
                                        <option value="n/a" {{ $availVal == 'n/a' ? 'selected' : '' }}>N/A</option>
                                        <option value="yes" {{ $availVal == 'yes' ? 'selected' : '' }}>Yes</option>
                                        <option value="no" {{ $availVal == 'no' ? 'selected' : '' }}>No</option>
                                    </select>
                                </td>
                                <td class="bg-success bg-opacity-10">
                                    <input type="number" name="issue_quantity[{{ $i }}]" class="form-control form-control-sm border-success text-center fw-bold v-issue"
                                        style="margin-top: 1.29rem;"
                                        value="{{ $item->issue_quantity }}" min="0" max="{{ $item->current_stock }}" 
                                        oninput="
                                            let max = {{ $item->current_stock }};
                                            let val = parseInt(this.value) || 0;
                                            if(val > max) { this.value = max; val = max; }
                                            if(val < 0) { this.value = 0; val = 0; }
                                            document.getElementById('rem_stock_{{ $i }}').innerText = (max - val);
                                        " 
                                        placeholder="0" title="Max available: {{ $item->current_stock }}">
                                    
                                    <div class="mt-1 text-center" style="font-size: 0.75rem; white-space: nowrap;">
                                        <span class="text-{{ $stockColor }} fw-bold">
                                            <i class="fas fa-box"></i> Left: <span id="rem_stock_{{ $i }}">{{ $currentRemaining }}</span> / {{ $item->current_stock }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" name="remarks[{{ $i }}]" class="form-control form-control-sm v-rem" placeholder="e.g. Price of the item..." value="{{ $item->remarks }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section-box">
            <h6 class="section-title"><i class="fa-solid fa-file-signature"></i> Signatures</h6>
            <div class="row text-center g-4">
                <div class="col-md-3">
                    <label class="d-block mb-3 text-uppercase small text-muted">Requested By</label>
                    <input type="text" name="sig_requested_by" id="v_req_name" class="form-control sig-line" value="{{ $req->sig_requested_by }}">
                    <input type="text" name="desig_requested" id="v_req_des" class="form-control desig-input mt-1" value="{{ $req->desig_requested }}" placeholder="Designation">
                </div>
                <div class="col-md-3">
                    <label class="d-block mb-3 text-uppercase small text-muted">Approved By</label>
                    <input type="text" name="sig_approved_by" id="v_app_name" class="form-control sig-line" value="{{ $req->sig_approved_by }}">
                    <input type="text" name="desig_approved" id="v_app_des" class="form-control desig-input mt-1" value="{{ $req->desig_approved }}" placeholder="Designation">
                </div>
                <div class="col-md-3">
                    <label class="d-block mb-3 text-uppercase small text-muted">Issued By</label>
                    <input type="text" name="sig_issued_by" id="v_iss_name" class="form-control sig-line" value="{{ $req->sig_issued_by ?? Auth::user()->firstname . ' ' . Auth::user()->lastname }}">
                    <input type="text" name="desig_issued" id="v_iss_des" class="form-control desig-input mt-1" value="{{ $req->desig_issued ?? Auth::user()->designation }}" placeholder="Designation">
                </div>
                <div class="col-md-3">
                    <label class="d-block mb-3 text-uppercase small text-muted">Received By</label>
                    <input type="text" name="sig_received_by" id="v_rec_name" class="form-control sig-line" value="{{ $req->sig_received_by }}">
                    <input type="text" name="desig_received" id="v_rec_des" class="form-control desig-input mt-1" value="{{ $req->desig_received }}" placeholder="Designation">
                </div>
            </div>
        </div>
    </form>
</div>

<div id="print-area">
    <div style="text-align: center; font-family: 'Times New Roman', Times, serif; margin-bottom: 5px;">
        <img src="{{ asset('assets/images/DepEdseal.png') }}" style="width: 60px; margin: 0 auto 2px auto; display: block;">
        <div style="font-size: 9pt;">Republic of the Philippines</div>
        <div style="font-size: 18pt; font-family: 'Old English Text MT', 'Engravers Old English', serif; line-height: 1;">Department of Education</div>
        <div style="font-size: 10pt;">Region V - Bicol</div>
        <div style="font-size: 12pt; font-weight: bold; margin-top: 5px;">REQUISITION AND ISSUE SLIP</div>
    </div>

    <table style="width: 100%; border: none; font-family: 'Times New Roman', Times, serif; font-size: 10pt; margin-bottom: 5px;">
        <tr>
            <td style="width: 12%; white-space: nowrap; padding: 2px;">Entity Name:</td>
            <td style="width: 38%; border-bottom: 1px solid black; padding: 2px;" id="p-entity"></td>
            <td style="width: 25%; text-align: right; padding-right: 10px; white-space: nowrap; padding: 2px;">Fund Cluster:</td>
            <td style="width: 25%; border-bottom: 1px solid black; padding: 2px;" id="p-fund"></td>
        </tr>
        <tr>
            <td style="white-space: nowrap; padding: 2px;">Division:</td>
            <td style="border-bottom: 1px solid black; padding: 2px;" id="p-division"></td>
            <td style="text-align: right; padding-right: 10px; white-space: nowrap; padding: 2px;">Responsibility Center Code:</td>
            <td style="border-bottom: 1px solid black; padding: 2px;" id="p-center"></td>
        </tr>
        <tr>
            <td style="white-space: nowrap; padding: 2px;">Office:</td>
            <td style="border-bottom: 1px solid black; padding: 2px;" id="p-office"></td>
            <td style="text-align: right; padding-right: 10px; white-space: nowrap; padding: 2px;">RIS No:</td>
            <td style="border-bottom: 1px solid black; font-weight: bold; padding: 2px;" id="p-ris"></td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; font-family: 'Times New Roman', Times, serif; font-size: 10pt; border: 1px solid black; table-layout: fixed;">
        <colgroup>
            <col style="width: 20%;"> 
            <col style="width: 8%;">  
            <col style="width: 38%;"> 
            <col style="width: 8%;">  
            <col style="width: 5%;">  
            <col style="width: 5%;">  
            <col style="width: 8%;">  
            <col style="width: 10%;"> 
        </colgroup>
        <thead>
            <tr>
                <th colspan="4" style="border: 1px solid black; padding: 3px; text-align: center;">REQUISITION</th>
                <th colspan="2" style="border: 1px solid black; padding: 3px; text-align: center;">Stock Available?</th>
                <th colspan="2" style="border: 1px solid black; padding: 3px; text-align: center;">Issue</th>
            </tr>
            <tr>
                <th style="border: 1px solid black; padding: 3px; text-align: center;">Stock No.</th>
                <th style="border: 1px solid black; padding: 3px; text-align: center;">Unit</th>
                <th style="border: 1px solid black; padding: 3px; text-align: center;">Description</th>
                <th style="border: 1px solid black; padding: 3px; text-align: center;">Quantity</th>
                <th style="border: 1px solid black; padding: 3px; text-align: center;">Yes</th>
                <th style="border: 1px solid black; padding: 3px; text-align: center;">No</th>
                <th style="border: 1px solid black; padding: 3px; text-align: center;">Quantity</th>
                <th style="border: 1px solid black; padding: 3px; text-align: center;">Remarks</th>
            </tr>
        </thead>
        <tbody id="print-items-body">
            </tbody>
        <tbody>
            <tr>
                <td colspan="8" style="border: 1px solid black; padding: 3px; text-align: left;">
                    <b>Purpose:</b> <span id="p-purpose"></span>
                </td>
            </tr>
        </tbody>
    </table>

    <table style="width: 100%; border-collapse: collapse; font-family: 'Times New Roman', Times, serif; font-size: 10pt; border: 1px solid black; border-top: none; table-layout: fixed;">
        <tbody>
            <tr>
                <td style="width: 12%; border: 1px solid black; padding: 3px; border-top: none;"></td>
                <td style="width: 22%; border: 1px solid black; padding: 3px; font-weight: bold; text-align: center; border-top: none;">Requested by:</td>
                <td style="width: 22%; border: 1px solid black; padding: 3px; font-weight: bold; text-align: center; border-top: none;">Approved by:</td>
                <td style="width: 22%; border: 1px solid black; padding: 3px; font-weight: bold; text-align: center; border-top: none;">Issued by:</td>
                <td style="width: 22%; border: 1px solid black; padding: 3px; font-weight: bold; text-align: center; border-top: none;">Received by:</td>
            </tr>
            <tr>
                <td style="border: 1px solid black; padding: 3px; text-align: left;">Signature</td>
                <td style="border: 1px solid black; padding: 3px;"></td>
                <td style="border: 1px solid black; padding: 3px;"></td>
                <td style="border: 1px solid black; padding: 3px;"></td>
                <td style="border: 1px solid black; padding: 3px;"></td>
            </tr>
            <tr>
                <td style="border: 1px solid black; padding: 3px; text-align: left;">Printed Name</td>
                <td style="border: 1px solid black; padding: 3px; text-align: center;"><b id="p-req-name"></b></td>
                <td style="border: 1px solid black; padding: 3px; text-align: center;"><b id="p-app-name"></b></td>
                <td style="border: 1px solid black; padding: 3px; text-align: center;"><b id="p-iss-name"></b></td>
                <td style="border: 1px solid black; padding: 3px; text-align: center;"><b id="p-rec-name"></b></td>
            </tr>
            <tr>
                <td style="border: 1px solid black; padding: 3px; text-align: left;">Designation</td>
                <td style="border: 1px solid black; padding: 3px; text-align: center;" id="p-req-des"></td>
                <td style="border: 1px solid black; padding: 3px; text-align: center;" id="p-app-des"></td>
                <td style="border: 1px solid black; padding: 3px; text-align: center;" id="p-iss-des"></td>
                <td style="border: 1px solid black; padding: 3px; text-align: center;" id="p-rec-des"></td>
            </tr>
            <tr>
                <td style="border: 1px solid black; padding: 3px; text-align: left;">Date</td>
                <td style="border: 1px solid black; padding: 3px; text-align: center;"><span id="p-req-date"></span></td>
                <td style="border: 1px solid black; padding: 3px; text-align: center;"></td>
                <td style="border: 1px solid black; padding: 3px; text-align: center;"></td>
                <td style="border: 1px solid black; padding: 3px; text-align: center;"></td>
            </tr>
        </tbody>
    </table>
</div>

<script>
    function updateClock() {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
        document.getElementById('clock').innerHTML = '<i class="fa-regular fa-calendar-check me-2"></i> ' + now.toLocaleDateString('en-US', options);
    }
    setInterval(updateClock, 1000);
    updateClock();

    function formatDesignation(val) {
        if (!val) return '';
        return val.replace(' (', '<br>(');
    }

    function prepareAndPrint() {
        // Map Headers
        document.getElementById('p-entity').innerText = document.getElementById('v_entity').value;
        document.getElementById('p-division').innerText = document.getElementById('v_division').value;
        document.getElementById('p-office').innerText = document.getElementById('v_office').value;
        document.getElementById('p-fund').innerText = document.getElementById('v_fund').value;
        document.getElementById('p-center').innerText = document.getElementById('v_center').value;
        document.getElementById('p-purpose').innerText = document.getElementById('v_purpose').value;
        document.getElementById('p-ris').innerText = document.getElementById('v_ris_no').innerText;

        // Map Signatures
        document.getElementById('p-req-name').innerText = document.getElementById('v_req_name').value;
        document.getElementById('p-app-name').innerText = document.getElementById('v_app_name').value;
        document.getElementById('p-iss-name').innerText = document.getElementById('v_iss_name').value;
        document.getElementById('p-rec-name').innerText = document.getElementById('v_rec_name').value;

        document.getElementById('p-req-des').innerHTML = formatDesignation(document.getElementById('v_req_des').value);
        document.getElementById('p-app-des').innerHTML = formatDesignation(document.getElementById('v_app_des').value);
        document.getElementById('p-iss-des').innerHTML = formatDesignation(document.getElementById('v_iss_des').value);
        document.getElementById('p-rec-des').innerHTML = formatDesignation(document.getElementById('v_rec_des').value);

        // Map Dynamic Table
        const printBody = document.getElementById('print-items-body');
        printBody.innerHTML = '';
        
        const rows = document.querySelectorAll('.v-item-row');
        let rowsAdded = 0;

        rows.forEach(row => {
            const stock = row.querySelector('.v-stock').value;
            const unit = row.querySelector('.v-unit').value;
            const desc = row.querySelector('.v-desc').value;
            const req = row.querySelector('.v-req').value;
            const avail = row.querySelector('.v-avail').value;
            const issue = row.querySelector('.v-issue').value;
            const rem = row.querySelector('.v-rem').value;

            const isYes = avail.toLowerCase() === "yes" ? "✔" : "";
            const isNo = avail.toLowerCase() === "no" ? "✔" : "";

            let newRow = `<tr>
                <td style="border: 1px solid black; padding: 4px; text-align: center;">${stock || '&nbsp;'}</td>
                <td style="border: 1px solid black; padding: 4px; text-align: center;">${unit || '&nbsp;'}</td>
                <td style="border: 1px solid black; padding: 4px; text-align: left;">${desc || '&nbsp;'}</td>
                <td style="border: 1px solid black; padding: 4px; text-align: center;">${req || '&nbsp;'}</td>
                <td style="border: 1px solid black; padding: 4px; text-align: center;">${isYes || '&nbsp;'}</td>
                <td style="border: 1px solid black; padding: 4px; text-align: center;">${isNo || '&nbsp;'}</td>
                <td style="border: 1px solid black; padding: 4px; text-align: center;">${issue || '&nbsp;'}</td>
                <td style="border: 1px solid black; padding: 4px; text-align: left;">${rem || '&nbsp;'}</td>
            </tr>`;
            printBody.innerHTML += newRow;
            rowsAdded++;
        });

        let minRows = 10; 
        for(let j=rowsAdded; j < minRows; j++) {
            let isLast = (j === minRows - 1);
            let borderStyle = isLast 
                ? "border-left: 1px solid black; border-right: 1px solid black; border-top: none; border-bottom: 1px solid black;" 
                : "border-left: 1px solid black; border-right: 1px solid black; border-top: none; border-bottom: none;";

            printBody.innerHTML += `<tr>
                <td style="${borderStyle} padding: 6px;">&nbsp;</td>
                <td style="${borderStyle} padding: 6px;">&nbsp;</td>
                <td style="${borderStyle} padding: 6px;">&nbsp;</td>
                <td style="${borderStyle} padding: 6px;">&nbsp;</td>
                <td style="${borderStyle} padding: 6px;">&nbsp;</td>
                <td style="${borderStyle} padding: 6px;">&nbsp;</td>
                <td style="${borderStyle} padding: 6px;">&nbsp;</td>
                <td style="${borderStyle} padding: 6px;">&nbsp;</td>
            </tr>`;
        }

        window.print();
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>