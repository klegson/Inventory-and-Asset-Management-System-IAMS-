<div class="modal-header border-0 py-3" style="background-color: #101954; color: white; border-radius: 10px 10px 0 0;">
    <h5 class="modal-title fw-bold mb-0"><i class="fas fa-file-signature me-2"></i> Final Admin Review</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<form action="{{ url('/admin/requests/'.$req->id.'/update') }}" method="POST">
    @csrf
    <div class="modal-body p-4 bg-light">
        
        <div class="bg-white p-4 shadow-sm border mb-4" style="font-family: 'Times New Roman', Times, serif; font-size: 10pt; color: black; overflow-x: auto;">
            
            <div style="text-align: center; margin-bottom: 15px;">
                <img src="{{ asset('assets/images/DepEdseal.png') }}" style="width: 60px; margin: 0 auto 2px auto; display: block;">
                <div style="font-size: 9pt;">Republic of the Philippines</div>
                <div style="font-size: 18pt; font-family: 'Old English Text MT', 'Engravers Old English', serif; line-height: 1;">Department of Education</div>
                <div style="font-size: 10pt;">Region V - Bicol</div>
                <div style="font-size: 12pt; font-weight: bold; margin-top: 5px;">REQUISITION AND ISSUE SLIP</div>
            </div>

            <table style="width: 100%; border: none; margin-bottom: 5px; min-width: 700px;">
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
                    <td style="border-bottom: 1px solid black; font-weight: bold; padding: 2px; color: #dc3545;">{{ $req->ris_no }}</td>
                </tr>
            </table>

            <table style="width: 100%; border-collapse: collapse; border: 1px solid black; table-layout: fixed; min-width: 700px;">
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
                    @php 
                        $rowsAdded = 0; 
                        $itemsCount = $req->items ? $req->items->count() : 0;
                    @endphp
                    
                    @if($itemsCount > 0)
                        @foreach($req->items as $item)
                            @php
                                $isYes = strtolower($item->stock_avail) == 'yes' ? '✔' : '&nbsp;';
                                $isNo = strtolower($item->stock_avail) == 'no' ? '✔' : '&nbsp;';
                            @endphp
                            <tr>
                                <td style="border: 1px solid black; padding: 4px; text-align: center;">{{ $item->stock_no ?: '' }}</td>
                                <td style="border: 1px solid black; padding: 4px; text-align: center;">{{ $item->unit ?: '' }}</td>
                                <td style="border: 1px solid black; padding: 4px; text-align: left;">{{ $item->description ?: '' }}</td>
                                <td style="border: 1px solid black; padding: 4px; text-align: center;">{{ $item->req_quantity ?: '' }}</td>
                                <td style="border: 1px solid black; padding: 4px; text-align: center;">{!! $isYes !!}</td>
                                <td style="border: 1px solid black; padding: 4px; text-align: center;">{!! $isNo !!}</td>
                                <td style="border: 1px solid black; padding: 4px; text-align: center; color: #198754; font-weight: bold; background-color: #e8f5e9;">{{ $item->issue_quantity ?: '' }}</td>
                                <td style="border: 1px solid black; padding: 4px; text-align: left;">{{ $item->remarks ?: '' }}</td>
                            </tr>
                            @php $rowsAdded++; @endphp
                        @endforeach
                    @endif

                    @for($j = $rowsAdded; $j < 10; $j++)
                        @php
                            $isLast = ($j === 9);
                            $borderStyle = $isLast 
                                ? "border-left: 1px solid black; border-right: 1px solid black; border-top: none; border-bottom: 1px solid black;" 
                                : "border-left: 1px solid black; border-right: 1px solid black; border-top: none; border-bottom: none;";
                        @endphp
                        <tr>
                            <td style="{!! $borderStyle !!} padding: 6px;">&nbsp;</td>
                            <td style="{!! $borderStyle !!} padding: 6px;">&nbsp;</td>
                            <td style="{!! $borderStyle !!} padding: 6px;">&nbsp;</td>
                            <td style="{!! $borderStyle !!} padding: 6px;">&nbsp;</td>
                            <td style="{!! $borderStyle !!} padding: 6px;">&nbsp;</td>
                            <td style="{!! $borderStyle !!} padding: 6px;">&nbsp;</td>
                            <td style="{!! $borderStyle !!} padding: 6px;">&nbsp;</td>
                            <td style="{!! $borderStyle !!} padding: 6px;">&nbsp;</td>
                        </tr>
                    @endfor
                    <tr>
                        <td colspan="8" style="border: 1px solid black; padding: 5px; text-align: left;">
                            <b>Purpose:</b> {{ $req->purpose }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <table style="width: 100%; border-collapse: collapse; border: 1px solid black; border-top: none; table-layout: fixed; min-width: 700px;">
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
                        <td style="border: 1px solid black; padding: 3px; text-align: center;"></td>
                        <td style="border: 1px solid black; padding: 3px; text-align: center;"></td>
                        <td style="border: 1px solid black; padding: 3px; text-align: center;"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if(in_array($req->status, ['Rejected', 'Declined', 'Cancelled']))
            <div class="alert alert-danger mb-0 border-0 shadow-sm">
                <i class="fas fa-times-circle me-2"></i> This RIS has been permanently rejected or cancelled. No further actions can be taken.
            </div>
        @elseif($req->status == 'Approved')
            <div class="alert alert-success mb-3 border-0 shadow-sm">
                <i class="fas fa-check-circle me-2"></i> This RIS is Approved. Master inventory stocks have been deducted.
            </div>
            <div class="text-center border-top pt-3 border-2 border-primary mt-2">
                <button type="submit" name="new_status" value="Pending Staff Review" class="btn btn-warning fw-bold px-4 shadow-sm" onclick="return confirm('Are you sure you want to reopen this RIS? This will automatically RESTORE the deducted stocks back to the inventory.')">
                    <i class="fas fa-undo me-2"></i> Revoke Approval & Return to Staff
                </button>
            </div>
        @else
            <div class="row g-3 border-top pt-3 border-2 border-primary mt-2">
                <div class="col-12 mt-3">
                    <label class="form-label fw-bold text-primary"><i class="fas fa-gavel me-1"></i> Final Admin Action</label>
                    <select name="new_status" id="adminActionSelect" class="form-select form-select-lg shadow-sm" style="border: 2px solid #101954;" required>
                        <option value="">-- Select Final Action --</option>
                        <option value="Approved">Approve Request (Release Stocks)</option>
                        <option value="Pending Staff Review">Return to Staff (Needs Corrections)</option>
                        <option value="Rejected">Decline / Cancel Request</option>
                    </select>
                    <small id="deductWarning" class="text-danger fw-bold d-none mt-2 d-block">
                        <i class="fas fa-exclamation-triangle"></i> Note: Approving this will automatically deduct the Issued Quantity from the master inventory!
                    </small>
                </div>
            </div>
        @endif

    </div>

    <div class="modal-footer border-0 bg-white rounded-bottom">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
        
        @if(!in_array($req->status, ['Rejected', 'Declined', 'Cancelled', 'Approved']))
            <button type="submit" class="btn btn-success px-4 fw-bold"><i class="fas fa-check-circle me-1"></i> Confirm & Save</button>
        @endif
    </div>
</form>

<script>
    // Simple script to show the warning only when Approved is selected
    const actionSelect = document.getElementById('adminActionSelect');
    if(actionSelect) {
        actionSelect.addEventListener('change', function() {
            if (this.value === 'Approved') {
                document.getElementById('deductWarning').classList.remove('d-none');
            } else {
                document.getElementById('deductWarning').classList.add('d-none');
            }
        });
    }
</script>