<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management - DepEd AMS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .main-content { margin-left: 250px; padding: 20px; transition: all 0.3s; }
        .table-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .bg-active { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .bg-inactive { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
        
        .clickable-row { cursor: pointer; transition: background-color 0.2s; }
        .clickable-row:hover { background-color: #f8f9fa !important; }
        
        @media (max-width: 768px) { .main-content { margin-left: 0; } }
    </style>
</head>
<body>

    @include('layouts.admin_header')
    @include('layouts.admin_sidebar')

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3" style="border-color: #003366 !important;">
            <div>
                <h2 style="color: #003366; margin: 0;">
                    <i class="fa-solid fa-users me-2"></i> User Management
                </h2>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-user-plus me-2"></i> Add New User
            </button>
        </div>

        @if(session('msg'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('msg') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-container">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Name</th>
                        <th>Email Address</th>
                        <th>Department</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $row)
                        <tr class="clickable-row" data-id="{{ $row->id }}">
                            <td class="ps-4 fw-bold">{{ $row->firstname }} {{ $row->lastname }}</td>
                            <td>{{ $row->email }}</td>
                            <td><small class="text-muted">{{ $row->department ?: 'No Office Assigned' }}</small></td>
                            <td>
                                @php $role = strtolower($row->role); @endphp
                                @if($role == 'admin')
                                    <span class="badge bg-dark">Admin</span>
                                @elseif($role == 'frontuser')
                                    <span class="badge bg-primary-subtle text-dark border">User</span>
                                @else
                                    <span class="badge bg-secondary">Personnel</span>
                                @endif
                            </td>
                            <td>
                                <span class="status-badge {{ $row->status == 'Active' ? 'bg-active' : 'bg-inactive' }}">
                                    {{ $row->status }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-primary text-white view-btn" data-id="{{ $row->id }}">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <button class="btn btn-sm btn-success text-white edit-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editUserModal"
                                            data-id="{{ $row->id }}"
                                            data-fname="{{ $row->firstname }}"
                                            data-lname="{{ $row->lastname }}"
                                            data-dept="{{ $row->department }}"
                                            data-contact="{{ $row->contact_number }}"
                                            data-email="{{ $row->email }}"
                                            data-role="{{ strtolower($row->role) }}"
                                            data-status="{{ $row->status }}"
                                            data-image="{{ $row->image }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <button class="btn btn-sm btn-danger delete-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteUserModal" 
                                            data-id="{{ $row->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i> Add New User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ url('/admin/users') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        
                        <div class="text-center mb-4">
                            <label class="form-label fw-bold d-block">Profile Image (Optional)</label>
                            <div class="rounded-circle bg-light d-flex justify-content-center align-items-center mx-auto mb-3 border shadow-sm" style="width: 120px; height: 120px; overflow: hidden; position: relative;">
                                <img id="imagePreviewAdd" src="" alt="Preview" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                                <i id="imagePlaceholderAdd" class="fas fa-camera fa-2x text-muted"></i>
                            </div>
                            <input type="file" name="image" id="imageInputAdd" class="form-control w-50 mx-auto" accept="image/*">
                        </div>

                        <h6 class="text-primary border-bottom pb-2 mb-3">Personal Information</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="firstname" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="lastname" class="form-control" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Contact Number</label>
                                <input type="text" name="contact_number" class="form-control" placeholder="e.g. 09123456789">
                            </div>
                        </div>

                        <h6 class="text-primary border-bottom pb-2 mb-3 mt-4">Employment Details</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Division <span class="text-danger">*</span></label>
                                <select id="add_division" class="form-select" required>
                                    <option value="" selected disabled>Select Division</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Section / Office <span class="text-danger">*</span></label>
                                <select name="department" id="add_section" class="form-select" required disabled>
                                    <option value="" selected disabled>Select Division first</option>
                                </select>
                            </div>
                        </div>

                        <h6 class="text-primary border-bottom pb-2 mb-3 mt-4">Account Settings</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required placeholder="name@deped.gov.ph">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password" id="add_password" class="form-control" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#add_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">System Role <span class="text-danger">*</span></label>
                                <select name="role" class="form-select" required>
                                    <option value="staff">Personnel (Inventory Manager)</option>
                                    <option value="admin">Admin (System Owner)</option>
                                    <option value="frontuser">User (Divisions/Requestor)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Save User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i> Edit User Profile</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        
                        <div class="text-center mb-4">
                            <label class="form-label fw-bold d-block">Update Profile Image</label>
                            <div class="rounded-circle bg-light d-flex justify-content-center align-items-center mx-auto mb-3 border shadow-sm" style="width: 120px; height: 120px; overflow: hidden; position: relative;">
                                <img id="imagePreviewEdit" src="" alt="Preview" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                                <i id="imagePlaceholderEdit" class="fas fa-camera fa-2x text-muted"></i>
                            </div>
                            <input type="file" name="image" id="imageInputEdit" class="form-control w-50 mx-auto" accept="image/*">
                            <small class="text-muted mt-1 d-block">Leave blank to keep existing photo</small>
                        </div>

                        <h6 class="text-success border-bottom pb-2 mb-3">Personal Information</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="firstname" id="edit_firstname" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="lastname" id="edit_lastname" class="form-control" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Contact Number</label>
                                <input type="text" name="contact_number" id="edit_contact" class="form-control">
                            </div>
                        </div>

                        <h6 class="text-success border-bottom pb-2 mb-3 mt-4">Employment Details</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Division <span class="text-danger">*</span></label>
                                <select id="edit_division" class="form-select" required>
                                    <option value="" selected disabled>Select Division</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Section / Office <span class="text-danger">*</span></label>
                                <select name="department" id="edit_section" class="form-select" required>
                                    <option value="" selected disabled>Select Division first</option>
                                </select>
                            </div>
                        </div>

                        <h6 class="text-success border-bottom pb-2 mb-3 mt-4">Account Settings</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="edit_email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password <small class="text-muted">(Leave blank to keep current)</small></label>
                                <div class="input-group">
                                    <input type="password" name="password" id="edit_password" class="form-control" placeholder="New Password">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#edit_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">System Role <span class="text-danger">*</span></label>
                                <select name="role" id="edit_role" class="form-select" required>
                                    <option value="staff">Personnel</option>
                                    <option value="admin">Admin</option>
                                    <option value="frontuser">User</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" id="edit_status" class="form-select" required>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success text-white px-4">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i> Confirm Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body text-center py-4">
                        <p class="fs-5 mb-1">Are you sure you want to delete this user?</p>
                        <p class="text-muted small mb-0">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer justify-content-center border-0">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger px-4">Delete User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0" id="view_details_content" style="border-radius: 12px; overflow: hidden;">
                </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // --- DYNAMIC DROPDOWN LOGIC ---
        const officeMapping = {
            "Administrative Division": ["Asset Management Section", "General Services Unit", "Payroll Services Unit", "Records Section", "Personnel Section", "Cash Section"],
            "Curriculum and Learning Management Division": ["Learning Resource Management Section"],
            "Education Support Services Division": ["Health and Nutrition", "Programs and Projects", "Facilities"],
            "Finance Division": ["Budget Section", "Accounting Section"],
            "Human Resource Development Division": ["NEAP"],
            "Office of the Regional Director": ["Procurement Unit", "Information and Communications Technology Unit", "Public Affairs Unit", "Legal Unit"]
        };

        // Populate Division Dropdowns on load
        function populateDivisions(selectElementId) {
            const select = document.getElementById(selectElementId);
            for (let division in officeMapping) {
                let option = document.createElement("option");
                option.value = division;
                option.text = division;
                select.appendChild(option);
            }
        }
        
        populateDivisions("add_division");
        populateDivisions("edit_division");

        // Event Listeners to update Sections when Division changes
        document.getElementById('add_division').addEventListener('change', function() {
            const division = this.value;
            const sectionSelect = document.getElementById('add_section');
            sectionSelect.innerHTML = '<option value="" selected disabled>Select Section</option>';
            
            if (division && officeMapping[division]) {
                officeMapping[division].forEach(function(section) {
                    let option = document.createElement("option");
                    option.value = section;
                    option.text = section;
                    sectionSelect.appendChild(option);
                });
                sectionSelect.disabled = false;
            } else {
                sectionSelect.disabled = true;
            }
        });

        document.getElementById('edit_division').addEventListener('change', function() {
            const division = this.value;
            const sectionSelect = document.getElementById('edit_section');
            sectionSelect.innerHTML = '<option value="" selected disabled>Select Section</option>';
            
            if (division && officeMapping[division]) {
                officeMapping[division].forEach(function(section) {
                    let option = document.createElement("option");
                    option.value = section;
                    option.text = section;
                    sectionSelect.appendChild(option);
                });
                sectionSelect.disabled = false;
            }
        });


        // --- View Modal AJAX ---
        function loadViewModal(id) {
            const contentArea = document.getElementById('view_details_content');
            
            new bootstrap.Modal(document.getElementById('viewUserModal')).show();
            contentArea.innerHTML = '<div class="p-5 text-center"><div class="spinner-border text-primary"></div><p class="mt-2 mb-0">Loading Profile...</p></div>';

            fetch(`/admin/users/${id}/details`)
                .then(response => response.text())
                .then(data => { contentArea.innerHTML = data; });
        }

        document.querySelectorAll('.clickable-row').forEach(row => {
            row.addEventListener('click', function(e) {
                if(e.target.closest('button') || e.target.closest('a')) { return; }
                loadViewModal(this.getAttribute('data-id'));
            });
        });

        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation(); 
                loadViewModal(this.getAttribute('data-id'));
            });
        });

        // --- Populate Edit Form ---
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                
                document.getElementById('editForm').action = `/admin/users/${id}`;
                document.getElementById('edit_firstname').value = this.getAttribute('data-fname');
                document.getElementById('edit_lastname').value = this.getAttribute('data-lname');
                document.getElementById('edit_contact').value = this.getAttribute('data-contact');
                document.getElementById('edit_email').value = this.getAttribute('data-email');
                
                document.getElementById('edit_role').value = this.getAttribute('data-role'); 
                document.getElementById('edit_status').value = this.getAttribute('data-status');

                // Advanced Logic to auto-select the correct Division and Section!
                const userDept = this.getAttribute('data-dept');
                const divisionSelect = document.getElementById('edit_division');
                const sectionSelect = document.getElementById('edit_section');
                
                // Reset Selects
                divisionSelect.value = "";
                sectionSelect.innerHTML = '<option value="" selected disabled>Select Division first</option>';
                sectionSelect.disabled = true;

                // Find which Division this Department belongs to
                if (userDept) {
                    for (let division in officeMapping) {
                        if (officeMapping[division].includes(userDept)) {
                            // 1. Set Division
                            divisionSelect.value = division;
                            
                            // 2. Populate corresponding sections
                            sectionSelect.innerHTML = '<option value="" disabled>Select Section</option>';
                            officeMapping[division].forEach(function(section) {
                                let option = document.createElement("option");
                                option.value = section;
                                option.text = section;
                                sectionSelect.appendChild(option);
                            });
                            
                            // 3. Select the actual section & enable
                            sectionSelect.value = userDept;
                            sectionSelect.disabled = false;
                            break;
                        }
                    }
                }

                // Handle Image Preview
                const currentImage = this.getAttribute('data-image');
                const preview = document.getElementById('imagePreviewEdit');
                const placeholder = document.getElementById('imagePlaceholderEdit');
                
                if (currentImage && currentImage !== '') {
                    preview.src = `/uploads/users/${currentImage}`;
                    preview.style.display = 'block';
                    placeholder.style.display = 'none';
                } else {
                    preview.src = '';
                    preview.style.display = 'none';
                    placeholder.style.display = 'block';
                }
                document.getElementById('imageInputEdit').value = '';
            });
        });

        // --- Populate Delete Form ---
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                document.getElementById('deleteForm').action = `/admin/users/${id}`;
            });
        });

        // --- Add Form Image Preview JS ---
        document.getElementById('imageInputAdd').addEventListener('change', function(event) {
            const preview = document.getElementById('imagePreviewAdd');
            const placeholder = document.getElementById('imagePlaceholderAdd');
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    placeholder.style.display = 'none';
                }
                reader.readAsDataURL(file);
            } else {
                preview.src = '';
                preview.style.display = 'none';
                placeholder.style.display = 'block';
            }
        });

        // --- Edit Form Image Preview JS ---
        document.getElementById('imageInputEdit').addEventListener('change', function(event) {
            const preview = document.getElementById('imagePreviewEdit');
            const placeholder = document.getElementById('imagePlaceholderEdit');
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    placeholder.style.display = 'none';
                }
                reader.readAsDataURL(file);
            }
        });

        // --- Toggle Password Visibility JS ---
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.querySelector(targetId);
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    </script>
</body>
</html>