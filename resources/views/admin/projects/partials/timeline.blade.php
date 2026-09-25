<link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">
<div class="row">
    <div class="col-md-12">
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clock"></i> Project Timeline
                </h3>
                <div class="card-tools">
                    <span class="badge bg-warning">{{ count($timelineData) }} Phases</span>
                </div>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @foreach($timelineData as $phase)
                        <div>
                            <i class="fas fa-{{ $phase['progress'] >= 100 ? 'check-circle' : 'circle' }} 
                                       bg-{{ $phase['progress'] >= 100 ? 'success' : ($phase['progress'] > 0 ? 'warning' : 'secondary') }}"></i>
                            <div class="timeline-item">
                                <span class="time">
                                    <i class="fas fa-clock"></i>
                                    @if($phase['start'] && $phase['end'])
                                        {{ \Carbon\Carbon::parse($phase['start'])->format('M d') }} - 
                                        {{ \Carbon\Carbon::parse($phase['end'])->format('M d, Y') }}
                                    @else
                                        No dates set
                                    @endif
                                </span>
                                <h3 class="timeline-header">
                                    <a href="{{ route('admin.phases.show', $phase['id']) }}">
                                        {{ $phase['name'] }}
                                    </a>
                                    <span class="badge badge-{{ $phase['color'] }} ml-2">
                                        {{ $phase['status'] }}
                                    </span>
                                </h3>
                                <div class="timeline-body">
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-{{ $phase['progress'] >= 100 ? 'success' : 'primary' }}"
                                             role="progressbar"
                                             style="width: {{ $phase['progress'] }}%">
                                            {{ $phase['progress'] }}%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    
                    @if(empty($timelineData))
                        <div class="text-center text-muted p-4">
                            <i class="fas fa-inbox fa-2x"></i>
                            <p class="mt-2">No phases created yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('css')
<style>
.timeline {
    position: relative;
    padding: 10px 0;
}
.timeline > div {
    margin-bottom: 20px;
}
.timeline > div > i {
    position: absolute;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
}
.timeline-item {
    margin-left: 50px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 5px;
    border-left: 4px solid #17a2b8;
}
.timeline-item .time {
    float: right;
    color: #6c757d;
    font-size: 0.9rem;
}
.timeline-item .timeline-header {
    margin-top: 0;
    font-size: 1rem;
}
.timeline-item .timeline-body {
    margin-top: 10px;
}
</style>
@endpush