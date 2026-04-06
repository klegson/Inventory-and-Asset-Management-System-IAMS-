<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICS Form - DepEd ROV</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

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
            overflow-x: hidden;
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
            display: block;
        }

        .form-control, .form-select { 
            border-radius: 8px; 
            border: 1px solid #ced4da;
            padding: 8px 12px;
            font-size: 0.9rem;
        }

        .form-control[readonly] {
            background-color: var(--light-bg);
        }

        .btn-print { background-color: #607d8b; color: white; }
        .btn-submit { background-color: var(--deped-blue); color: white; font-weight: 600; }

        .sig-line {
            border: none;
            border-bottom: 2px solid #333;
            border-radius: 0;
            font-weight: bold;
            background: transparent;
            text-align: center;
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
            display: block;
        }

        .item-row {
            position: relative;
            padding-top: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .btn-remove-row {
            color: #dc3545;
            cursor: pointer;
            font-size: 0.8rem;
            text-decoration: none;
            float: right;
            margin-top: -5px;
        }

        .btn-remove-row:hover { text-decoration: underline; }

        /* Custom Checkbox Group for Category */
        .category-group {
            display: flex;
            gap: 20px;
            margin-top: 5px;
            padding: 8px 12px;
            border-radius: 8px;
            background: var(--light-bg);
            border: 1px solid #eee;
        }

        .custom-radio {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-weight: 500;
            color: #444;
            font-size: 0.9rem;
            margin-bottom: 0;
        }

        .custom-radio input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--deped-gold);
            cursor: pointer;
        }

        /* --- PRINT IMPROVEMENTS --- */
        #print-area { display: none; }

        @media print {
            @page { size: A4 portrait; margin: 10mm; }

            body { background: white !important; margin: 0; padding: 0; }
            body * { visibility: hidden; }
            .no-print, .sidebar, .top-bar, .main-content, .btn, .btn-remove-row { display: none !important; }
            
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
                font-size: 10.5pt; /* Slightly reduced base font to save space */
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

        @media (max-width: 992px) { .main-content { margin-left: 0; } }
    </style>
</head>
<body>

    @include('layouts.user_sidebar')

    <div class="main-content">
        
        <div class="top-bar">
            <div><i class="fa-solid fa-building-shield me-2"></i> <strong>DEPED REGION V - ASSET SYSTEM</strong></div>
            <div id="clock-display"><i class="fa-regular fa-clock me-2"></i> Loading time...</div>
        </div>

        <form action="{{ url('/user/ics') }}" method="POST">
            @csrf
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold m-0" style="color: var(--deped-blue);">INVENTORY CUSTODIAN SLIP</h3>
                    <p class="text-muted small">Appendix 63 - Government Accounting Manual</p>
                </div>
                <div class="no-print">
                    <button type="button" class="btn btn-print me-2 shadow-sm" onclick="prepareAndPrint()">
                        <i class="fa-solid fa-print me-1"></i> Print PDF
                    </button>
                    <button type="submit" class="btn btn-submit shadow-sm" onclick="return confirm('Submit this ICS request?')">
                        <i class="fa-solid fa-paper-plane me-1"></i> Submit Request
                    </button>
                </div>
            </div>

            <div class="section-box">
                <h6 class="section-title"><i class="fa-solid fa-circle-info"></i> General Information</h6>
                
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label>Entity Name</label>
                        <input type="text" class="form-control" value="Department of Education - ROV" readonly>
                    </div>
                    <div class="col-md-6">
                        <label>Fund Cluster</label>
                        <input type="text" name="fund_cluster" id="fund_cluster" class="form-control" placeholder="e.g. 01">
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Category</label>
                        <div class="category-group">
                            <label class="custom-radio">
                                <input type="checkbox" name="item_category" value="PPE" onclick="selectOnlyThis(this)"> PPE
                            </label>
                            <label class="custom-radio">
                                <input type="checkbox" name="item_category" value="High - Valued" onclick="selectOnlyThis(this)"> High - Valued
                            </label>
                            <label class="custom-radio">
                                <input type="checkbox" name="item_category" value="Low - Valued" onclick="selectOnlyThis(this)"> Low - Valued
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label>ICS Number</label>
                        <input type="text" name="ics_no" id="ics_no" class="form-control fw-bold text-danger" value="{{ $icsNumber ?? 'ICS-2026-0001' }}" readonly>
                    </div>
                </div>
            </div>

            <div class="section-box requisition-block">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                    <h6 class="section-title mb-0 border-0 pb-0"><i class="fa-solid fa-list-check"></i> Item Details</h6>
                </div>

                <div id="items-container">
                    <div class="item-row">
                        <a href="javascript:void(0)" class="btn-remove-row no-print" onclick="removeRow(this)"><i class="fa-solid fa-trash-can"></i> Remove Item</a>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-2">
                                <label>Quantity</label>
                                <input type="number" name="qty[]" class="form-control" placeholder="0">
                            </div>
                            <div class="col-md-2">
                                <label>Unit</label>
                                <select name="unit[]" class="form-select">
                                    <option value="">- Select -</option>
                                    <option value="pcs">pcs</option>
                                    <option value="boxes">boxes</option>
                                    <option value="kg">kg</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label>Item Description</label>
                                <input type="text" name="desc[]" class="form-control" placeholder="Complete name of the item...">
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label>Inventory Item No.</label>
                                <input type="text" name="inv_no[]" class="form-control" placeholder="Enter Item No.">
                            </div>
                            <div class="col-md-4">
                                <label>Estimated Useful Life</label>
                                <input type="text" name="est_life[]" class="form-control" placeholder="e.g. 5 Years">
                            </div>
                            <div class="col-md-2">
                                <label>Unit Cost</label>
                                <input type="number" step="0.01" name="unit_cost[]" class="form-control" placeholder="0.00">
                            </div>
                            <div class="col-md-2">
                                <label>Total Cost</label>
                                <input type="number" step="0.01" name="total_cost[]" class="form-control" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="no-print mt-3">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addMoreItem()">
                        <i class="fa-solid fa-plus me-1"></i> Add Another Item
                    </button>
                </div>
            </div>

            <div class="section-box">
                <h6 class="section-title"><i class="fa-solid fa-file-signature"></i> Signatures</h6>

                <div class="row text-center g-4 mb-4 mt-2">
                    <div class="col-md-6 border-end">
                        <label class="d-block mb-3 text-uppercase small text-muted">Received From</label>
                        <input type="text" name="sig_from_name" id="sig_from_name" class="form-control sig-line" placeholder="Printed Name">
                        <input type="text" name="sig_from_pos" id="sig_from_pos" class="form-control desig-input mt-2 mb-3" placeholder="Position / Title">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="d-block mb-3 text-uppercase small text-muted">Received By</label>
                        <input type="text" name="sig_by_name" id="sig_by_name" class="form-control sig-line" placeholder="Printed Name">
                        <input type="text" name="sig_by_pos" id="sig_by_pos" class="form-control desig-input mt-2 mb-3" placeholder="Position / Title">
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div id="print-area">
        <div style="text-align: center; font-family: 'Times New Roman', Times, serif; margin-bottom: 15px;">
            <img src="{{ asset('assets/images/DepEdseal.png') }}" style="width: 70px; margin: 0 auto 5px auto; display: block;">
            <div style="font-size: 10pt;">Republic of the Philippines</div>
            <div style="font-size: 20pt; font-family: 'Old English Text MT', 'Engravers Old English', serif; line-height: 1;">Department of Education</div>
            <div style="font-size: 11pt;">Region V - Bicol</div>
            <div style="font-size: 13pt; font-weight: bold; margin-top: 10px;">INVENTORY CUSTODIAN SLIP</div>
            <div id="p_category_value" style="font-size: 12pt; color: #777; font-weight: bold; margin-top: -2px; min-height: 20px;">Value</div>
        </div>

        <table style="width: 100%; border: none; font-family: 'Times New Roman', Times, serif; font-size: 11pt; margin-bottom: 5px;">
            <tr>
                <td style="width: 12%; white-space: nowrap;">Fund Cluster:</td>
                <td style="width: 43%; border-bottom: 1px solid black;" id="p_fund_cluster"></td>
                <td style="width: 10%; text-align: right; padding-right: 10px; white-space: nowrap;">ICS No.</td>
                <td style="width: 35%; border-bottom: 1px solid black; font-weight: bold;" id="p_ics_no"></td>
            </tr>
        </table>

        <table style="width: 100%; border-collapse: collapse; font-family: 'Times New Roman', Times, serif; border: 1px solid black;">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 8%; border: 1px solid black; padding: 4px; font-weight: bold; text-align: center;">Quantity</th>
                    <th rowspan="2" style="width: 8%; border: 1px solid black; padding: 4px; font-weight: bold; text-align: center;">Unit</th>
                    <th colspan="2" style="width: 20%; border: 1px solid black; padding: 4px; font-weight: bold; text-align: center;">Amount</th>
                    <th rowspan="2" style="width: 34%; border: 1px solid black; padding: 4px; font-weight: bold; text-align: center;">Description</th>
                    <th rowspan="2" style="width: 15%; border: 1px solid black; padding: 4px; font-weight: bold; text-align: center;">Inventory<br>Item Nos.</th>
                    <th rowspan="2" style="width: 15%; border: 1px solid black; padding: 4px; font-weight: bold; text-align: center;">Estimated<br>Useful Life</th>
                </tr>
                <tr>
                    <th style="border: 1px solid black; padding: 4px; font-weight: bold; text-align: center;">Unit Cost</th>
                    <th style="border: 1px solid black; padding: 4px; font-weight: bold; text-align: center;">Total Cost</th>
                </tr>
            </thead>
            
            <tbody id="p_items_body">
                </tbody>
            
            <tbody>
                <tr>
                    <td colspan="4" style="border: 1px solid black; padding: 5px; text-align: left; font-weight: bold;">Received from:</td>
                    <td colspan="3" style="border: 1px solid black; padding: 5px; text-align: left; font-weight: bold;">Received by:</td>
                </tr>
                <tr>
                    <td colspan="4" style="border: 1px solid black; border-bottom: none; padding: 15px 5px 2px 5px; text-align: center;">
                        <span id="p_sig_from_name" style="display:inline-block; width: 85%; border-bottom: 1px solid black; min-height: 15px; font-weight: bold; text-transform: uppercase;"></span><br>
                        <span style="font-size: 9pt;">Signature over Printed Name</span>
                    </td>
                    <td colspan="3" style="border: 1px solid black; border-bottom: none; padding: 15px 5px 2px 5px; text-align: center;">
                        <span id="p_sig_by_name" style="display:inline-block; width: 85%; border-bottom: 1px solid black; min-height: 15px; font-weight: bold; text-transform: uppercase;"></span><br>
                        <span style="font-size: 9pt;">Signature over Printed Name</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="border: 1px solid black; border-top: none; border-bottom: none; padding: 8px 5px 2px 5px; text-align: center;">
                        <span id="p_sig_from_pos" style="display:inline-block; width: 85%; border-bottom: 1px solid black; min-height: 15px;"></span><br>
                        <span style="font-size: 9pt;">Position/Title</span>
                    </td>
                    <td colspan="3" style="border: 1px solid black; border-top: none; border-bottom: none; padding: 8px 5px 2px 5px; text-align: center;">
                        <span id="p_sig_by_pos" style="display:inline-block; width: 85%; border-bottom: 1px solid black; min-height: 15px;"></span><br>
                        <span style="font-size: 9pt;">Position/Title</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="border: 1px solid black; border-top: none; padding: 8px 5px 8px 5px; text-align: center;">
                        <span style="display:inline-block; width: 85%; border-bottom: 1px solid black; min-height: 15px;"></span><br>
                        <span style="font-size: 9pt;">Date</span>
                    </td>
                    <td colspan="3" style="border: 1px solid black; border-top: none; padding: 8px 5px 8px 5px; text-align: center;">
                        <span style="display:inline-block; width: 85%; border-bottom: 1px solid black; min-height: 15px;"></span><br>
                        <span style="font-size: 9pt;">Date</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set Header Date
        function updateDate() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
            const clockEl = document.getElementById('clock-display');
            if(clockEl) clockEl.innerHTML = '<i class="fa-regular fa-calendar-check me-2"></i> ' + now.toLocaleDateString('en-US', options);
        }
        setInterval(updateDate, 1000);
        updateDate();

        // Add Item Row
        function addMoreItem() {
            const container = document.getElementById('items-container');
            const rows = container.querySelectorAll('.item-row');
            const firstRow = rows[0];
            const newRow = firstRow.cloneNode(true);
            newRow.querySelectorAll('input').forEach(input => input.value = '');
            newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
            container.appendChild(newRow);
        }

        // Remove Row
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

        // Category Selection Rule
        let selectedCategoryValue = 'Value';
        function selectOnlyThis(clickedCheckbox) {
            let checkboxes = document.getElementsByName('item_category');
            checkboxes.forEach((item) => {
                if (item !== clickedCheckbox) item.checked = false;
            });
            selectedCategoryValue = clickedCheckbox.checked ? clickedCheckbox.value : 'Value';
        }

        // Print Mapping
        function prepareAndPrint() {
            document.getElementById('p_fund_cluster').innerText = document.getElementById('fund_cluster')?.value || '';
            document.getElementById('p_ics_no').innerText = document.getElementById('ics_no')?.value || '';
            document.getElementById('p_category_value').innerText = selectedCategoryValue;

            document.getElementById('p_sig_from_name').innerText = document.getElementById('sig_from_name')?.value || '';
            document.getElementById('p_sig_by_name').innerText = document.getElementById('sig_by_name')?.value || '';
            document.getElementById('p_sig_from_pos').innerText = document.getElementById('sig_from_pos')?.value || '';
            document.getElementById('p_sig_by_pos').innerText = document.getElementById('sig_by_pos')?.value || '';

            let tbody = document.getElementById('p_items_body');
            tbody.innerHTML = ''; 

            let qtys = document.getElementsByName('qty[]');
            let units = document.getElementsByName('unit[]');
            let uCosts = document.getElementsByName('unit_cost[]');
            let tCosts = document.getElementsByName('total_cost[]');
            let descs = document.getElementsByName('desc[]');
            let invs = document.getElementsByName('inv_no[]');
            let ests = document.getElementsByName('est_life[]');

            let rowsAdded = 0;
            for(let i = 0; i < qtys.length; i++) {
                if(qtys[i].value || units[i].value || descs[i].value) {
                    tbody.innerHTML += `
                        <tr>
                            <td style="border: 1px solid black; padding: 4px; text-align: center;">${qtys[i].value}</td>
                            <td style="border: 1px solid black; padding: 4px; text-align: center;">${units[i].value}</td>
                            <td style="border: 1px solid black; padding: 4px; text-align: center;">${uCosts[i].value}</td>
                            <td style="border: 1px solid black; padding: 4px; text-align: center;">${tCosts[i].value}</td>
                            <td style="border: 1px solid black; padding: 4px; text-align: left;">${descs[i].value}</td>
                            <td style="border: 1px solid black; padding: 4px; text-align: center;">${invs[i].value}</td>
                            <td style="border: 1px solid black; padding: 4px; text-align: center;">${ests[i].value}</td>
                        </tr>
                    `;
                    rowsAdded++;
                }
            }

            let minRows = 15; 
            for(let i = rowsAdded; i < minRows; i++) {
                let isLast = (i === minRows - 1);
                let descText = isLast ? "<div style='margin-top: 10px; font-weight: bold;'>Construct No.</div>" : "";
                let borderStyle = isLast 
                    ? "border-left: 1px solid black; border-right: 1px solid black; border-top: none; border-bottom: 1px solid black;" 
                    : "border-left: 1px solid black; border-right: 1px solid black; border-top: none; border-bottom: none;";

                tbody.innerHTML += `
                    <tr>
                        <td style="${borderStyle} padding: 6px;">&nbsp;</td>
                        <td style="${borderStyle} padding: 6px;"></td>
                        <td style="${borderStyle} padding: 6px;"></td>
                        <td style="${borderStyle} padding: 6px;"></td>
                        <td style="${borderStyle} padding: 6px; text-align: left; vertical-align: bottom;">${descText}</td>
                        <td style="${borderStyle} padding: 6px;"></td>
                        <td style="${borderStyle} padding: 6px;"></td>
                    </tr>
                `;
            }
            
            window.print();
        }
    </script>
</body>
</html>