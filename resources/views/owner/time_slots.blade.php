@extends('layouts.owner')

@section('title', 'Time Slots & Operating Hours | Owner Portal')

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- =========================================
         PAGE HEADER & QUICK ACTIONS
    ========================================== -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-clock-history text-success me-2"></i>Time Slots & Operating Hours
            </h3>
            <p class="text-muted small mb-0">Configure court operating hours, toggle slot availability, and bulk generate daily schedules.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-success rounded-pill px-3 py-2 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#bulkTimeSlotModal">
                <i class="bi bi-lightning-charge-fill me-1 text-warning"></i> Bulk Generate Slots
            </button>
            <button type="button" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addTimeSlotModal">
                <i class="bi bi-plus-lg me-1"></i> Add Single Slot
            </button>
        </div>
    </div>


    <!-- =========================================
         COURT SELECTION & STATS SUMMARY
    ========================================== -->
    <div class="owner-card p-4 mb-4">
        <div class="row g-3 align-items-center">
            
            <!-- COURT FILTER -->
            <div class="col-md-5 col-12">
                <label class="form-label fw-semibold small text-muted">Select Court Venue</label>
                <select id="filterCourtSelect" class="form-select bg-light fw-semibold">
                    <option value="">Loading your courts...</option>
                </select>
            </div>

            <!-- STATUS FILTER -->
            <div class="col-md-3 col-6">
                <label class="form-label fw-semibold small text-muted">Status</label>
                <select id="filterSlotStatus" class="form-select bg-light">
                    <option value="">All Statuses</option>
                    <option value="active">Active Only</option>
                    <option value="inactive">Inactive Only</option>
                </select>
            </div>

            <!-- KPI STATS BADGES -->
            <div class="col-md-4 col-12 text-md-end">
                <div class="d-flex justify-content-md-end gap-3 text-center">
                    <div class="px-3 py-2 bg-light rounded-3 border">
                        <small class="text-muted fs-8 d-block fw-semibold">TOTAL SLOTS</small>
                        <strong class="text-dark fs-5" id="statTotalSlots">0</strong>
                    </div>
                    <div class="px-3 py-2 bg-success bg-opacity-10 rounded-3 border border-success border-opacity-25">
                        <small class="text-success fs-8 d-block fw-semibold">ACTIVE</small>
                        <strong class="text-success fs-5" id="statActiveSlots">0</strong>
                    </div>
                    <div class="px-3 py-2 bg-danger bg-opacity-10 rounded-3 border border-danger border-opacity-25">
                        <small class="text-danger fs-8 d-block fw-semibold">INACTIVE</small>
                        <strong class="text-danger fs-5" id="statInactiveSlots">0</strong>
                    </div>
                </div>
            </div>

        </div>
    </div>


    <!-- =========================================
         TIME SLOTS GRID
    ========================================== -->
    <div class="row g-3" id="ownerTimeSlotsGrid">
        <!-- Skeleton Loaders -->
        @for ($i = 0; $i < 6; $i++)
        <div class="col-lg-3 col-md-4 col-6">
            <div class="owner-card p-3">
                <div class="skeleton skeleton-title w-50 mb-2"></div>
                <div class="skeleton skeleton-text w-75 mb-3"></div>
                <div class="d-flex justify-content-between pt-2 border-top">
                    <div class="skeleton skeleton-text w-25"></div>
                    <div class="skeleton skeleton-text w-25"></div>
                </div>
            </div>
        </div>
        @endfor
    </div>

</div>


<!-- =========================================
     MODAL 1: ADD SINGLE TIME SLOT MODAL
========================================== -->
<div class="modal fade" id="addTimeSlotModal" tabindex="-1" aria-labelledby="addTimeSlotModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="addTimeSlotModalLabel">
                    <i class="bi bi-clock me-2 text-success"></i> Add Single Time Slot
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addTimeSlotForm">
                <div class="modal-body py-4">
                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label fw-semibold small text-muted">Select Court *</label>
                            <select name="court_id" id="addSlotCourtId" class="form-select" required>
                                <option value="">Loading courts...</option>
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold small text-muted">Start Time *</label>
                            <input type="time" name="start_time" class="form-control" value="06:00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-muted">End Time *</label>
                            <input type="time" name="end_time" class="form-control" value="07:00" required>
                        </div>

                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" id="submitAddSlotBtn">
                        Create Time Slot
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- =========================================
     MODAL 2: BULK GENERATE TIME SLOTS MODAL
========================================== -->
<div class="modal fade" id="bulkTimeSlotModal" tabindex="-1" aria-labelledby="bulkTimeSlotModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="bulkTimeSlotModalLabel">
                    <i class="bi bi-lightning-charge-fill me-2 text-warning"></i> Bulk Generate Time Slots
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkTimeSlotForm">
                <div class="modal-body py-4">
                    
                    <div class="alert alert-success bg-success bg-opacity-10 border-success border-opacity-25 small mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i> Automatically splits operating hours into consecutive slots. Overlapping slots will be skipped.
                    </div>

                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label fw-semibold small text-muted">Select Court *</label>
                            <select name="court_id" id="bulkSlotCourtId" class="form-select" required>
                                <option value="">Loading courts...</option>
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold small text-muted">Opening Start Time *</label>
                            <input type="time" name="start_time" class="form-control" value="06:00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-muted">Closing End Time *</label>
                            <input type="time" name="end_time" class="form-control" value="23:00" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small text-muted">Slot Duration *</label>
                            <select name="slot_duration_minutes" class="form-select" required>
                                <option value="60" selected>1 Hour (60 Minutes)</option>
                                <option value="30">30 Minutes</option>
                                <option value="90">1.5 Hours (90 Minutes)</option>
                                <option value="120">2 Hours (120 Minutes)</option>
                            </select>
                        </div>

                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark rounded-pill px-4 fw-bold shadow-sm" id="submitBulkSlotBtn">
                        <i class="bi bi-lightning-fill me-1"></i> Generate Slots
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
@vite('resources/js/owner/time_slots.js')
@endpush
