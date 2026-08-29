@extends('layouts.owner')

@section('title', 'Earnings & Financial Payouts | Owner Portal')

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- =========================================
         PAGE HEADER
    ========================================== -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-wallet2 text-success me-2"></i>Earnings & Financial Payout Reports
            </h3>
            <p class="text-muted small mb-0">Monitor total revenue, track 90% net court payouts, 10% admin commissions, and venue performance.</p>
        </div>
    </div>


    <!-- =========================================
         FINANCIAL SUMMARY KPI STAT CARDS
    ========================================== -->
    <div class="row g-3 mb-4">
        
        <!-- NET PAYOUT (90%) -->
        <div class="col-lg-3 col-md-6 col-12">
            <div class="owner-card p-3 border-start border-4 border-success">
                <small class="text-muted fw-semibold d-block fs-8">NET OWNER EARNINGS (90%)</small>
                <h3 class="fw-bold text-success my-1" id="earnNetPayout">₹0</h3>
                <small class="text-muted fs-8"><i class="bi bi-shield-check text-success me-1"></i>Net payouts after commission</small>
            </div>
        </div>

        <!-- GROSS VOLUME -->
        <div class="col-lg-3 col-md-6 col-12">
            <div class="owner-card p-3 border-start border-4 border-primary">
                <small class="text-muted fw-semibold d-block fs-8">GROSS BOOKING VOLUME</small>
                <h3 class="fw-bold text-dark my-1" id="earnGrossVolume">₹0</h3>
                <small class="text-muted fs-8"><i class="bi bi-receipt me-1 text-primary"></i>Total court fees collected</small>
            </div>
        </div>

        <!-- SETTLED / COMPLETED PAYOUTS -->
        <div class="col-lg-3 col-md-6 col-12">
            <div class="owner-card p-3 border-start border-4 border-info">
                <small class="text-muted fw-semibold d-block fs-8">COMPLETED SETTLEMENTS</small>
                <h3 class="fw-bold text-info my-1" id="earnCompletedPayout">₹0</h3>
                <small class="text-muted fs-8"><i class="bi bi-check2-circle text-info me-1"></i>Settled completed bookings</small>
            </div>
        </div>

        <!-- PENDING SETTLEMENTS -->
        <div class="col-lg-3 col-md-6 col-12">
            <div class="owner-card p-3 border-start border-4 border-warning">
                <small class="text-muted fw-semibold d-block fs-8">PENDING SETTLEMENTS</small>
                <h3 class="fw-bold text-warning my-1" id="earnPendingSettlements">₹0</h3>
                <small class="text-muted fs-8"><i class="bi bi-clock text-warning me-1"></i>Awaiting match completion</small>
            </div>
        </div>

    </div>


    <!-- =========================================
         MONTHLY INCOME CHART & COURT BREAKDOWN
    ========================================== -->
    <div class="row g-4 mb-4">
        
        <!-- MONTHLY BAR CHART -->
        <div class="col-lg-7 col-12">
            <div class="owner-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-bar-chart-line text-success me-2"></i>Monthly Revenue Growth</h5>
                        <small class="text-muted">Net 90% owner earnings over the last 6 months</small>
                    </div>
                </div>

                <div id="monthlyChartContainer" class="pt-3 pb-2" style="min-height: 220px;">
                    <!-- Monthly Bar Chart loaded dynamically -->
                </div>
            </div>
        </div>

        <!-- COURT REVENUE BREAKDOWN -->
        <div class="col-lg-5 col-12">
            <div class="owner-card p-4 h-100">
                <h5 class="fw-bold text-dark mb-1"><i class="bi bi-pie-chart text-primary me-2"></i>Court Venue Revenue</h5>
                <p class="text-muted small mb-3">Earnings breakdown per registered court venue</p>

                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="text-muted fs-8">
                                <th>VENUE</th>
                                <th class="text-center">BOOKINGS</th>
                                <th class="text-end">NET PAYOUT</th>
                            </tr>
                        </thead>
                        <tbody id="courtRevenueTbody">
                            <!-- Loaded dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>


    <!-- =========================================
         PAYOUT TRANSACTIONS LEDGER
    ========================================== -->
    <div class="owner-card p-0 overflow-hidden">
        <div class="p-4 border-bottom bg-light">
            <h5 class="fw-bold text-dark mb-1"><i class="bi bi-journal-text text-secondary me-2"></i>Recent Payout Ledger</h5>
            <small class="text-muted">Itemized financial statement of court reservations & net payouts</small>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-muted small">
                        <th class="ps-4">BOOKING ID</th>
                        <th>PLAYER NAME</th>
                        <th>COURT VENUE</th>
                        <th>BOOKING DATE</th>
                        <th>COURT FEE</th>
                        <th>COMMISSION (10%)</th>
                        <th>NET PAYOUT (90%)</th>
                        <th class="text-end pe-4">STATUS</th>
                    </tr>
                </thead>
                <tbody id="earningsLedgerTbody">
                    <!-- Loaded dynamically -->
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
@vite('resources/js/owner/earnings.js')
@endpush
