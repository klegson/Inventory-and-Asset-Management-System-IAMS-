<style>
    .sidebar {
        width: 250px;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        background-color: #101954; /* DepEd Blue */
        color: white;
        transition: all 0.3s;
        z-index: 1000;
        overflow-y: auto;
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
    .nav-link {
        color: rgba(255, 255, 255, 0.8);
        padding: 15px 20px;
        font-size: 1rem;
        border-left: 4px solid transparent;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        text-decoration: none;
    }
    .nav-link i {
        width: 30px;
        font-size: 1.1rem;
    }
    .nav-link:hover, .nav-link.active {
        background-color: rgba(255, 255, 255, 0.1);
        color: #fff;
        border-left-color: #fca311; /* Accent Yellow */
    }
    .sidebar-footer {
        position: absolute;
        bottom: 0;
        width: 100%;
        padding: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
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
        <h5>AMS PERSONNEL</h5>
    </div>

    <nav class="nav flex-column mt-3">
        <a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt fa-fw"></i> Dashboard
        </a>

        <a href="{{ url('/asset-list') }}" class="nav-link {{ request()->is('asset-list*') ? 'active' : '' }}">
            <i class="fas fa-laptop fa-fw"></i> Assets List
        </a>

        <a href="{{ url('/supplies') }}" class="nav-link {{ request()->is('supplies*') ? 'active' : '' }}">
            <i class="fas fa-box-open fa-fw"></i> Supplies List
        </a>
        
        <a href="{{ url('/ris') }}" class="nav-link {{ request()->is('ris*') ? 'active' : '' }}">
            <i class="fas fa-clipboard-list fa-fw"></i> Requests (RIS)
        </a>
        
        <a href="{{ url('/barcodes') }}" class="nav-link {{ request()->is('barcodes*') ? 'active' : '' }}">
            <i class="fas fa-barcode fa-fw"></i> Barcodes List
        </a>

        <a href="{{ url('/transactions') }}" class="nav-link {{ request()->is('transactions*') ? 'active' : '' }}">
            <i class="fas fa-history fa-fw"></i> Transactions History
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="#" class="btn-logout" data-bs-toggle="modal" data-bs-target="#logoutModal">
            <i class="fas fa-sign-out-alt"></i> Logout
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