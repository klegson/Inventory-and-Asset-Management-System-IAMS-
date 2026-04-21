@php
    $initials = strtoupper(substr($user->firstname, 0, 1) . substr($user->lastname, 0, 1));
    $statusColor = ($user->status == 'Active') ? 'success' : 'danger';
    
    // Updated Role Names
    $roleName = 'Personnel';
    if(strtolower($user->role) == 'admin') $roleName = 'Admin';
    if(strtolower($user->role) == 'frontuser') $roleName = 'User';
@endphp

<div class="modal-header border-0 pb-0" style="background-color: #101954; height: 100px;">
    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body text-center px-4 pt-0 pb-4 position-relative">
    
    <div class="rounded-circle d-inline-flex justify-content-center align-items-center bg-white shadow-sm overflow-hidden" 
         style="width: 100px; height: 100px; margin-top: -50px; border: 4px solid white;">
        
        @if(!empty($user->image))
            <img src="{{ asset('uploads/users/' . $user->image) }}" alt="Profile Photo" style="width: 100%; height: 100%; object-fit: cover;">
        @else
            <div class="rounded-circle d-flex justify-content-center align-items-center text-white fw-bold fs-2 w-100 h-100" 
                 style="background: linear-gradient(135deg, #101954, #0a4d9c);">
                {{ $initials }}
            </div>
        @endif

    </div>

    <h4 class="fw-bold mt-3 mb-2 text-dark">{{ $user->firstname }} {{ $user->lastname }}</h4>
    
    <div class="mb-4">
        <span class="badge bg-{{ $roleName == 'Admin' ? 'dark' : 'primary' }} me-1 px-3 py-2">{{ $roleName }}</span>
        <span class="badge bg-{{ $statusColor }} px-3 py-2 border border-{{ $statusColor }}-subtle">{{ $user->status }}</span>
    </div>

    <div class="text-start bg-light rounded p-3 mb-3 border">
        <div class="row mb-2">
            <div class="col-5 text-muted small fw-bold text-uppercase">Department</div>
            <div class="col-7 fw-semibold">{{ $user->department ?: 'Not provided' }}</div>
        </div>
        <div class="row mb-2">
            <div class="col-5 text-muted small fw-bold text-uppercase">Email Address</div>
            <div class="col-7 fw-semibold text-primary" style="word-break: break-word;">{{ $user->email }}</div>
        </div>
        <div class="row">
            <div class="col-5 text-muted small fw-bold text-uppercase">Joined Date</div>
            <div class="col-7 fw-semibold">{{ \Carbon\Carbon::parse($user->created_at)->format('F d, Y') }}</div>
        </div>
    </div>

</div>