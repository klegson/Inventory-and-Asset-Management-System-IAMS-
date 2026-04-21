@php
    $user = auth()->user();
    $initials = 'U';
    if($user) {
        $initials = strtoupper(substr($user->firstname ?? '', 0, 1) . substr($user->lastname ?? '', 0, 1));
        if(empty($initials)) $initials = 'U';
    }
@endphp

<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<style>
    .main-content { padding-top: 75px !important; }
    .top-header { position: fixed; top: 0; right: 0; left: 250px; height: 60px; background: linear-gradient(135deg, #101954 0%, #0a4d9c 100%); box-shadow: 0 2px 10px rgba(0,0,0,0.03); z-index: 1040; display: flex; align-items: center; justify-content: space-between; padding: 0 25px; transition: all 0.3s; }
    
    .global-search-container { position: relative; width: 38px; height: 38px; transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1); margin-right: 10px; }
    .global-search-container.active { width: 350px; }
    .global-search-container input { width: 100%; height: 100%; border-radius: 25px; padding: 8px 35px 8px 15px; background-color: transparent; border: 1px solid transparent; font-size: 0.9rem; color: transparent; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; }
    
    .global-search-container:not(.active):hover input { background-color: rgba(255, 255, 255, 0.15); }
    .global-search-container.active input { background-color: #f4f6f9; border-color: #e0e0e0; color: #333; cursor: text; border-radius: 8px; padding: 8px 60px 8px 15px; }
    .global-search-container.active input:focus { background-color: #fff; box-shadow: 0 0 0 4px rgba(16, 25, 84, 0.1); border-color: #101954; outline: none; }
    .global-search-container input::placeholder { color: transparent; }
    .global-search-container.active input::placeholder { color: #adb5bd; }
    
    .global-search-container .search-icon { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: rgba(255, 255, 255, 0.9); font-size: 1rem; pointer-events: none; transition: color 0.3s; }
    .global-search-container.active .search-icon { color: #101954; }
    .global-search-container .clear-icon { position: absolute; right: 35px; top: 50%; transform: translateY(-50%); color: #6c757d; cursor: pointer; display: none; z-index: 10; font-size: 0.9rem; }
    .global-search-container .clear-icon:hover { color: #dc3545; }
    
    .search-results-dropdown { position: absolute; top: 45px; left: 0; width: 100%; background: white; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); max-height: 400px; overflow-y: auto; display: none; z-index: 9999; border: 1px solid #e0e0e0; scrollbar-width: none; }
    .search-results-dropdown::-webkit-scrollbar { display: none; }
    .search-result-item { padding: 10px 15px; border-bottom: 1px solid #f4f6f9; display: block; text-decoration: none; color: #333; transition: background-color 0.2s; }
    .search-result-item:hover { background-color: #f8f9fa; }
    .search-result-title { font-weight: 700; font-size: 0.9rem; color: #101954; margin-bottom: 2px; }
    .search-result-meta { font-size: 0.75rem; color: #6c757d; }
    .search-result-badge { font-size: 0.65rem; padding: 2px 6px; border-radius: 4px; background: #e9ecef; color: #495057; float: right; }
    
    .header-actions { display: flex; align-items: center; gap: 8px; }
    
    .notification-btn { position: relative; color: rgba(255, 255, 255, 0.9); font-size: 1.15rem; cursor: pointer; transition: all 0.2s; padding: 8px; border-radius: 50%; margin-left: 5px;}
    .notification-btn:hover { color: #ffffff; background-color: rgba(255, 255, 255, 0.15); }
    .notification-badge { position: absolute; top: 0px; right: 0px; background-color: #dc3545; color: white; font-size: 0.6rem; font-weight: bold; padding: 2px 4px; border-radius: 10px; border: 2px solid white; display: none; }
    
    .notif-backdrop { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.5); z-index: 2000; opacity: 0; visibility: hidden; transition: all 0.3s ease; }
    .notif-backdrop.show { opacity: 1; visibility: visible; }
    .notif-drawer { position: fixed; top: 0; right: -400px; width: 380px; height: 100vh; background: white; z-index: 2001; box-shadow: -5px 0 15px rgba(0,0,0,0.1); transition: right 0.3s ease; display: flex; flex-direction: column; }
    .notif-drawer.open { right: 0; }
    .notif-drawer-header { padding: 20px; border-bottom: 1px solid #eee; display: flex; flex-direction: column; }
    .notif-drawer-body { flex: 1; overflow-y: auto; scrollbar-width: none; }
    .notif-drawer-body::-webkit-scrollbar { display: none; }
    .close-drawer-btn { cursor: pointer; color: #6c757d; font-size: 1.2rem; transition: color 0.2s; }
    .close-drawer-btn:hover { color: #dc3545; }
    .notif-item { position: relative; border-bottom: 1px solid #f4f6f9; border-left: 4px solid transparent; background-color: #ffffff; transition: background-color 0.2s; }
    .notif-item.unread-notif { border-left-color: #0d6efd !important; background-color: #f0f4ff; }
    .notif-item:hover { background-color: #f8f9fa; }
    .notif-item.unread-notif:hover { background-color: #e6edff; }
    .close-single-notif { position: absolute; top: 15px; right: 15px; color: #adb5bd; cursor: pointer; font-size: 1rem; transition: color 0.2s; z-index: 10; padding: 5px; }
    .close-single-notif:hover { color: #dc3545; }
    .hover-underline:hover { text-decoration: underline !important; }
    
    .user-profile { display: flex; align-items: center; gap: 10px; cursor: pointer; margin-left: 5px; }
    .user-avatar { width: 36px; height: 36px; border-radius: 50%; background-color: #ffffff; color: #101954; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1rem; box-shadow: 0 2px 5px rgba(0,0,0,0.2); transition: transform 0.2s; border: 2px solid rgba(255, 255, 255, 0.3); object-fit: cover;}
    .user-avatar:hover { transform: scale(1.05); }
    
    @keyframes highlightFade { 0% { background-color: #dbe7ff; } 100% { background-color: #f0f4ff; } }
    tr.highlight-target > td { animation: highlightFade 1s ease-out; background-color: #f0f4ff !important; }
    tr.highlight-target > td:first-child { border-left: 4px solid #0d6efd !important; }
    @media (max-width: 768px) { .top-header { left: 0; padding: 0 15px; } .global-search-container.active { width: 220px; } .main-content { padding-top: 75px !important; } .notif-drawer { width: 300px; } }
    @media print { .top-header, .notif-backdrop, .notif-drawer { display: none !important; } }

    /* Modal Profile Styles */
    .modal-profile-avatar {
        width: 90px; height: 90px; border-radius: 50%;
        background-color: #ffffff; color: #101954;
        border: 3px solid #101954; display: flex;
        align-items: center; justify-content: center;
        font-size: 2.5rem; font-weight: bold;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1); object-fit: cover; cursor: pointer;
        transition: filter 0.2s;
    }
    .modal-profile-avatar:hover { filter: brightness(0.9); }
    
    .modal-avatar-btn {
        position: absolute; bottom: 0; right: 0;
        width: 30px; height: 30px; border-radius: 50%;
        background-color: #0d6efd; color: white;
        border: 2px solid white; display: flex;
        align-items: center; justify-content: center;
        cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.2); transition: all 0.2s;
    }
    .modal-avatar-btn:hover { background-color: #0b5ed7; transform: scale(1.05); }
    
    /* Cropper Styles */
    .cropper-container-wrapper { width: 100%; max-height: 350px; background-color: #333; border-radius: 8px; overflow: hidden; }
</style>

@if(session('profile_success'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({ icon: 'success', title: 'Success!', text: '{{ session('profile_success') }}', timer: 2000, showConfirmButton: false });
        });
    </script>
@endif

<div class="notif-backdrop no-print" id="notifBackdrop"></div>

<div class="notif-drawer no-print" id="notifDrawer">
    <div class="notif-drawer-header">
        <div class="d-flex justify-content-between align-items-center w-100 mb-2">
            <h5 class="mb-0 fw-bold text-dark">Notifications <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill ms-1" id="drawerBadge">0</span></h5>
            <i class="fas fa-times close-drawer-btn" id="closeDrawerBtn"></i>
        </div>
        <div class="d-flex gap-3">
            <a href="#" class="text-primary text-decoration-none fw-semibold hover-underline" id="markAllReadBtn" style="font-size: 0.85rem;">Mark all as read</a>
            <a href="#" class="text-danger text-decoration-none fw-semibold hover-underline" id="clearNotifsBtn" style="font-size: 0.85rem;">Clear All</a>
        </div>
    </div>
    <div class="notif-drawer-body" id="notifList">
        <div class="p-4 text-center"><i class="fas fa-spinner fa-spin text-primary fs-4"></i></div>
    </div>
</div>

<div class="top-header no-print">
    <div></div> 
    <div class="header-actions">
        
        <div class="global-search-container" id="searchContainer">
            <input type="text" id="globalSearchInput" placeholder="Search inventory, requests, barcodes..." autocomplete="off">
            <i class="fas fa-search search-icon"></i>
            <i class="fas fa-times clear-icon" id="globalClearBtn" title="Clear Search"></i>
            <div class="search-results-dropdown" id="globalSearchResults"></div>
        </div>

        <div class="notification-btn" id="notifToggle" title="Notifications">
            <i class="far fa-bell"></i>
            <span class="notification-badge" id="notifBadge">0</span> 
        </div>

        <div class="dropdown">
            <div class="user-profile" data-bs-toggle="dropdown" aria-expanded="false" title="{{ $user->firstname ?? 'Staff' }} {{ $user->lastname ?? '' }}">
                @if($user && $user->image)
                    <img src="{{ asset('uploads/users/' . $user->image) }}" class="user-avatar" alt="Profile">
                @else
                    <div class="user-avatar">{{ $initials }}</div>
                @endif
            </div>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                <li><h6 class="dropdown-header fw-bold text-dark">{{ $user->firstname ?? 'User' }} {{ $user->lastname ?? '' }}</h6></li>
                
                <li><a class="dropdown-item btn" href="#" data-bs-toggle="modal" data-bs-target="#profileEditModal">
                    <i class="fas fa-user-edit fa-sm fa-fw me-2 text-muted"></i> Edit Profile</a>
                </li>
                
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item btn text-danger fw-semibold" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="fas fa-sign-out-alt fa-sm fa-fw me-2"></i> Logout</a></li>
            </ul>
        </div>

    </div>
</div>

<div class="modal fade no-print" id="profileEditModal" tabindex="-1" aria-labelledby="profileEditModalLabel" aria-hidden="true" style="z-index: 1051;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background: linear-gradient(135deg, #101954 0%, #0a4d9c 100%); color: white;">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-circle me-2"></i>My Profile</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="profileUpdateForm" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body bg-light p-4">
                    
                    <div id="avatarDisplayArea" class="d-flex align-items-center mb-4 pb-4 border-bottom">
                        <div class="position-relative me-4">
                            @if($user->image)
                                <img src="{{ asset('uploads/users/' . $user->image) }}" class="modal-profile-avatar" id="modalPreviewImg" onclick="editExistingImage()" title="Click to adjust image">
                                <div class="modal-profile-avatar d-none" id="modalPreviewInitials" onclick="document.getElementById('modalImageInput').click();">{{ $initials }}</div>
                            @else
                                <img src="" class="modal-profile-avatar d-none" id="modalPreviewImg" onclick="editExistingImage()" title="Click to adjust image">
                                <div class="modal-profile-avatar cursor-pointer" id="modalPreviewInitials" onclick="document.getElementById('modalImageInput').click();" title="Upload Photo">{{ $initials }}</div>
                            @endif
                            
                            <input type="file" name="image" id="modalImageInput" class="d-none" accept="image/png, image/jpeg, image/jpg" onchange="initImageEditor(this)">
                            
                            <div class="modal-avatar-btn" onclick="document.getElementById('modalImageInput').click();" title="Upload New Photo">
                                <i class="fas fa-camera fa-sm"></i>
                            </div>
                        </div>

                        <div>
                            <h3 class="fw-bold mb-1" id="modalPreviewName" style="color: #101954;">{{ $user->firstname }} {{ $user->lastname }}</h3>
                            <p class="text-muted mb-0"><span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-1">{{ $user->role ?? 'Staff' }}</span></p>
                        </div>
                    </div>

                    <div id="imageEditorArea" class="d-none mb-4 pb-4 border-bottom">
                        <h6 class="fw-bold text-dark mb-3"><i class="fas fa-crop-alt me-2 text-primary"></i>Adjust Photo</h6>
                        <p class="small text-muted mb-2"><i class="fas fa-arrows-alt me-1"></i>Drag image to pan. Scroll to zoom.</p>
                        
                        <div class="cropper-container-wrapper mb-3">
                            <img id="cropperTarget" src="" style="max-width: 100%; display: block;">
                        </div>
                        
                        <div class="row g-3 align-items-center mb-3">
                            <div class="col-md-4 text-center">
                                <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="cropper.zoom(0.1)" title="Zoom In"><i class="fas fa-search-plus"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cropper.zoom(-0.1)" title="Zoom Out"><i class="fas fa-search-minus"></i></button>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted mb-0">Brightness</label>
                                <input type="range" class="form-range" id="filterBrightness" min="50" max="150" value="100" oninput="applyLiveFilters()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted mb-0">Contrast</label>
                                <input type="range" class="form-range" id="filterContrast" min="50" max="150" value="100" oninput="applyLiveFilters()">
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-sm btn-light border px-3 me-2" onclick="cancelImageEditor()">Cancel</button>
                            <button type="button" class="btn btn-sm btn-primary px-3" onclick="confirmImageEdit()"><i class="fas fa-check me-1"></i>Apply Image</button>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="firstname" id="modalFirstName" class="form-control bg-white" value="{{ $user->firstname }}" required oninput="updateModalUI()">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="lastname" id="modalLastName" class="form-control bg-white" value="{{ $user->lastname }}" required oninput="updateModalUI()">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-muted small text-uppercase">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control bg-white" value="{{ $user->email }}" required>
                        </div>
                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-bold text-muted small text-uppercase">Department / Office</label>
                            <input type="text" class="form-control text-muted" value="{{ $user->department ?? 'Asset Management Section' }}" disabled>
                        </div>
                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-bold text-muted small text-uppercase text-danger"><i class="fas fa-key me-1"></i> New Password</label>
                            <input type="password" name="password" class="form-control border-danger border-opacity-50" placeholder="Leave blank to keep current">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-white">
                    <button type="button" class="btn btn-light border px-4 fw-bold text-muted" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm"><i class="fas fa-save me-2"></i>Save Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="logoutModal" tabindex="-1" style="z-index: 1052;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Logout</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="fs-5 mb-0">Are you sure you want to log out?</p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ url('/logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger px-4">Yes, Logout</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // --- IMAGE CROPPER & FILTER LOGIC ---
    let cropper = null;

    function updateModalUI() {
        const first = document.getElementById("modalFirstName").value;
        const last = document.getElementById("modalLastName").value;
        document.getElementById("modalPreviewName").innerText = first + " " + last;
    }

    // Triggered when a NEW file is selected via input
    function initImageEditor(input) {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                openCropperWorkspace(e.target.result);
            }
            reader.readAsDataURL(file);
        }
    }

    // Triggered when clicking the EXISTING avatar image
    function editExistingImage() {
        const imgElement = document.getElementById('modalPreviewImg');
        if (imgElement && !imgElement.classList.contains('d-none') && imgElement.src) {
            openCropperWorkspace(imgElement.src);
        }
    }

    function openCropperWorkspace(imageSrc) {
        document.getElementById('avatarDisplayArea').classList.add('d-none');
        document.getElementById('imageEditorArea').classList.remove('d-none');
        
        const target = document.getElementById('cropperTarget');
        target.src = imageSrc;

        // Reset sliders
        document.getElementById('filterBrightness').value = 100;
        document.getElementById('filterContrast').value = 100;

        if (cropper) { cropper.destroy(); }
        
        cropper = new Cropper(target, {
            aspectRatio: 1, // Perfect square/circle for avatar
            viewMode: 1,
            dragMode: 'move', // Panning left/right/up/down
            autoCropArea: 1,
            cropBoxMovable: false,
            cropBoxResizable: false,
            toggleDragModeOnDblclick: false,
        });
    }

    function cancelImageEditor() {
        document.getElementById('imageEditorArea').classList.add('d-none');
        document.getElementById('avatarDisplayArea').classList.remove('d-none');
        if (cropper) { cropper.destroy(); cropper = null; }
        // Clear input to allow re-selection of the same file
        document.getElementById('modalImageInput').value = ''; 
    }

    function applyLiveFilters() {
        if (!cropper) return;
        const b = document.getElementById('filterBrightness').value;
        const c = document.getElementById('filterContrast').value;
        const filterString = `brightness(${b}%) contrast(${c}%)`;
        
        // Cropper JS creates copies of the image. We apply CSS filters to those copies.
        document.querySelectorAll('.cropper-canvas img, .cropper-view-box img').forEach(img => {
            img.style.filter = filterString;
        });
    }

    function confirmImageEdit() {
        if (!cropper) return;
        
        // Get raw cropped canvas
        const canvas = cropper.getCroppedCanvas({ width: 400, height: 400, fillColor: '#fff' });
        
        // Apply Filters via Context Context to a final canvas
        const b = document.getElementById('filterBrightness').value;
        const c = document.getElementById('filterContrast').value;
        
        const filteredCanvas = document.createElement('canvas');
        filteredCanvas.width = canvas.width;
        filteredCanvas.height = canvas.height;
        const ctx = filteredCanvas.getContext('2d');
        ctx.filter = `brightness(${b}%) contrast(${c}%)`;
        ctx.drawImage(canvas, 0, 0);

        // Update the visual preview avatar
        const dataUrl = filteredCanvas.toDataURL('image/jpeg', 0.9);
        document.getElementById('modalPreviewImg').src = dataUrl;
        document.getElementById('modalPreviewImg').classList.remove('d-none');
        document.getElementById('modalPreviewInitials').classList.add('d-none');

        // SILENTLY ATTACH THE NEW BLOB TO THE FORM INPUT SO THE BACKEND RECEIVES IT
        filteredCanvas.toBlob(function(blob) {
            let file = new File([blob], "edited_avatar.jpg", { type: "image/jpeg" });
            let dt = new DataTransfer();
            dt.items.add(file);
            document.getElementById('modalImageInput').files = dt.files;
        }, 'image/jpeg', 0.9);

        // Close workspace
        document.getElementById('imageEditorArea').classList.add('d-none');
        document.getElementById('avatarDisplayArea').classList.remove('d-none');
        cropper.destroy(); cropper = null;
    }


    // --- ORIGINAL HEADER SEARCH & NOTIFICATION SCRIPTS ---
    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        const highlightQuery = urlParams.get('search');
        if (highlightQuery && highlightQuery.length > 1) {
            const queryLower = highlightQuery.toLowerCase();
            const tableRows = document.querySelectorAll('table tbody tr');
            tableRows.forEach(row => {
                if (row.textContent.toLowerCase().includes(queryLower)) {
                    row.classList.add('highlight-target');
                    setTimeout(() => { row.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 300);
                }
            });
        }

        const searchContainer = document.getElementById('searchContainer');
        const searchInput = document.getElementById('globalSearchInput');
        const clearBtn = document.getElementById('globalClearBtn');
        const resultsDropdown = document.getElementById('globalSearchResults');
        let debounceTimer;

        searchInput.addEventListener('click', function() {
            if (!searchContainer.classList.contains('active')) {
                searchContainer.classList.add('active');
                setTimeout(() => searchInput.focus(), 100);
            }
        });

        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            if (query.length > 0) { clearBtn.style.display = 'block'; } 
            else { clearBtn.style.display = 'none'; resultsDropdown.style.display = 'none'; return; }
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => { fetchSearchResults(query); }, 400); 
        });

        clearBtn.addEventListener('click', function(e) {
            e.preventDefault(); searchInput.value = ''; this.style.display = 'none'; resultsDropdown.style.display = 'none'; searchInput.focus();
        });

        searchInput.addEventListener('focus', function() {
            if (this.value.trim().length > 0 && resultsDropdown.innerHTML.trim() !== '') { resultsDropdown.style.display = 'block'; }
        });

        function fetchSearchResults(query) {
            if(query.length < 2) return;
            resultsDropdown.innerHTML = '<div class="p-3 text-center text-muted small"><i class="fas fa-spinner fa-spin me-2"></i>Searching...</div>';
            resultsDropdown.style.display = 'block';
            fetch(`{{ url('/global-search') }}?q=${encodeURIComponent(query)}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json()).then(data => {
                resultsDropdown.innerHTML = '';
                if (data.length === 0) { resultsDropdown.innerHTML = `<div class="p-3 text-center text-muted small">No results found</div>`; return; }
                data.forEach(item => {
                    resultsDropdown.insertAdjacentHTML('beforeend', `
                        <a href="${item.url}" class="search-result-item">
                            <span class="search-result-badge">${item.type}</span>
                            <div class="search-result-title">${item.title}</div>
                            <div class="search-result-meta">${item.meta}</div>
                        </a>`);
                });
            }).catch(() => { resultsDropdown.innerHTML = '<div class="p-3 text-center text-danger small">Error loading results.</div>'; });
        }

        const notifToggle = document.getElementById('notifToggle');
        const notifDrawer = document.getElementById('notifDrawer');
        const notifBackdrop = document.getElementById('notifBackdrop');
        const closeDrawerBtn = document.getElementById('closeDrawerBtn');
        const clearNotifsBtn = document.getElementById('clearNotifsBtn');
        const markAllReadBtn = document.getElementById('markAllReadBtn');
        const notifBadge = document.getElementById('notifBadge');
        const drawerBadge = document.getElementById('drawerBadge');
        const notifList = document.getElementById('notifList');
        let currentNotifData = [];

        function renderNotifications() {
            let dismissed = JSON.parse(localStorage.getItem('dismissedNotifs')) || [];
            let readNotifs = JSON.parse(localStorage.getItem('readNotifs')) || [];
            let activeNotifs = currentNotifData.filter(n => n.type === 'low_stock' || !dismissed.includes(n.id.toString()));
            let unreadCount = activeNotifs.filter(n => n.type === 'low_stock' || !readNotifs.includes(n.id.toString())).length;

            if(unreadCount > 0) {
                notifBadge.textContent = unreadCount;
                notifBadge.style.display = 'block';
                drawerBadge.textContent = unreadCount;
            } else {
                notifBadge.style.display = 'none';
                drawerBadge.textContent = '0';
            }

            let html = '';
            if (activeNotifs.length === 0) {
                html = '<div class="p-5 text-center text-muted"><i class="fas fa-check-circle fs-1 text-success mb-3 d-block"></i>You are all caught up!</div>';
            } else {
                activeNotifs.forEach(notif => {
                    let closeBtn = notif.type !== 'low_stock' ? `<i class="fas fa-times close-single-notif" data-id="${notif.id}" title="Clear"></i>` : '';
                    let unreadClass = (notif.type === 'low_stock' || !readNotifs.includes(notif.id.toString())) ? 'unread-notif' : '';
                    html += `
                    <div class="notif-item ${unreadClass}">
                        <a href="${notif.url}" class="notif-link d-flex align-items-start p-3 text-decoration-none text-dark pe-5" data-id="${notif.id}" data-type="${notif.type}">
                            <div class="me-3 mt-1"><i class="${notif.icon} fs-4"></i></div>
                            <div class="flex-grow-1">
                                <div class="fw-bold fs-6" style="color: #101954;">${notif.title}</div>
                                <div class="small text-muted mb-1">${notif.message}</div>
                                <div class="text-primary fw-bold" style="font-size: 0.7rem;">${notif.time}</div>
                            </div>
                        </a>
                        ${closeBtn}
                    </div>`;
                });
            }
            notifList.innerHTML = html;
        }

        function fetchNotifications() {
            fetch(`{{ url('/notifications/fetch') }}`, { 
                method: 'GET', credentials: 'same-origin', 
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => { if (!res.ok) throw new Error('Fetch failed'); return res.json(); })
            .then(data => { if (data && data.notifications) { currentNotifData = data.notifications; renderNotifications(); } })
            .catch(err => {});
        }

        fetchNotifications();
        if (window.notifInterval) clearInterval(window.notifInterval);
        window.notifInterval = setInterval(fetchNotifications, 15000);

        document.addEventListener('click', function(e) {
            if(e.target.classList.contains('close-single-notif')) {
                e.preventDefault(); e.stopPropagation();
                let id = e.target.getAttribute('data-id');
                let dismissed = JSON.parse(localStorage.getItem('dismissedNotifs')) || [];
                if(!dismissed.includes(id)) dismissed.push(id);
                localStorage.setItem('dismissedNotifs', JSON.stringify(dismissed));
                renderNotifications();
                return;
            }

            let notifLink = e.target.closest('.notif-link');
            if (notifLink) {
                let id = notifLink.getAttribute('data-id');
                let type = notifLink.getAttribute('data-type');
                if (type !== 'low_stock') {
                    let readNotifs = JSON.parse(localStorage.getItem('readNotifs')) || [];
                    if (!readNotifs.includes(id)) {
                        readNotifs.push(id);
                        localStorage.setItem('readNotifs', JSON.stringify(readNotifs));
                    }
                }
            }

            if (!searchContainer.contains(e.target)) {
                searchContainer.classList.remove('active');
                resultsDropdown.style.display = 'none';
                searchInput.value = '';
                clearBtn.style.display = 'none';
            }
        });

        markAllReadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            let readNotifs = JSON.parse(localStorage.getItem('readNotifs')) || [];
            document.querySelectorAll('.close-single-notif').forEach(btn => {
                let id = btn.getAttribute('data-id');
                if(!readNotifs.includes(id)) readNotifs.push(id);
            });
            localStorage.setItem('readNotifs', JSON.stringify(readNotifs));
            renderNotifications();
        });

        clearNotifsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            let dismissed = JSON.parse(localStorage.getItem('dismissedNotifs')) || [];
            document.querySelectorAll('.close-single-notif').forEach(btn => {
                let id = btn.getAttribute('data-id');
                if(!dismissed.includes(id)) dismissed.push(id);
            });
            localStorage.setItem('dismissedNotifs', JSON.stringify(dismissed));
            renderNotifications();
        });

        function openDrawer() { notifDrawer.classList.add('open'); notifBackdrop.classList.add('show'); resultsDropdown.style.display = 'none'; searchContainer.classList.remove('active'); }
        function closeDrawer() { notifDrawer.classList.remove('open'); notifBackdrop.classList.remove('show'); }

        notifToggle.addEventListener('click', openDrawer);
        closeDrawerBtn.addEventListener('click', closeDrawer);
        notifBackdrop.addEventListener('click', closeDrawer);
    });
</script>