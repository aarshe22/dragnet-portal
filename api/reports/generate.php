<?php

/**
 * API: Report Generation
 * Generates reports and returns HTML or PDF
 */

// Load configuration first
$config = require __DIR__ . '/../../config.php';
$GLOBALS['config'] = $config;

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/tenant.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/reports.php';

db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('ReadOnly');

$reportType = $_GET['type'] ?? '';
$format = $_GET['format'] ?? 'html'; // html, pdf
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$assetId = isset($_GET['asset_id']) ? (int)$_GET['asset_id'] : null;
$deviceId = isset($_GET['device_id']) ? (int)$_GET['device_id'] : null;

if (!$reportType) {
    json_response(['error' => 'Report type required'], 400);
}

try {
    $filters = [];
    if ($assetId) $filters['asset_id'] = $assetId;
    if ($deviceId) $filters['device_id'] = $deviceId;
    
    $report = generate_report($reportType, $startDate, $endDate, $format, $filters);
    
    if ($format === 'pdf') {
        // For PDF, return HTML that can be printed to PDF by browser
        // In production, you could use TCPDF or similar library here
        header('Content-Type: text/html; charset=utf-8');
        // Add print script to auto-print
        $report = str_replace('</body>', '<script>window.onload = function() { window.print(); }</script></body>', $report);
        echo $report;
    } else {
        if (isset($_GET['download']) && $_GET['download'] === '1') {
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="report_' . $reportType . '_' . date('Y-m-d') . '.html"');
        } else {
            header('Content-Type: text/html; charset=utf-8');
        }
        echo $report;
    }
} catch (Exception $e) {
    json_response(['error' => $e->getMessage()], 500);
}

