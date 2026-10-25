@extends('layouts.user')

@section('title', 'Become a Court Owner | Pickleball Hub')

@section('content')
<div class="container py-4">

    <!-- =========================================
         HERO HEADER BANNER
    ========================================== -->
    <div class="card border-0 shadow-lg rounded-4 p-3 p-sm-4 p-md-5 mb-4 text-white overflow-hidden position-relative" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
        <div class="row align-items-center position-relative" style="z-index: 2;">
            <div class="col-lg-8 col-12">
                <span class="badge bg-white text-success rounded-pill px-3 py-1 fw-bold mb-2 shadow-sm">
                    <i class="bi bi-star-fill me-1 text-warning"></i> COURT OWNER PARTNER PROGRAM
                </span>
                <h1 class="fw-bold mb-2" style="font-size: clamp(1.4rem, 3.5vw, 2.3rem); line-height: 1.25;">Host Your Courts & Earn 90% Net Payouts</h1>
                <p class="fs-6 mb-0" style="color: rgba(255, 255, 255, 0.92);">Join India's premier pickleball network. List your indoor/outdoor courts, set hourly slots, host official leagues, and manage player bookings seamlessly.</p>
            </div>
            <div class="col-lg-4 col-12 text-center text-lg-end mt-3 mt-lg-0">
                <div class="bg-white rounded-4 p-3 p-sm-4 d-inline-block text-start shadow-sm w-100 w-sm-auto text-dark" style="border: 1px solid rgba(255, 255, 255, 0.9);">
                    <div class="text-uppercase fw-bold text-success mb-2 small" style="font-size: 11px; letter-spacing: 0.5px;">
                        <i class="bi bi-shield-check me-1"></i> Partner Benefits
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-check-circle-fill text-success fs-5 flex-shrink-0"></i>
                        <span class="fw-bold text-dark" style="font-size: 14px;">90% Direct Net Owner Fee</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-check-circle-fill text-success fs-5 flex-shrink-0"></i>
                        <span class="fw-bold text-dark" style="font-size: 14px;">Automated Time Scheduler</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success fs-5 flex-shrink-0"></i>
                        <span class="fw-bold text-dark" style="font-size: 14px;">League & Tournament Host</span>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- =========================================
         DYNAMIC APPLICATION STATUS CARD CONTAINER
    ========================================== -->
    <div id="ownerAppStatusCard">
        <!-- Skeleton Loading State -->
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 text-center">
            <div class="spinner-border text-success mx-auto mb-3" role="status"></div>
            <p class="text-muted mb-0">Checking owner application status...</p>
        </div>
    </div>

</div>
@endsection

@push('scripts')
@vite('resources/js/user/become_owner.js')
@endpush
