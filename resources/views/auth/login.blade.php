<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Login - AMS Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #ffffff; }
        
        .bg-image {
            background-size: cover;
            background-position: center;
            position: relative;
            /* Ensure the parent has height for vertical centering */
            height: 100%; 
        }
        
        .overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(to bottom right, rgba(16, 25, 84, 0.7), rgba(196, 203, 211, 0.34));
            z-index: 1;
        }

        /* Centered Logo Container */
        .logo-container {
            position: absolute;
            top: 45%;
            left: 48%;
            transform: translate(-50%, -50%); /* Perfectly center */
            z-index: 2; /* Keeps it above the overlay */
            text-align: center;
        }

        /* styling to make square image big, circular, and defined */
        .circular-seal {
            width: 250px;
            height: 250px;
            object-fit: cover;
            border-radius: 50%; /* Makes it a circle */
            border: 8px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
        
        .text-overlay {
            position: absolute;
            bottom: 7%;
            left: 10%;
            color: white;
            padding-right: 20px;
            z-index: 2; /* Keeps text above overlay */
        }
        
        .login-container {
            max-width: 360px; 
            width: 100%;
            padding: 2rem 1.5rem;
        }
        
        .text-theme { color: #101954; }
        .bg-theme { background-color: #101954; }
        
        .btn-theme {
            background-color: #101954;
            border-color: #101954;
            color: white;
            transition: all 0.3s;
        }
        .btn-theme:hover {
            background-color: #0b113a;
            border-color: #0b113a;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(16, 25, 84, 0.2);
        }
        
        .form-control:focus {
            border-color: #101954;
            box-shadow: 0 0 0 0.25rem rgba(16, 25, 84, 0.15);
        }
        .form-check-input:checked {
            background-color: #101954;
            border-color: #101954;
        }
        
        .password-toggle {
            cursor: pointer;
            border-left: none;
            background-color: transparent;
        }
        .password-input {
            border-right: none;
        }
        .password-input:focus + .password-toggle {
            border-color: #101954;
        }
    </style>
</head>
<body class="vh-100 overflow-hidden">
    <div class="row g-0 h-100">
        
        <div class="col-md-7 col-lg-9 d-none d-md-block bg-image" style="background-image: url('{{ asset('assets/images/front.png') }}');">
            <div class="overlay"></div>
            
            <div class="logo-container">
                <img src="{{ asset('assets/images/DepEdROV.png') }}" alt="DepEd ROV Seal" class="circular-seal">
            </div>

            <div class="text-overlay">
                <h1 class="fw-bold display-5 mb-3 text-white">Asset & Supply Management</h1>
                <p class="fs-5 text-light opacity-75 w-75">
                    Centralized platform for tracking inventory, managing deployments, and generating real-time stock insights.
                </p>
            </div>
        </div>

        <div class="col-md-5 col-lg-3 d-flex align-items-center bg-white h-100 shadow-lg position-relative z-3">
            <div class="login-container mx-auto">
                
                <div class="text-center mb-4">
                    <img src="{{ asset('assets/images/DepEdseal.png') }}" alt="DepEd Logo" style="width: 75px; height: 75px;" class="mb-3">
                    <h4 class="fw-bold mb-0 text-theme">DepEd ROV AMS</h4>
                </div>

                <div class="text-center mb-4">
                    <h5 class="fw-bold text-dark mb-1">Welcome Back</h5>
                    <small class="text-muted">Sign in to your account</small>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger p-2 small border-0 shadow-sm rounded-3">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ url('/login') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Email address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control py-2" value="{{ old('email') }}" placeholder="Email" required autofocus>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label small fw-bold text-dark mb-1">Password <span class="text-danger">*</span></label>
                        </div>
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control py-2 password-input" placeholder="••••••••" required>
                            <span class="input-group-text password-toggle" id="togglePassword">
                                <i class="fas fa-eye text-muted" id="eyeIcon"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label small text-muted" for="remember">Remember me</label>
                    </div>

                    <button type="submit" class="btn btn-theme w-100 rounded-3 py-2 fw-bold fs-6">Sign In</button>
                </form>
                
                <div class="text-center mt-5 text-muted small">
                    &copy; {{ date('Y') }} DepEd AMS.
                </div>
            </div>
        </div>
        
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function (e) {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            if(type === 'text') {
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>