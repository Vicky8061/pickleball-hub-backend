@extends('layouts.admin')

@section('title', 'Payout Settlements | Admin Panel')

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- =========================================
         PAGE HEADER
    ========================================== -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-wallet2 text-success me-2"></i>Financial Commission & Owner Payout Settlements
            </h3>
            <p class="text-muted small mb-0">Track platform gross revenue, 10% retained admin commission, 90% net owner payouts, and record settlement transactions.</p>
        </div>
    </div>


    <!-- =========================================
         STAT CARDS
    ========================================== -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">GROSS PLATFORM VOLUME</small>
                <h3 class="fw-extrabold text-dark my-1" id="statGrossVolume">₹0</h3>
                <small class="text-muted fs-8"><i class="bi bi-currency-rupee me-1 text-primary"></i>Total paid reservations</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">RETAINED 10% COMMISSION</small>
                <h3 class="fw-extrabold text-success my-1" id="statRetainedCommission">₹0</h3>
                <small class="text-success fs-8 fw-semibold"><i class="bi bi-pie-chart-fill me-1"></i>Platform revenue share</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-info">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">SETTLED OWNER PAYOUTS</small>
                <h3 class="fw-extrabold text-info my-1" id="statSettledPayouts">₹0</h3>
                <small class="text-info fs-8 fw-semibold"><i class="bi bi-check-all me-1"></i>Disbursed to court owners</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-warning">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">PENDING PAYOUTS</small>
                <h3 class="fw-extrabold text-warning my-1" id="statPendingPayouts">₹0</h3>
                <small class="text-muted fs-8"><i class="bi bi-clock-history me-1 text-warning"></i>Awaiting disbursement</small>
            </div>
        </div>
    </div>


    <!-- =========================================
         FILTERS & SEARCH BAR
    ========================================== -->
    <div class="card border-0 shadow-sm rounded-4 p-3.5 mb-4 bg-white">
        <div class="row g-3 align-items-center">

            <!-- STATUS TABS -->
            <div class="col-lg-6 col-12">
                <div class="nav nav-pills gap-1 p-1 bg-light rounded-pill d-inline-flex border" id="payoutStatusPillsNav">
                    <button class="nav-link active rounded-pill px-3.5 py-1.5 fs-8 fw-bold" data-status="">All Owners</button>
                    <button class="nav-link rounded-pill px-3.5 py-1.5 fs-8 fw-bold text-warning" data-status="pending">Pending Payout</button>
                    <button class="nav-link rounded-pill px-3.5 py-1.5 fs-8 fw-bold text-success" data-status="settled">Settled</button>
                    <button class="nav-link rounded-pill px-3.5 py-1.5 fs-8 fw-bold text-info" data-status="processing">Processing</button>
                </div>
            </div>

            <!-- SEARCH INPUT -->
            <div class="col-lg-6 col-12 d-flex align-items-center gap-2 justify-content-lg-end">
                <div class="input-group search-input-group" style="max-width: 320px;">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" id="payoutSearchInput" class="form-control border-start-0 ps-0" placeholder="Search owner name or email...">
                </div>
            </div>

        </div>
    </div>


    <!-- =========================================
         PAYOUT LEDGER TABLE
    ========================================== -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small">
                    <tr>
                        <th class="ps-4 py-3">COURT OWNER / PARTNER</th>
                        <th class="py-3 text-center">VENUES</th>
                        <th class="py-3 text-center">PAID BOOKINGS</th>
                        <th class="py-3">GROSS REVENUE</th>
                        <th class="py-3">10% COMMISSION</th>
                        <th class="py-3">90% NET PAYOUT</th>
                        <th class="py-3">SETTLEMENT STATUS</th>
                        <th class="text-end pe-4 py-3">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="payoutsTbody">
                    <tr><td colspan="8" class="text-center py-5 text-muted">Loading payout ledger...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>


<!-- =========================================
     MODAL 1: INSPECT OWNER FINANCIALS
========================================== -->
<div class="modal fade" id="inspectPayoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white p-4">
                <h5 class="modal-title fw-bold" id="inspectPayoutModalTitle">
                    <i class="bi bi-pie-chart-fill me-2 text-success"></i>Owner Financial Breakdown
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="inspectPayoutModalBody">
                <div class="text-center py-5"><span class="spinner-border text-primary"></span></div>
            </div>
            <div class="modal-footer bg-light p-3 border-top d-flex justify-content-between" id="inspectPayoutModalFooter">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<!-- =========================================
     MODAL 2: SETTLE OWNER PAYOUT
========================================== -->
<div class="modal fade" id="settlePayoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-success text-white p-4">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-check-circle-fill me-2"></i>Record Owner Payout Settlement
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="settlePayoutForm">
                <input type="hidden" id="settleOwnerId" name="owner_id">

                <div class="modal-body p-4">
                    <p class="text-dark mb-3" id="settleOwnerPrompt">
                        Recording payout settlement for court owner.
                    </p>

                    <!-- SETTLEMENT STATUS -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Settlement Status <span class="text-danger">*</span></label>
                        <select id="settleStatusSelect" name="status" class="form-select rounded-3" required>
                            <option value="settled" selected>Settled (Disbursed to Bank/UPI)</option>
                            <option value="processing">Processing (Transfer Initiated)</option>
                            <option value="pending">Pending (Awaiting Payout)</option>
                        </select>
                    </div>

                    <!-- TX REFERENCE -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Transaction Reference ID (Optional)</label>
                        <input type="text" id="settleTxReference" name="tx_reference" class="form-control rounded-3" placeholder="e.g. UTR9876543210 or BANK-TXN-456">
                    </div>

                    <!-- PAYMENT METHOD -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Payment Method</label>
                        <select id="settlePaymentMethod" name="payment_method" class="form-select rounded-3">
                            <option value="Bank Transfer (NEFT/RTGS/IMPS)" selected>Bank Transfer (NEFT/RTGS/IMPS)</option>
                            <option value="UPI Transfer">UPI Transfer</option>
                            <option value="Cheque / Draft">Cheque / Draft</option>
                            <option value="Cash / Manual">Cash / Manual</option>
                        </select>
                    </div>

                    <!-- NOTES -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Settlement Notes (Optional)</label>
                        <textarea id="settleNotes" name="notes" class="form-control rounded-3" rows="2" placeholder="e.g. Monthly payout settlement for July 2026."></textarea>
                    </div>

                </div>
                <div class="modal-footer bg-light p-3 border-top">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="submitSettlePayoutBtn" class="btn btn-success rounded-pill px-4 fw-bold">
                        <i class="bi bi-check-lg me-1"></i> Save Settlement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/admin/payouts.js')
@endpush
