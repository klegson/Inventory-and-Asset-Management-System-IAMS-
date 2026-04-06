<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Background */
        body { 
            background-color: #f4f6f9; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }

        /* Sidebar space */
        .main-content {
            margin-left: 260px; 
            padding: 30px;
        }

        /* Cards */
        .custom-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            border: none;
            overflow: hidden;
            margin-bottom: 30px;
        }

        /* Profile Banner */
        .profile-banner {
            height: 150px;
            background-color: {{ $user->theme_color ?? '#101954' }};
            position: relative;
            transition: background-color 0.3s;
        }

        /* Profile Picture */
        .profile-pic {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid white;
            position: absolute;
            bottom: -50px;
            left: 20px;
            background-color: #e9ecef;
            object-fit: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #aaa;
            overflow: hidden;
            cursor: pointer;
        }
        
        .profile-pic:hover::after {
            content: "\f030"; /* FontAwesome Camera icon */
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            background: rgba(0,0,0,0.5);
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        /* Form Inputs */
        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #555;
            margin-bottom: 8px;
        }

        .gray-input {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 10px 15px;
            font-size: 0.95rem;
            width: 100%;
            outline: none;
            transition: 0.3s;
        }

        .gray-input:focus {
            background-color: #fff;
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }

        /* Color Picker */
        .color-picker {
            width: 50px;
            height: 50px;
            padding: 0;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            overflow: hidden;
        }

        /* Save Button */
        .btn-save {
            background-color: #101954;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 30px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-save:hover {
            background-color: #0a113d;
            color: white;
        }

        @media (max-width: 992px) {
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

    @include('layouts.user_sidebar')

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold m-0" style="color: #101954;">Profile Settings</h3>
        </div>

        @if(session('msg'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('msg') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            
            <div class="col-lg-8">
                <form action="{{ url('/user/profile') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="custom-card p-4">
                        <h5 class="fw-bold mb-4">Edit Profile</h5>
                        
                        <div class="mb-4 pb-4 border-bottom">
                            <label class="form-label d-block">Profile Theme Color</label>
                            <div class="d-flex align-items-center gap-3">
                                <input type="color" name="theme_color" id="themePicker" class="color-picker" value="{{ $user->theme_color ?? '#101954' }}" oninput="updatePreview()">
                                <span class="text-muted small">Pick a color for your profile banner</span>
                            </div>
                        </div>

                        <input type="file" name="image" id="profileImageInput" style="display: none;" accept="image/*" onchange="previewImage(this)">

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">First Name</label>
                                <input type="text" name="firstname" id="inputFirstName" class="gray-input" value="{{ $user->firstname }}" oninput="updatePreview()" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="lastname" id="inputLastName" class="gray-input" value="{{ $user->lastname }}" oninput="updatePreview()" required>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Designation / Position</label>
                                <input type="text" name="designation" id="inputRole" class="gray-input" value="{{ $user->designation }}" oninput="updatePreview()">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" id="inputEmail" class="gray-input" value="{{ $user->email }}" oninput="updatePreview()" readonly>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Short Bio</label>
                            <textarea name="bio" id="inputBio" class="gray-input" rows="3" oninput="updatePreview()">{{ $user->bio }}</textarea>
                        </div>

                        <div class="text-end mt-4">
                            <a href="{{ url('/user/dashboard') }}" class="btn btn-light me-2 fw-bold">Cancel</a>
                            <button type="submit" class="btn-save">Save Changes</button>
                        </div>

                    </div>
                </form>
            </div>

            <div class="col-lg-4">
                <p class="text-muted fw-bold small mb-2 text-uppercase">Live Preview</p>
                <div class="custom-card">
                    
                    <div class="profile-banner" id="previewBanner">
                        <div class="profile-pic bg-white shadow-sm" onclick="document.getElementById('profileImageInput').click()" style="cursor: pointer; position: absolute; bottom: -50px; left: 20px; width: 100px; height: 100px; border-radius: 50%; border: 4px solid white; overflow: hidden; display: flex; justify-content: center; align-items: center;">
                            @if($user->image)
                                <img id="previewPic" src="{{ asset('uploads/users/' . $user->image) }}" style="width: 100%; height: 100%; object-fit: cover;">
                                <div id="previewInitials" style="display: none; width: 100%; height: 100%; background: #101954; color: white; font-weight: bold; font-size: 2.5rem; justify-content: center; align-items: center;"></div>
                            @else
                                <img id="previewPic" src="" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                                @php
                                    $initials = strtoupper(substr($user->firstname, 0, 1) . substr($user->lastname, 0, 1));
                                @endphp
                                <div id="previewInitials" style="display: flex; width: 100%; height: 100%; background:  linear-gradient(135deg, #101954, #0a4d9c); color: white; font-weight: bold; font-size: 2.5rem; justify-content: center; align-items: center;">
                                    {{ $initials }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div style="padding: 65px 20px 25px 20px;">
                        <h4 class="fw-bold mb-1" id="previewName">{{ $user->firstname }} {{ $user->lastname }}</h4>
                        <p class="text-primary fw-bold small mb-3" id="previewRole">{{ $user->designation ?: 'Your Role' }}</p>
                        
                        <p class="text-muted small mb-4" id="previewBio">{{ $user->bio ?: 'Write a little bit about yourself here...' }}</p>
                        
                        <div class="d-flex align-items-center gap-2 text-muted small border-top pt-3">
                            <i class="fa-solid fa-envelope"></i>
                            <span id="previewEmail">{{ $user->email }}</span>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Update Text & Color Preview
        function updatePreview() {
            var firstName = document.getElementById("inputFirstName").value;
            var lastName = document.getElementById("inputLastName").value;
            var role = document.getElementById("inputRole").value;
            var email = document.getElementById("inputEmail").value;
            var bio = document.getElementById("inputBio").value;
            var themeColor = document.getElementById("themePicker").value;

            var fullName = firstName + " " + lastName;
            if(fullName.trim() === "") fullName = "Your Name";

            document.getElementById("previewName").innerText = fullName;
            document.getElementById("previewRole").innerText = role || "Your Role";
            document.getElementById("previewEmail").innerText = email || "your.email@example.com";
            document.getElementById("previewBio").innerText = bio || "Write a little bit about yourself here...";
            
            document.getElementById("previewBanner").style.backgroundColor = themeColor;

            // Live update initials
            var initialsEl = document.getElementById("previewInitials");
            if (initialsEl) {
                var firstLetter = firstName ? firstName.charAt(0).toUpperCase() : "";
                var lastLetter = lastName ? lastName.charAt(0).toUpperCase() : "";
                initialsEl.innerText = firstLetter + lastLetter;
            }
        }

        // Preview Selected Image
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var img = document.getElementById('previewPic');
                    var initials = document.getElementById('previewInitials');
                    
                    img.src = e.target.result;
                    img.style.display = 'block';
                    
                    if(initials) initials.style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>