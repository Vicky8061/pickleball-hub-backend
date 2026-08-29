@extends('layouts.owner')

@section('title', 'My Courts | Owner Portal')

@section('content')
<div class="container-fluid py-4 px-lg-4">

    <!-- =========================================
         PAGE HEADER & QUICK ACTION
    ========================================== -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="fw-extrabold text-dark mb-1">My Pickleball Courts 🏟️</h2>
            <p class="text-muted small mb-0">Manage your court venues, update pricing, toggle availability, and upload photos.</p>
        </div>
        <div>
            <button type="button" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addCourtModal">
                <i class="bi bi-plus-lg me-1"></i> Add New Court
            </button>
        </div>
    </div>


    <!-- =========================================
         SEARCH & FILTER CONTROLS
    ========================================== -->
    <div class="owner-card p-3 mb-4">
        <div class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="searchCourtInput" class="form-control bg-light border-start-0 ps-0" placeholder="Search court by name or address...">
                </div>
            </div>
            <div class="col-md-3 col-6">
                <select id="filterCourtType" class="form-select bg-light">
                    <option value="">All Court Types</option>
                    <option value="indoor">Indoor</option>
                    <option value="outdoor">Outdoor</option>
                </select>
            </div>
            <div class="col-md-3 col-6">
                <select id="filterCourtStatus" class="form-select bg-light">
                    <option value="">All Statuses</option>
                    <option value="active">Active Only</option>
                    <option value="inactive">Inactive Only</option>
                </select>
            </div>
            <div class="col-md-1 col-12 text-end">
                <button type="button" id="resetCourtFiltersBtn" class="btn btn-outline-secondary w-100 rounded-3">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </button>
            </div>
        </div>
    </div>


    <!-- =========================================
         COURTS GRID
    ========================================== -->
    <div class="row g-4" id="ownerCourtsGrid">
        <!-- Skeleton Cards -->
        @for ($i = 0; $i < 3; $i++)
        <div class="col-lg-4 col-md-6">
            <div class="owner-card h-100 p-3">
                <div class="skeleton skeleton-img w-100 rounded-3 mb-3" style="height: 180px;"></div>
                <div class="skeleton skeleton-title w-75 mb-2"></div>
                <div class="skeleton skeleton-text w-50 mb-3"></div>
                <div class="skeleton skeleton-text w-100 mb-2"></div>
                <div class="d-flex justify-content-between mt-3 pt-3 border-top">
                    <div class="skeleton skeleton-badge w-25"></div>
                    <div class="skeleton skeleton-badge w-25"></div>
                </div>
            </div>
        </div>
        @endfor
    </div>

</div>


<!-- =========================================
     MODAL 1: ADD COURT MODAL
========================================== -->
<div class="modal fade" id="addCourtModal" tabindex="-1" aria-labelledby="addCourtModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="addCourtModalLabel">
                    <i class="bi bi-building-add text-success me-2"></i> Add New Court Venue
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addCourtForm">
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small text-muted">Court Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Sukhkarta Pickleball Arena" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Court Type *</label>
                            <select name="court_type" class="form-select" required>
                                <option value="Indoor">Indoor</option>
                                <option value="Outdoor" selected>Outdoor</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small text-muted">Full Address *</label>
                            <input type="text" name="address" class="form-control" placeholder="e.g. Vesu Main Road, Near Green Park, Surat" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Price Per Hour (₹) *</label>
                            <input type="number" name="price_per_hour" class="form-control" placeholder="500" min="0" step="10" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Opening Time *</label>
                            <input type="time" name="opening_time" class="form-control" value="06:00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Closing Time *</label>
                            <input type="time" name="closing_time" class="form-control" value="23:00" required>
                        </div>

                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label fw-semibold small text-muted mb-0">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> Drag Map Pin to Set Court Location
                                </label>
                                <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 py-1 fs-8 fw-bold" onclick="detectAddCourtLocation()">
                                    <i class="bi bi-crosshair me-1"></i> Detect Location
                                </button>
                            </div>
                            <div id="addCourtMap" style="height: 200px;" class="rounded-3 border"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Latitude</label>
                            <input type="number" step="any" name="latitude" id="addCourtLat" class="form-control" value="21.1702" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Longitude</label>
                            <input type="number" step="any" name="longitude" id="addCourtLng" class="form-control" value="72.8311" readonly>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small text-muted">Description & Amenities</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Describe court surface, lighting, seating, parking, etc."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold" id="saveCourtBtn">
                        <span class="spinner-border spinner-border-sm me-1 d-none" id="saveCourtSpinner"></span> Create Court
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- =========================================
     MODAL 2: EDIT COURT MODAL
========================================== -->
<div class="modal fade" id="editCourtModal" tabindex="-1" aria-labelledby="editCourtModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="editCourtModalLabel">
                    <i class="bi bi-pencil-square text-success me-2"></i> Edit Court Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCourtForm">
                <input type="hidden" name="court_id" id="editCourtId">
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small text-muted">Court Name *</label>
                            <input type="text" name="name" id="editCourtName" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Court Type *</label>
                            <select name="court_type" id="editCourtType" class="form-select" required>
                                <option value="Indoor">Indoor</option>
                                <option value="Outdoor">Outdoor</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small text-muted">Full Address *</label>
                            <input type="text" name="address" id="editCourtAddress" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Price Per Hour (₹) *</label>
                            <input type="number" name="price_per_hour" id="editCourtPrice" class="form-control" min="0" step="10" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Opening Time *</label>
                            <input type="time" name="opening_time" id="editCourtOpening" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Closing Time *</label>
                            <input type="time" name="closing_time" id="editCourtClosing" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Status *</label>
                            <select name="status" id="editCourtStatus" class="form-select" required>
                                <option value="active">Active (Available for booking)</option>
                                <option value="inactive">Inactive (Temporarily disabled)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label fw-semibold small text-muted mb-0">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> Drag Map Pin to Set Court Location
                                </label>
                                <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 py-1 fs-8 fw-bold" onclick="detectEditCourtLocation()">
                                    <i class="bi bi-crosshair me-1"></i> Detect Location
                                </button>
                            </div>
                            <div id="editCourtMap" style="height: 200px;" class="rounded-3 border"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Latitude</label>
                            <input type="number" step="any" name="latitude" id="editCourtLat" class="form-control" placeholder="Lat" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Longitude</label>
                            <input type="number" step="any" name="longitude" id="editCourtLng" class="form-control" placeholder="Lng" readonly>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small text-muted">Description & Amenities</label>
                            <textarea name="description" id="editCourtDesc" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold" id="updateCourtBtn">
                        <span class="spinner-border spinner-border-sm me-1 d-none" id="updateCourtSpinner"></span> Update Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- =========================================
     MODAL 3: COURT IMAGES GALLERY MODAL
========================================== -->
<div class="modal fade" id="courtImagesModal" tabindex="-1" aria-labelledby="courtImagesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="courtImagesModalLabel">
                    <i class="bi bi-images text-success me-2"></i> Manage Court Photos - <span id="galleryCourtTitle">Court</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">

                <!-- UPLOAD BOX -->
                <form id="uploadCourtImageForm" class="mb-4">
                    <input type="hidden" name="court_id" id="galleryCourtId">
                    <div class="p-3 bg-light rounded-3 border text-center">
                        <label class="form-label fw-semibold text-dark mb-2">Upload High Resolution Photo</label>
                        <div class="input-group">
                            <input type="file" name="images[]" id="courtPhotoInput" class="form-control" accept="image/*" multiple required>
                            <button type="submit" class="btn btn-success fw-bold px-4" id="uploadPhotoBtn">
                                <i class="bi bi-upload me-1"></i> Upload
                            </button>
                        </div>
                    </div>
                </form>

                <!-- EXISTING GALLERY -->
                <h6 class="fw-bold text-dark mb-3">Court Image Gallery</h6>
                <div class="row g-3" id="courtPhotosGrid">
                    <!-- Photo cards loaded dynamically -->
                </div>

            </div>
        </div>
    </div>
</div>


<!-- =========================================
     MODAL 4: VIEW COURT MAP LOCATION MODAL
========================================== -->
<div class="modal fade" id="viewCourtMapModal" tabindex="-1" aria-labelledby="viewCourtMapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="viewCourtMapModalLabel">
                    <i class="bi bi-map-fill text-success me-2"></i> Court Location Pin - <span id="mapModalCourtTitle">Court Location</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="text-muted small mb-3"><i class="bi bi-geo-alt me-1 text-danger"></i><span id="mapModalCourtAddress">Address</span></p>
                <div id="previewCourtMap" style="height: 340px;" class="rounded-3 border"></div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script type="module">
    import "{{ Vite::asset('resources/js/owner/courts.js') }}";
</script>
@endpush
