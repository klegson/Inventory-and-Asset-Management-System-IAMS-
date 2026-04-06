@php
    $user = auth()->user();
    $initials = 'U';
    if($user) {
        $initials = strtoupper(substr($user->firstname ?? '', 0, 1) . substr($user->lastname ?? '', 0, 1));
        if(empty($initials)) $initials = 'U';
    }
@endphp

<style>
    .main-content { padding-top: 90px !important; }
    .top-header {
        position: fixed; top: 0; right: 0; left: 250px; height: 70px; background-color: #ffffff; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.03); z-index: 1040; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; transition: all 0.3s;
    }
    .global-search-container { position: relative; width: 450px; }
    .global-search-container input {
        width: 100%; border-radius: 20px; padding: 10px 40px 10px 45px; background-color: #f4f6f9; border: 1px solid #e0e0e0; font-size: 0.95rem; transition: all 0.2s;
    }
    .global-search-container input:focus { background-color: #fff; box-shadow: 0 0 0 4px rgba(16, 25, 84, 0.1); border-color: #101954; outline: none; }
    .global-search-container .search-icon { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #6c757d; }
    .global-search-container .clear-icon { position: absolute; right: 18px; top: 50%; transform: translateY(-50%); color: #6c757d; cursor: pointer; display: none; z-index: 10; }
    .global-search-container .clear-icon:hover { color: #dc3545; }
    .search-results-dropdown {
        position: absolute; top: 50px; left: 0; width: 100%; background: white; border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15); max-height: 400px; overflow-y: auto; display: none; z-index: 9999; border: 1px solid #e0e0e0;
    }
    .search-result-item { padding: 12px 20px; border-bottom: 1px solid #f4f6f9; display: block; text-decoration: none; color: #333; transition: background-color 0.2s; }
    .search-result-item:last-child { border-bottom: none; }
    .search-result-item:hover { background-color: #f8f9fa; }
    .search-result-title { font-weight: 700; font-size: 0.95rem; color: #101954; margin-bottom: 2px; }
    .search-result-meta { font-size: 0.8rem; color: #6c757d; }
    .search-result-badge { font-size: 0.7rem; padding: 2px 6px; border-radius: 4px; background: #e9ecef; color: #495057; float: right; }
    .header-actions { display: flex; align-items: center; gap: 25px; }
    .notification-btn { position: relative; color: #495057; font-size: 1.3rem; cursor: pointer; transition: color 0.2s; }
    .notification-btn:hover { color: #101954; }
    .notification-badge {
        position: absolute; top: -6px; right: -8px; background-color: #28a745; color: white;
        font-size: 0.65rem; font-weight: bold; padding: 2px 5px; border-radius: 10px; border: 2px solid white;
    }
    .user-profile { display: flex; align-items: center; gap: 10px; cursor: pointer; }
    .user-avatar {
        width: 42px; height: 42px; border-radius: 50%; background-color: #000; color: #fff;
        display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.1rem; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    @media (max-width: 768px) { .top-header { left: 0; padding: 0 15px; } .global-search-container { width: 200px; } .main-content { padding-top: 80px !important; } }
    @media print { .top-header { display: none !important; } }
</style>

<div class="top-header no-print">
    <div class="global-search-container">
        <i class="fas fa-search search-icon"></i>
        <input type="text" id="globalSearchInput" placeholder="Search inventory, requests, barcodes..." autocomplete="off">
        <i class="fas fa-times clear-icon" id="globalClearBtn" title="Clear Search"></i>
        <div class="search-results-dropdown" id="globalSearchResults"></div>
    </div>
    <div class="header-actions">
        <div class="notification-btn" title="Notifications"><i class="far fa-bell"></i><span class="notification-badge">24</span></div>
        <div class="user-profile" title="{{ $user->firstname ?? 'Admin' }} {{ $user->lastname ?? '' }}"><div class="user-avatar">{{ $initials }}</div></div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById('globalSearchInput');
    const clearBtn = document.getElementById('globalClearBtn');
    const resultsDropdown = document.getElementById('globalSearchResults');
    let debounceTimer;

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

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !resultsDropdown.contains(e.target) && !clearBtn.contains(e.target)) {
            resultsDropdown.style.display = 'none';
        }
    });

    searchInput.addEventListener('focus', function() {
        if (this.value.trim().length > 0 && resultsDropdown.innerHTML.trim() !== '') { resultsDropdown.style.display = 'block'; }
    });

    function fetchSearchResults(query) {
        if(query.length < 2) return;
        resultsDropdown.innerHTML = '<div class="p-3 text-center text-muted small"><i class="fas fa-spinner fa-spin me-2"></i>Searching...</div>';
        resultsDropdown.style.display = 'block';
        
        // CRITICAL: Hitting the ADMIN endpoint
        const fetchUrl = `{{ url('/admin/global-search') }}?q=${encodeURIComponent(query)}`;

        fetch(fetchUrl, { headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'} })
        .then(response => { if (!response.ok) throw new Error('Network error'); return response.json(); })
        .then(data => {
            resultsDropdown.innerHTML = '';
            if (data.length === 0) { resultsDropdown.innerHTML = `<div class="p-3 text-center text-muted small">No results found for "${query}"</div>`; return; }
            data.forEach(item => {
                const html = `<a href="${item.url}" class="search-result-item"><span class="search-result-badge">${item.type}</span><div class="search-result-title">${item.title}</div><div class="search-result-meta">${item.meta}</div></a>`;
                resultsDropdown.insertAdjacentHTML('beforeend', html);
            });
        }).catch(error => { resultsDropdown.innerHTML = '<div class="p-3 text-center text-danger small">Error loading results.</div>'; });
    }

    // --- MAGICAL AUTO-HIGHLIGHT SCROLL ---
    const urlParams = new URLSearchParams(window.location.search);
    const hl = urlParams.get('hl');
    if (hl) {
        setTimeout(() => {
            const rows = document.querySelectorAll('table tbody tr');
            rows.forEach(row => {
                if (row.innerText.includes(hl)) {
                    row.style.backgroundColor = '#fff3cd'; // Highlight color
                    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    // Fade out the highlight after 3 seconds
                    setTimeout(() => { 
                        row.style.transition = 'background-color 2s ease';
                        row.style.backgroundColor = 'transparent'; 
                    }, 3000);
                }
            });
        }, 300); // Small delay to let the table render
    }
});
</script>