{{-- =========================================
     PAYMENT GATEWAY SIMULATOR MODAL (LEARNING SANDBOX)
========================================= --}}
<div class="modal fade" id="paymentSimulatorModal" tabindex="-1" aria-labelledby="paymentSimulatorLabel" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header bg-dark text-white py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success px-2 py-1">LEARNING SANDBOX</span>
                    <h5 class="modal-title mb-0 fw-bold fs-6" id="paymentSimulatorLabel">Razorpay Payment Simulator</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info py-2 px-3 small mb-3">
                    <i class="bi bi-info-circle-fill me-1"></i>
                    <strong>Learning Test Mode:</strong> No real money will be charged. This simulates the official Razorpay payment flow.
                </div>

                <div class="text-center py-2 mb-3">
                    <small class="text-muted text-uppercase fw-semibold" style="letter-spacing: 1px;">Amount Payable</small>
                    <h2 class="fw-bold text-success mt-1 mb-0" id="simModalAmount">₹0</h2>
                    <small class="text-muted" id="simModalOrderId">Order ID: --</small>
                </div>

                <div class="payment-method-selector mb-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Select Test Payment Method</label>
                    <div class="list-group">
                        <label class="list-group-item list-group-item-action d-flex align-items-center gap-3 p-3">
                            <input class="form-check-input flex-shrink-0" type="radio" name="simPaymentMethod" value="upi" checked>
                            <div>
                                <div class="fw-bold"><i class="bi bi-qr-code-scan me-1 text-primary"></i> UPI (GPay / PhonePe / Paytm)</div>
                                <small class="text-muted">Simulates instant UPI pin verification</small>
                            </div>
                        </label>
                        <label class="list-group-item list-group-item-action d-flex align-items-center gap-3 p-3">
                            <input class="form-check-input flex-shrink-0" type="radio" name="simPaymentMethod" value="card">
                            <div>
                                <div class="fw-bold"><i class="bi bi-credit-card-2-front me-1 text-success"></i> Test Debit/Credit Card</div>
                                <small class="text-muted">Simulates 4111-XXXX-XXXX-4242 with OTP</small>
                            </div>
                        </label>
                        <label class="list-group-item list-group-item-action d-flex align-items-center gap-3 p-3">
                            <input class="form-check-input flex-shrink-0" type="radio" name="simPaymentMethod" value="netbanking">
                            <div>
                                <div class="fw-bold"><i class="bi bi-bank me-1 text-warning"></i> NetBanking</div>
                                <small class="text-muted">Simulates mock bank gateway redirect</small>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-success fw-bold py-2" id="simSuccessBtn">
                        <i class="bi bi-check-circle-fill me-1"></i> Simulate Successful Payment
                    </button>
                    <button type="button" class="btn btn-outline-danger fw-semibold py-2" id="simFailBtn">
                        <i class="bi bi-x-circle-fill me-1"></i> Simulate Payment Failure
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
