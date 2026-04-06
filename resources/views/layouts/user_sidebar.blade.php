<style>
    .sidebar {
        background-color: #101954;
        min-height: 100vh;
        color: white;
        display: flex;
        flex-direction: column;
        position: fixed; 
        width: 250px;
        z-index: 100;
        top: 0;
        left: 0;
    }

    .sidebar-header {
        padding: 20px;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .sidebar-header img {
        width: 60px;
        height: 60px;
        margin-bottom: 10px;
    }
    .sidebar-header h5 {
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0;
        letter-spacing: 1px;
    }

    .nav-menu {
        flex-grow: 1;
        margin-top: 20px;
    }

    .nav-link {
        color: rgba(255, 255, 255, 0.7);
        padding: 15px 25px;
        text-decoration: none;
        display: block;
        transition: 0.3s;
    }

    .nav-link i {
        margin-right: 10px;
        width: 20px;
        text-align: center;
    }

    .nav-link:hover, .nav-link.active {
        background-color: rgba(255, 255, 255, 0.1);
        color: white;
        border-left: 4px solid #0d6efd;
    }

    .logout-container {
        padding: 20px;
    }

    .btn-logout {
        width: 100%;
        background-color: #dc3545;
        color: white;
        border: none;
        padding: 10px;
        border-radius: 5px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        text-decoration: none;
    }

    .btn-logout:hover {
        background-color: #bb2d3b;
        color: white;
    }
</style>

<div class="sidebar">
    <div class="sidebar-header">
        <img src="{{ asset('assets/images/DepEdseal.png') }}" alt="Logo">
        <h5>DIVISION USER</h5>
    </div>
    
    <div class="nav-menu">
        <a href="{{ url('/user/dashboard') }}" class="nav-link {{ request()->is('user/dashboard*') ? 'active' : '' }}">
            <i class="fa-solid fa-gauge"></i> Dashboard
        </a>
        
        <a href="{{ url('/user/ris/create') }}" class="nav-link {{ request()->is('user/ris/create*') ? 'active' : '' }}">
            <i class="fa-solid fa-file-signature"></i> RIS
        </a>
        
        <a href="{{ url('/user/ris/history') }}" class="nav-link {{ request()->is('user/ris/history*') ? 'active' : '' }}">
            <i class="fa-solid fa-clock-rotate-left"></i> RIS History
        </a>
        
        <a href="{{ url('/user/ics') }}" class="nav-link {{ request()->is('user/ics*') ? 'active' : '' }}">
            <i class="fa-solid fa-file-invoice"></i> ICS
        </a>
        
        <a href="{{ url('/user/profile') }}" class="nav-link {{ request()->is('user/profile*') ? 'active' : '' }}">
            <i class="fa-solid fa-user"></i> Personal Information
        </a>
    </div>

    <div class="logout-container">
        <a href="#" class="btn-logout" data-bs-toggle="modal" data-bs-target="#logoutModal">
            <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
        </a>
    </div>
</div>

<div class="modal fade" id="logoutModal" tabindex="-1" style="z-index: 1051;">
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
    let idleTimer;
    
    // =====================================================================
    // EDIT IDLE TIME HERE:
    // Change the number below to set the idle timeout in milliseconds.
    // 1 Minute  = 60000
    // 5 Minutes = 300000
    // =====================================================================
    const idleTimeLimit = 60000; 

    function resetIdleTimer() {
        clearTimeout(idleTimer);
        idleTimer = setTimeout(() => {
            // Redirect to the idle screen when the time runs out
            window.location.href = "{{ url('/idle-screen') }}";
        }, idleTimeLimit);
    }

    // Listen for user interactions to reset the timer
    const activityEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];
    
    activityEvents.forEach(event => {
        document.addEventListener(event, resetIdleTimer, true);
    });

    // Start the timer when the page loads
    resetIdleTimer();
</script>