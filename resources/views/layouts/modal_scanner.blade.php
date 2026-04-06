<style>
    .scanner-input:focus {
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
    }
    .log-scroll {
        height: 320px;
        overflow-y: auto;
    }
    .flash-success { background-color: #d1e7dd !important; transition: background-color 0.3s ease; }
    .flash-error { background-color: #f8d7da !important; transition: background-color 0.3s ease; }
</style>

<div class="modal fade" id="scannerModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 1000px;">
        <div class="modal-content shadow-lg border-0 rounded-3">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="scannerModalTitle"><i class="fas fa-barcode me-2"></i> Inventory Scanner (Stock IN)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="location.reload();"></button>
            </div>
            
            <div class="modal-body p-4 bg-light" id="scannerBody">
                <div class="row g-4">
                    <div class="col-5">
                        <div class="card shadow-sm border-0 mb-3 rounded-3">
                            <div class="card-body p-4">
                                
                                <label class="fw-bold text-muted small mb-2"><i class="fas fa-sort-numeric-up me-1"></i> QTY PER SCAN</label>
                                <input type="number" id="scan_qty" class="form-control form-control-lg text-center fw-bold mb-4 shadow-sm" value="1" min="1">

                                <div class="form-group text-center">
                                    <label class="fw-bold text-primary small mb-2">AWAITING BARCODE NUMBER</label>
                                    <input type="text" id="scanner_input" class="form-control form-control-lg text-center border-2 border-primary scanner-input fw-bold" placeholder="Barcode..." autocomplete="off">
                                    <small class="text-muted d-block mt-2"><i class="fas fa-keyboard"></i> Press Enter to submit</small>
                                </div>
                            </div>
                        </div>
                        <div id="scan_status" class="text-center fw-bold text-muted p-3 rounded bg-white shadow-sm border fs-5">Ready to Scan...</div>
                    </div>

                    <div class="col-7">
                        <div class="card shadow-sm border-0 h-100 rounded-3">
                            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                                <span class="fw-bold text-dark"><i class="fas fa-history text-primary me-2"></i>Recent Scans</span>
                                <button class="btn btn-sm btn-outline-secondary" onclick="$('#scanner_logs').html('<div class=\'text-center text-muted mt-5 pt-4\' id=\'empty-log-msg\'><i class=\'fas fa-clipboard-list fa-3x mb-3 opacity-25\'></i><p>No recent scans found.</p></div>')"><i class="fas fa-eraser me-1"></i> Clear</button>
                            </div>
                            <div class="card-body p-2 log-scroll">
                                <div id="scanner_logs" class="list-group list-group-flush">
                                    <div class="text-center text-muted mt-5 pt-4" id="empty-log-msg">
                                        <i class="fas fa-spinner fa-spin fa-2x mb-3 opacity-25"></i>
                                        <p>Loading recent scans...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white border-top-0">
                <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal" onclick="location.reload();">Close & Refresh Data</button>
            </div>
        </div>
    </div>
</div>

<audio id="audioSuccess" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>
<audio id="audioError" src="https://assets.mixkit.co/active_storage/sfx/2573/2573-preview.mp3" preload="auto"></audio>

<script>
    let isProcessing = false;
    let scanContext = 'all';

    // Global function to open scanner from anywhere
    window.openScanner = function(mode, context = 'all') {
        scanContext = context;
        let contextLabel = context === 'assets' ? ' (ASSETS)' : (context === 'supplies' ? ' (SUPPLIES)' : '');
        $('#scannerModalTitle').html('<i class="fas fa-barcode me-2"></i> Stock IN Scanner' + contextLabel);
        $('#scannerModal').modal('show');
    };

    $(document).ready(function() {
        
        // Ensure CSRF token is attached to all AJAX requests
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        $('#scannerModal').on('shown.bs.modal', function () {
            $('#scanner_input').focus();
            $('#scan_status').html('<span class="text-success"><i class="fas fa-plug"></i> SCANNER READY</span>');
            
            $.post('/barcodes/recent-scans', { context: scanContext }, function(data) {
                if(data && data.trim() !== '') {
                    $('#scanner_logs').html(data);
                } else {
                    $('#scanner_logs').html('<div class="text-center text-muted mt-5 pt-4" id="empty-log-msg"><i class="fas fa-clipboard-list fa-3x mb-3 opacity-25"></i><p>No recent scans found.</p></div>');
                }
            });
        });

        $('#scannerModal').on('click', function(e) {
            if (!$(e.target).closest('button, input, label').length && !isProcessing) { 
                $('#scanner_input').focus(); 
            }
        });

        $('#scanner_input').off('keydown').on('keydown', function(e) {
            if (e.which === 13 || e.key === 'Enter') { 
                e.preventDefault(); 
                processModalScan(); 
            }
        });
    });

    function processModalScan() {
        if (isProcessing) return;

        let barcode = $('#scanner_input').val().trim();
        let qty = $('#scan_qty').val();
        let mode = 'IN'; // HARDCODED: Only IN scans happen here now!

        if (barcode === "" || qty < 1) return;

        isProcessing = true;
        $('#scanner_input').prop('disabled', true);
        $('#scan_status').html('<span class="spinner-border spinner-border-sm text-primary"></span> Processing...');

        $.ajax({
            url: '/barcodes/scan',
            type: 'POST',
            data: { 
                barcode: barcode, 
                qty: qty, 
                mode: mode, 
                context: scanContext
            },
            dataType: 'json' 
        }).done(function(res) {
            if(res.status === 'success') {
                document.getElementById('audioSuccess').play().catch(e => console.log(e));
                
                $('#scannerBody').addClass('flash-success');
                setTimeout(() => $('#scannerBody').removeClass('flash-success'), 500);

                let color = 'success';
                
                let logItem = `
                    <div class="list-group-item d-flex justify-content-between align-items-center bg-${color} bg-opacity-10 border-start border-${color} border-4 mb-2 shadow-sm rounded">
                        <div>
                            <div class="fw-bold text-dark fs-5">${res.item_name}</div>
                            <small class="text-muted font-monospace"><i class="fas fa-barcode me-1"></i>${res.barcode}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-${color} px-3 py-2 shadow-sm fs-6">${res.mode} ${res.qty}</span>
                            <div class="small fw-bold mt-1 text-secondary">New Stock: ${res.new_stock}</div>
                        </div>
                    </div>`;
                
                $('#empty-log-msg').hide();
                $('#scanner_logs').prepend(logItem); 
                $('#scan_status').html('<span class="text-success fw-bold"><i class="fas fa-check-circle fa-lg"></i> SUCCESS: ' + res.item_name + '</span>');
                
            } else {
                document.getElementById('audioError').play().catch(e => console.log(e));
                $('#scannerBody').addClass('flash-error');
                setTimeout(() => $('#scannerBody').removeClass('flash-error'), 500);
                $('#scan_status').html('<span class="text-danger fw-bold"><i class="fas fa-times-circle fa-lg"></i> ' + res.message + '</span>');
            }
        }).fail(function(xhr) {
            document.getElementById('audioError').play().catch(e => console.log(e));
            $('#scannerBody').addClass('flash-error');
            setTimeout(() => $('#scannerBody').removeClass('flash-error'), 500);
            $('#scan_status').html('<span class="text-danger fw-bold"><i class="fas fa-bug"></i> Connection Error</span>');
        }).always(function() {
            isProcessing = false;
            $('#scanner_input').val('').prop('disabled', false).focus();
        });
    }
</script>