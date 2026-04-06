<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DepEd ROV - Requisition and Issue Slip</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        :root {
            --deped-blue: #1a237e;
            --deped-gold: #fbc02d;
            --light-bg: #f8f9fa;
            --border-color: #e0e0e0;
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
            border: none;
            padding: 25px; 
            margin-bottom: 25px; 
            background: white; 
            border-radius: 12px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            position: relative;
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
            border-radius: 8px; 
            border: 1px solid #ced4da;
            padding: 8px 12px;
            font-size: 0.9rem;
        }

        .btn-print { background-color: #607d8b; color: white; }
        .btn-submit { background-color: var(--deped-blue); color: white; font-weight: 600; }

        .sig-line {
            border: none;
            border-bottom: 2px solid #333;
            border-radius: 0;
            font-weight: bold;
            background: transparent;
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

        .item-row {
            position: relative;
            padding-top: 10px;
        }

        .btn-remove-row {
            color: #dc3545;
            cursor: pointer;
            font-size: 0.8rem;
            text-decoration: none;
            float: right;
            margin-top: -5px;
            padding-top: 10px;
        }

        .btn-remove-row:hover { text-decoration: underline; }

        /* Style tweaks for Select2 to match Bootstrap */
        .select2-container--bootstrap-5 .select2-selection--single {
            border-radius: 8px !important;
            min-height: 39px !important;
            padding: 4px 0px !important;
            border-color: #ced4da !important;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            padding-left: 12px !important;
            padding-top: 2px !important;
            color: #444 !important;
        }
        .select2-container--bootstrap-5 .select2-selection { box-shadow: none !important; }
        
        /* Select2 Dropdown List specific styling */
        .select2-results__option {
            padding: 8px 12px !important;
            border-bottom: 1px solid #f1f1f1;
        }

        /* =========================================
           PRINT UI (Strict override)
           ========================================= */
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
                font-size: 11pt;
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

    @include('layouts.user_sidebar')

<div class="main-content">
    <div class="top-bar">
        <div><i class="fa-solid fa-building-shield me-2"></i> <strong>ASSET MANAGEMENT/SUPPLY SECTION SYSTEM</strong></div>
        <div id="clock"><i class="fa-regular fa-clock me-2"></i> Loading time...</div>
    </div>

    <form action="{{ url('/user/ris') }}" id="requisitionForm" method="POST">
        @csrf
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold m-0" style="color: var(--deped-blue);">REQUISITION AND ISSUE SLIP</h3>
                <p class="text-muted small">RIS before Release!</p>
            </div>
            <div class="no-print">
                <button type="button" onclick="prepareAndPrint()" class="btn btn-print me-2 shadow-sm"><i class="fa-solid fa-print me-1"></i> Print PDF</button>
                <button type="submit" class="btn btn-submit shadow-sm" onclick="return confirm('Submit this request?')"><i class="fa-solid fa-paper-plane me-1"></i> Submit Request</button>
            </div>
        </div>

        <div class="section-box">
            <h6 class="section-title"><i class="fa-solid fa-circle-info"></i> General Information</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label>Entity Name</label>
                        <input type="text" name="entity_name" id="entity_name" class="form-control bg-light" value="Department of Education - ROV" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Office Name  <span class="text-danger">*</span></label>
                        <select name="office" id="officeSelect" class="form-select" onchange="updateUnits()" required>
                            <option value="">-- Select Office --</option>
                            <option value="Administrative Division">Administrative Division</option>
                            <option value="Curriculum and Learning Management Division">Curriculum and Learning Management Division</option>
                            <option value="Education Support Services Division">Education Support Services Division</option>
                            <option value="Field Technical Assistance Division">Field Technical Assistance Division</option>
                            <option value="Finance Division">Finance Division</option>
                            <option value="Human Resource Development Division">Human Resource Development Division</option>
                            <option value="Office of the Assistant Regional Director">Office of the Assistant Regional Director</option>
                            <option value="Office of the Regional Director">Office of the Regional Director</option>
                            <option value="Policy Planning and Research Division">Policy Planning and Research Division</option>
                            <option value="Quality Assurance Division">Quality Assurance Division</option>
                        </select>
                    </div>
                    <div>
                        <label>Unit / Section</label>
                        <select name="unit_section" id="unitSelect" class="form-select">
                            <option value="">-- Select Office First --</option>
                        </select>
                    </div>
                </div>
    
                <div class="col-md-6">
                    <div class="mb-3">
                        <label>Fund Cluster</label>
                        <input type="text" name="fund_cluster" id="fund_cluster" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Responsible Center Code</label>
                        <input type="text" name="center_code" id="center_code" class="form-control">
                    </div>
                    <div>
                        <label>RIS Number</label>
                        <input type="text" name="ris_no" id="ris_no" class="form-control fw-bold text-danger bg-light" value="{{ $risNumber }}" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-box requisition-block">
            <h6 class="section-title"><i class="fa-solid fa-list-check"></i> Requisition Details</h6>
            
            <div id="items-container">
                <div class="row g-3 mb-4 item-row border-bottom pb-3">
                    <div class="col-md-12 text-end">
                        <a href="javascript:void(0)" class="btn-remove-row no-print" onclick="removeRow(this)"><i class="fa-solid fa-trash-can"></i> Remove Item</a>
                    </div>
                    <div class="col-md-2">
                        <label>Stock No.</label>
                        <input type="text" name="stock_no[]" class="form-control bg-light stock-input" readonly placeholder="Auto-filled">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Unit Measure <span class="text-danger">*</span></label>
                        <select name="unit_measure[]" class="form-select" required>
                            <option value="" selected disabled>Select Unit</option>
                            <optgroup label="Individual Pieces">
                                <option value="Piece">Piece (pc)</option>
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
                    <div class="col-md-2">
                        <label>Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity[]" class="form-control" required>
                    </div>
                    <div class="col-md-5">
                        <label>Item Description <span class="text-danger">*</span></label>
                        <select name="description[]" class="form-select select2-supply" required>
                            <option value="" selected disabled>-- Select Supply Item --</option>
                            @foreach($supplies as $supply)
                                <option value="{{ $supply->article }}, {{ $supply->description }}" data-barcode="{{ $supply->barcode_id }}" data-qty="{{ $supply->quantity }}">{{ $supply->article }} - {{ $supply->description }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label>Remarks</label>
                        <input type="text" name="remarks[]" class="form-control">
                    </div>
                </div>
            </div>
            
            <div class="no-print mt-3">
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addItem()">
                    <i class="fa-solid fa-plus me-1"></i> Add Item Row
                </button>
            </div>
        </div>
        <div class="section-box purpose-block">
            <div class="col-md-13">
                <label>Purpose</label>
                <textarea name="purpose[]" class="form-control" rows="1" placeholder="Enter your purpose..."></textarea>
            </div>
        </div>

        <div class="section-box">
            <h6 class="section-title"><i class="fa-solid fa-file-signature"></i> Signatures</h6>
            <div class="row text-center g-4">
                <div class="col-md-3">
                    <label class="d-block mb-3 text-uppercase small text-muted">Requested By <span class="text-danger">*</span></label>
                    <input type="text" name="requested_by" id="req_by" class="form-control sig-line text-center" placeholder="Printed Name" required>
                    <input type="text" name="desig_requested" id="desig_req" class="form-control desig-input mt-2" placeholder="Enter Designation" required>
                </div>
                <div class="col-md-3">
                    <label class="d-block mb-3 text-uppercase small text-muted">Approved By</label>
                    <input type="text" name="approved_by" id="app_by" class="form-control sig-line text-center" value="JEFFREY B. PAGATPAT" readonly>
                    <input type="text" name="desig_approved" id="desig_app" class="form-control desig-input mt-2" value="Admin, Officer V (Supply Officer)" readonly>
                </div>
                <div class="col-md-3">
                    <label class="d-block mb-3 text-uppercase small text-muted">Issued By</label>
                    <input type="text" name="issued_by" id="iss_by" class="form-control sig-line text-center" value="ALDRIN RELLAMA" readonly>
                    <input type="text" name="desig_issued" id="desig_iss" class="form-control desig-input mt-2" value="AA-VI (Storekeeper II)" readonly>
                </div>
                <div class="col-md-3">
                    <label class="d-block mb-3 text-uppercase small text-muted">Received By <span class="text-danger">*</span></label>
                    <input type="text" name="received_by" id="rec_by" class="form-control sig-line text-center" placeholder="Printed Name" required>
                    <input type="text" name="desig_received" id="desig_rec" class="form-control desig-input mt-2" placeholder="Enter Designation" required>
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

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    // Custom template logic to add badges to Select2 items!
    function formatSupplyOption(state) {
        if (!state.id) { return state.text; }
        
        let qty = $(state.element).data('qty');
        let badgeHtml = '';
        
        if (qty !== undefined) {
            if (parseInt(qty) > 0) {
                badgeHtml = `<span class="badge bg-success ms-2 py-1" style="font-size:0.7rem;">Available</span>`;
            } else {
                badgeHtml = `<span class="badge bg-danger ms-2 py-1" style="font-size:0.7rem;">Out of Stock</span>`;
            }
        }
        
        return $(`<span>${state.text} ${badgeHtml}</span>`);
    }

    function initSelect2Fields() {
        $('.select2-supply').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: '-- Select Supply Item --',
            templateResult: formatSupplyOption, 
            templateSelection: formatSupplyOption, 
            escapeMarkup: function(m) { return m; } // Allows HTML badges to render safely
        });

        $('.select2-supply').on('select2:select', function (e) {
            const selectedOption = $(this).select2('data')[0].element; 
            const barcode = $(selectedOption).data('barcode'); 
            $(this).closest('.item-row').find('.stock-input').val(barcode || '');
        });
    }

    $(document).ready(function() {
        initSelect2Fields();
    });

    function updateClock() {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
        const clockEl = document.getElementById('clock');
        if(clockEl) clockEl.innerHTML = '<i class="fa-regular fa-calendar-check me-2"></i> ' + now.toLocaleDateString('en-US', options);
    }
    setInterval(updateClock, 1000);
    updateClock();

    const officeMapping = {
        "Administrative Division": ["Asset Management Section", "General Services Unit", "Payroll Services Unit", "Records Section", "Personnel Section", "Cash Section"],
        "Curriculum and Learning Management Division": ["Learning Resource Management Section"],
        "Education Support Services Division": ["Health and Nutrition", "Programs and Projects", "Facilities"],
        "Finance Division": ["Budget Section", "Accounting Section"],
        "Human Resource Development Division": ["NEAP"],
        "Office of the Regional Director": ["Procurement Unit", "Information and Communications Technology Unit", "Public Affairs Unit", "Legal Unit"]
    };

    function updateUnits() {
        const officeSelect = document.getElementById("officeSelect");
        const unitSelect = document.getElementById("unitSelect");
        const selectedOffice = officeSelect.value;
        unitSelect.innerHTML = '<option value="">-- Select Unit/Section --</option>';

        if (selectedOffice && officeMapping[selectedOffice]) {
            officeMapping[selectedOffice].forEach(unit => {
                const option = document.createElement("option");
                option.value = unit;
                option.textContent = unit;
                unitSelect.appendChild(option);
            });
        } else {
            unitSelect.innerHTML = '<option value="N/A">General Office Use</option>';
        }
    }

    function addItem() {
        const container = document.getElementById('items-container');
        const firstRow = container.querySelector('.item-row');
        
        const newRow = firstRow.cloneNode(true);
        
        newRow.querySelectorAll('.select2-container').forEach(el => el.remove());
        
        const selectElement = newRow.querySelector('.select2-supply');
        selectElement.classList.remove('select2-hidden-accessible');
        selectElement.removeAttribute('data-select2-id');
        selectElement.removeAttribute('aria-hidden');
        selectElement.removeAttribute('tabindex');
        
        newRow.querySelectorAll('input, textarea').forEach(input => input.value = '');
        newRow.querySelectorAll('select').forEach(select => {
            select.selectedIndex = 0;
            select.querySelectorAll('option').forEach(opt => opt.removeAttribute('data-select2-id'));
        });
        
        newRow.removeAttribute('data-select2-id');
        
        container.appendChild(newRow);
        
        initSelect2Fields();
    }

    function removeRow(link) {
        const container = document.getElementById('items-container');
        const rows = container.querySelectorAll('.item-row');
        if (rows.length > 1) {
            if (confirm("Are you sure you want to delete this item?")) {
                link.closest('.item-row').remove();
            }
        } else {
            alert("The form must have at least one item.");
        }
    }

    function formatDesignation(val) {
        if (!val) return '';
        return val.replace(' (', '<br>(');
    }

    function prepareAndPrint() {
        try {
            document.getElementById('p-entity').innerText = document.getElementById('entity_name')?.value || '';
            document.getElementById('p-office').innerText = document.getElementById('officeSelect')?.value || '';
            document.getElementById('p-division').innerText = document.getElementById('unitSelect')?.value || '';
            document.getElementById('p-fund').innerText = document.getElementById('fund_cluster')?.value || '';
            document.getElementById('p-center').innerText = document.getElementById('center_code')?.value || '';
            document.getElementById('p-ris').innerText = document.getElementById('ris_no')?.value || '';

            document.getElementById('p-req-name').innerText = document.getElementById('req_by')?.value || '';
            document.getElementById('p-app-name').innerText = document.getElementById('app_by')?.value || '';
            document.getElementById('p-iss-name').innerText = document.getElementById('iss_by')?.value || '';
            document.getElementById('p-rec-name').innerText = document.getElementById('rec_by')?.value || '';

            document.getElementById('p-req-des').innerHTML = formatDesignation(document.getElementById('desig_req')?.value);
            document.getElementById('p-app-des').innerHTML = formatDesignation(document.getElementById('desig_app')?.value);
            document.getElementById('p-iss-des').innerHTML = formatDesignation(document.getElementById('desig_iss')?.value);
            document.getElementById('p-rec-des').innerHTML = formatDesignation(document.getElementById('desig_rec')?.value);

            const today = new Date();
            const yyyy = today.getFullYear();
            let mm = today.getMonth() + 1; 
            let dd = today.getDate();
            if (dd < 10) dd = '0' + dd;
            if (mm < 10) mm = '0' + mm;
            const formattedToday = yyyy + '-' + mm + '-' + dd;
            
            document.getElementById('p-req-date').innerText = formattedToday;

            const printBody = document.getElementById('print-items-body');
            printBody.innerHTML = '';
            
            const rows = document.querySelectorAll('.item-row');
            let rowsAdded = 0;

            rows.forEach(row => {
                const stockInput = row.querySelector('[name="stock_no[]"]');
                const unitInput = row.querySelector('[name="unit_measure[]"]');
                const descInput = row.querySelector('[name="description[]"]');
                const qtyInput = row.querySelector('[name="quantity[]"]');
                const remarkInput = row.querySelector('[name="remarks[]"]');

                if (stockInput && unitInput && descInput && qtyInput && remarkInput) {
                    let tr = `<tr>
                        <td style="border: 1px solid black; padding: 5px; text-align: center;">${stockInput.value || ''}</td>
                        <td style="border: 1px solid black; padding: 5px; text-align: center;">${unitInput.value || ''}</td>
                        <td style="border: 1px solid black; padding: 5px; text-align: left;">${descInput.value || ''}</td>
                        <td style="border: 1px solid black; padding: 5px; text-align: center;">${qtyInput.value || ''}</td>
                        <td style="border: 1px solid black; padding: 5px; text-align: center;">&nbsp;</td> 
                        <td style="border: 1px solid black; padding: 5px; text-align: center;">&nbsp;</td> 
                        <td style="border: 1px solid black; padding: 5px; text-align: center;"></td>
                        <td style="border: 1px solid black; padding: 5px; text-align: left;">${remarkInput.value || ''}</td>
                    </tr>`;
                    printBody.innerHTML += tr;
                    rowsAdded++;
                }
            });

            for(let j=rowsAdded; j<15; j++) {
                printBody.innerHTML += `<tr>
                    <td style="border: 1px solid black; padding: 7px;">&nbsp;</td>
                    <td style="border: 1px solid black; padding: 7px;"></td>
                    <td style="border: 1px solid black; padding: 7px;"></td>
                    <td style="border: 1px solid black; padding: 7px;"></td>
                    <td style="border: 1px solid black; padding: 7px;"></td>
                    <td style="border: 1px solid black; padding: 7px;"></td>
                    <td style="border: 1px solid black; padding: 7px;"></td>
                    <td style="border: 1px solid black; padding: 7px;"></td>
                </tr>`;
            }

            const purposes = document.getElementsByName('purpose[]');
            let combinedPurpose = "";
            for(let p=0; p < purposes.length; p++) {
                if(purposes[p] && purposes[p].value) {
                    combinedPurpose += purposes[p].value + " ";
                }
            }
            document.getElementById('p-purpose').innerText = combinedPurpose.trim();

            window.print();
        } catch (err) {
            console.error('Print generation failed:', err);
            alert("Could not generate print layout. Please check console for details.");
        }
    }
</script>
</body>
</html>