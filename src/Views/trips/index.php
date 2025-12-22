<?php
$title = 'Trips - DragNet Portal';
ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-route me-2"></i>Trips</h1>
    </div>
    <div class="col-auto">
        <input type="date" id="start-date" class="form-control d-inline-block" style="width: auto;">
        <input type="date" id="end-date" class="form-control d-inline-block ms-2" style="width: auto;">
        <button class="btn btn-primary ms-2" onclick="loadTrips()">
            <i class="fas fa-search me-1"></i>Search
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped" id="trips-table">
            <thead>
                <tr>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Distance</th>
                    <th>Duration</th>
                    <th>Max Speed</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6" class="text-center">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
const assetId = <?= $asset_id ?? 0 ?>;

$(document).ready(function() {
    // Set default date range (last 7 days)
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - 7);
    
    $('#start-date').val(startDate.toISOString().split('T')[0]);
    $('#end-date').val(endDate.toISOString().split('T')[0]);
    
    if (assetId) {
        loadTrips();
    }
});

function loadTrips() {
    const startDate = $('#start-date').val();
    const endDate = $('#end-date').val();
    
    $.get(`/api/assets/${assetId}/trips`, { start_date: startDate, end_date: endDate }, function(trips) {
        const tbody = $('#trips-table tbody');
        if (trips.length === 0) {
            tbody.html('<tr><td colspan="6" class="text-center">No trips found</td></tr>');
            return;
        }
        
        const html = trips.map(trip => `
            <tr>
                <td>${new Date(trip.start_time).toLocaleString()}</td>
                <td>${new Date(trip.end_time).toLocaleString()}</td>
                <td>${trip.distance ? trip.distance.toFixed(2) + ' mi' : '-'}</td>
                <td>${trip.duration ? Math.round(trip.duration / 60) + ' min' : '-'}</td>
                <td>${trip.max_speed ? trip.max_speed.toFixed(0) + ' mph' : '-'}</td>
                <td>
                    <a href="/api/trips/${trip.id}/playback" class="btn btn-sm btn-primary">
                        <i class="fas fa-play"></i> Playback
                    </a>
                    <a href="/api/trips/${trip.id}/export" class="btn btn-sm btn-secondary">
                        <i class="fas fa-download"></i> Export
                    </a>
                </td>
            </tr>
        `).join('');
        
        tbody.html(html);
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>

