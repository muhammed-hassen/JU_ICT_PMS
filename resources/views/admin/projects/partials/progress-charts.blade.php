<div class="mt-6 grid gap-5 lg:grid-cols-2">
    <x-ui.card title="Progress trend" description="Overall progress over the last 7 days.">
        <div class="relative h-64">
            <canvas id="progressTrendChart" role="img" aria-label="Progress trend chart"></canvas>
        </div>
    </x-ui.card>

    <x-ui.card title="Progress by phase" description="How far along each phase is.">
        <div class="relative h-64">
            <canvas id="phaseBreakdownChart" role="img" aria-label="Progress by phase chart"></canvas>
        </div>
    </x-ui.card>
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var css = getComputedStyle(document.documentElement);
    var token = function (name) { return css.getPropertyValue(name).trim(); };
    var blue = token('--ju-blue');

    Chart.defaults.font.family = "'Source Sans 3', system-ui, sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = token('--muted-foreground');
    Chart.defaults.borderColor = token('--border');

    var percentAxis = {
        beginAtZero: true,
        max: 100,
        ticks: { callback: function (value) { return value + '%'; } }
    };

    new Chart(document.getElementById('progressTrendChart'), {
        type: 'line',
        data: {
            labels: @json($progressTrend['labels']),
            datasets: [{
                label: 'Progress %',
                data: @json($progressTrend['data']),
                fill: true,
                borderColor: blue,
                backgroundColor: 'rgba(31, 117, 203, 0.08)',
                tension: 0.35,
                pointBackgroundColor: blue,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { grid: { display: false } }, y: percentAxis }
        }
    });

    // One series, one colour. The service's per-phase colours are not used here.
    new Chart(document.getElementById('phaseBreakdownChart'), {
        type: 'bar',
        data: {
            labels: @json($phaseBreakdown['labels']),
            datasets: [{
                label: 'Progress %',
                data: @json($phaseBreakdown['data']),
                backgroundColor: blue,
                borderRadius: 6,
                maxBarThickness: 40,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { grid: { display: false } }, y: percentAxis }
        }
    });
});
</script>
@endpush
