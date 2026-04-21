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

    .sidebar-footer {
        margin-top: auto;
        padding: 20px;
        text-align: center;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.5);
        font-size: 0.85rem;
    }
</style>

<div class="sidebar">
    <div class="sidebar-header">
        <img src="{{ asset('assets/images/depedRovCirc.png') }}" alt="Logo">
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
    </div>

    <div class="sidebar-footer">
        &copy; {{ date('Y') }} DepEd AMS.
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
    const idleTimeLimit = 120000; 

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