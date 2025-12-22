<?php
$title = 'Video Review - DragNet Portal';
ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-video me-2"></i>Video Review</h1>
    </div>
    <div class="col-auto">
        <input type="date" id="start-date" class="form-control d-inline-block" style="width: auto;">
        <input type="date" id="end-date" class="form-control d-inline-block ms-2" style="width: auto;">
        <button class="btn btn-primary ms-2" onclick="loadVideos()">
            <i class="fas fa-search me-1"></i>Search
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div id="video-list">
            <p class="text-muted">Loading...</p>
        </div>
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
        loadVideos();
    }
});

function loadVideos() {
    const startDate = $('#start-date').val();
    const endDate = $('#end-date').val();
    
    $.get(`/api/assets/${assetId}/video`, { start_date: startDate, end_date: endDate }, function(segments) {
        if (segments.length === 0) {
            $('#video-list').html('<p class="text-muted">No video segments found</p>');
            return;
        }
        
        const html = segments.map(segment => `
            <div class="card mb-3">
                <div class="card-body">
                    <h5>${new Date(segment.start_time).toLocaleString()} - ${new Date(segment.end_time).toLocaleString()}</h5>
                    <p class="text-muted">Duration: ${segment.duration ? Math.round(segment.duration / 60) + ' min' : 'N/A'}</p>
                    <div class="btn-group">
                        <a href="/api/video/${segment.id}/stream" class="btn btn-primary" target="_blank">
                            <i class="fas fa-play me-1"></i>Play
                        </a>
                        <a href="/api/video/${segment.id}/download" class="btn btn-secondary">
                            <i class="fas fa-download me-1"></i>Download
                        </a>
                    </div>
                </div>
            </div>
        `).join('');
        
        $('#video-list').html(html);
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>

