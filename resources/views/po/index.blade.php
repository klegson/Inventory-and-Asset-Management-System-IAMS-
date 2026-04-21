<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Orders - DepEd AMS</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .main-content { margin-left: 250px; padding: 30px; transition: all 0.3s; padding-top: 90px !important; }
        .header-title { font-weight: 700; color: #101954; }
        .table-card { background: #fff; border: 1px solid #e0e0e0; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .table thead th { background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; }
        @media (max-width: 768px) { .main-content { margin-left: 0; } }
        
        .swal2-styled.swal2-confirm { background-color: #0d6efd !important; color: #fff !important; }

        .clickable-row { cursor: pointer; transition: background-color 0.2s ease-in-out; }
        .clickable-row:hover td { background-color: #f1f5f9 !important; }

        .paper-scroll::-webkit-scrollbar { width: 8px; }
        .paper-scroll::-webkit-scrollbar-track { background: #343a40; border-radius: 8px; }
        .paper-scroll::-webkit-scrollbar-thumb { background: #adb5bd; border-radius: 8px; }

        .po-paper {
            background: white; width: 100%; max-width: 800px; margin: 0 auto; padding: 0.4in;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5); font-family: "Times New Roman", Times, serif; font-size: 11px; color: black; line-height: 1.2;
        }

        .po-paper table { width: 100%; border-collapse: collapse; border: 1px solid black; margin-bottom: -1px; }
        .po-paper td, .po-paper th { border: 1px solid black; padding: 4px 8px; vertical-align: top; }
        .po-paper .header-section { text-align: center; margin-bottom: 5px; }
        .po-paper .header-section h4 { margin: 0; font-weight: bold; font-size: 14px; }
        .po-paper .header-section h3 { margin: 5px 0; font-weight: bold; font-size: 18px; }
        .po-paper .entity-line { border-bottom: 1px solid black; display: inline-block; min-width: 300px; font-weight: bold; text-transform: uppercase; margin-bottom: 10px; font-size: 13px; }
        
        .po-paper .empty-row td { height: 22px; }
    </style>
</head>
<body>

    @include('layouts.header')
    @include('layouts.sidebar')

    <div class="main-content no-print">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h2 class="header-title mb-1"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Purchase Orders</h2>
                <p class="text-muted small mb-0">Manage and view official DepEd Purchase Orders</p>
            </div>
            <button class="btn btn-primary px-4 fw-bold" onclick="openCreateModal()">
                <i class="fas fa-plus me-2"></i> Create New P.O.
            </button>
        </div>

        <div class="table-card">
            
            <form action="{{ route('po.index') }}" method="GET" id="filterForm" class="d-flex justify-content-between align-items-center mb-3">
                <div class="input-group" style="width: 300px;">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" id="poSearchInput" class="form-control border-start-0 ps-0" placeholder="Search PO or Supplier..." value="{{ request('search') }}">
                </div>

                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                        <i class="fas fa-filter me-1"></i> Filter & Sort
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-3 shadow-lg border-0" style="width: 250px;">
                        
                        <h6 class="dropdown-header px-0 text-dark fw-bold"><i class="fas fa-tasks me-2"></i>Status</h6>
                        <select name="status_filter" class="form-select form-select-sm mb-3 cursor-pointer" onchange="document.getElementById('filterForm').submit();">
                            <option value="All" {{ request('status_filter') == 'All' ? 'selected' : '' }}>All Statuses</option>
                            <option value="Pending" {{ request('status_filter') == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Partial" {{ request('status_filter') == 'Partial' ? 'selected' : '' }}>Partial</option>
                            <option value="Complete" {{ request('status_filter') == 'Complete' ? 'selected' : '' }}>Complete</option>
                        </select>

                        <h6 class="dropdown-header px-0 text-dark fw-bold"><i class="fas fa-sort-amount-down me-2"></i>Sort By</h6>
                        <select name="sort" class="form-select form-select-sm mb-3 cursor-pointer" onchange="document.getElementById('filterForm').submit();">
                            <option value="date_desc" {{ request('sort', 'date_desc') == 'date_desc' ? 'selected' : '' }}>Date (Newest First)</option>
                            <option value="date_asc" {{ request('sort') == 'date_asc' ? 'selected' : '' }}>Date (Oldest First)</option>
                            <option value="supplier_asc" {{ request('sort') == 'supplier_asc' ? 'selected' : '' }}>Supplier Name (A-Z)</option>
                            <option value="supplier_desc" {{ request('sort') == 'supplier_desc' ? 'selected' : '' }}>Supplier Name (Z-A)</option>
                        </select>

                        <a href="{{ route('po.index') }}" class="btn btn-sm btn-light w-100 border text-danger fw-bold">Clear Filters</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>P.O. Number</th>
                            <th>Supplier</th>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseOrders as $po)
                        <tr class="clickable-row" data-id="{{ $po->id }}">
                            <td class="fw-bold text-primary">{{ $po->po_no }}</td>
                            <td>{{ $po->supplier_name }}</td>
                            <td>{{ \Carbon\Carbon::parse($po->po_date)->format('M d, Y') }}</td>
                            <td class="fw-bold">₱{{ number_format($po->total_amount, 2) }}</td>
                            <td class="text-center">
                                @php
                                    $statusClass = match($po->status) {
                                        'Complete' => 'bg-success text-success',
                                        'Partial' => 'bg-warning-subtle text-dark',
                                        default => 'bg-secondary text-secondary'
                                    };
                                @endphp
                                <span class="badge rounded-pill {{ $statusClass }} bg-opacity-10 border border-{{ explode(' ', $statusClass)[1] ?? 'secondary' }} px-3">
                                    {{ $po->status ?? 'Pending' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button class="btn btn-sm btn-light border text-primary" title="View & Print" onclick="viewPO({{ $po->id }})"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-light border text-success" title="Edit" onclick="editPO({{ $po->id }})"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-light border text-danger" title="Delete" onclick="deletePO({{ $po->id }})"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">No Purchase Orders match your filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade no-print" id="viewPoPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 bg-transparent">
                <div class="modal-header border-0 p-0 mb-2 justify-content-end">
                    <button type="button" class="btn-close btn-close-white me-2" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body paper-scroll p-4" style="background-color: #525659; border-radius: 8px;">
                    <div class="po-paper">
                        <div class="header-section">
                            <img src="{{ asset('assets/images/DepEdseal.png') }}" alt="Logo" style="width: 60px;" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/f/f3/Department_of_Education.svg'">
                            <div style="font-size: 12px;">Republic of the Philippines</div>
                            <h4>DEPARTMENT OF EDUCATION</h4>
                            <div style="font-size: 11px;">REGION V - BICOL</div>
                            <hr style="border: 0.5px solid black; margin: 5px 0;">
                            <h3>PURCHASE ORDER</h3>
                            <div class="entity-line" id="v-entity"></div>
                        </div>

                        <table>
                            <tr>
                                <td style="width: 55%;">Supplier: <span id="v-supplier" style="font-weight: bold;"></span><br>Address: <span id="v-address"></span><br>TIN: _______________________</td>
                                <td style="width: 45%;">PO No: <span id="v-pono" style="font-weight: bold;"></span><br>Date: <span id="v-date"></span><br>Mode of Procurement: <span id="v-mode"></span></td>
                            </tr>
                            <tr><td colspan="2" style="font-style: italic; font-size: 10px; padding: 2px 8px;">Gentlemen: Please furnish this Office the following articles subject to the terms and conditions contained herein:</td></tr>
                            <tr>
                                <td>Place of Delivery: <span id="v-place-delivery" class="fw-bold"></span><br>Date of Delivery: <span id="v-date-delivery" class="fw-bold"></span></td>
                                <td>Delivery Term: <span id="v-delivery-term" class="fw-bold"></span><br>Payment Term: <span id="v-payment-term" class="fw-bold"></span></td>
                            </tr>
                        </table>

                        <table>
                            <thead style="background-color: #e5e7eb; text-align: center; font-weight: bold;">
                                <tr><td style="width: 10%;">Stocks No.</td><td style="width: 10%;">Unit</td><td style="width: 45%;">Description</td><td style="width: 10%;">Quantity</td><td style="width: 10%;">Unit Cost</td><td style="width: 15%;">Amount</td></tr>
                            </thead>
                            <tbody id="v-items"></tbody>
                            <tr>
                                <td colspan="5" style="font-weight: bold;">(Total Amount in Words) <span id="v-words" style="text-transform: uppercase; margin-left: 10px;"></span></td>
                                <td id="v-total" style="text-align: center; font-weight: bold;">0.00</td>
                            </tr>
                        </table>
                        <div style="font-size: 9px; border: 1px solid black; border-top: none; padding: 4px 8px; font-style: italic;">In case of failure to make the full delivery within the time specified above, a penalty of one-tenth (1/10) of one percent for every day of delay shall be imposed on the undelivered item/s.</div>

                        <table style="width: 100%; border: 1px solid black; border-top: none; border-collapse: collapse;">
                            <tr>
                                <td style="width: 50%; padding: 10px; vertical-align: top; border: none;">
                                    <div style="width: 85%; margin: 0 auto;">
                                        <div style="text-align: left; margin-bottom: 30px;">Conforme:</div>
                                        <div style="border-bottom: 1px solid black; width: 100%;">&nbsp;</div>
                                        <div style="text-align: center; font-size: 10px;">Signature over Printed Name of Supplier</div>
                                        <div style="text-align: center; margin-top: 10px;">DATE: ___________</div>
                                    </div>
                                </td>
                                <td style="width: 50%; padding: 10px; vertical-align: top; border: none;">
                                    <div style="width: 85%; margin: 0 auto;">
                                        <div style="text-align: left; margin-bottom: 30px;">Very truly yours,</div>
                                        <div id="v-auth-name" style="border-bottom: 1px solid black; width: 100%; font-weight: bold; text-transform: uppercase; text-align: center;"></div>
                                        <div style="text-align: center; font-size: 10px;">Signature over Printed Name of Authorized Official</div>
                                        <div id="v-auth-designation" style="text-align: center; font-size: 10px; font-weight: bold;">REGIONAL DIRECTOR</div>
                                        <div style="text-align: center; font-size: 10px;">Designation</div>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <table>
                            <tr>
                                <td style="width: 55%;">
                                    Fund Cluster: _________________<br>Funds Available: _______________<br><br>
                                    <div id="v-acc-name" style="border-bottom: 1px solid black; width: 80%; margin: 0 auto; text-align: center; font-weight: bold; text-transform: uppercase;"></div>
                                    <div id="v-acc-designation" style="text-align: center; font-weight: bold; font-size: 10px;">ACCOUNTANT II</div>
                                    <div style="text-align: center; font-size: 9px;">Signature over Printed Name of Chief Accountant/Head of Accounting Division/Unit</div>
                                </td>
                                <td style="width: 45%;">ORS/BURS No: _______________<br>Date of the ORS/BURS: ________<br>Amount: _____________________</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary px-4 fw-bold shadow" onclick="triggerActualPrint()"><i class="fas fa-print me-2"></i> Print Document</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deletePoModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header bg-danger text-white"><h5 class="modal-title"><i class="fas fa-trash-alt me-2"></i> Confirm Delete</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><form id="deletePoForm" method="POST">@csrf @method('DELETE')<div class="modal-body text-center py-4"><p class="fs-5 mb-1">Are you sure you want to delete this Purchase Order?</p></div><div class="modal-footer justify-content-center"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete</button></div></form></div></div></div>

    @include('po.receive_modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentPoData = null;

        // Auto-search logic (Debounce)
        document.addEventListener("DOMContentLoaded", function() {
            const poSearchInput = document.getElementById('poSearchInput');
            const filterForm = document.getElementById('filterForm');
            let typingTimer;

            if(poSearchInput) {
                poSearchInput.addEventListener('input', function() {
                    clearTimeout(typingTimer);
                    typingTimer = setTimeout(() => {
                        filterForm.submit();
                    }, 600); // Waits 600ms after user stops typing to submit
                });

                // Keep cursor focused and at the end of the text after reload
                if (poSearchInput.value.length > 0) {
                    poSearchInput.focus();
                    const val = poSearchInput.value;
                    poSearchInput.value = '';
                    poSearchInput.value = val;
                }
            }
        });

        function numberToWords(num) {
            const a = ['','one ','two ','three ','four ','five ','six ','seven ','eight ','nine ','ten ','eleven ','twelve ','thirteen ','fourteen ','fifteen ','sixteen ','seventeen ','eighteen ','nineteen '];
            const b = ['', '', 'twenty','thirty','forty','fifty','sixty','seventy','eighty','ninety'];
            let n = ('000000000' + Math.floor(num)).slice(-9);
            let match = n.match(/^(\d{3})(\d{3})(\d{3})$/);
            if (!match) return ''; 
            const convert = (digits) => {
                let s = '';
                s += (digits[0] != 0) ? a[Number(digits[0])] + 'hundred ' : '';
                s += (digits.slice(1) != 0) ? (a[Number(digits.slice(1))] || b[digits[1]] + ' ' + a[digits[2]]) : '';
                return s;
            };
            let str = '';
            str += (match[1] != 0) ? convert(match[1]) + 'million ' : '';
            str += (match[2] != 0) ? convert(match[2]) + 'thousand ' : '';
            str += (match[3] != 0) ? convert(match[3]) : '';
            return str.trim().toUpperCase() || 'ZERO';
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.clickable-row').forEach(row => {
                row.addEventListener('click', function(e) {
                    if(e.target.closest('button') || e.target.closest('a')) { return; }
                    let id = this.getAttribute('data-id');
                    viewPO(id);
                });
            });
        });

        function parsePoItems(itemsData) {
            if (!itemsData) return [];
            let parsed = itemsData;
            for (let i = 0; i < 3; i++) {
                if (typeof parsed === 'string') {
                    try { parsed = JSON.parse(parsed); } catch(e) { break; }
                } else { break; }
            }
            return Array.isArray(parsed) ? parsed : [];
        }

// POPULATE THE VIEW MODAL
        function viewPO(id) {
            fetch(`/po/${id}`)
            .then(res => {
                if(!res.ok) throw new Error('Database Error. Make sure you ran the migration.');
                return res.json();
            })
            .then(data => {
                currentPoData = data;
                document.getElementById('v-entity').innerText = data.entity_name || "ENTITY NAME";
                document.getElementById('v-supplier').innerText = data.supplier_name;
                document.getElementById('v-address').innerText = data.supplier_address;
                document.getElementById('v-pono').innerText = data.po_no;
                document.getElementById('v-date').innerText = data.po_date;
                document.getElementById('v-mode').innerText = data.procurement_mode;
                
                // Signatories
                document.getElementById('v-auth-name').innerText = data.auth_official || "";
                document.getElementById('v-auth-designation').innerText = data.auth_official_designation || "REGIONAL DIRECTOR";
                document.getElementById('v-acc-name').innerText = data.chief_accountant || "";
                document.getElementById('v-acc-designation').innerText = data.chief_accountant_designation || "ACCOUNTANT II";
                
                // Fetch Delivery Details
                document.getElementById('v-place-delivery').innerText = data.place_of_delivery || "________________";
                document.getElementById('v-date-delivery').innerText = data.date_of_delivery || "________________";
                document.getElementById('v-delivery-term').innerText = data.delivery_term || "________________";
                document.getElementById('v-payment-term').innerText = data.payment_term || "________________";

                const vBody = document.getElementById('v-items');
                vBody.innerHTML = '';
                let total = 0;
                let itemsArray = parsePoItems(data.items);
                
                itemsArray.forEach((item, index) => {
                    let uCost = parseFloat(item.unit_cost !== undefined ? item.unit_cost : (item.cost || 0));
                    let q = parseFloat(item.qty || 0);
                    let sub = q * uCost;
                    total += sub;
                    
                    // Add a small visual indicator to the description if it arrived
                    let isDelivered = (item.is_delivered == 1 || item.is_delivered == true);
                    let checkBadge = isDelivered ? '<span class="badge bg-success float-end rounded-pill"><i class="fas fa-check"></i> Rcvd</span>' : '';

                    vBody.innerHTML += `<tr><td style="text-align:center">${String(index+1).padStart(3,'0')}</td><td style="text-align:center">${item.unit || ''}</td><td style="text-align:left">${item.description || ''} ${checkBadge}</td><td style="text-align:center">${q}</td><td style="text-align:right">${uCost.toLocaleString(undefined,{minimumFractionDigits:2})}</td><td style="text-align:right">${sub.toLocaleString(undefined,{minimumFractionDigits:2})}</td></tr>`;
                });

                for(let i=0; i < (8 - itemsArray.length); i++) vBody.innerHTML += `<tr class="empty-row"><td></td><td></td><td></td><td></td><td></td><td></td></tr>`;

                let finalTotal = total > 0 ? total : parseFloat(data.total_amount || 0);
                document.getElementById('v-total').innerText = finalTotal.toLocaleString(undefined,{minimumFractionDigits:2});
                document.getElementById('v-words').innerText = numberToWords(finalTotal) + " PESOS ONLY";

                new bootstrap.Modal(document.getElementById('viewPoPreviewModal')).show();
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Failed to load PO data. Please run your database migrations.', 'error');
            });
        }

        // TRIGGER THE ACTUAL PRINT
        function triggerActualPrint() {
            if(!currentPoData) return;
            const data = currentPoData;
            
            document.getElementById('p-entity').innerText = data.entity_name || "ENTITY NAME";
            document.getElementById('p-supplier').innerText = data.supplier_name;
            document.getElementById('p-address').innerText = data.supplier_address;
            document.getElementById('p-pono').innerText = data.po_no;
            document.getElementById('p-date').innerText = data.po_date;
            document.getElementById('p-mode').innerText = data.procurement_mode;
            
            // Signatories 
            document.getElementById('p-auth-name-display').innerText = data.auth_official || "";
            document.getElementById('p-auth-designation').innerText = data.auth_official_designation || "REGIONAL DIRECTOR";
            document.getElementById('p-acc-name-display').innerText = data.chief_accountant || "";
            document.getElementById('p-acc-designation').innerText = data.chief_accountant_designation || "ACCOUNTANT II";
            
            // Push to Delivery Details
            document.getElementById('p-place-delivery').innerText = data.place_of_delivery || "________________";
            document.getElementById('p-date-delivery').innerText = data.date_of_delivery || "________________";
            document.getElementById('p-delivery-term').innerText = data.delivery_term || "________________";
            document.getElementById('p-payment-term').innerText = data.payment_term || "________________";

            const pBody = document.getElementById('p-items');
            pBody.innerHTML = '';
            let total = 0;
            let itemsArray = parsePoItems(data.items);

            itemsArray.forEach((item, index) => {
                let uCost = parseFloat(item.unit_cost !== undefined ? item.unit_cost : (item.cost || 0));
                let q = parseFloat(item.qty || 0);
                let sub = q * uCost;
                total += sub;
                pBody.innerHTML += `<tr><td style="text-align:center">${String(index+1).padStart(3,'0')}</td><td style="text-align:center">${item.unit || ''}</td><td style="text-align:left">${item.description || ''}</td><td style="text-align:center">${q}</td><td style="text-align:right">${uCost.toLocaleString(undefined,{minimumFractionDigits:2})}</td><td style="text-align:right">${sub.toLocaleString(undefined,{minimumFractionDigits:2})}</td></tr>`;
            });

            for(let i=0; i < (8 - itemsArray.length); i++) pBody.innerHTML += `<tr class="empty-row"><td></td><td></td><td></td><td></td><td></td><td></td></tr>`;
            
            let finalTotal = total > 0 ? total : parseFloat(data.total_amount || 0);
            document.getElementById('p-total').innerText = finalTotal.toLocaleString(undefined,{minimumFractionDigits:2});
            document.getElementById('p-words').innerText = numberToWords(finalTotal) + " PESOS ONLY";
            
            window.print();
        }

        // TRIGGER EDIT MODAL
        function editPO(id) {
            resetModalState();
            document.querySelector('.modal-title').innerHTML = '<i class="fas fa-edit me-2"></i> Edit Purchase Order';
            fetch(`/po/${id}`).then(res => res.json()).then(data => {
                populateModalData(data);
                new bootstrap.Modal(document.getElementById('receivePoModal')).show();
            });
        }

        function deletePO(id) {
            document.getElementById('deletePoForm').action = `/po/${id}`;
            new bootstrap.Modal(document.getElementById('deletePoModal')).show();
        }

        function openCreateModal() {
            resetModalState();
            document.getElementById('poForm').reset();
            document.getElementById('itemsContainer').innerHTML = '';
            document.getElementById('modal_po_id').value = ''; 
            if (typeof addEmptyItemRow === "function") addEmptyItemRow();
            new bootstrap.Modal(document.getElementById('receivePoModal')).show();
        }

        function resetModalState() {
            document.querySelectorAll('#poForm input, #poForm select').forEach(input => input.disabled = false);
            document.getElementById('addItemBtn').style.display = 'inline-block';
            document.querySelector('button[form="poForm"]').style.display = 'inline-block'; 
            document.querySelector('.modal-title').innerHTML = '<i class="fas fa-file-invoice me-2"></i> Receive Purchase Order';
        }

        // POPULATE THE CREATE/EDIT FORM
        function populateModalData(data) {
            document.getElementById('modal_po_id').value = data.id;
            document.getElementById('in-entity').value = data.entity_name || '';
            document.getElementById('po_no').value = data.po_no || '';
            document.getElementById('in-supplier').value = data.supplier_name || '';
            document.getElementById('in-address').value = data.supplier_address || '';
            document.getElementById('in-date').value = data.po_date || '';
            document.getElementById('in-mode').value = data.procurement_mode || '';
            
            // Populate Signatories
            document.getElementById('in-auth-name').value = data.auth_official || '';
            document.getElementById('in-auth-designation').value = data.auth_official_designation || 'REGIONAL DIRECTOR';
            document.getElementById('in-acc-name').value = data.chief_accountant || '';
            document.getElementById('in-acc-designation').value = data.chief_accountant_designation || 'ACCOUNTANT II';
            
            // Populate Delivery Details Form
            document.getElementById('in-place-delivery').value = data.place_of_delivery || '';
            document.getElementById('in-date-delivery').value = data.date_of_delivery || '';
            document.getElementById('in-delivery-term').value = data.delivery_term || '';
            document.getElementById('in-payment-term').value = data.payment_term || '';
            
            if(document.getElementById('in-status')) document.getElementById('in-status').value = data.status || 'Pending';

            const container = document.getElementById('itemsContainer');
            container.innerHTML = '';
            let itemsArray = parsePoItems(data.items);
            if(itemsArray.length > 0) {
                itemsArray.forEach(item => {
                    let uCost = parseFloat(item.unit_cost !== undefined ? item.unit_cost : (item.cost || 0));
                    // Pass the checkbox status down
                    let isD = item.is_delivered == 1 || item.is_delivered == true;
                    
                    if (typeof addEmptyItemRow === "function") {
                        addEmptyItemRow({unit: item.unit || 'pc', desc: item.description || '', qty: item.qty || 0, cost: uCost, is_delivered: isD});
                    }
                });
            } else {
                if (typeof addEmptyItemRow === "function") addEmptyItemRow();
            }
        }
    </script>
</body>
</html>