@extends('layouts.admin')

@section('title', 'Promotional Banners | Admin Panel')

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- =========================================
         PAGE HEADER
    ========================================== -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-images text-success me-2"></i>Promotional Banner & Announcement Management
            </h3>
            <p class="text-muted small mb-0">Upload and manage promotional hero banners displayed on the player homepage carousel and mobile slider.</p>
        </div>
        <div>
            <button type="button" class="btn btn-success rounded-pill px-4 shadow-sm fw-bold d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createBannerModal">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Upload New Banner</span>
            </button>
        </div>
    </div>


    <!-- =========================================
         STAT CARDS
    ========================================== -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">TOTAL BANNERS</small>
                <h3 class="fw-extrabold text-dark my-1" id="statTotalBanners">0</h3>
                <small class="text-muted fs-8"><i class="bi bi-collection me-1 text-primary"></i>All uploaded banners</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">ACTIVE CAROUSEL BANNERS</small>
                <h3 class="fw-extrabold text-success my-1" id="statActiveBanners">0</h3>
                <small class="text-success fs-8 fw-semibold"><i class="bi bi-play-circle me-1"></i>Published on homepage</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-warning">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">INACTIVE / DRAFTS</small>
                <h3 class="fw-extrabold text-warning my-1" id="statInactiveBanners">0</h3>
                <small class="text-muted fs-8"><i class="bi bi-pause-circle me-1 text-warning"></i>Hidden from players</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-info">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">LINKED REDIRECTS</small>
                <h3 class="fw-extrabold text-info my-1" id="statLinkedBanners">0</h3>
                <small class="text-info fs-8 fw-semibold"><i class="bi bi-link-45deg me-1"></i>Interactive call-to-actions</small>
            </div>
        </div>
    </div>


    <!-- =========================================
         FILTERS & STATUS PILLS
    ========================================== -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="nav nav-pills gap-1 p-1 bg-light rounded-pill d-inline-flex border" id="bannerStatusPillsNav">
                <button class="nav-link active rounded-pill px-3.5 py-1.5 fs-8 fw-bold" data-status="">All Banners</button>
                <button class="nav-link rounded-pill px-3.5 py-1.5 fs-8 fw-bold text-success" data-status="active">Active Only</button>
                <button class="nav-link rounded-pill px-3.5 py-1.5 fs-8 fw-bold text-secondary" data-status="inactive">Inactive Only</button>
            </div>
            <small class="text-muted fs-8"><i class="bi bi-info-circle me-1"></i>Active banners cycle automatically on player devices.</small>
        </div>
    </div>


    <!-- =========================================
         BANNERS GRID CONTAINER
    ========================================== -->
    <div class="row g-4" id="bannersGrid">
        <div class="col-12 text-center py-5 text-muted">
            <span class="spinner-border spinner-border-sm text-primary me-2"></span>Loading promotional banners...
        </div>
    </div>

</div>


<!-- =========================================
     MODAL 1: CREATE NEW BANNER
========================================== -->
<div class="modal fade" id="createBannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white p-4">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-cloud-arrow-up-fill me-2 text-success"></i>Upload New Promotional Banner
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createBannerForm">
                <div class="modal-body p-4">

                    <!-- TITLE -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Banner Title / Heading <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control rounded-3" placeholder="e.g. Summer Pickleball Championship 2026" required>
                    </div>

                    <!-- IMAGE FILE UPLOAD -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Banner Image File <span class="text-danger">*</span></label>
                        <input type="file" name="image" id="createBannerFileInput" class="form-control rounded-3" accept="image/*" required>
                        <small class="text-muted fs-8 d-block mt-1">Recommended aspect ratio: 16:9 or landscape (JPEG, PNG, WEBP max 5MB)</small>

                        <!-- LIVE PREVIEW CONTAINER -->
                        <div id="createBannerPreviewContainer" class="mt-3 d-none">
                            <div class="position-relative rounded-3 overflow-hidden border shadow-sm" style="max-height: 180px;">
                                <img id="createBannerPreviewImg" src="" class="w-100 h-100 object-fit-cover" alt="Banner Preview">
                            </div>
                        </div>
                    </div>

                    <!-- REDIRECT URL -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Target Redirect Link (Optional)</label>
                        <input type="text" name="redirect_url" class="form-control rounded-3" placeholder="e.g. /tournaments or https://pickleballhub.com/offer">
                        <small class="text-muted fs-8">Players who click the banner will be navigated to this page.</small>
                    </div>

                    <!-- STATUS SELECT -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Initial Status</label>
                        <select name="status" class="form-select rounded-3">
                            <option value="active" selected>Active (Publish Immediately)</option>
                            <option value="inactive">Inactive (Draft / Save for Later)</option>
                        </select>
                    </div>

                </div>
                <div class="modal-footer bg-light p-3 border-top">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="submitCreateBannerBtn" class="btn btn-success rounded-pill px-4 fw-bold">
                        <i class="bi bi-cloud-upload me-1"></i> Upload Banner
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- =========================================
     MODAL 2: EDIT BANNER
========================================== -->
<div class="modal fade" id="editBannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white p-4">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-pencil-square me-2 text-warning"></i>Edit Promotional Banner
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editBannerForm">
                <input type="hidden" id="editBannerId" name="banner_id">

                <div class="modal-body p-4">

                    <!-- CURRENT IMAGE PREVIEW -->
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1 fs-8 fw-bold text-uppercase">CURRENT BANNER IMAGE</small>
                        <div class="position-relative rounded-3 overflow-hidden border shadow-sm" style="max-height: 160px;">
                            <img id="editCurrentBannerImg" src="" class="w-100 h-100 object-fit-cover" alt="Current Banner">
                        </div>
                    </div>

                    <!-- TITLE -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Banner Title <span class="text-danger">*</span></label>
                        <input type="text" id="editBannerTitle" name="title" class="form-control rounded-3" required>
                    </div>

                    <!-- REPLACE IMAGE FILE -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Replace Image File (Optional)</label>
                        <input type="file" id="editBannerFileInput" name="image" class="form-control rounded-3" accept="image/*">
                        <small class="text-muted fs-8 d-block mt-1">Leave empty to keep the existing banner image.</small>
                    </div>

                    <!-- REDIRECT URL -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Target Redirect Link (Optional)</label>
                        <input type="text" id="editBannerRedirectUrl" name="redirect_url" class="form-control rounded-3">
                    </div>

                    <!-- STATUS SELECT -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Publish Status</label>
                        <select id="editBannerStatus" name="status" class="form-select rounded-3">
                            <option value="active">Active (Visible to Players)</option>
                            <option value="inactive">Inactive (Hidden)</option>
                        </select>
                    </div>

                </div>
                <div class="modal-footer bg-light p-3 border-top">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="submitEditBannerBtn" class="btn btn-warning text-dark rounded-pill px-4 fw-bold">
                        <i class="bi bi-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- =========================================
     MODAL 3: DELETE BANNER CONFIRMATION
========================================== -->
<div class="modal fade" id="deleteBannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-danger p-4 text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-trash-fill me-2"></i>Delete Promotional Banner
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteBannerForm">
                <div class="modal-body p-4">
                    <p class="text-dark mb-3" id="deleteBannerPrompt">
                        Are you sure you want to delete this banner?
                    </p>
                    <div class="alert alert-danger rounded-3 small mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> This action is permanent and will permanently remove the banner file from storage and the player homepage.
                    </div>
                </div>
                <div class="modal-footer bg-light p-3 border-top">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="submitDeleteBannerBtn" class="btn btn-danger rounded-pill px-4 fw-bold">
                        Confirm Permanent Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/admin/banners.js')
@endpush
