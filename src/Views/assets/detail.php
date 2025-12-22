<?php
$title = 'Asset Detail - DragNet Portal';
ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-car me-2"></i>Asset Details</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs" id="assetTabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#overview">Overview</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#map">Live Map</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#telemetry">Telemetry</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#trips">Trips</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#alerts">Alerts</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#video">Video</a>
            </li>
        </ul>
        
        <div class="tab-content mt-3">
            <div class="tab-pane fade show active" id="overview">
                <div class="card">
                    <div class="card-body" id="asset-overview">
                        <p class="text-muted">Loading...</p>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="map">
                <div class="card">
                    <div class="card-body p-0">
                        <div id="asset-map" style="height: 500px;"></div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="telemetry">
                <div class="card">
                    <div class="card-body" id="asset-telemetry">
                        <p class="text-muted">Loading...</p>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="trips">
                <div class="card">
                    <div class="card-body">
                        <a href="/assets/<?= $asset_id ?>/trips" class="btn btn-primary">View All Trips</a>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="alerts">
                <div class="card">
                    <div class="card-body" id="asset-alerts">
                        <p class="text-muted">Loading...</p>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="video">
                <div class="card">
                    <div class="card-body">
                        <a href="/assets/<?= $asset_id ?>/video" class="btn btn-primary">View Video</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const assetId = <?= $asset_id ?? 0 ?>;

$(document).ready(function() {
    if (assetId) {
        loadAssetDetails();
    }
});

function loadAssetDetails() {
    $.get(`/api/assets/${assetId}`, function(asset) {
        const html = `
            <h4>${asset.name}</h4>
            <p><strong>Vehicle ID:</strong> ${asset.vehicle_id || '-'}</p>
            <p><strong>Device:</strong> ${asset.device_uid || '-'}</p>
            <p><strong>Status:</strong> <span class="badge bg-${asset.status === 'active' ? 'success' : 'secondary'}">${asset.status}</span></p>
        `;
        $('#asset-overview').html(html);
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>

