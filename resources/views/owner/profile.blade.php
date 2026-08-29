@extends('layouts.owner')

@section('title', 'Profile & Settings | Owner Portal')

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- =========================================
         PAGE HEADER
    ========================================== -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-person-gear text-success me-2"></i>Owner Profile & Venue Settings
            </h3>
            <p class="text-muted small mb-0">Manage your account information, business organization details, and security credentials.</p>
        </div>
    </div>


    <div class="row g-4">

        <!-- =========================================
             LEFT SIDEBAR: PROFILE BADGE CARD
        ========================================== -->
        <div class="col-lg-4 col-12">
            <div class="owner-card p-4 text-center">
                
                <div class="bg-success bg-opacity-10 text-success rounded-circle fw-bold d-inline-flex align-items-center justify-content-center border border-success border-opacity-25 mb-3 shadow-sm" style="width: 80px; height: 80px; font-size: 2.2rem;" id="profileAvatarBadge">
                    O
                </div>

                <h4 class="fw-bold text-dark mb-1" id="profileCardName">Owner Name</h4>
                <p class="text-muted small mb-3" id="profileCardEmail">owner@example.com</p>

                <div class="d-flex flex-column gap-2 border-top pt-3 text-start">
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">Account Status</span>
                        <span class="badge bg-success rounded-pill px-3 py-1" id="profileStatusBadge">Verified</span>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">Role</span>
                        <strong class="text-dark">Court Owner</strong>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">Member Since</span>
                        <span class="text-dark" id="profileMemberSince">-</span>
                    </div>
                </div>

            </div>
        </div>


        <!-- =========================================
             RIGHT FORM: TABBED SETTINGS
        ========================================== -->
        <div class="col-lg-8 col-12">
            <div class="owner-card p-4">

                <div id="profileAlertContainer"></div>

                <form id="ownerProfileForm">

                    <!-- SECTION 1: PERSONAL & CONTACT -->
                    <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                        <i class="bi bi-person-vcard text-primary me-2"></i>Personal & Contact Details
                    </h5>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Full Name *</label>
                            <input type="text" name="name" id="inputProfileName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Email Address (Read-only)</label>
                            <input type="email" id="inputProfileEmail" class="form-control bg-light" readonly disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Phone / Contact Number *</label>
                            <input type="text" name="phone" id="inputProfilePhone" class="form-control" placeholder="+91 98765 43210">
                        </div>
                    </div>


                    <!-- SECTION 2: BUSINESS & VENUE -->
                    <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                        <i class="bi bi-building text-warning me-2"></i>Business & Venue Details
                    </h5>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Business / Organization Name</label>
                            <input type="text" name="business_name" id="inputProfileBusinessName" class="form-control" placeholder="e.g. Surat Pickleball Club Pvt Ltd">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">City</label>
                            <input type="text" name="city" id="inputProfileCity" class="form-control" placeholder="e.g. Surat">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">State</label>
                            <input type="text" name="state" id="inputProfileState" class="form-control" placeholder="e.g. Gujarat">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Pincode</label>
                            <input type="text" name="pincode" id="inputProfilePincode" class="form-control" placeholder="395007">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-muted">Full Address</label>
                            <textarea name="address" id="inputProfileAddress" class="form-control" rows="2" placeholder="Full street address..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-muted">About Business / Description</label>
                            <textarea name="description" id="inputProfileDescription" class="form-control" rows="3" placeholder="Brief details about your court facilities and experience..."></textarea>
                        </div>
                    </div>


                    <!-- SECTION 3: SECURITY & PASSWORD CHANGE -->
                    <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                        <i class="bi bi-shield-lock text-danger me-2"></i>Security Credentials (Optional)
                    </h5>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Current Password</label>
                            <input type="password" name="current_password" id="inputProfileCurrentPassword" class="form-control" placeholder="••••••••">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">New Password</label>
                            <input type="password" name="new_password" id="inputProfileNewPassword" class="form-control" placeholder="••••••••">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Confirm New Password</label>
                            <input type="password" name="new_password_confirmation" id="inputProfileConfirmPassword" class="form-control" placeholder="••••••••">
                        </div>
                    </div>


                    <!-- SUBMIT BUTTON -->
                    <div class="d-flex justify-content-end pt-3 border-top">
                        <button type="submit" id="saveProfileBtn" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm">
                            <i class="bi bi-check-lg me-1"></i> Save Profile Changes
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
@vite('resources/js/owner/profile.js')
@endpush
