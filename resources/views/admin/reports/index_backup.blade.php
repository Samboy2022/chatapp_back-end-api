@extends('layouts.admin')

@section('title', 'Reports & Analytics')
@section('page-title', 'Reports & Analytics Dashboard')

@section('toolbar-buttons')
<div class="btn-group">
    <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
        <i class="bi bi-download"></i> Export Reports
    </button>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="{{ route('admin.reports.export', ['type' => 'users']) }}">
            <i class="bi bi-people"></i> Users Report</a></li>
        <li><a class="dropdown-item" href="{{ route('admin.reports.export', ['type' => 'messages']) }}">
            <i class="bi bi-envelope"></i> Messages Report</a></li>
        <li><a class="dropdown-item" href="{{ route('admin.reports.export', ['type' => 'chats']) }}">
            <i class="bi bi-chat-left-text"></i> Chats Report</a></li>
        <li><a class="dropdown-item" href="{{ route('admin.reports.export', ['type' => 'calls']) }}">
            <i class="bi bi-telephone"></i> Calls Report</a></li>
    </ul>
</div>
<a href="{{ route('admin.reports.users') }}" class="btn btn-outline-primary">
    <i class="bi bi-people"></i> User Reports
</a>
<a href="{{ route('admin.reports.activity') }}" class="btn btn-outline-success">
    <i class="bi bi-activity"></i> Activity Reports
</a>
<a href="{{ route('admin.reports.performance') }}" class="btn btn-outline-warning">
    <i class="bi bi-speedometer2"></i> Performance
</a>
@endsection

@section('content')
<!-- Quick Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-1">{{ $stats['total_users'] }}</h4>
                        <p class="card-text">Total Users</p>
                        <small class="text-white-50">
                            <i class="bi bi-arrow-up"></i> {{ $stats['weekly_users'] }} this week
                        </small>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-people" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-1">{{ App\Models\Message::count() }}</h4>
                        <p class="card-text">Total Messages</p>
                        <small class="text-white-50">
                            <i class="bi bi-arrow-up"></i> {{ App\Models\Message::whereDate('created_at', today())->count() }} today
                        </small>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-envelope" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-1">{{ App\Models\Chat::count() }}</h4>
                        <p class="card-text">Total Chats</p>
                        <small class="text-white-50">
                            <i class="bi bi-arrow-up"></i> {{ App\Models\Chat::where('type', 'group')->count() }} groups
                        </small>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-chat-left-text" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-1">{{ App\Models\Call::count() }}</h4>
                        <p class="card-text">Total Calls</p>
                        <small class="text-white-50">
                            <i class="bi bi-check-circle"></i> {{ App\Models\Call::where('status', 'ended')->count() }} completed
                        </small>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-telephone" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up"></i> Activity Overview (Last 30 Days)
                </h5>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-primary active" data-chart="messages">Messages</button>
                    <button type="button" class="btn btn-outline-primary" data-chart="users">Users</button>
                    <button type="button" class="btn btn-outline-primary" data-chart="calls">Calls</button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="activityChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-pie-chart"></i> User Status Distribution
                </h5>
            </div>
            <div class="card-body">
                <canvas id="userStatusChart" height="300"></canvas>
                <div class="mt-3">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h6 class="text-success">{{ $userStatusDistribution['active_users'] }}</h6>
                                <small class="text-muted">Active Users</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h6 class="text-muted">{{ $userStatusDistribution['inactive_users'] }}</h6>
                            <small class="text-muted">Inactive Users</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Real-time Metrics -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock"></i> Real-time Activity
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="d-flex flex-column align-items-center">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
                                <i class="bi bi-people" style="font-size: 1.5rem;"></i>
                            </div>
                            <h6 class="mb-1">{{ App\Models\User::where('last_seen_at', '>=', now()->subMinutes(5))->count() }}</h6>
                            <small class="text-muted">Online Now</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="d-flex flex-column align-items-center">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
                                <i class="bi bi-envelope" style="font-size: 1.5rem;"></i>
                            </div>
                            <h6 class="mb-1">{{ App\Models\Message::whereDate('created_at', today())->count() }}</h6>
                            <small class="text-muted">Today's Messages</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="d-flex flex-column align-items-center">
                            <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
                                <i class="bi bi-camera-reels" style="font-size: 1.5rem;"></i>
                            </div>
                            <h6 class="mb-1">{{ App\Models\Status::where('expires_at', '>', now())->count() }}</h6>
                            <small class="text-muted">Active Stories</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-speedometer2"></i> System Health
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small">Server Performance</span>
                        <span class="small text-success">Excellent</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: 92%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small">Database Response</span>
                        <span class="small text-success">Good</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: 88%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small">Storage Usage</span>
                        <span class="small text-warning">65%</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-warning" style="width: 65%"></div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <button class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-speedometer2"></i> View Details
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Reports Grid -->
<div class="row">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="bi bi-people-fill text-primary" style="font-size: 3rem;"></i>
                </div>
                <h5 class="card-title">User Analytics</h5>
                <p class="card-text text-muted">
                    Comprehensive user growth, engagement metrics, registration trends, and user behavior analysis.
                </p>
                <div class="mt-auto">
                    <button class="btn btn-primary">
                        <i class="bi bi-arrow-right"></i> View User Reports
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="bi bi-activity text-success" style="font-size: 3rem;"></i>
                </div>
                <h5 class="card-title">Activity Reports</h5>
                <p class="card-text text-muted">
                    Message activity, chat engagement, call statistics, and overall platform usage patterns.
                </p>
                <div class="mt-auto">
                    <button class="btn btn-success">
                        <i class="bi bi-arrow-right"></i> View Activity Reports
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="bi bi-speedometer2 text-warning" style="font-size: 3rem;"></i>
                </div>
                <h5 class="card-title">Performance Metrics</h5>
                <p class="card-text text-muted">
                    System performance, server health, database metrics, and application monitoring insights.
                </p>
                <div class="mt-auto">
                    <button class="btn btn-warning">
                        <i class="bi bi-arrow-right"></i> View Performance
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Activity Chart
const activityCtx = document.getElementById('activityChart').getContext('2d');
const activityChart = new Chart(activityCtx, {
    type: 'line',
    data: {
        labels: Array.from({length: 30}, (_, i) => {
            const date = new Date();
            date.setDate(date.getDate() - (29 - i));
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }),
        datasets: [{
            label: 'Messages',
            data: [45, 59, 80, 81, 56, 55, 40, 48, 62, 75, 88, 92, 67, 78, 85, 90, 95, 88, 76, 82, 89, 94, 78, 85, 91, 88, 82, 79, 86, 93],
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    display: true,
                    color: 'rgba(0,0,0,0.1)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// User Status Chart
const userStatusCtx = document.getElementById('userStatusChart').getContext('2d');
const userStatusChart = new Chart(userStatusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Active Users', 'Inactive Users'],
        datasets: [{
            data: [{{ $userStatusDistribution['active_users'] }}, {{ $userStatusDistribution['inactive_users'] }}],
            backgroundColor: ['#28a745', '#6c757d'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true
                }
            }
        },
        cutout: '60%'
    }
});

// Chart switching functionality
document.querySelectorAll('[data-chart]').forEach(button => {
    button.addEventListener('click', function() {
        // Update active button
        document.querySelectorAll('[data-chart]').forEach(btn => btn.classList.remove('active'));
        this.classList.add('active');
        
        // Update chart data based on selection
        const chartType = this.dataset.chart;
        let newData, newLabel, newColor;
        
        switch(chartType) {
            case 'messages':
                newData = [45, 59, 80, 81, 56, 55, 40, 48, 62, 75, 88, 92, 67, 78, 85, 90, 95, 88, 76, 82, 89, 94, 78, 85, 91, 88, 82, 79, 86, 93];
                newLabel = 'Messages';
                newColor = '#28a745';
                break;
            case 'users':
                newData = [12, 15, 18, 16, 14, 17, 19, 21, 18, 22, 25, 23, 26, 28, 24, 27, 29, 26, 24, 28, 30, 32, 28, 31, 33, 30, 28, 32, 34, 36];
                newLabel = 'New Users';
                newColor = '#007bff';
                break;
            case 'calls':
                newData = [8, 12, 15, 11, 9, 14, 16, 18, 13, 17, 20, 19, 15, 18, 21, 17, 19, 22, 16, 20, 23, 21, 18, 22, 24, 20, 19, 23, 25, 27];
                newLabel = 'Calls';
                newColor = '#ffc107';
                break;
        }
        
        activityChart.data.datasets[0].data = newData;
        activityChart.data.datasets[0].label = newLabel;
        activityChart.data.datasets[0].borderColor = newColor;
        activityChart.data.datasets[0].backgroundColor = newColor + '20';
        activityChart.update();
    });
});
</script>
@endpush 