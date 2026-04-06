<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View RIS - DepEd ROV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; font-family: 'Inter', sans-serif; color: #444; }
        .main-content { margin-left: 260px; padding: 20px; }
        .section-box { background: white; padding: 25px; margin-bottom: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .section-title { color: #1a237e; font-weight: 700; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #eee; }
        .table-custom th { background-color: #f8f9fa; font-size: 0.85rem; text-transform: uppercase; color: #555; }
        .info-label { font-size: 0.8rem; text-transform: uppercase; color: #888; font-weight: bold; margin-bottom: 2px; }
        .info-value { font-size: 1rem; font-weight: 600; color: #333; margin-bottom: 15px; }

        /* Dynamic Status Badge Colors */
        .status-pending { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .status-approved { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .status-declined { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
        .status-forwarded { background-color: #cff4fc; color: #055160; border: 1px solid #b6effb; }

        /* --- PRINT IMPROVEMENTS --- */
        #print-area { display: none; }

        @media print {
            @page { size: A4 portrait; margin: 10mm; }

            body { background: white !important; margin: 0; padding: 0; }
            body * { visibility: hidden; }
            .no-print, .sidebar, .main-content { display: none !important; margin: 0 !important; padding: 0 !important; }
            
            #print-area, #print-area * {
                visibility: visible;
            }

            #print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                color: black;
                font-family: 'Times New Roman', Times, serif;
                font-size: 10pt;
                display: block;
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
    </style>
</head>
<body>

    @include('layouts.user_sidebar')

<div class="main-content no-print">
    
    @php
        $badgeClass = 'status-pending';
        if ($req->status == 'Approved') {
            $badgeClass = 'status-approved';
        } elseif (in_array($req->status, ['Declined', 'Cancelled', 'Rejected'])) {
            $badgeClass = 'status-declined';
        } elseif ($req->status == 'Forwarded to Admin') {
            $badgeClass = 'status-forwarded';
        }
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ url('/user/ris/history') }}" class="text-decoration-none text-muted mb-2 d-inline-block"><i class="fas fa-arrow-left me-1"></i> Back to History</a>
            <h3 class="fw-bold m-0" style="color: #1a237e;">RIS Details: {{ $req->ris_no }}</h3>
            <span class="badge rounded-pill {{ $badgeClass }} mt-2 px-3 py-2">{{ $req->status }}</span>
        </div>
        
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-secondary fw-bold shadow-sm" onclick="window.print()">
                <i class="fa-solid fa-print me-1"></i> Print PDF
            </button>
            @if($req->status == 'Pending Staff Review' || strtolower($req->status) == 'declined' || strtolower($req->status) == 'rejected')
            <a href="{{ url('/user/ris/' . $req->id . '/edit') }}" class="btn btn-primary fw-bold shadow-sm">
                <i class="fa-solid fa-pen-to-square me-1"></i> Edit Request
            </a>
            @endif
        </div>
    </div>

    <div class="section-box">
        <h6 class="section-title"><i class="fa-solid fa-circle-info me-2 text-warning"></i>General Information</h6>
        <div class="row">
            <div class="col-md-4">
                <div class="info-label">Entity Name</div>
                <div class="info-value">{{ $req->entity_name }}</div>
            </div>
            <div class="col-md-4">
                <div class="info-label">Division</div>
                <div class="info-value">{{ $req->division }}</div>
            </div>
            <div class="col-md-4">
                <div class="info-label">Office</div>
                <div class="info-value">{{ $req->office }}</div>
            </div>
            <div class="col-md-4">
                <div class="info-label">Fund Cluster</div>
                <div class="info-value">{{ $req->fund_cluster ?: 'N/A' }}</div>
            </div>
            <div class="col-md-4">
                <div class="info-label">Responsibility Center Code</div>
                <div class="info-value">{{ $req->rcc ?: 'N/A' }}</div>
            </div>
            <div class="col-md-4">
                <div class="info-label">Purpose</div>
                <div class="info-value">{{ $req->purpose ?: 'N/A' }}</div>
            </div>
        </div>
    </div>

    <div class="section-box">
        <h6 class="section-title"><i class="fa-solid fa-list-check me-2 text-warning"></i>Requested Items</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-custom align-middle">
                <thead>
                    <tr>
                        <th style="width: 15%">Stock No.</th>
                        <th style="width: 10%">Unit</th>
                        <th style="width: 35%">Description</th>
                        <th style="width: 10%" class="text-center">Req Qty</th>
                        <th style="width: 10%" class="text-center text-success">Issued Qty</th>
                        <th style="width: 20%">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($req->items as $item)
                        <tr>
                            <td>{{ $item->stock_no }}</td>
                            <td>{{ $item->unit }}</td>
                            <td class="fw-bold">{{ $item->description }}</td>
                            <td class="text-center">{{ $item->req_quantity }}</td>
                            <td class="text-center fw-bold text-success">{{ $item->issue_quantity ?: '-' }}</td>
                            <td>{{ $item->remarks }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">No items found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

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
            <td style="width: 15%; white-space: nowrap; padding: 2px;">Entity Name:</td>
            <td style="width: 45%; border-bottom: 1px solid black; padding: 2px;">{{ $req->entity_name }}</td>
            <td style="width: 15%; text-align: right; padding-right: 10px; white-space: nowrap; padding: 2px;">Fund Cluster:</td>
            <td style="width: 25%; border-bottom: 1px solid black; padding: 2px;">{{ $req->fund_cluster }}</td>
        </tr>
        <tr>
            <td style="white-space: nowrap; padding: 2px;">Division:</td>
            <td style="border-bottom: 1px solid black; padding: 2px;">{{ $req->division }}</td>
            <td style="text-align: right; padding-right: 10px; white-space: nowrap; padding: 2px;">Responsibility Center Code:</td>
            <td style="border-bottom: 1px solid black; padding: 2px;">{{ $req->rcc }}</td>
        </tr>
        <tr>
            <td style="white-space: nowrap; padding: 2px;">Office:</td>
            <td style="border-bottom: 1px solid black; padding: 2px;">{{ $req->office }}</td>
            <td style="text-align: right; padding-right: 10px; white-space: nowrap; padding: 2px;">RIS No:</td>
            <td style="border-bottom: 1px solid black; font-weight: bold; padding: 2px;">{{ $req->ris_no }}</td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; font-family: 'Times New Roman', Times, serif; font-size: 10pt; border: 1px solid black; table-layout: fixed;">
        <colgroup>
            <col style="width: 10%;"> 
            <col style="width: 8%;">  
            <col style="width: 38%;"> 
            <col style="width: 8%;">  
            <col style="width: 5%;">  
            <col style="width: 5%;">  
            <col style="width: 8%;">  
            <col style="width: 18%;"> 
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
        <tbody>
            @php $rowsAdded = 0; @endphp
            @foreach($req->items as $item)
                @php
                    $isYes = strtolower($item->stock_avail) == 'yes' ? '✔' : '&nbsp;';
                    $isNo = strtolower($item->stock_avail) == 'no' ? '✔' : '&nbsp;';
                @endphp
                <tr>
                    <td style="border: 1px solid black; padding: 4px; text-align: center;">{{ $item->stock_no ?: '&nbsp;' }}</td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center;">{{ $item->unit ?: '&nbsp;' }}</td>
                    <td style="border: 1px solid black; padding: 4px; text-align: left;">{{ $item->description ?: '&nbsp;' }}</td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center;">{{ $item->req_quantity ?: '&nbsp;' }}</td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center;">{!! $isYes !!}</td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center;">{!! $isNo !!}</td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center;">{{ $item->issue_quantity ?: '&nbsp;' }}</td>
                    <td style="border: 1px solid black; padding: 4px; text-align: left;">{{ $item->remarks ?: '&nbsp;' }}</td>
                </tr>
                @php $rowsAdded++; @endphp
            @endforeach

            @for($j = $rowsAdded; $j < 10; $j++)
                @php
                    $isLast = ($j === 9);
                    $borderStyle = $isLast 
                        ? "border-left: 1px solid black; border-right: 1px solid black; border-top: none; border-bottom: 1px solid black;" 
                        : "border-left: 1px solid black; border-right: 1px solid black; border-top: none; border-bottom: none;";
                @endphp
                <tr>
                    <td style="border: 1px solid black; padding: 7px;">&nbsp;</td>
                    <td style="border: 1px solid black; padding: 7px;"></td>
                    <td style="border: 1px solid black; padding: 7px;"></td>
                    <td style="border: 1px solid black; padding: 7px;"></td>
                    <td style="border: 1px solid black; padding: 7px;"></td>
                    <td style="border: 1px solid black; padding: 7px;"></td>
                    <td style="border: 1px solid black; padding: 7px;"></td>
                    <td style="border: 1px solid black; padding: 7px;"></td>
                </tr>
            @endfor
            <tr>
                <td colspan="8" style="border: 1px solid black; padding: 3px; text-align: left;">
                    <b>Purpose:</b> {{ $req->purpose }}
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
                <td style="border: 1px solid black; padding: 3px; text-align: center;"><b>{{ $req->sig_requested_by }}</b></td>
                <td style="border: 1px solid black; padding: 3px; text-align: center;"><b>{{ $req->sig_approved_by }}</b></td>
                <td style="border: 1px solid black; padding: 3px; text-align: center;"><b>{{ $req->sig_issued_by }}</b></td>
                <td style="border: 1px solid black; padding: 3px; text-align: center;"><b>{{ $req->sig_received_by }}</b></td>
            </tr>
            <tr>
                <td style="border: 1px solid black; padding: 3px; text-align: left;">Designation</td>
                <td style="border: 1px solid black; padding: 3px; text-align: center;">{!! str_replace(' (', '<br>(', $req->desig_requested) !!}</td>
                <td style="border: 1px solid black; padding: 3px; text-align: center;">{!! str_replace(' (', '<br>(', $req->desig_approved) !!}</td>
                <td style="border: 1px solid black; padding: 3px; text-align: center;">{!! str_replace(' (', '<br>(', $req->desig_issued) !!}</td>
                <td style="border: 1px solid black; padding: 3px; text-align: center;">{!! str_replace(' (', '<br>(', $req->desig_received) !!}</td>
            </tr>
            <tr>
                <td style="border: 1px solid black; padding: 3px; text-align: left;">Date</td>
                <td style="border: 1px solid black; padding: 3px; text-align: center;">{{ $req->date_requested }}</td>
                <td style="border: 1px solid black; padding: 3px; text-align: center;">{{ $req->date_approved }}</td>
                <td style="border: 1px solid black; padding: 3px; text-align: center;">{{ $req->date_issued }}</td>
                <td style="border: 1px solid black; padding: 3px; text-align: center;">{{ $req->date_received }}</td>
            </tr>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>