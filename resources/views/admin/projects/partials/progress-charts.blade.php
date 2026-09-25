<link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">
<div class="row">
    {{-- Progress Trend Chart --}}
    <div class="col-md-6">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-area"></i> Progress Trend (Last 7 Days)
                </h3>
            </div>
            <div class="card-body">
                <canvas id="progressTrendChart" style="height: 250px;"></canvas>
            </div>
        </div>
    </div>

    {{-- Phase Breakdown Chart --}}
    <div class="col-md-6">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie"></i> Phase Progress Breakdown
                </h3>
            </div>
            <div class="card-body">
                <canvas id="phaseBreakdownChart" style="height: 250px;"></canvas>
            </div>
        </div>
    </div>
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== Progress Trend Chart =====
    var ctx1 = document.getElementById('progressTrendChart').getContext('2d');
    var progressTrendChart = new Chart(ctx1, {
        type: 'line',
        data: {
            labels: @json($progressTrend['labels']),
            datasets: [{
                label: 'Progress %',
                data: @json($progressTrend['data']),
                fill: true,
                borderColor: '#17a2b8',
                backgroundColor: 'rgba(23, 162, 184, 0.1)',
                tension: 0.4,
                pointBackgroundColor: '#17a2b8',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
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
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });

    // ===== Phase Breakdown Chart =====
    var ctx2 = document.getElementById('phaseBreakdownChart').getContext('2d');
    var phaseBreakdownChart = new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: @json($phaseBreakdown['labels']),
            datasets: [{
                label: 'Progress %',
                data: @json($phaseBreakdown['data']),
                backgroundColor: @json($phaseBreakdown['colors']),
                borderColor: '#ffffff',
                borderWidth: 2,
                borderRadius: 4,
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
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush