<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assets Management System - DepEd</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
            cursor: pointer;
        }

        .hero-section {
            background-image: url('{{ asset('assets/images/front.png') }}'); 
            height: 100vh;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            position: relative;
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(3, 37, 158, 0.6) 0%, rgba(175, 174, 177, 0.14) 100%);
        }

        .content-wrapper {
            position: relative;
            z-index: 2;
            color: white;
            pointer-events: none; 
        }

        .system-title {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 5px;
            text-shadow: 5px 5px 8px rgba(0,0,0,0.3);
        }

        .main-heading {
            font-size: 4.5rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 20px;
            text-shadow: 5px 5px 8px rgba(0,0,0,0.3);
        }

        .description-text {
            font-size: 1rem;
            max-width: 500px;
            opacity: 0.9;
            margin-bottom: 40px;
        }

        .logo-container {
            width: 450px; 
            height: 450px;
            perspective: 1000px; 
            margin: 0 auto; 
        }

        .spinner-box {
            position: relative;
            width: 100%;
            height: 100%;
            text-align: center;
            transition: transform 1s;
            transform-style: preserve-3d; 
            animation: spin-logo 10s linear infinite; 
        }

        .logo-face {
            position: absolute;
            width: 100%;
            height: 100%;
            -webkit-backface-visibility: hidden; 
            backface-visibility: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-face img {
            max-width: 100%;
            height: auto;
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.3));
        }

        .face-back {
            transform: rotateY(180deg);
        }

        @keyframes spin-logo {
            0% { transform: rotateY(0deg); }
            100% { transform: rotateY(360deg); }
        }

        .tap-msg {
            position: absolute;
            bottom: 50px;
            width: 100%;
            text-align: center;
            color: rgb(255, 255, 255);
            font-weight: 600;
            letter-spacing: 2px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 0.4; }
            50% { opacity: 1; }
            100% { opacity: 0.4; }
        }

        @media (max-width: 768px) {
            .main-heading { font-size: 3rem; }
            .hero-section { text-align: center; }
            .description-text { margin: 0 auto 30px auto; }
            .logo-container { width: 280px; height: 280px; margin-top: 40px; }
            span{
                margin-right: 50px;
            }
        }
        
        span {
            margin-left: 50px;
        }
    </style>
</head>
<body onclick="window.history.back();">

    <section class="hero-section d-flex align-items-center">
        <div class="overlay"></div>

        <div class="container content-wrapper">
            <div class="row align-items-center">
                <div class="col-lg-7 col-md-12">
                    <div class="system-title">Assets Management/Supply Section</div>
                    <h1 class="main-heading">
                        RIS Before <br> <span>Release</span>
                    </h1>
                </div>

                <div class="col-lg-5 col-md-12">
                    <div class="logo-container">
                        <div class="spinner-box">
                            <div class="logo-face face-front">
                                <img src="{{ asset('assets/images/DepEdseal.png') }}" alt="DepEd Emblem">
                            </div>
                            <div class="logo-face face-back">
                                <img src="{{ asset('assets/images/DepEd.png') }}" alt="DepEd Logo">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tap-msg">
            <i class="fas fa-hand-pointer me-2"></i> CLICK OR PRESS ANY KEY TO RETURN
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Listen for any keyboard key press to return to the previous page
        document.addEventListener('keydown', function(event) {
            window.history.back();
        });
    </script>
</body>
</html>