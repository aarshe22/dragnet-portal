<?php
$title = 'Reports - DragNet Portal';
ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-chart-bar me-2"></i>Reports</h1>
    </div>
</div>

<div class="row" id="reports-list">
    <!-- Reports will be loaded here -->
</div>

<script>
$(document).ready(function() {
    loadReports();
});

function loadReports() {
    $.get('/api/reports', function(reports) {
        const html = reports.map(report => `
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">${report.name}</h5>
                        <p class="card-text text-muted">${report.description}</p>
                        <button class="btn btn-primary" onclick="generateReport('${report.id}')">
                            Generate Report
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
        
        $('#reports-list').html(html);
    });
}

function generateReport(id) {
    const startDate = prompt('Start date (YYYY-MM-DD):');
    const endDate = prompt('End date (YYYY-MM-DD):');
    
    if (startDate && endDate) {
        $.post('/api/reports/generate', {
            type: id,
            start_date: startDate,
            end_date: endDate
        }, function(response) {
            alert('Report generation initiated. Report ID: ' + response.report_id);
        });
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>

