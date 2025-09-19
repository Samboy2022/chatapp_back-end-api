@extends('layouts.admin')

@section('title', 'Redirecting...')
@section('page-title', 'Redirecting to Realtime Settings')

@php
    function getGroupIcon($group) {
        $icons = [
            'general' => 'gear-fill',
            'pusher' => 'broadcast-pin',
            'reverb' => 'router-fill',
            'websocket' => 'wifi',
            'performance' => 'speedometer2',
            'redis' => 'database-fill'
        ];
        return $icons[$group] ?? 'gear-fill';
    }
    
    function getGroupColor($group) {
        $colors = [
            'general' => 'primary',
            'pusher' => 'success',
            'reverb' => 'info',
            'websocket' => 'warning',
            'performance' => 'danger',
            'redis' => 'secondary'
        ];
        return $colors[$group] ?? 'primary';
    }
@endphp

@section('content')
<div class="container-fluid">
    <!-- Redirect Notice -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-lg">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-arrow-right-circle text-primary" style="font-size: 4rem;"></i>
                    </div>
                    <h2 class="card-title text-primary mb-3">Page Moved</h2>
                    <p class="card-text text-muted mb-4">
                        The Broadcast Settings page has been upgraded and moved to a new location with enhanced features.
                    </p>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="{{ route('admin.realtime-settings.index') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-broadcast me-2"></i>
                            Go to Realtime Settings
                        </a>
                    </div>
                    <div class="mt-4">
                        <small class="text-muted">
                            You will be automatically redirected in <span id="countdown">5</span> seconds...
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-redirect after 5 seconds
let countdown = 5;
const countdownElement = document.getElementById('countdown');

const timer = setInterval(() => {
    countdown--;
    countdownElement.textContent = countdown;

    if (countdown <= 0) {
        clearInterval(timer);
        window.location.href = '{{ route("admin.realtime-settings.index") }}';
    }
}, 1000);

// Immediate redirect if user has been here before
if (localStorage.getItem('broadcast_settings_redirected')) {
    window.location.href = '{{ route("admin.realtime-settings.index") }}';
} else {
    localStorage.setItem('broadcast_settings_redirected', 'true');
}
</script>

@endsection
