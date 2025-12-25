<?php

/**
 * Admin Dashboard Page
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/tenant.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin.php';

$config = $GLOBALS['config'];
db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('Administrator');

$title = 'Administration - Dragnet Intelematics';
$showNav = true;

ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-cog me-2"></i>Administration</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tenants-tab" data-bs-toggle="tab" data-bs-target="#tenants" type="button">
                    <i class="fas fa-building me-1"></i>Tenants
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button">
                    <i class="fas fa-users me-1"></i>Users
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="devices-tab" data-bs-toggle="tab" data-bs-target="#devices" type="button">
                    <i class="fas fa-microchip me-1"></i>Devices
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs" type="button">
                    <i class="fas fa-list-alt me-1"></i>Telematics Logs
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="simulator-tab" data-bs-toggle="tab" data-bs-target="#simulator" type="button">
                    <i class="fas fa-flask me-1"></i>Device Simulator
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button">
                    <i class="fas fa-envelope me-1"></i>Email Integration
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button">
                    <i class="fas fa-cog me-1"></i>Settings
                </button>
            </li>
        </ul>
        
        <div class="tab-content" id="adminTabContent">
            <!-- Tenants Tab -->
            <div class="tab-pane fade show active" id="tenants" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Tenant Management</h5>
                        <button class="btn btn-primary btn-sm" id="btnAddTenant">
                            <i class="fas fa-plus me-1"></i>Add Tenant
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="tenantsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Region</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="tenantsTableBody">
                                    <!-- Loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Users Tab -->
            <div class="tab-pane fade" id="users" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">User Management</h5>
                        <button class="btn btn-primary btn-sm" id="btnAddUser">
                            <i class="fas fa-plus me-1"></i>Add User
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <input type="text" class="form-control" id="userSearch" placeholder="Search by email or tenant...">
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped" id="usersTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Email</th>
                                        <th>Tenant</th>
                                        <th>Role</th>
                                        <th>Last Login</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTableBody">
                                    <!-- Loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Devices Tab -->
            <div class="tab-pane fade" id="devices" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Device Management</h5>
                        <button class="btn btn-primary btn-sm" id="btnAddDevice">
                            <i class="fas fa-plus me-1"></i>Add Device
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <input type="text" class="form-control" id="deviceSearch" placeholder="Search by IMEI, device UID, or tenant...">
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped" id="devicesTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Device UID</th>
                                        <th>IMEI</th>
                                        <th>Tenant</th>
                                        <th>Model</th>
                                        <th>Status</th>
                                        <th>Last Seen</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="devicesTableBody">
                                    <!-- Loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Email Integration Tab -->
            <div class="tab-pane fade" id="email" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Email Integration Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <p class="text-muted">Configure the email relay provider for sending notifications and alerts.</p>
                            
                            <form id="emailSettingsForm" onsubmit="return false;">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Email Provider</label>
                                    <select class="form-select" id="emailProviderSelect">
                                        <optgroup label="SMTP Providers">
                                            <option value="smtp">SMTP (Generic/Custom)</option>
                                            <option value="smtp_com">SMTP.com</option>
                                            <option value="smtp2go">SMTP2GO</option>
                                            <option value="gmail">Gmail</option>
                                            <option value="outlook">Outlook / Office 365</option>
                                            <option value="yahoo">Yahoo Mail</option>
                                            <option value="zoho">Zoho Mail</option>
                                            <option value="protonmail">ProtonMail</option>
                                            <option value="fastmail">FastMail</option>
                                            <option value="mail_com">Mail.com</option>
                                            <option value="aol">AOL Mail</option>
                                        </optgroup>
                                        <optgroup label="API Providers">
                                            <option value="sendgrid">SendGrid</option>
                                            <option value="mailgun">Mailgun</option>
                                            <option value="ses">Amazon SES</option>
                                            <option value="postmark">Postmark</option>
                                            <option value="sparkpost">SparkPost</option>
                                            <option value="mailjet">Mailjet</option>
                                            <option value="mandrill">Mandrill (Mailchimp)</option>
                                            <option value="sendinblue">Sendinblue (Brevo)</option>
                                            <option value="pepipost">Pepipost</option>
                                            <option value="postal">Postal</option>
                                        </optgroup>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">From Email Address</label>
                                    <input type="email" class="form-control" id="emailFrom" placeholder="noreply@example.com">
                                </div>
                            </div>
                            
                            <div class="row mt-3" id="smtpFields">
                                <div class="col-md-6">
                                    <label class="form-label">SMTP Host</label>
                                    <input type="text" class="form-control" id="smtpHost" placeholder="smtp.example.com">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">SMTP Port</label>
                                    <input type="number" class="form-control" id="smtpPort" placeholder="587" min="1" max="65535">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Encryption</label>
                                    <select class="form-select" id="smtpEncryption">
                                        <option value="tls">TLS</option>
                                        <option value="ssl">SSL</option>
                                        <option value="">None</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mt-3" id="smtpAuthFields">
                                <div class="col-md-6">
                                    <label class="form-label">SMTP Username</label>
                                    <input type="text" class="form-control" id="smtpUsername" placeholder="username">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">SMTP Password</label>
                                    <input type="password" class="form-control" id="smtpPassword" placeholder="••••••••">
                                    <small class="text-muted">Leave blank to keep existing password</small>
                                </div>
                            </div>
                            
                            <div class="row mt-3" id="apiKeyFields" style="display: none;">
                                <div class="col-md-12">
                                    <label class="form-label">API Key</label>
                                    <input type="password" class="form-control" id="emailApiKey" placeholder="API Key">
                                    <small class="text-muted">Leave blank to keep existing API key</small>
                                </div>
                            </div>
                            
                            <div class="row mt-3" id="sendgridFields" style="display: none;">
                                <div class="col-md-12">
                                    <label class="form-label">SendGrid API Key</label>
                                    <input type="password" class="form-control" id="sendgridApiKey" placeholder="SG.xxxxxxxxxxxxx">
                                    <small class="text-muted">Leave blank to keep existing API key</small>
                                </div>
                            </div>
                            
                            <div class="row mt-3" id="mailgunFields" style="display: none;">
                                <div class="col-md-6">
                                    <label class="form-label">Mailgun Domain</label>
                                    <input type="text" class="form-control" id="mailgunDomain" placeholder="mg.example.com">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mailgun API Key</label>
                                    <input type="password" class="form-control" id="mailgunApiKey" placeholder="key-xxxxxxxxxxxxx">
                                    <small class="text-muted">Leave blank to keep existing API key</small>
                                </div>
                            </div>
                            
                            <div class="row mt-3" id="sesFields" style="display: none;">
                                <div class="col-md-4">
                                    <label class="form-label">AWS Region</label>
                                    <input type="text" class="form-control" id="sesRegion" placeholder="us-east-1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">AWS Access Key ID</label>
                                    <input type="text" class="form-control" id="sesAccessKey" placeholder="AKIAIOSFODNN7EXAMPLE">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">AWS Secret Access Key</label>
                                    <input type="password" class="form-control" id="sesSecretKey" placeholder="••••••••">
                                    <small class="text-muted">Leave blank to keep existing key</small>
                                </div>
                            </div>
                            
                            <div class="row mt-3" id="postmarkFields" style="display: none;">
                                <div class="col-md-6">
                                    <label class="form-label">Postmark Server API Token</label>
                                    <input type="password" class="form-control" id="postmarkToken" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
                                    <small class="text-muted">Leave blank to keep existing token</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Postmark Server ID</label>
                                    <input type="text" class="form-control" id="postmarkServerId" placeholder="12345678">
                                </div>
                            </div>
                            
                            <div class="row mt-3" id="sparkpostFields" style="display: none;">
                                <div class="col-md-12">
                                    <label class="form-label">SparkPost API Key</label>
                                    <input type="password" class="form-control" id="sparkpostApiKey" placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                                    <small class="text-muted">Leave blank to keep existing API key</small>
                                </div>
                            </div>
                            
                            <div class="row mt-3" id="mailjetFields" style="display: none;">
                                <div class="col-md-6">
                                    <label class="form-label">Mailjet API Key</label>
                                    <input type="text" class="form-control" id="mailjetApiKey" placeholder="xxxxxxxxxxxxxxxxxxxxxxxx">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mailjet Secret Key</label>
                                    <input type="password" class="form-control" id="mailjetSecretKey" placeholder="••••••••">
                                    <small class="text-muted">Leave blank to keep existing key</small>
                                </div>
                            </div>
                            
                            <div class="row mt-3" id="mandrillFields" style="display: none;">
                                <div class="col-md-12">
                                    <label class="form-label">Mandrill API Key</label>
                                    <input type="password" class="form-control" id="mandrillApiKey" placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                                    <small class="text-muted">Leave blank to keep existing API key. Get your API key from <a href="https://mandrillapp.com/settings" target="_blank">Mandrill Dashboard</a></small>
                                </div>
                            </div>
                            
                            <div class="row mt-3" id="sendinblueFields" style="display: none;">
                                <div class="col-md-12">
                                    <label class="form-label">Sendinblue (Brevo) API Key</label>
                                    <input type="password" class="form-control" id="sendinblueApiKey" placeholder="xkeysib-xxxxxxxxxxxxx">
                                    <small class="text-muted">Leave blank to keep existing API key. Get your API key from <a href="https://app.brevo.com/settings/keys/api" target="_blank">Brevo Dashboard</a></small>
                                </div>
                            </div>
                            
                            <div class="row mt-3" id="pepipostFields" style="display: none;">
                                <div class="col-md-12">
                                    <label class="form-label">Pepipost API Key</label>
                                    <input type="password" class="form-control" id="pepipostApiKey" placeholder="xxxxxxxxxxxxxxxxxxxxxxxx">
                                    <small class="text-muted">Leave blank to keep existing API key. Get your API key from <a href="https://app.pepipost.com/index.php/settings/api" target="_blank">Pepipost Dashboard</a></small>
                                </div>
                            </div>
                            
                            <div class="row mt-3" id="postalFields" style="display: none;">
                                <div class="col-md-6">
                                    <label class="form-label">Postal Server URL</label>
                                    <input type="text" class="form-control" id="postalServerUrl" placeholder="https://postal.example.com">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Postal API Key</label>
                                    <input type="password" class="form-control" id="postalApiKey" placeholder="xxxxxxxxxxxxxxxx">
                                    <small class="text-muted">Leave blank to keep existing API key</small>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <button type="button" class="btn btn-primary" id="btnSaveEmailSettings">
                                    <i class="fas fa-save me-1"></i>Save Email Settings
                                </button>
                                <button type="button" class="btn btn-secondary" id="btnLoadEmailSettings">
                                    <i class="fas fa-sync me-1"></i>Reset to Defaults
                                </button>
                                <button type="button" class="btn btn-info" id="btnTestEmailSettings">
                                    <i class="fas fa-paper-plane me-1"></i>Send Test Email
                                </button>
                            </div>
                            </form>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0"><i class="fas fa-bug me-2"></i>Email Debug & Logs</h6>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="emailDebugToggle" role="switch">
                                    <label class="form-check-label" for="emailDebugToggle">
                                        Enable Debug Logging
                                    </label>
                                </div>
                            </div>
                            <p class="text-muted">View email sending logs and debug information. Enable debug logging to capture detailed information about email sending attempts.</p>
                            
                            <div class="card mt-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><i class="fas fa-list me-2"></i>Email Logs</h6>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-secondary" id="btnRefreshEmailLogs">
                                            <i class="fas fa-sync"></i> Refresh
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" id="btnClearEmailLogs">
                                            <i class="fas fa-trash"></i> Clear Logs
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select form-select-sm" id="emailLogStatusFilter">
                                                <option value="">All Statuses</option>
                                                <option value="pending">Pending</option>
                                                <option value="sent">Sent</option>
                                                <option value="failed">Failed</option>
                                                <option value="bounced">Bounced</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Provider</label>
                                            <select class="form-select form-select-sm" id="emailLogProviderFilter">
                                                <option value="">All Providers</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Search</label>
                                            <input type="text" class="form-control form-control-sm" id="emailLogSearch" placeholder="Recipient, subject, error...">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Sort</label>
                                            <select class="form-select form-select-sm" id="emailLogSort">
                                                <option value="created_at DESC">Newest First</option>
                                                <option value="created_at ASC">Oldest First</option>
                                                <option value="recipient ASC">Recipient A-Z</option>
                                                <option value="status ASC">Status</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                                        <table class="table table-sm table-striped table-hover">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th>Time</th>
                                                    <th>Recipient</th>
                                                    <th>Subject</th>
                                                    <th>Provider</th>
                                                    <th>Status</th>
                                                    <th>Error</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="emailLogsTableBody">
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted">Loading logs...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <small class="text-muted" id="emailLogCount">0 logs</small>
                                        <nav>
                                            <ul class="pagination pagination-sm mb-0" id="emailLogPagination">
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Device Simulator Tab -->
            <div class="tab-pane fade" id="simulator" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-flask me-2"></i>Teltonika Telemetry Simulator</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Generate and stream realistic test telemetry data like a real Teltonika device. Perfect for testing without physical hardware.</p>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Select Device</label>
                                <select class="form-select" id="simulatorDeviceId">
                                    <option value="">Loading devices...</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Update Interval (seconds)</label>
                                <input type="number" class="form-control" id="simulatorInterval" value="30" min="5" max="300">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Route Type</label>
                                <select class="form-select" id="simulatorRoute">
                                    <option value="random">Random Movement</option>
                                    <option value="circle">Circular Route</option>
                                    <option value="line">Straight Line</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Speed (km/h)</label>
                                <input type="number" class="form-control" id="simulatorSpeed" placeholder="Auto (random)" min="0" max="200">
                                <small class="text-muted">Leave empty for random speed</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Movement State</label>
                                <select class="form-select" id="simulatorMoving">
                                    <option value="">Auto (random)</option>
                                    <option value="1">Moving</option>
                                    <option value="0">Stopped</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Iterations</label>
                                <input type="number" class="form-control" id="simulatorIterations" placeholder="Unlimited" min="1">
                                <small class="text-muted">Leave empty for continuous streaming</small>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> The simulator will generate realistic GPS coordinates, speed, heading, IO elements, and device status. 
                            Data will be sent to the same endpoint as real devices and will appear in the Live Map and Telematics Logs.
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary" id="simulatorSendSingle" onclick="simulatorSendSingle()">
                                <i class="fas fa-paper-plane me-1"></i>Send Single Packet
                            </button>
                            <button class="btn btn-success" id="simulatorStartStream" onclick="simulatorStartStream()">
                                <i class="fas fa-play me-1"></i>Start Streaming
                            </button>
                            <button class="btn btn-danger" id="simulatorStopStream" onclick="simulatorStopStream()" style="display: none;">
                                <i class="fas fa-stop me-1"></i>Stop Streaming
                            </button>
                        </div>
                        
                        <div id="simulatorStatus" class="mt-3"></div>
                        
                        <div class="mt-4">
                            <h6>Simulation Status</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Time</th>
                                            <th>Status</th>
                                            <th>Packets Sent</th>
                                            <th>Packets Failed</th>
                                            <th>Last Location</th>
                                        </tr>
                                    </thead>
                                    <tbody id="simulatorStatusTable">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No simulation running</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Settings Tab -->
            <div class="tab-pane fade" id="settings" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Application Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6><i class="fas fa-map me-2"></i>Map Provider Settings</h6>
                            <p class="text-muted">Select the default mapping provider for the Live Map view.</p>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Map Provider</label>
                                    <select class="form-select" id="mapProviderSelect">
                                        <option value="openstreetmap">OpenStreetMap</option>
                                        <option value="openstreetmap_fr">OpenStreetMap France</option>
                                        <option value="openstreetmap_de">OpenStreetMap DE</option>
                                        <option value="cartodb_positron">CartoDB Positron</option>
                                        <option value="cartodb_dark">CartoDB Dark Matter</option>
                                        <option value="stamen_terrain">Stamen Terrain</option>
                                        <option value="stamen_toner">Stamen Toner</option>
                                        <option value="stamen_watercolor">Stamen Watercolor</option>
                                        <option value="esri_worldstreetmap">Esri World Street Map</option>
                                        <option value="esri_worldtopomap">Esri World Topo Map</option>
                                        <option value="esri_worldimagery">Esri World Imagery</option>
                                        <option value="opentopomap">OpenTopoMap</option>
                                        <option value="cyclosm">CyclOSM</option>
                                        <option value="wikimedia">Wikimedia Maps</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Default Zoom Level</label>
                                    <input type="number" class="form-control" id="mapZoom" min="1" max="20" value="10">
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label class="form-label">Default Center Latitude</label>
                                    <input type="number" class="form-control" id="mapCenterLat" step="0.000001" value="40.7128">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Default Center Longitude</label>
                                    <input type="number" class="form-control" id="mapCenterLon" step="0.000001" value="-74.0060">
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <div id="mapPreview" style="height: 300px; border: 1px solid #ddd; border-radius: 0.375rem;"></div>
                                <small class="text-muted">Preview of selected map provider</small>
                            </div>
                            
                            <div class="mt-3">
                                <button class="btn btn-primary" id="btnSaveMapSettings">
                                    <i class="fas fa-save me-1"></i>Save Map Settings
                                </button>
                                <button class="btn btn-secondary" id="btnLoadMapSettings">
                                    <i class="fas fa-sync me-1"></i>Reset to Defaults
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Telematics Logs Tab -->
            <div class="tab-pane fade" id="logs" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Telematics Logs</h5>
                        <div>
                            <button class="btn btn-sm btn-secondary" id="autoRefreshBtn">
                                <i class="fas fa-pause me-1"></i>Pause Auto-Refresh
                            </button>
                            <button class="btn btn-sm btn-primary" id="btnClearLogs">
                                <i class="fas fa-trash me-1"></i>Clear View
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <select class="form-select" id="logTenantFilter" onchange="loadLogs()">
                                    <option value="">All Tenants</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="logDeviceFilter" onchange="loadLogs()">
                                    <option value="">All Devices</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" id="logSearch" placeholder="Type to search..." onkeyup="filterLogs()">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="logSort" onchange="loadLogs()">
                                    <option value="timestamp_desc">Newest First</option>
                                    <option value="timestamp_asc">Oldest First</option>
                                    <option value="device_asc">Device A-Z</option>
                                    <option value="device_desc">Device Z-A</option>
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-sm table-striped" id="logsTable">
                                <thead class="sticky-top bg-white">
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>Device</th>
                                        <th>Tenant</th>
                                        <th>Lat</th>
                                        <th>Lon</th>
                                        <th>Speed</th>
                                        <th>Status</th>
                                        <th>Data</th>
                                    </tr>
                                </thead>
                                <tbody id="logsTableBody">
                                    <!-- Loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tenant Modal -->
<div class="modal fade" id="tenantModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tenantModalTitle">Add Tenant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="tenantForm">
                    <input type="hidden" id="tenantId" name="id">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" id="tenantName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Region</label>
                        <input type="text" class="form-control" id="tenantRegion" name="region" value="us-east">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveTenant">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalTitle">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="userForm">
                    <input type="hidden" id="userId" name="id">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="userEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tenant</label>
                        <select class="form-select" id="userTenantId" name="tenant_id" required>
                            <option value="">Select Tenant</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" id="userRole" name="role" required>
                            <option value="Guest">Guest</option>
                            <option value="ReadOnly">ReadOnly</option>
                            <option value="Operator">Operator</option>
                            <option value="Administrator">Administrator</option>
                            <option value="TenantOwner">TenantOwner</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveUser">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Device Modal -->
<div class="modal fade" id="deviceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deviceModalTitle">Add Device</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="deviceForm">
                    <input type="hidden" id="deviceId" name="id">
                    <div class="mb-3">
                        <label class="form-label">Tenant</label>
                        <select class="form-select" id="deviceTenantId" name="tenant_id" required>
                            <option value="">Select Tenant</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Device UID</label>
                        <input type="text" class="form-control" id="deviceUid" name="device_uid" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">IMEI</label>
                        <input type="text" class="form-control" id="deviceImei" name="imei" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ICCID</label>
                        <input type="text" class="form-control" id="deviceIccid" name="iccid">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Model</label>
                        <input type="text" class="form-control" id="deviceModel" name="model" value="FMM13A">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Firmware Version</label>
                        <input type="text" class="form-control" id="deviceFirmware" name="firmware_version">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveDevice">Save</button>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Wait for jQuery and Leaflet to be loaded
(function() {
    // Check if jQuery and Leaflet are loaded
    function waitForDependencies(callback) {
        if (typeof jQuery !== 'undefined' && typeof L !== 'undefined') {
            callback();
        } else {
            setTimeout(function() { waitForDependencies(callback); }, 50);
        }
    }
    
    waitForDependencies(function() {
        // Now jQuery is available, use $ safely
        const $ = jQuery;
        
        let mapPreview = null;
        let autoRefreshEnabled = true;
        let autoRefreshInterval = null;
        let currentLogSearch = '';
        
        $(document).ready(function() {
            // Attach event listeners for static buttons
            $('#btnAddTenant').on('click', function() { showTenantModal(); });
            $('#btnAddUser').on('click', function() { showUserModal(); });
            $('#btnAddDevice').on('click', function() { showDeviceModal(); });
            $('#btnSaveTenant').on('click', function() { saveTenant(); });
            $('#btnSaveUser').on('click', function() { saveUser(); });
            $('#btnSaveDevice').on('click', function() { saveDevice(); });
            $('#btnSaveMapSettings').on('click', function() { saveMapSettings(); });
            $('#btnLoadMapSettings').on('click', function() { loadMapSettings(); });
            $('#btnSaveEmailSettings').on('click', function() { saveEmailSettings(); });
            $('#btnLoadEmailSettings').on('click', function() { loadEmailSettings(); });
            $('#btnTestEmailSettings').on('click', function() { testEmailSettings(); });
            $('#autoRefreshBtn').on('click', function() { toggleAutoRefresh(); });
            $('#btnClearLogs').on('click', function() { clearLogs(); });
            $('#mapProviderSelect').on('change', function() { updateMapPreview(); });
            $('#emailProviderSelect').on('change', function() { updateEmailProviderFields(); });
            $('#emailDebugToggle').on('change', function() { saveEmailDebugSetting(); });
            $('#btnRefreshEmailLogs').on('click', function() { loadEmailLogs(); });
            $('#btnClearEmailLogs').on('click', function() { clearEmailLogs(); });
            $('#emailLogStatusFilter, #emailLogProviderFilter, #emailLogSort').on('change', function() { loadEmailLogs(); });
            $('#emailLogSearch').on('keyup', debounce(function() { loadEmailLogs(); }, 500));
            $('#userSearch').on('keyup', function() { loadUsers(); });
            $('#deviceSearch').on('keyup', function() { loadDevices(); });
            
            // Event delegation for dynamically generated buttons
            $(document).on('click', '.btn-edit-tenant', function() {
                const id = $(this).data('id');
                editTenant(id);
            });
            $(document).on('click', '.btn-delete-tenant', function() {
                const id = $(this).data('id');
                deleteTenant(id);
            });
            $(document).on('click', '.btn-edit-user', function() {
                const id = $(this).data('id');
                editUser(id);
            });
            $(document).on('click', '.btn-delete-user', function() {
                const id = $(this).data('id');
                deleteUser(id);
            });
            $(document).on('click', '.btn-edit-device', function() {
                const id = $(this).data('id');
                editDevice(id);
            });
            $(document).on('click', '.btn-delete-device', function() {
                const id = $(this).data('id');
                deleteDevice(id);
            });
            
            loadTenants();
            loadUsers();
            loadDevices();
            loadTenantOptions();
            loadLogFilters();
            loadLogs();
            loadMapSettings();
            
            // Auto-refresh logs every 5 seconds
            autoRefreshInterval = setInterval(function() {
                if (autoRefreshEnabled) {
                    loadLogs();
                }
            }, 5000);
            
            // Switch tabs
            $('#adminTabs button').on('shown.bs.tab', function(e) {
                const target = $(e.target).data('bs-target');
                if (target === '#tenants') {
                    loadTenants();
                } else if (target === '#users') {
                    loadUsers();
                } else if (target === '#devices') {
                    loadDevices();
                } else if (target === '#logs') {
                    loadLogs();
                } else if (target === '#email') {
                    loadEmailSettings();
                } else if (target === '#settings') {
                    setTimeout(function() {
                        if (!mapPreview && typeof L !== 'undefined') {
                            initMapPreview();
                        }
                    }, 200);
                }
            });
        });
        
        // Make functions available globally
        window.loadTenants = function() {
            $.get('/api/admin/tenants.php', function(tenants) {
                const tbody = $('#tenantsTableBody');
                tbody.empty();
                
                if (tenants.length === 0) {
                    tbody.append('<tr><td colspan="5" class="text-center">No tenants found</td></tr>');
                    return;
                }
                
                tenants.forEach(tenant => {
                    const row = `
                        <tr>
                            <td>${tenant.id}</td>
                            <td>${escapeHtml(tenant.name)}</td>
                            <td>${escapeHtml(tenant.region)}</td>
                            <td>${formatDate(tenant.created_at)}</td>
                            <td>
                                <button class="btn btn-sm btn-primary btn-edit-tenant" data-id="${tenant.id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger btn-delete-tenant" data-id="${tenant.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            });
        };
        
        window.showTenantModal = function(id = null) {
            $('#tenantModalTitle').text(id ? 'Edit Tenant' : 'Add Tenant');
            $('#tenantForm')[0].reset();
            $('#tenantId').val(id || '');
            
            if (id) {
                $.get('/api/admin/tenants.php', function(tenants) {
                    const tenant = tenants.find(t => t.id == id);
                    if (tenant) {
                        $('#tenantName').val(tenant.name);
                        $('#tenantRegion').val(tenant.region);
                    }
                });
            }
            
            new bootstrap.Modal(document.getElementById('tenantModal')).show();
        };
        
        window.saveTenant = function() {
            const data = {
                name: $('#tenantName').val(),
                region: $('#tenantRegion').val()
            };
            
            const id = $('#tenantId').val();
            const url = '/api/admin/tenants.php';
            const method = id ? 'PUT' : 'POST';
            
            if (id) {
                data.id = id;
            }
            
            $.ajax({
                url: url,
                method: method,
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function() {
                    bootstrap.Modal.getInstance(document.getElementById('tenantModal')).hide();
                    loadTenants();
                    loadTenantOptions();
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.error || 'Unknown error'));
                }
            });
        };
        
        window.editTenant = function(id) {
            showTenantModal(id);
        };
        
        window.deleteTenant = function(id) {
            if (!confirm('Are you sure you want to delete this tenant? This will also delete all associated users, devices, and data.')) {
                return;
            }
            
            $.ajax({
                url: '/api/admin/tenants.php',
                method: 'DELETE',
                data: { id: id },
                success: function() {
                    loadTenants();
                    loadTenantOptions();
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.error || 'Unknown error'));
                }
            });
        };
        
        window.loadUsers = function() {
            const search = $('#userSearch').val();
            const url = '/api/admin/users.php' + (search ? '?email=' + encodeURIComponent(search) : '');
            
            $.get(url, function(users) {
                const tbody = $('#usersTableBody');
                tbody.empty();
                
                if (users.length === 0) {
                    tbody.append('<tr><td colspan="6" class="text-center">No users found</td></tr>');
                    return;
                }
                
                users.forEach(user => {
                    const row = `
                        <tr>
                            <td>${user.id}</td>
                            <td>${escapeHtml(user.email)}</td>
                            <td>${escapeHtml(user.tenant_name || 'N/A')}</td>
                            <td><span class="badge bg-info">${escapeHtml(user.role)}</span></td>
                            <td>${user.last_login ? formatDate(user.last_login) : 'Never'}</td>
                            <td>
                                <button class="btn btn-sm btn-primary btn-edit-user" data-id="${user.id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger btn-delete-user" data-id="${user.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            });
        };
        
        window.showUserModal = function(id = null) {
            $('#userModalTitle').text(id ? 'Edit User' : 'Add User');
            $('#userForm')[0].reset();
            $('#userId').val(id || '');
            
            if (id) {
                $.get('/api/admin/users.php', function(users) {
                    const user = users.find(u => u.id == id);
                    if (user) {
                        $('#userEmail').val(user.email);
                        $('#userTenantId').val(user.tenant_id);
                        $('#userRole').val(user.role);
                    }
                });
            }
            
            new bootstrap.Modal(document.getElementById('userModal')).show();
        };
        
        window.saveUser = function() {
            const data = {
                email: $('#userEmail').val(),
                tenant_id: parseInt($('#userTenantId').val()),
                role: $('#userRole').val()
            };
            
            const id = $('#userId').val();
            const url = '/api/admin/users.php';
            const method = id ? 'PUT' : 'POST';
            
            if (id) {
                data.id = id;
            }
            
            $.ajax({
                url: url,
                method: method,
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function() {
                    bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
                    loadUsers();
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.error || 'Unknown error'));
                }
            });
        };
        
        window.editUser = function(id) {
            showUserModal(id);
        };
        
        window.deleteUser = function(id) {
            if (!confirm('Are you sure you want to delete this user?')) {
                return;
            }
            
            $.ajax({
                url: '/api/admin/users.php',
                method: 'DELETE',
                data: { id: id },
                success: function() {
                    loadUsers();
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.error || 'Unknown error'));
                }
            });
        };
        
        window.loadDevices = function() {
            const search = $('#deviceSearch').val();
            const url = '/api/admin/devices.php' + (search ? '?imei=' + encodeURIComponent(search) : '');
            
            $.get(url, function(devices) {
                const tbody = $('#devicesTableBody');
                tbody.empty();
                
                if (devices.length === 0) {
                    tbody.append('<tr><td colspan="8" class="text-center">No devices found</td></tr>');
                    return;
                }
                
                devices.forEach(device => {
                    const statusBadge = getStatusBadge(device.status);
                    const row = `
                        <tr>
                            <td>${device.id}</td>
                            <td>${escapeHtml(device.device_uid)}</td>
                            <td>${escapeHtml(device.imei)}</td>
                            <td>${escapeHtml(device.tenant_name || 'N/A')}</td>
                            <td>${escapeHtml(device.model || 'FMM13A')}</td>
                            <td>${statusBadge}</td>
                            <td>${device.last_seen ? formatDate(device.last_seen) : 'Never'}</td>
                            <td>
                                <button class="btn btn-sm btn-primary btn-edit-device" data-id="${device.id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger btn-delete-device" data-id="${device.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            });
        };
        
        window.showDeviceModal = function(id = null) {
            $('#deviceModalTitle').text(id ? 'Edit Device' : 'Add Device');
            $('#deviceForm')[0].reset();
            $('#deviceId').val(id || '');
            
            if (id) {
                $.get('/api/admin/devices.php', function(devices) {
                    const device = devices.find(d => d.id == id);
                    if (device) {
                        $('#deviceTenantId').val(device.tenant_id);
                        $('#deviceUid').val(device.device_uid);
                        $('#deviceImei').val(device.imei);
                        $('#deviceIccid').val(device.iccid || '');
                        $('#deviceModel').val(device.model || 'FMM13A');
                        $('#deviceFirmware').val(device.firmware_version || '');
                    }
                });
            }
            
            new bootstrap.Modal(document.getElementById('deviceModal')).show();
        };
        
        window.saveDevice = function() {
            const data = {
                tenant_id: parseInt($('#deviceTenantId').val()),
                device_uid: $('#deviceUid').val(),
                imei: $('#deviceImei').val(),
                iccid: $('#deviceIccid').val() || null,
                model: $('#deviceModel').val() || 'FMM13A',
                firmware_version: $('#deviceFirmware').val() || null
            };
            
            const id = $('#deviceId').val();
            const url = '/api/admin/devices.php';
            const method = id ? 'PUT' : 'POST';
            
            if (id) {
                data.id = id;
            }
            
            $.ajax({
                url: url,
                method: method,
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function() {
                    bootstrap.Modal.getInstance(document.getElementById('deviceModal')).hide();
                    loadDevices();
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.error || 'Unknown error'));
                }
            });
        };
        
        window.editDevice = function(id) {
            showDeviceModal(id);
        };
        
        window.deleteDevice = function(id) {
            if (!confirm('Are you sure you want to delete this device? This will also delete all associated telemetry data.')) {
                return;
            }
            
            $.ajax({
                url: '/api/admin/devices.php',
                method: 'DELETE',
                data: { id: id },
                success: function() {
                    loadDevices();
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.error || 'Unknown error'));
                }
            });
        };
        
        window.loadLogFilters = function() {
            // Load tenants for filter
            $.get('/api/admin/tenants.php', function(tenants) {
                const select = $('#logTenantFilter');
                select.empty().append('<option value="">All Tenants</option>');
                tenants.forEach(tenant => {
                    select.append(`<option value="${tenant.id}">${escapeHtml(tenant.name)}</option>`);
                });
            });
            
            // Load devices for filter
            $.get('/api/admin/devices.php', function(devices) {
                const select = $('#logDeviceFilter');
                select.empty().append('<option value="">All Devices</option>');
                const uniqueDevices = [...new Map(devices.map(d => [d.id, d])).values()];
                uniqueDevices.forEach(device => {
                    select.append(`<option value="${device.id}">${escapeHtml(device.device_uid)} (${escapeHtml(device.imei)})</option>`);
                });
            });
        };
        
        window.loadLogs = function() {
            const tenantId = $('#logTenantFilter').val();
            const deviceId = $('#logDeviceFilter').val();
            const sort = $('#logSort').val();
            const search = $('#logSearch').val();
            
            let url = '/api/admin/logs.php?limit=500';
            if (tenantId) url += '&tenant_id=' + tenantId;
            if (deviceId) url += '&device_id=' + deviceId;
            if (sort) url += '&sort=' + sort;
            if (search) url += '&search=' + encodeURIComponent(search);
            
            $.get(url, function(logs) {
                const tbody = $('#logsTableBody');
                tbody.empty();
                
                if (logs.length === 0) {
                    tbody.append('<tr><td colspan="8" class="text-center">No logs found</td></tr>');
                    return;
                }
                
                logs.forEach(log => {
                    const ioData = log.io_payload ? JSON.parse(log.io_payload) : {};
                    const ioHtml = Object.keys(ioData).map(key => 
                        `<span class="badge bg-secondary me-1">${escapeHtml(key)}: ${escapeHtml(ioData[key])}</span>`
                    ).join('');
                    
                    // Highlight search terms
                    let deviceUid = escapeHtml(log.device_uid);
                    if (search) {
                        const regex = new RegExp(`(${escapeRegex(search)})`, 'gi');
                        deviceUid = deviceUid.replace(regex, '<mark>$1</mark>');
                    }
                    
                    const row = `
                        <tr>
                            <td>${formatDateTime(log.timestamp)}</td>
                            <td>${deviceUid}</td>
                            <td>${escapeHtml(log.tenant_name || 'N/A')}</td>
                            <td>${log.lat ? parseFloat(log.lat).toFixed(6) : '-'}</td>
                            <td>${log.lon ? parseFloat(log.lon).toFixed(6) : '-'}</td>
                            <td>${log.speed ? parseFloat(log.speed).toFixed(1) + ' km/h' : '-'}</td>
                            <td>
                                <span class="badge bg-${log.ignition ? 'success' : 'secondary'}">
                                    ${log.ignition ? 'On' : 'Off'}
                                </span>
                            </td>
                            <td>${ioHtml || '-'}</td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            });
        };
        
        window.filterLogs = function() {
            currentLogSearch = $('#logSearch').val();
            loadLogs();
        };
        
        window.toggleAutoRefresh = function() {
            autoRefreshEnabled = !autoRefreshEnabled;
            const btn = $('#autoRefreshBtn');
            if (autoRefreshEnabled) {
                btn.html('<i class="fas fa-pause me-1"></i>Pause Auto-Refresh');
            } else {
                btn.html('<i class="fas fa-play me-1"></i>Resume Auto-Refresh');
            }
        };
        
        window.clearLogs = function() {
            $('#logsTableBody').empty();
        };
        
        window.loadTenantOptions = function() {
            $.get('/api/admin/tenants.php', function(tenants) {
                $('#userTenantId, #deviceTenantId').each(function() {
                    const select = $(this);
                    const currentVal = select.val();
                    select.empty().append('<option value="">Select Tenant</option>');
                    tenants.forEach(tenant => {
                        select.append(`<option value="${tenant.id}">${escapeHtml(tenant.name)}</option>`);
                    });
                    if (currentVal) {
                        select.val(currentVal);
                    }
                });
            });
        };
        
        window.initMapPreview = function() {
            if (typeof L === 'undefined') {
                console.error('Leaflet not loaded');
                return;
            }
            
            if (mapPreview) {
                mapPreview.remove();
            }
            
            const lat = parseFloat($('#mapCenterLat').val()) || 40.7128;
            const lon = parseFloat($('#mapCenterLon').val()) || -74.0060;
            const zoom = parseInt($('#mapZoom').val()) || 10;
            
            mapPreview = L.map('mapPreview').setView([lat, lon], zoom);
            updateMapPreview();
        };
        
        window.updateMapPreview = function() {
            if (typeof L === 'undefined') {
                console.error('Leaflet not loaded');
                return;
            }
            
            if (!mapPreview) {
                initMapPreview();
                return;
            }
            
            const provider = $('#mapProviderSelect').val();
            const providers = {
                'openstreetmap': { url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', attr: '© OpenStreetMap contributors', sub: ['a', 'b', 'c'] },
                'openstreetmap_fr': { url: 'https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', attr: '© OpenStreetMap France | © OpenStreetMap contributors', sub: ['a', 'b', 'c'] },
                'openstreetmap_de': { url: 'https://{s}.tile.openstreetmap.de/{z}/{x}/{y}.png', attr: '© OpenStreetMap DE | © OpenStreetMap contributors', sub: ['a', 'b', 'c'] },
                'cartodb_positron': { url: 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', attr: '© OpenStreetMap contributors © CARTO', sub: ['a', 'b', 'c', 'd'] },
                'cartodb_dark': { url: 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', attr: '© OpenStreetMap contributors © CARTO', sub: ['a', 'b', 'c', 'd'] },
                'stamen_terrain': { url: 'https://stamen-tiles-{s}.a.ssl.fastly.net/terrain/{z}/{x}/{y}{r}.png', attr: 'Map tiles by Stamen Design, under CC BY 3.0. Data by OpenStreetMap, under ODbL.', sub: ['a', 'b', 'c', 'd'] },
                'stamen_toner': { url: 'https://stamen-tiles-{s}.a.ssl.fastly.net/toner/{z}/{x}/{y}{r}.png', attr: 'Map tiles by Stamen Design, under CC BY 3.0. Data by OpenStreetMap, under ODbL.', sub: ['a', 'b', 'c', 'd'] },
                'stamen_watercolor': { url: 'https://stamen-tiles-{s}.a.ssl.fastly.net/watercolor/{z}/{x}/{y}.jpg', attr: 'Map tiles by Stamen Design, under CC BY 3.0. Data by OpenStreetMap, under ODbL.', sub: ['a', 'b', 'c', 'd'] },
                'esri_worldstreetmap': { url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', attr: 'Tiles © Esri', sub: [] },
                'esri_worldtopomap': { url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', attr: 'Tiles © Esri', sub: [] },
                'esri_worldimagery': { url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', attr: 'Tiles © Esri', sub: [] },
                'opentopomap': { url: 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', attr: 'Map data: &copy; OpenStreetMap contributors, SRTM | Map style: &copy; OpenTopoMap (CC-BY-SA)', sub: ['a', 'b', 'c'] },
                'cyclosm': { url: 'https://{s}.tile-cyclosm.openstreetmap.fr/cyclosm/{z}/{x}/{y}.png', attr: '© OpenStreetMap contributors, style by CyclOSM', sub: ['a', 'b', 'c'] },
                'wikimedia': { url: 'https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}.png', attr: '© OpenStreetMap contributors', sub: [] }
            };
            
            const config = providers[provider] || providers['openstreetmap'];
            
            // Remove existing tiles
            mapPreview.eachLayer(function(layer) {
                if (layer instanceof L.TileLayer) {
                    mapPreview.removeLayer(layer);
                }
            });
            
            // Add new tiles with proper subdomain handling
            const tileOptions = {
                attribution: config.attr,
                maxZoom: 19
            };
            
            // Only add subdomains if array exists and has items
            if (config.sub && Array.isArray(config.sub) && config.sub.length > 0) {
                tileOptions.subdomains = config.sub;
            }
            
            const tileLayer = L.tileLayer(config.url, tileOptions);
            tileLayer.addTo(mapPreview);
        };
        
        window.loadMapSettings = function() {
            $.ajax({
                url: '/api/admin/settings.php',
                method: 'GET',
                dataType: 'json',
                success: function(settings) {
                    $('#mapProviderSelect').val(settings.map_provider || 'openstreetmap');
                    $('#mapZoom').val(settings.map_zoom || 10);
                    $('#mapCenterLat').val(settings.map_center_lat || 40.7128);
                    $('#mapCenterLon').val(settings.map_center_lon || -74.0060);
                    if (mapPreview) {
                        updateMapPreview();
                    }
                },
                error: function(xhr) {
                    console.error('Failed to load map settings:', xhr);
                    // Use defaults
                    $('#mapProviderSelect').val('openstreetmap');
                    $('#mapZoom').val(10);
                    $('#mapCenterLat').val(40.7128);
                    $('#mapCenterLon').val(-74.0060);
                }
            });
        };
        
        window.saveMapSettings = function() {
            // Validate inputs
            const provider = $('#mapProviderSelect').val();
            const zoom = parseInt($('#mapZoom').val());
            const lat = parseFloat($('#mapCenterLat').val());
            const lon = parseFloat($('#mapCenterLon').val());
            
            if (!provider) {
                alert('Please select a map provider');
                return;
            }
            
            if (isNaN(zoom) || zoom < 1 || zoom > 20) {
                alert('Zoom level must be between 1 and 20');
                return;
            }
            
            if (isNaN(lat) || lat < -90 || lat > 90) {
                alert('Latitude must be between -90 and 90');
                return;
            }
            
            if (isNaN(lon) || lon < -180 || lon > 180) {
                alert('Longitude must be between -180 and 180');
                return;
            }
            
            const settings = {
                map_provider: provider,
                map_zoom: zoom,
                map_center_lat: lat,
                map_center_lon: lon
            };
            
            // Show loading state
            const btn = $('button:contains("Save Map Settings")');
            const originalText = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...');
            
            $.ajax({
                url: '/api/admin/settings.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(settings),
                dataType: 'json',
                timeout: 10000,
                success: function(response) {
                    btn.prop('disabled', false).html(originalText);
                    if (response.success || response.message) {
                        alert('Map settings saved successfully!');
                        updateMapPreview();
                    } else {
                        alert('Settings saved but no confirmation received');
                    }
                },
                error: function(xhr, status, error) {
                    btn.prop('disabled', false).html(originalText);
                    
                    let errorMsg = 'Unknown error';
                    let errorDetails = '';
                    
                    // Try to parse JSON error
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    } else if (xhr.responseText) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.error || errorMsg;
                        } catch (e) {
                            errorMsg = xhr.statusText || error || 'Request failed';
                            errorDetails = '\n\nRaw response: ' + xhr.responseText.substring(0, 300);
                        }
                    } else {
                        errorMsg = status === 'timeout' ? 'Request timed out' : (error || 'Network error');
                    }
                    
                    // Add HTTP status if available
                    if (xhr.status) {
                        errorDetails += '\n\nHTTP Status: ' + xhr.status;
                    }
                    
                    let fullMessage = 'Error saving settings:\n\n' + errorMsg + errorDetails;
                    
                    // Add helpful messages for common errors
                    if (errorMsg.includes('does not exist') || errorMsg.includes('Table') || errorMsg.includes('Unknown table')) {
                    } else if (errorMsg.includes('Duplicate entry') || errorMsg.includes('23000')) {
                        fullMessage += '\n\nThis might be a duplicate key error. The settings may have been saved. Try refreshing the page.';
                    } else if (errorMsg.includes('Database error')) {
                        fullMessage += '\n\nCheck your database connection and ensure the settings table exists.';
                    }
                    
                    console.error('Settings save error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });
                    
                    alert(fullMessage);
                }
            });
        };
        
        // Utility functions
        window.escapeHtml = function(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };
        
        window.escapeRegex = function(text) {
            return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        };
        
        window.formatDate = function(date) {
            if (!date) return '-';
            return new Date(date).toLocaleDateString();
        };
        
        window.formatDateTime = function(date) {
            if (!date) return '-';
            return new Date(date).toLocaleString();
        };
        
        window.getStatusBadge = function(status) {
            const badges = {
                'online': '<span class="badge bg-success">Online</span>',
                'offline': '<span class="badge bg-danger">Offline</span>',
                'moving': '<span class="badge bg-primary">Moving</span>',
                'idle': '<span class="badge bg-warning">Idle</span>',
                'parked': '<span class="badge bg-secondary">Parked</span>'
            };
            return badges[status] || '<span class="badge bg-secondary">' + escapeHtml(status) + '</span>';
        };
        
        window.updateEmailProviderFields = function() {
            const provider = $('#emailProviderSelect').val();
            
            // Hide all provider-specific fields
            $('#smtpFields, #smtpAuthFields, #apiKeyFields, #sendgridFields, #mailgunFields, #sesFields, #postmarkFields, #sparkpostFields, #mailjetFields, #mandrillFields, #sendinblueFields, #pepipostFields, #postalFields').hide();
            
            // Clear hints
            $('#smtpHostHint, #smtpPortHint, #smtpEncryptionHint, #smtpUsernameHint, #smtpPasswordHint').text('');
            
            // Provider configurations
            const providerConfigs = {
                'smtp': { host: '', port: '587', encryption: 'tls', username: '', password: '', hostHint: '', portHint: '', encryptionHint: '', usernameHint: '', passwordHint: '' },
                'smtp_com': { host: 'mail.smtp.com', port: '587', encryption: 'tls', username: '', password: '', hostHint: 'Default: mail.smtp.com', portHint: 'Default: 587 (TLS) or 465 (SSL)', encryptionHint: 'Use TLS for port 587, SSL for port 465', usernameHint: 'Your SMTP.com username', passwordHint: 'Your SMTP.com password or API key' },
                'smtp2go': { host: 'mail.smtp2go.com', port: '587', encryption: 'tls', username: '', password: '', hostHint: 'Default: mail.smtp2go.com', portHint: 'Default: 587 (TLS) or 465 (SSL)', encryptionHint: 'Use TLS for port 587, SSL for port 465', usernameHint: 'Your SMTP2GO username', passwordHint: 'Your SMTP2GO password' },
                'gmail': { host: 'smtp.gmail.com', port: '587', encryption: 'tls', username: '', password: '', hostHint: 'Default: smtp.gmail.com', portHint: 'Default: 587 (TLS) or 465 (SSL)', encryptionHint: 'Use TLS for port 587, SSL for port 465', usernameHint: 'Your Gmail email address', passwordHint: 'Gmail App Password (not your regular password)' },
                'outlook': { host: 'smtp-mail.outlook.com', port: '587', encryption: 'tls', username: '', password: '', hostHint: 'Default: smtp-mail.outlook.com', portHint: 'Default: 587', encryptionHint: 'Use TLS', usernameHint: 'Your Outlook/Office 365 email address', passwordHint: 'Your Outlook/Office 365 password' },
                'yahoo': { host: 'smtp.mail.yahoo.com', port: '587', encryption: 'tls', username: '', password: '', hostHint: 'Default: smtp.mail.yahoo.com', portHint: 'Default: 587 (TLS) or 465 (SSL)', encryptionHint: 'Use TLS for port 587, SSL for port 465', usernameHint: 'Your Yahoo email address', passwordHint: 'Yahoo App Password (not your regular password)' },
                'zoho': { host: 'smtp.zoho.com', port: '587', encryption: 'tls', username: '', password: '', hostHint: 'Default: smtp.zoho.com (or smtp.zoho.eu for EU)', portHint: 'Default: 587 (TLS) or 465 (SSL)', encryptionHint: 'Use TLS for port 587, SSL for port 465', usernameHint: 'Your Zoho email address', passwordHint: 'Your Zoho password or App Password' },
                'protonmail': { host: '127.0.0.1', port: '1025', encryption: '', username: '', password: '', hostHint: 'Requires ProtonMail Bridge (localhost:1025)', portHint: 'Default: 1025 (via Bridge)', encryptionHint: 'No encryption (handled by Bridge)', usernameHint: 'Not required (via Bridge)', passwordHint: 'Not required (via Bridge)' },
                'fastmail': { host: 'smtp.fastmail.com', port: '587', encryption: 'tls', username: '', password: '', hostHint: 'Default: smtp.fastmail.com', portHint: 'Default: 587 (TLS) or 465 (SSL)', encryptionHint: 'Use TLS for port 587, SSL for port 465', usernameHint: 'Your FastMail email address', passwordHint: 'FastMail App Password' },
                'mail_com': { host: 'smtp.mail.com', port: '587', encryption: 'tls', username: '', password: '', hostHint: 'Default: smtp.mail.com', portHint: 'Default: 587 (TLS) or 465 (SSL)', encryptionHint: 'Use TLS for port 587, SSL for port 465', usernameHint: 'Your Mail.com email address', passwordHint: 'Your Mail.com password' },
                'aol': { host: 'smtp.aol.com', port: '587', encryption: 'tls', username: '', password: '', hostHint: 'Default: smtp.aol.com', portHint: 'Default: 587 (TLS) or 465 (SSL)', encryptionHint: 'Use TLS for port 587, SSL for port 465', usernameHint: 'Your AOL email address', passwordHint: 'AOL App Password (not your regular password)' }
            };
            
            // Show relevant fields based on provider
            if (['smtp', 'smtp_com', 'smtp2go', 'gmail', 'outlook', 'yahoo', 'zoho', 'protonmail', 'fastmail', 'mail_com', 'aol'].includes(provider)) {
                $('#smtpFields, #smtpAuthFields').show();
                
                // Apply provider-specific defaults
                if (providerConfigs[provider]) {
                    const config = providerConfigs[provider];
                    if (config.host) $('#smtpHost').val(config.host);
                    if (config.port) $('#smtpPort').val(config.port);
                    if (config.encryption) $('#smtpEncryption').val(config.encryption);
                    if (config.hostHint) $('#smtpHostHint').text(config.hostHint);
                    if (config.portHint) $('#smtpPortHint').text(config.portHint);
                    if (config.encryptionHint) $('#smtpEncryptionHint').text(config.encryptionHint);
                    if (config.usernameHint) $('#smtpUsernameHint').text(config.usernameHint);
                    if (config.passwordHint) $('#smtpPasswordHint').text(config.passwordHint);
                }
            } else if (provider === 'sendgrid') {
                $('#sendgridFields').show();
            } else if (provider === 'mailgun') {
                $('#mailgunFields').show();
            } else if (provider === 'ses') {
                $('#sesFields').show();
            } else if (provider === 'postmark') {
                $('#postmarkFields').show();
            } else if (provider === 'sparkpost') {
                $('#sparkpostFields').show();
            } else if (provider === 'mailjet') {
                $('#mailjetFields').show();
            } else if (provider === 'mandrill') {
                $('#mandrillFields').show();
            } else if (provider === 'sendinblue') {
                $('#sendinblueFields').show();
            } else if (provider === 'pepipost') {
                $('#pepipostFields').show();
            } else if (provider === 'postal') {
                $('#postalFields').show();
            }
        };
        
        window.loadEmailSettings = function() {
            $.ajax({
                url: '/api/admin/settings.php',
                method: 'GET',
                dataType: 'json',
                success: function(settings) {
                    $('#emailProviderSelect').val(settings.email_provider || 'smtp');
                    $('#emailFrom').val(settings.email_from || '');
                    $('#smtpHost').val(settings.smtp_host || '');
                    $('#smtpPort').val(settings.smtp_port || '587');
                    $('#smtpEncryption').val(settings.smtp_encryption || 'tls');
                    $('#smtpUsername').val(settings.smtp_username || '');
                    // Don't populate password fields for security
                    $('#sendgridApiKey').val('');
                    $('#mailgunDomain').val(settings.mailgun_domain || '');
                    $('#mailgunApiKey').val('');
                    $('#sesRegion').val(settings.ses_region || 'us-east-1');
                    $('#sesAccessKey').val(settings.ses_access_key || '');
                    $('#sesSecretKey').val('');
                    $('#postmarkToken').val('');
                    $('#postmarkServerId').val(settings.postmark_server_id || '');
                    $('#sparkpostApiKey').val('');
                    $('#mailjetApiKey').val(settings.mailjet_api_key || '');
                    $('#mailjetSecretKey').val('');
                    $('#mandrillApiKey').val('');
                    
                    updateEmailProviderFields();
                },
                error: function(xhr) {
                    console.error('Failed to load email settings:', xhr);
                    // Use defaults
                    $('#emailProviderSelect').val('smtp');
                    $('#emailFrom').val('');
                    updateEmailProviderFields();
                }
            });
        };
        
        window.saveEmailSettings = function() {
            const provider = $('#emailProviderSelect').val();
            const fromEmail = $('#emailFrom').val();
            
            if (!fromEmail) {
                alert('Please enter a From email address');
                return;
            }
            
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(fromEmail)) {
                alert('Please enter a valid email address');
                return;
            }
            
            const settings = {
                email_provider: provider,
                email_from: fromEmail
            };
            
            // Add provider-specific settings
            if (['smtp', 'smtp_com', 'smtp2go', 'gmail', 'outlook', 'yahoo', 'zoho', 'protonmail', 'fastmail', 'mail_com', 'aol'].includes(provider)) {
                const host = $('#smtpHost').val();
                const port = parseInt($('#smtpPort').val());
                const encryption = $('#smtpEncryption').val();
                const username = $('#smtpUsername').val();
                const password = $('#smtpPassword').val();
                
                if (!host) {
                    alert('Please enter SMTP host');
                    return;
                }
                
                if (!port || port < 1 || port > 65535) {
                    alert('Please enter a valid SMTP port (1-65535)');
                    return;
                }
                
                settings.smtp_host = host;
                settings.smtp_port = port;
                settings.smtp_encryption = encryption;
                settings.smtp_username = username;
                if (password) {
                    settings.smtp_password = password;
                }
            } else if (provider === 'sendgrid') {
                const apiKey = $('#sendgridApiKey').val();
                if (apiKey) {
                    settings.sendgrid_api_key = apiKey;
                }
            } else if (provider === 'mailgun') {
                const domain = $('#mailgunDomain').val();
                const apiKey = $('#mailgunApiKey').val();
                
                if (!domain) {
                    alert('Please enter Mailgun domain');
                    return;
                }
                
                settings.mailgun_domain = domain;
                if (apiKey) {
                    settings.mailgun_api_key = apiKey;
                }
            } else if (provider === 'ses') {
                const region = $('#sesRegion').val();
                const accessKey = $('#sesAccessKey').val();
                const secretKey = $('#sesSecretKey').val();
                
                if (!region) {
                    alert('Please enter AWS region');
                    return;
                }
                
                if (!accessKey) {
                    alert('Please enter AWS Access Key ID');
                    return;
                }
                
                settings.ses_region = region;
                settings.ses_access_key = accessKey;
                if (secretKey) {
                    settings.ses_secret_key = secretKey;
                }
            } else if (provider === 'postmark') {
                const token = $('#postmarkToken').val();
                const serverId = $('#postmarkServerId').val();
                
                if (!serverId) {
                    alert('Please enter Postmark Server ID');
                    return;
                }
                
                settings.postmark_server_id = serverId;
                if (token) {
                    settings.postmark_token = token;
                }
            } else if (provider === 'sparkpost') {
                const apiKey = $('#sparkpostApiKey').val();
                if (apiKey) {
                    settings.sparkpost_api_key = apiKey;
                }
            } else if (provider === 'mailjet') {
                const apiKey = $('#mailjetApiKey').val();
                const secretKey = $('#mailjetSecretKey').val();
                
                if (!apiKey) {
                    alert('Please enter Mailjet API Key');
                    return;
                }
                
                settings.mailjet_api_key = apiKey;
                if (secretKey) {
                    settings.mailjet_secret_key = secretKey;
                }
            } else if (provider === 'mandrill') {
                const apiKey = $('#mandrillApiKey').val();
                if (apiKey) {
                    settings.mandrill_api_key = apiKey;
                }
            } else if (provider === 'sendinblue') {
                const apiKey = $('#sendinblueApiKey').val();
                if (apiKey) {
                    settings.sendinblue_api_key = apiKey;
                }
            } else if (provider === 'pepipost') {
                const apiKey = $('#pepipostApiKey').val();
                if (apiKey) {
                    settings.pepipost_api_key = apiKey;
                }
            } else if (provider === 'postal') {
                const serverUrl = $('#postalServerUrl').val();
                const apiKey = $('#postalApiKey').val();
                
                if (!serverUrl) {
                    alert('Please enter Postal Server URL');
                    return;
                }
                
                settings.postal_server_url = serverUrl;
                if (apiKey) {
                    settings.postal_api_key = apiKey;
                }
            }
            
            // Show loading state
            const btn = $('button:contains("Save Email Settings")');
            const originalText = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...');
            
            $.ajax({
                url: '/api/admin/settings.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(settings),
                dataType: 'json',
                timeout: 10000,
                success: function(response) {
                    btn.prop('disabled', false).html(originalText);
                    if (response.success || response.message) {
                        alert('Email settings saved successfully!');
                    } else {
                        alert('Settings saved but no confirmation received');
                    }
                },
                error: function(xhr, status, error) {
                    btn.prop('disabled', false).html(originalText);
                    
                    let errorMsg = 'Unknown error';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    } else if (xhr.responseText) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.error || errorMsg;
                        } catch (e) {
                            errorMsg = xhr.statusText || error || 'Request failed';
                        }
                    } else {
                        errorMsg = status === 'timeout' ? 'Request timed out' : (error || 'Network error');
                    }
                    
                    alert('Error saving email settings:\n\n' + errorMsg);
                    console.error('Email settings save error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });
                }
            });
        };
        
        window.testEmailSettings = function() {
            const fromEmail = $('#emailFrom').val();
            if (!fromEmail) {
                alert('Please configure and save email settings first');
                return;
            }
            
            const testEmail = prompt('Enter email address to send test email to:', '');
            if (!testEmail || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(testEmail)) {
                alert('Please enter a valid email address');
                return;
            }
            
            if (!confirm('Send test email to ' + testEmail + '?')) {
                return;
            }
            
            const btn = $('button:contains("Send Test Email")');
            const originalText = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Sending...');
            
            $.ajax({
                url: '/api/admin/settings.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    test_email: true,
                    test_email_to: testEmail
                }),
                dataType: 'json',
                timeout: 15000,
                success: function(response) {
                    btn.prop('disabled', false).html(originalText);
                    if (response.success || response.message) {
                        alert('Test email sent successfully! Check ' + testEmail + ' for the message.');
                    } else {
                        alert('Test email may have been sent, but no confirmation received.');
                    }
                },
                error: function(xhr, status, error) {
                    btn.prop('disabled', false).html(originalText);
                    
                    let errorMsg = 'Unknown error';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    } else if (xhr.responseText) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.error || errorMsg;
                        } catch (e) {
                            errorMsg = xhr.statusText || error || 'Request failed';
                        }
                    } else {
                        errorMsg = status === 'timeout' ? 'Request timed out' : (error || 'Network error');
                    }
                    
                    alert('Error sending test email:\n\n' + errorMsg);
                    console.error('Test email error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });
                }
            });
        };
        
        // Load email settings when settings tab is shown
        $('#settings-tab').on('shown.bs.tab', function() {
            setTimeout(function() {
                loadEmailSettings();
            }, 100);
        });
        
        // Initialize email provider fields on page load
        updateEmailProviderFields();
        
        // Email Log Viewer Functions
        let currentEmailLogPage = 1;
        
        window.loadEmailLogs = function(page = 1) {
            currentEmailLogPage = page;
            const status = $('#emailLogStatusFilter').val();
            const provider = $('#emailLogProviderFilter').val();
            const search = $('#emailLogSearch').val();
            const sort = $('#emailLogSort').val();
            
            $.ajax({
                url: '/api/admin/email_logs.php',
                method: 'GET',
                data: {
                    status: status || undefined,
                    provider: provider || undefined,
                    search: search || undefined,
                    sort: sort,
                    limit: 50,
                    page: page
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        displayEmailLogs(response.logs);
                        updateEmailLogPagination(response.page, response.pages, response.total);
                        $('#emailLogCount').text(`${response.total} log${response.total !== 1 ? 's' : ''}`);
                    }
                },
                error: function(xhr) {
                    console.error('Failed to load email logs:', xhr);
                    $('#emailLogsTableBody').html('<tr><td colspan="7" class="text-center text-danger">Failed to load logs</td></tr>');
                }
            });
        };
        
        window.displayEmailLogs = function(logs) {
            const tbody = $('#emailLogsTableBody');
            tbody.empty();
            
            if (logs.length === 0) {
                tbody.append('<tr><td colspan="7" class="text-center text-muted">No logs found</td></tr>');
                return;
            }
            
            logs.forEach(log => {
                const statusBadge = getEmailLogStatusBadge(log.status);
                const time = formatDate(log.created_at);
                const error = log.error_message ? escapeHtml(log.error_message.substring(0, 100)) + (log.error_message.length > 100 ? '...' : '') : '-';
                const subject = log.subject ? escapeHtml(log.subject.substring(0, 50)) + (log.subject.length > 50 ? '...' : '') : '-';
                const provider = log.provider ? escapeHtml(log.provider) : '-';
                
                const row = `
                    <tr>
                        <td><small>${time}</small></td>
                        <td>${escapeHtml(log.recipient)}</td>
                        <td>${subject}</td>
                        <td>${provider}</td>
                        <td>${statusBadge}</td>
                        <td><small class="text-muted">${error}</small></td>
                        <td>
                            <button class="btn btn-sm btn-outline-info" onclick="viewEmailLogDetails(${log.id})" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });
        };
        
        window.getEmailLogStatusBadge = function(status) {
            const badges = {
                'pending': '<span class="badge bg-warning">Pending</span>',
                'sent': '<span class="badge bg-success">Sent</span>',
                'failed': '<span class="badge bg-danger">Failed</span>',
                'bounced': '<span class="badge bg-secondary">Bounced</span>'
            };
            return badges[status] || '<span class="badge bg-secondary">' + escapeHtml(status) + '</span>';
        };
        
        window.updateEmailLogPagination = function(currentPage, totalPages, total) {
            const pagination = $('#emailLogPagination');
            pagination.empty();
            
            if (totalPages <= 1) {
                return;
            }
            
            // Previous button
            pagination.append(`
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="loadEmailLogs(${currentPage - 1}); return false;">Previous</a>
                </li>
            `);
            
            // Page numbers
            const startPage = Math.max(1, currentPage - 2);
            const endPage = Math.min(totalPages, currentPage + 2);
            
            if (startPage > 1) {
                pagination.append(`<li class="page-item"><a class="page-link" href="#" onclick="loadEmailLogs(1); return false;">1</a></li>`);
                if (startPage > 2) {
                    pagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                }
            }
            
            for (let i = startPage; i <= endPage; i++) {
                pagination.append(`
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="loadEmailLogs(${i}); return false;">${i}</a>
                    </li>
                `);
            }
            
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    pagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                }
                pagination.append(`<li class="page-item"><a class="page-link" href="#" onclick="loadEmailLogs(${totalPages}); return false;">${totalPages}</a></li>`);
            }
            
            // Next button
            pagination.append(`
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="loadEmailLogs(${currentPage + 1}); return false;">Next</a>
                </li>
            `);
        };
        
        window.viewEmailLogDetails = function(logId) {
            $.ajax({
                url: '/api/admin/email_logs.php',
                method: 'GET',
                data: { log_id: logId },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.logs.length > 0) {
                        const log = response.logs[0];
                        showEmailLogModal(log);
                    } else {
                        alert('Log not found');
                    }
                },
                error: function(xhr) {
                    alert('Failed to load log details');
                }
            });
        };
        
        window.showEmailLogModal = function(log) {
            const modal = `
                <div class="modal fade" id="emailLogModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Email Log Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <table class="table table-sm">
                                    <tr><th>Time:</th><td>${formatDate(log.created_at)}</td></tr>
                                    <tr><th>Recipient:</th><td>${escapeHtml(log.recipient)}</td></tr>
                                    <tr><th>Subject:</th><td>${escapeHtml(log.subject || '-')}</td></tr>
                                    <tr><th>Provider:</th><td>${escapeHtml(log.provider || '-')}</td></tr>
                                    <tr><th>Status:</th><td>${getEmailLogStatusBadge(log.status)}</td></tr>
                                    ${log.error_message ? `<tr><th>Error:</th><td><pre class="text-danger">${escapeHtml(log.error_message)}</pre></td></tr>` : ''}
                                    ${log.response_data ? `<tr><th>Response:</th><td><pre>${escapeHtml(JSON.stringify(log.response_data, null, 2))}</pre></td></tr>` : ''}
                                    ${log.debug_data ? `<tr><th>Debug Data:</th><td><pre>${escapeHtml(JSON.stringify(log.debug_data, null, 2))}</pre></td></tr>` : ''}
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('#emailLogModal').remove();
            $('body').append(modal);
            new bootstrap.Modal(document.getElementById('emailLogModal')).show();
        };
        
        window.clearEmailLogs = function() {
            if (!confirm('Are you sure you want to clear all email logs? This cannot be undone.')) {
                return;
            }
            
            $.ajax({
                url: '/api/admin/email_logs.php',
                method: 'DELETE',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Email logs cleared successfully');
                        loadEmailLogs(1);
                    } else {
                        alert('Failed to clear logs');
                    }
                },
                error: function(xhr) {
                    alert('Failed to clear logs: ' + (xhr.responseJSON?.error || 'Unknown error'));
                }
            });
        };
        
        window.saveEmailDebugSetting = function() {
            const debugEnabled = $('#emailDebugToggle').is(':checked');
            $.ajax({
                url: '/api/admin/settings.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ email_debug: debugEnabled ? '1' : '0' }),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        console.log('Email debug setting saved');
                    }
                },
                error: function(xhr) {
                    console.error('Failed to save email debug setting:', xhr);
                }
            });
        };
        
        window.debounce = function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        };
        
        window.loadEmailDebugSetting = function() {
            $.ajax({
                url: '/api/admin/settings.php',
                method: 'GET',
                dataType: 'json',
                success: function(settings) {
                    $('#emailDebugToggle').prop('checked', settings.email_debug === '1' || settings.email_debug === 1);
                }
            });
        };
        
        window.loadEmailLogProviders = function() {
            $.ajax({
                url: '/api/admin/email_logs.php',
                method: 'GET',
                data: { limit: 1000 },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const providers = [...new Set(response.logs.map(log => log.provider).filter(p => p))].sort();
                        const select = $('#emailLogProviderFilter');
                        select.find('option:not(:first)').remove();
                        providers.forEach(provider => {
                            select.append(`<option value="${escapeHtml(provider)}">${escapeHtml(provider)}</option>`);
                        });
                    }
                }
            });
        };
        
        // Load email logs when email tab is shown
        $(document).on('shown.bs.tab', '#email-tab', function() {
            loadEmailLogs();
            loadEmailDebugSetting();
            loadEmailLogProviders();
        });
        
        // Simulator functions
        let simulatorStreamInterval = null;
        let simulatorStreaming = false;
        
        window.loadSimulatorDevices = function() {
            $.ajax({
                url: '/api/admin/simulator.php?action=devices',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    const select = $('#simulatorDeviceId');
                    const currentValue = select.val();
                    select.empty().append('<option value="">Select Device</option>');
                    
                    if (response.devices && response.devices.length > 0) {
                        response.devices.forEach(device => {
                            const isSimulated = device.device_uid && device.device_uid.startsWith('SIM-');
                            const label = isSimulated 
                                ? `${escapeHtml(device.device_uid)} (${escapeHtml(device.imei || 'N/A')}) - Simulated`
                                : `${escapeHtml(device.device_uid)} (${escapeHtml(device.imei || 'N/A')})`;
                            select.append(`<option value="${device.id}">${label}</option>`);
                        });
                        
                        // If no device was selected and we have devices, select the first one (likely the auto-created one)
                        if (!currentValue && response.devices.length === 1 && response.devices[0].device_uid.startsWith('SIM-')) {
                            select.val(response.devices[0].id);
                            $('#simulatorStatus').html(`<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>A simulated device has been automatically created for testing. You can start streaming immediately!</div>`);
                        }
                    } else {
                        $('#simulatorStatus').html(`<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>No devices found. Please create a device first.</div>`);
                    }
                },
                error: function(xhr) {
                    const select = $('#simulatorDeviceId');
                    select.empty().append('<option value="">Error loading devices</option>');
                    $('#simulatorStatus').html(`<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Failed to load devices: ${escapeHtml(xhr.responseJSON?.error || 'Unknown error')}</div>`);
                }
            });
        };
        
        window.simulatorSendSingle = function() {
            const deviceId = $('#simulatorDeviceId').val();
            if (!deviceId) {
                alert('Please select a device');
                return;
            }
            
            const config = {
                device_id: deviceId,
                speed: $('#simulatorSpeed').val() || null,
                moving: $('#simulatorMoving').val() !== '' ? ($('#simulatorMoving').val() === '1') : null,
                route: $('#simulatorRoute').val(),
                interval: parseInt($('#simulatorInterval').val()) || 30
            };
            
            $('#simulatorSendSingle').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Sending...');
            
            $.ajax({
                url: '/api/admin/simulator.php?action=send',
                method: 'POST',
                data: config,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#simulatorStatus').html(`<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Telemetry packet sent successfully!</div>`);
                        updateSimulatorStatus('success', response.telemetry);
                    } else {
                        $('#simulatorStatus').html(`<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>${escapeHtml(response.error || 'Failed to send telemetry')}</div>`);
                    }
                    $('#simulatorSendSingle').prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i>Send Single Packet');
                },
                error: function(xhr) {
                    const error = xhr.responseJSON?.error || 'Failed to send telemetry';
                    $('#simulatorStatus').html(`<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>${escapeHtml(error)}</div>`);
                    $('#simulatorSendSingle').prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i>Send Single Packet');
                }
            });
        };
        
        window.simulatorStartStream = function() {
            const deviceId = $('#simulatorDeviceId').val();
            if (!deviceId) {
                alert('Please select a device');
                return;
            }
            
            if (simulatorStreaming) {
                return;
            }
            
            const config = {
                device_id: deviceId,
                speed: $('#simulatorSpeed').val() || null,
                moving: $('#simulatorMoving').val() !== '' ? ($('#simulatorMoving').val() === '1') : null,
                route: $('#simulatorRoute').val(),
                interval: parseInt($('#simulatorInterval').val()) || 30,
                iterations: $('#simulatorIterations').val() || null
            };
            
            simulatorStreaming = true;
            $('#simulatorStartStream').hide();
            $('#simulatorStopStream').show();
            $('#simulatorSendSingle').prop('disabled', true);
            $('#simulatorStatus').html(`<div class="alert alert-info"><i class="fas fa-spinner fa-spin me-2"></i>Starting stream...</div>`);
            
            // Send packets at interval
            let packetCount = 0;
            let failedCount = 0;
            const maxIterations = config.iterations ? parseInt(config.iterations) : null;
            
            const sendPacket = function() {
                if (!simulatorStreaming) {
                    return;
                }
                
                if (maxIterations && packetCount >= maxIterations) {
                    simulatorStopStream();
                    return;
                }
                
                $.ajax({
                    url: '/api/admin/simulator.php?action=send',
                    method: 'POST',
                    data: config,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            packetCount++;
                            updateSimulatorStatus('streaming', response.telemetry, packetCount, failedCount);
                        } else {
                            failedCount++;
                            updateSimulatorStatus('streaming', null, packetCount, failedCount);
                        }
                    },
                    error: function() {
                        failedCount++;
                        updateSimulatorStatus('streaming', null, packetCount, failedCount);
                    }
                });
            };
            
            // Send first packet immediately
            sendPacket();
            
            // Then send at interval
            simulatorStreamInterval = setInterval(sendPacket, config.interval * 1000);
        };
        
        window.simulatorStopStream = function() {
            simulatorStreaming = false;
            if (simulatorStreamInterval) {
                clearInterval(simulatorStreamInterval);
                simulatorStreamInterval = null;
            }
            $('#simulatorStartStream').show();
            $('#simulatorStopStream').hide();
            $('#simulatorSendSingle').prop('disabled', false);
            $('#simulatorStatus').html(`<div class="alert alert-warning"><i class="fas fa-stop-circle me-2"></i>Streaming stopped</div>`);
        };
        
        function updateSimulatorStatus(status, telemetry, sent = 0, failed = 0) {
            const tbody = $('#simulatorStatusTable');
            const now = new Date().toLocaleTimeString();
            const location = telemetry ? `${telemetry.lat.toFixed(6)}, ${telemetry.lon.toFixed(6)}` : 'N/A';
            
            if (status === 'success' || status === 'streaming') {
                tbody.html(`
                    <tr>
                        <td>${now}</td>
                        <td><span class="badge bg-${status === 'success' ? 'success' : 'info'}">${status === 'success' ? 'Sent' : 'Streaming'}</span></td>
                        <td>${sent}</td>
                        <td>${failed}</td>
                        <td>${location}</td>
                    </tr>
                `);
            }
        }
        
        // Load devices when simulator tab is shown
        $(document).on('shown.bs.tab', '#simulator-tab', function() {
            loadSimulatorDevices();
        });
        
    });
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../views/layout.php';
?>
