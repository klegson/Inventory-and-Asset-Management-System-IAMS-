<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIMS - DepEd Home</title>
    
    <style>
        /* Set styles */
        * {
            box-sizing: border-box;
            transition: all 0.3s ease; 
        }

        /* Set body */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7fb; 
            margin: 0;
            padding: 0;
            overflow-x: hidden; 
            scroll-behavior: smooth;
        }

        body::-webkit-scrollbar {
        display: none;
        }

        /* Add fade animation */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Add text animation */
        @keyframes showText {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        /* Add hidden item */
        .animate-item {
            opacity: 0; 
        }
        
        /* Add show item */
        .animated .animate-item {
            animation: fadeInUp 0.8s ease forwards;
        }

        /* Add delay */
        .staggered-entrance:nth-child(1) .animate-item { animation-delay: 0.1s; }
        .staggered-entrance:nth-child(2) .animate-item { animation-delay: 0.3s; }
        .staggered-entrance:nth-child(3) .animate-item { animation-delay: 0.5s; }

        /* Set top header */
        .main-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 2rem;
            background-color: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky; 
            top: 0;
            z-index: 1001; 
        }

        /* Set header text */
        .main-header .text {
            color: #161db1;
            font-size: 20px;
            font-weight: bold;
            margin: 0;
        }

        /* Set menu */
        .nav {
            display: flex;
            gap: 25px;
            align-items: center;
        }

        /* Set links */
        .nav a {
            text-decoration: none;
            color: #4a5568;
            font-size: 16px;
            font-weight: 500;
        }

        /* Add hover */
        .nav a:hover {
            color: #161db1;
        }

        /* Set mobile button */
        .burger-btn { display: none; font-size: 28px; cursor: pointer; color: #161db1; }
        .close-btn { display: none; }

        /* Set main image */
        .lower-header {
            /* Add background */
            background-image: linear-gradient(rgba(30, 41, 150, 0.8), rgba(80, 120, 200, 0.7)), url('{{ asset('assets/images/DEPED.jpg') }}');
            background-size: cover; 
            background-position: center;
            background-repeat: no-repeat;
            
            /* Make image smaller */
            height: 35vh; 
            min-height: 300px; 
            position: relative; 
            overflow: hidden; 
        }

        /* Center both boxes */
        .hero-content {
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8vw; 
            top: 50%;
            left: 50%; 
            transform: translate(-50%, -50%); 
            width: 100%;
        }

        /* Set left side */
        .hero-left {
            display: flex;
            align-items: center;
            gap: 20px;
            animation: showText 1s ease forwards;
        }

        /* Add flip box */
        .flip-logo-container {
            width: 120px; 
            height: 120px;
            perspective: 1000px; 
            -webkit-perspective: 1000px; 
            margin: 0 auto;
        }

        /* Set flipper */
        .flipper {
            width: 100%;
            height: 100%;
            position: relative;
            transform-style: preserve-3d;
            -webkit-transform-style: preserve-3d; 
            /* Add flip */
            animation: flipAnimation 6s infinite linear;
        }

        /* Add spin */
        @keyframes flipAnimation {
            from { transform: rotateY(0deg); -webkit-transform: rotateY(0deg); }
            to { transform: rotateY(360deg); -webkit-transform: rotateY(360deg); }
        }

        /* Set images */
        .logo-front, .logo-back {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            backface-visibility: hidden; 
            -webkit-backface-visibility: hidden; 
            object-fit: contain;
            background-color: transparent; 
        }

        /* Turn image */
        .logo-back {
            transform: rotateY(180deg);
            -webkit-transform: rotateY(180deg); 
        }

        /* Set text content */
        .logo-text-content {
            display: flex;
            flex-direction: column;
            color: #ffffff; 
        }

        /* Set top line */
        .text-top-line { 
            font-size: 12px; 
            margin: 0; 
            padding-bottom: 5px; 
            border-bottom: 1px solid rgba(255, 255, 255, 0.5); 
            display: inline-block; 
            letter-spacing: 1px;
        }
        
        /* Set bottom line */
        .text-bottom-line { 
            font-size: 28px; 
            font-weight: bold; 
            margin: 0; 
            padding-top: 5px; 
            letter-spacing: 0.5px;
        }

        /* Add sub line */
        .text-sub-line { 
            font-size: 13px; 
            margin: 0; 
            padding-top: 2px; 
            display: inline-block; 
        }

        /* Set right side */
        .hero-right {
            display: flex;
            flex-direction: column;
            align-items: center; 
            animation: showText 1s ease forwards;
            animation-delay: 0.3s;
            opacity: 0;
        }

        /* Set big letters */
        .ams-title {
            font-family: 'Times New Roman', Times, serif;
            font-size: 90px;
            font-weight: normal;
            color: #ffffff;
            margin: 0;
            line-height: 1;
            letter-spacing: 1px;
        }

        /* Set small letters */
        .ams-subtitle {
            font-size: 18px;
            color: #ffffff;
            margin: 10px 0 25px 0;
            font-weight: 500;
        }

        /* Add buttons box */
        .hero-buttons {
            display: flex;
            gap: 15px;
        }

        /* Set button style */
        .hero-btn {
            padding: 10px 30px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 14px;
            text-decoration: none;
            text-align: center;
        }

        /* Add login button */
        .btn-login {
            background-color: #d1c4e9;
            color: #161db1;
        }

        /* Add about button */
        .btn-about {
            background-color: transparent;
            border: 1px solid #ffffff;
            color: #ffffff;
            cursor: pointer;
        }

        /* Add hover move */
        .btn-login:hover, .btn-about:hover {
            opacity: 0.9;
            transform: scale(1.05);
        }

        /* Add section space */
        .page-section {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 20px; 
        }

        /* Reduce top gap */
        #about-section {
            padding-top: 0px; 
        }

        /* Set Main Title */
        .welcome-title {
            color: #161db1;
            font-size: 28px; 
            text-align: left; 
            margin-bottom: 20px; 
            font-weight: bold;
            margin-top: 0;
        }

        /* Add intro text */
        .intro-text {
            text-align: center;
            color: #555;
            font-size: 20px;
            max-width: 800px;
            margin: 0 auto 30px auto;
            line-height: 1.6;
        }

        /* Add About System Title */
        .about-title {
            color: #000000;
            font-size: 36px;
            text-align: center;
            margin-bottom: 25px;
            font-weight: normal;
            margin-top: 0;
        }

        /* Add About Section box */
        .about-wrapper {
            display: flex;
            align-items: flex-start; 
            gap: 30px; 
            margin-bottom: 50px;
        }

        /* Add About Image box */
        .about-image-box {
            flex: 0 0 45%; 
            width: 45%;
        }

        .about-image-box img {
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
        }

        /* Add About Text box */
        .about-text-box {
            flex: 1;
            font-size: 15px; 
            color: #333;
            line-height: 1.6;
            text-align: justify; 
        }

        /* Fix margin */
        .about-text-box p {
            margin-top: 0;
            margin-bottom: 0;
        }

        /* Add title */
        .section-title {
            color: #161db1;
            font-size: 30px;
            text-align: center;
            margin-bottom: 40px;
            font-weight: bold;
        }

        /* Group timeline and image */
        .timeline-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 40px;
            margin-bottom: 60px;
        }

        /* Set left box */
        .timeline-side {
            flex: 1;
            width: 55%;
        }

        /* Set right box */
        .image-side {
            flex: 1;
            width: 45%;
            text-align: center;
        }

        /* Style the side picture */
        .side-image {
            max-width: 100%;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(22, 29, 177, 0.15);
            transition: transform 0.3s ease;
        }

        /* Add hover */
        .side-image:hover {
            transform: scale(1.02);
        }

        /* Set Timeline Box */
        .timeline {
            position: relative;
            width: 100%;
            padding: 10px 0;
        }

        /* Set item row */
        .timeline-item {
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            opacity: 0; 
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Slide layout */
        .timeline-item.left { transform: translateX(-50px); }
        .timeline-item.right { transform: translateX(50px); flex-direction: row-reverse; }
        .timeline-item.visible { opacity: 1; transform: translateX(0); }

        /* Set circle aesthetic */
        .timeline-circle {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: 45px; 
            height: 45px; 
            background: linear-gradient(135deg, #161db1, #5c6bc0);
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px; 
            font-weight: bold;
            border: 3px solid #ffffff;
            z-index: 3;
            box-shadow: 0 8px 15px rgba(22, 29, 177, 0.3);
        }

        /* Set box aesthetic */
        .timeline-content {
            width: 40%; 
            background-color: #ffffff;
            padding: 15px 20px; 
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
            border-left: 5px solid #161db1;
            position: relative;
            z-index: 2;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        /* Reverse border */
        .timeline-item.right .timeline-content {
            border-left: none;
            border-right: 5px solid #161db1;
            text-align: right;
        }

        /* Add hover */
        .timeline-content:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(22, 29, 177, 0.15);
        }

        /* Add box text */
        .timeline-content h4 {
            margin: 0 0 5px 0;
            font-size: 15px; 
            color: #161db1;
            font-weight: bold;
        }

        .timeline-content p {
            margin: 0;
            color: #64748b;
            font-size: 13px; 
            line-height: 1.5;
        }

        /* Add subtle horizontal line */
        .timeline-item::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 25%;
            height: 2px;
            background: linear-gradient(to right, #cbd5e1, transparent);
            z-index: 1;
            transform: translateY(-50%);
        }
        
        /* Flip line */
        .timeline-item.left::before { transform: translate(-100%, -50%); background: linear-gradient(to left, #cbd5e1, transparent); }
        .timeline-item.right::before { transform: translate(0, -50%); }

        /* Set FAQ style */
        .faq-container {
            max-width: 800px;
            margin: 0 auto 60px auto;
        }

        .faq-header-text {
            text-align: center;
            color: #555;
            font-size: 16px;
            margin-bottom: 40px;
            margin-top: -30px;
        }

        /* Set faq line */
        .faq-item {
            border-bottom: 1px solid #e2e8f0;
        }

        /* Set question button */
        .faq-question {
            width: 100%;
            background: transparent;
            border: none;
            text-align: left;
            padding: 20px 0;
            font-size: 16px;
            font-weight: 600;
            color: #111;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: inherit;
            outline: none;
        }

        /* Set arrow icon */
        .faq-icon {
            width: 20px;
            height: 20px;
            transition: transform 0.3s ease;
        }

        /* Turn arrow when open */
        .faq-item.active .faq-icon {
            transform: rotate(180deg);
        }

        /* Hide answer box */
        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        /* Set answer text */
        .faq-answer p {
            margin: 0 0 20px 0;
            color: #555;
            font-size: 15px;
            line-height: 1.6;
        }

        /* Show answer box when clicked */
        .faq-item.active .faq-answer {
            max-height: 150px; 
        }

        /* Update footer */
        .site-footer {
            background-color: #111827;
            color: #ffffff;
            padding: 60px 20px 30px 20px;
        }

        .footer-content {
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 40px;
        }

        .footer-section {
            flex: 1;
            min-width: 250px;
        }

        .footer-section h3 {
            color: #d1c4e9;
            font-size: 20px;
            margin-top: 0;
            margin-bottom: 20px;
        }

        .footer-section p {
            color: #9ca3af;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-section ul li {
            margin-bottom: 10px;
        }

        .footer-section ul li a {
            color: #9ca3af;
            text-decoration: none;
            transition: color 0.3s ease;
            font-size: 14px;
        }

        .footer-section ul li a:hover {
            color: #ffffff;
        }

        .footer-bottom {
            max-width: 1100px;
            margin: 40px auto 0 auto;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            text-align: center;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .footer-bottom p {
            margin: 0;
            color: #9ca3af;
            font-size: 14px;
        }

        .developer-credit {
            text-align: right;
        }

        .developer-credit span {
            display: block;
            font-size: 12px;
            color: #6b7280;
        }

        .developer-credit strong {
            font-size: 16px;
            color: #ffffff;
        }

        /* Add Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6); 
            z-index: 2000;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal-active {
            opacity: 1;
            visibility: visible;
        }

        /* Add Modal Box */
        .modal-content {
            position: relative;
            background-color: #ffffff;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            transform: scale(0.95);
            transition: all 0.3s ease;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .modal-active .modal-content {
            transform: scale(1);
        }

        /* Add Modal Header */
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 30px;
            background-color: #ffffff;
            border-bottom: 2px solid #f0f2f5;
        }

        .modal-header h3 {
            margin: 0;
            color: #161db1;
            font-size: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Add Close Button */
        .close-modal {
            color: #888;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s ease;
            line-height: 1;
        }

        .close-modal:hover {
            color: #d32f2f;
        }

        /* Add Modal Body */
        .modal-body {
            padding: 20px;
            overflow-y: auto;
            text-align: center;
            background-color: #f8fafc;
        }

        /* Add image inside Modal */
        .modal-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            display: block;
            margin: 0 auto;
        }

        /* Phone view */
        @media (max-width: 850px) {
            /* Set phone menu */
            .burger-btn { display: block; }
            .close-btn { display: block; font-size: 30px; cursor: pointer; color: #000000; align-self: flex-end; margin-bottom: 20px; }
            .nav { position: fixed; top: 0; right: -250px; width: 200px; height: 100vh; background-color: #ffffff; flex-direction: column; align-items: flex-start; padding: 20px; box-shadow: -2px 0 5px rgba(0, 0, 0, 0.2); z-index: 1000; }
            .nav.active { right: 0; }
            .nav a { padding: 10px 0; width: 100%; border-bottom: 1px solid #eeeeee; }

            /* Set phone image */
            .lower-header { height: auto; padding: 50px 0; min-height: auto; }

            /* Center phone content */
            .hero-content { 
                flex-direction: column; 
                gap: 20px; 
                text-align: center;
                position: relative;
                transform: none;
                top: 0;
                left: 0;
                margin: 0 auto;
            }
            
            /* Fix phone parts */
            .hero-left { flex-direction: column; text-align: center; gap: 15px; }
            .hero-right { align-items: center; text-align: center; }
            .logo-text-content { align-items: center; }
            .text-bottom-line { font-size: 24px; }
            .ams-title { font-size: 70px; margin-top: 10px; }
            .ams-subtitle { font-size: 16px; margin-bottom: 20px; }
            .flip-logo-container { width: 100px; height: 100px; margin: 0 auto; }

            /* Fix phone about */
            .about-wrapper { flex-direction: column; gap: 20px; }
            .about-image-box, .about-text-box { width: 100%; flex: auto; }
            .about-text-box { font-size: 15px; }

            /* Fix phone wrapper */
            .timeline-wrapper { flex-direction: column; gap: 60px; }
            .timeline-side, .image-side { width: 100%; }

            /* Fix phone timeline */
            .timeline-item.left, .timeline-item.right { 
                flex-direction: column; 
                align-items: flex-start; 
                padding-left: 60px; 
                margin-bottom: 30px; 
                transform: translateX(-30px); 
            }
            .timeline-circle { 
                left: 10px; 
                transform: translateY(-50%); 
                top: 50%; 
            }
            .timeline-content { 
                width: 100%; 
                border-left: 5px solid #161db1 !important; 
                border-right: none !important; 
                text-align: left !important; 
            }
            .timeline-item::before { display: none; }
            
            /* Fix footer */
            .footer-bottom {
                flex-direction: column;
                text-align: center;
            }
            .developer-credit {
                text-align: center;
            }
        }
    </style>
</head>
<body id="top">
    
    <header class="main-header">
        <div class="logo-container">
            <h1 class="text"> Asset Inventory Management System</h1>
        </div>
        
        <div class="burger-btn" id="burgerBtn">☰</div>

        <nav class="nav" id="navMenu">
            <div class="close-btn" id="closeBtn">&times;</div>
            <a href="#top" class="home">Home</a>
            <a href="#" class="About open-modal-btn">Organizational Chart</a>
        </nav>
    </header>

    <div class="lower-header start-animation-header"> 
        
        <div class="hero-content">
            <div class="hero-left">
                <div class="flip-logo-container">
                    <div class="flipper">
                        <img src="{{ asset('assets/images/logo_two.webp') }}" alt="Logo Front" class="logo-front">
                        <img src="{{ asset('assets/images/DepEdseal.png') }}" alt="Logo Back" class="logo-back">
                    </div>
                </div>
                
                <div class="logo-text-content">
                    <p class="text-top-line">REPUBLIC OF THE PHILIPPINES</p>
                    <p class="text-bottom-line">DEPARTMENT OF EDUCATION</p>
                    <p class="text-sub-line">Regional Office V</p>
                </div>
            </div>

            <div class="hero-right">
                <h1 class="ams-title">AMS</h1>
                <p class="ams-subtitle">Asset Management Section</p>
                <div class="hero-buttons">
                    
                    @auth
                        @php
                            $role = strtolower(Auth::user()->role);
                            $dashUrl = '/dashboard';
                            if($role === 'admin') $dashUrl = '/admin/dashboard';
                            elseif($role === 'frontuser') $dashUrl = '/user/dashboard';
                        @endphp
                        <a href="{{ url($dashUrl) }}" class="hero-btn btn-login">Access Now</a>
                    @else
                        <a href="{{ url('/login') }}" class="hero-btn btn-login">LOGIN</a>
                    @endauth

                    <a href="#timeline-section" class="hero-btn btn-about">HOW IT WORKS</a>
                </div>
            </div>
        </div>

    </div>

    <main id="home-section" class="page-section">
        
        <h2 class="welcome-title animate-item">RIS BEFORE RELEASED - Home Page</h2> 
        
        <div id="about-section">
            <div class="about-wrapper">
                <div class="about-image-box animate-item">
                    <img src="{{ asset('assets/images/screenS.png') }}" alt="RIS Before Release Banner">
                </div>
                
                <div class="about-text-box animate-item">
                    <p>The Asset Inventory Management System is an interactive web-based solution developed to modernize the inventory and requisition protocols of the Department of Education ROV. It eliminates manual redundancies by integrating Purchase Order tracking, bar code-assisted inventory management, and a structured Requisition and Issue Slip (RIS) workflow. By connecting Division Users, Asset Management Staff, and Administrative Officials into a single digital ecosystem, the system enforces strict accountability. It features real-time stock monitoring, automated unit calculations, and digitized approval routing, ultimately optimizing the allocation of government resources and ensuring accurate, up-to-date transaction logs.

By centralizing these essential tasks, the platform fosters a more transparent environment where staff can focus on impactful work. It serves as a reliable foundation for data-driven decisions and long-term efficiency.</p>
                </div>
            </div>
        </div>

        <h2 id="timeline-section" class="section-title animate-item">RIS Processing Timeline</h2>

        <div class="timeline-wrapper">
            
            <div class="timeline-side">
                <div class="timeline">
                    
                    <div class="timeline-item left">
                        <div class="timeline-circle">1</div>
                        <div class="timeline-content">
                            <h4>RIS</h4>
                            <p>Submit the Requisition and Issue Slip form.</p>
                        </div>
                    </div>

                    <div class="timeline-item right">
                        <div class="timeline-circle">2</div>
                        <div class="timeline-content">
                            <h4>Review Stocks</h4>
                            <p>Wait for review of staff for stocks availability.</p>
                        </div>
                    </div>

                    <div class="timeline-item left">
                        <div class="timeline-circle">3</div>
                        <div class="timeline-content">
                            <h4>Forward to Head</h4>
                            <p>The request is forwarded to the Head of Office.</p>
                        </div>
                    </div>

                    <div class="timeline-item right">
                        <div class="timeline-circle">4</div>
                        <div class="timeline-content">
                            <h4>Approval</h4>
                            <p>Wait for the final approval of the Head.</p>
                        </div>
                    </div>

                    <div class="timeline-item left">
                        <div class="timeline-circle">5</div>
                        <div class="timeline-content">
                            <h4>Release</h4>
                            <p>Supplies are successfully processed and released.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="image-side animate-item">
                <img src="{{ asset('assets/images/ris.png') }}" alt="Supply Chain Process" class="side-image">
            </div>

        </div>

        <h3 class="section-title animate-item">Frequently Asked Questions</h3>
        <p class="faq-header-text animate-item">Get answers to common questions about using the Asset Inventory Management System.</p>
        
        <div class="faq-container animate-item">
            <div class="faq-item">
                <button class="faq-question">
                    How do I access my AMS account?
                    <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="#0084ff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="faq-answer">
                    <p>Simply log in with your DepEd credentials. The office owner will set up your account when you begin working.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question">
                    Can I request supplies from my mobile device?
                    <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="#0084ff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="faq-answer">
                    <p>Yes, the system is mobile-friendly. You can submit and check your Requisition and Issue Slip (RIS) from any device.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question">
                    What if the item I want is out of stock?
                    <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="#0084ff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="faq-answer">
                    <p>The system will tag your request as pending. The supply staff will automatically notify you when new items arrive in the inventory.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question">
                    What if I need help with the platform?
                    <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="#0084ff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="faq-answer">
                    <p>If you experience any issues, please contact the Asset Management Supply Section directly for technical assistance.</p>
                </div>
            </div>
        </div>

    </main>

    <footer class="site-footer">
        <div class="footer-content animate-item">
            <div class="footer-section">
                <h3>AMS</h3>
                <p>A comprehensive web-based solution developed to modernize the inventory and requisition protocols of the Department of Education.</p>
            </div>
            
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#top">Home Page</a></li>
                    <li><a href="#" class="open-modal-btn">Organizational Chart</a></li>
                    
                    <li>
                        @auth
                            @php
                                $role = strtolower(Auth::user()->role);
                                $dashUrl = '/dashboard';
                                if($role === 'admin') $dashUrl = '/admin/dashboard';
                                elseif($role === 'frontuser') $dashUrl = '/user/dashboard';
                            @endphp
                            <a href="{{ url($dashUrl) }}">Dashboard</a>
                        @else
                            <a href="{{ url('/login') }}">System Login</a>
                        @endauth
                    </li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p>Asset Management Section</p>
                <p>Department of Education - Regional Office V</p>
                <p>Rawis, Legazpi City, Philippines</p>
            </div>
        </div>
    
        <div class="footer-bottom animate-item">
            <p>&copy; {{ date('Y') }} Department of Education - Regional Office V. All Rights Reserved.</p>
            <div class="developer-credit">
                <span>Developer of the System</span>
                <strong>OJT-2026</strong>
                <span>Kristian Lex Dela Cruz, Jhoanna Marie Rimpola, Karen Ocbian</span>
            </div>
        </div>
    </footer>

    <div class="modal-overlay" id="aboutModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Organizational Chart</h3>
                <span class="close-modal" id="closeAboutModal">&times;</span>
            </div>
            <div class="modal-body">
                <img src="{{ asset('assets/images/organizational_chart.png') }}" alt="Designation Chart" class="modal-image">
            </div>
        </div>
    </div>

    <script>
        const burgerBtn = document.getElementById('burgerBtn');
        const navMenu = document.getElementById('navMenu');
        const closeBtn = document.getElementById('closeBtn');

        /* Add click menu */
        burgerBtn.addEventListener('click', function() {
            navMenu.classList.add('active');
        });

        /* Add close menu */
        closeBtn.addEventListener('click', function() {
            navMenu.classList.remove('active');
        });

        /* Add click link */
        const navLinks = document.querySelectorAll('.nav a:not(.open-modal-btn)');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
            });
        });

        /* Add faq click drop down */
        const faqQuestions = document.querySelectorAll('.faq-question');

        faqQuestions.forEach(question => {
            question.addEventListener('click', () => {
                /* Find the box holding this question */
                const item = question.parentElement;
                
                /* Open or close the answer */
                item.classList.toggle('active');
            });
        });

        /* Add modal functions */
        const modal = document.getElementById('aboutModal');
        const openModalBtns = document.querySelectorAll('.open-modal-btn');
        const closeModalBtn = document.getElementById('closeAboutModal');

        /* Open modal */
        openModalBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault(); 
                modal.classList.add('modal-active');
                /* Close menu if open */
                navMenu.classList.remove('active');
            });
        });

        /* Close modal */
        closeModalBtn.addEventListener('click', function() {
            modal.classList.remove('modal-active');
        });

        /* Close outside image */
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('modal-active');
            }
        });

        /* Add scroll watch */
        const options = {
            threshold: 0.1, 
            rootMargin: "0px 0px -50px 0px" 
        };

        const callback = (entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    /* Check if timeline item */
                    if (entry.target.classList.contains('timeline-item')) {
                        entry.target.classList.add('visible');
                    } else {
                        entry.target.classList.add('animated');
                    }
                    observer.unobserve(entry.target);
                }
            });
        };

        const observer = new IntersectionObserver(callback, options);

        /* Watch items */
        const targets = document.querySelectorAll('.page-section, .welcome-title, .intro-text, .about-title, .about-wrapper, .faq-container, .faq-header-text, .site-footer, .section-title, .timeline-item, .image-side');

        targets.forEach(target => {
            observer.observe(target);
        });
    </script>

</body>
</html>