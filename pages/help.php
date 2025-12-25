<?php
/**
 * Help System Page
 * Comprehensive online help documentation
 */

require_once __DIR__ . '/../config.php';
$GLOBALS['config'] = $config;

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/tenant.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

db_init($config['database']);
session_start_custom($config['session']);

require_auth();

$showNav = true;

ob_start();
?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Help Sidebar Navigation -->
        <div class="col-md-3">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i>Help Center</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#getting-started" class="list-group-item list-group-item-action help-nav-item active" data-section="getting-started">
                        <i class="fas fa-rocket me-2"></i>Getting Started
                    </a>
                    <a href="#devices" class="list-group-item list-group-item-action help-nav-item" data-section="devices">
                        <i class="fas fa-microchip me-2"></i>Devices & Telematics
                    </a>
                    <a href="#assets" class="list-group-item list-group-item-action help-nav-item" data-section="assets">
                        <i class="fas fa-truck me-2"></i>Assets & Vehicles
                    </a>
                    <a href="#alerts" class="list-group-item list-group-item-action help-nav-item" data-section="alerts">
                        <i class="fas fa-bell me-2"></i>Alerts & Notifications
                    </a>
                    <a href="#geofences" class="list-group-item list-group-item-action help-nav-item" data-section="geofences">
                        <i class="fas fa-map-marked-alt me-2"></i>Geofences
                    </a>
                    <a href="#reports" class="list-group-item list-group-item-action help-nav-item" data-section="reports">
                        <i class="fas fa-chart-bar me-2"></i>Reports & Analytics
                    </a>
                    <a href="#users" class="list-group-item list-group-item-action help-nav-item" data-section="users">
                        <i class="fas fa-users me-2"></i>User Management
                    </a>
                    <a href="#admin" class="list-group-item list-group-item-action help-nav-item" data-section="admin">
                        <i class="fas fa-cog me-2"></i>Administration
                    </a>
                    <a href="#email" class="list-group-item list-group-item-action help-nav-item" data-section="email">
                        <i class="fas fa-envelope me-2"></i>Email Configuration
                    </a>
                    <a href="#maps" class="list-group-item list-group-item-action help-nav-item" data-section="maps">
                        <i class="fas fa-map me-2"></i>Map Settings
                    </a>
                    <a href="#troubleshooting" class="list-group-item list-group-item-action help-nav-item" data-section="troubleshooting">
                        <i class="fas fa-tools me-2"></i>Troubleshooting
                    </a>
                </div>
            </div>
        </div>

        <!-- Help Content -->
        <div class="col-md-9">
            <!-- Getting Started -->
            <div id="help-getting-started" class="help-section">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-rocket me-2"></i>Getting Started</h4>
                    </div>
                    <div class="card-body">
                        <h5>Welcome to DragNet Portal</h5>
                        <p>DragNet Portal is a comprehensive telematics and asset tracking platform designed to help you monitor and manage your fleet, vehicles, and equipment in real-time.</p>
                        
                        <h6 class="mt-4">Quick Start Guide</h6>
                        <ol>
                            <li><strong>Access the Platform:</strong> Log in at <a href="https://portal.dragnet.ca" target="_blank">portal.dragnet.ca</a></li>
                            <li><strong>Add Your First Device:</strong> Go to <strong>Devices</strong> and add a telematics device (see <a href="#devices">Devices & Telematics</a> section)</li>
                            <li><strong>Create an Asset:</strong> Link your device to a vehicle or asset (see <a href="#assets">Assets & Vehicles</a> section)</li>
                            <li><strong>View Live Tracking:</strong> Check the <strong>Map</strong> page to see real-time location data</li>
                            <li><strong>Set Up Alerts:</strong> Configure alerts for important events (see <a href="#alerts">Alerts & Notifications</a> section)</li>
                        </ol>

                        <h6 class="mt-4">Platform Overview</h6>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6><i class="fas fa-microchip text-primary me-2"></i>Device Management</h6>
                                        <p class="mb-0">Add and configure telematics devices, monitor device status, and view device health metrics.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6><i class="fas fa-map-marked-alt text-success me-2"></i>Live Tracking</h6>
                                        <p class="mb-0">Real-time GPS tracking with interactive maps, route history, and location analytics.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6><i class="fas fa-bell text-warning me-2"></i>Alerts & Notifications</h6>
                                        <p class="mb-0">Configure intelligent alerts for geofence violations, speed limits, device offline, and more.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6><i class="fas fa-chart-bar text-info me-2"></i>Reports & Analytics</h6>
                                        <p class="mb-0">Generate detailed reports on vehicle usage, driver behavior, fuel consumption, and maintenance.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Devices & Telematics -->
            <div id="help-devices" class="help-section" style="display: none;">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-microchip me-2"></i>Devices & Telematics</h4>
                    </div>
                    <div class="card-body">
                        <h5>Adding Telematics Devices</h5>
                        <p>DragNet Portal supports Teltonika FMM13A and other compatible telematics devices. Follow these steps to add and configure your device:</p>

                        <h6 class="mt-4">Step 1: Access Device Management</h6>
                        <ol>
                            <li>Navigate to <strong>Admin</strong> → <strong>Devices</strong> tab (Administrators only)</li>
                            <li>Or go to <strong>Devices</strong> from the main menu (for viewing)</li>
                        </ol>

                        <h6 class="mt-4">Step 2: Add a New Device</h6>
                        <ol>
                            <li>Click the <strong>"Add Device"</strong> button</li>
                            <li>Fill in the required information:
                                <ul>
                                    <li><strong>Device UID:</strong> Unique identifier for the device (e.g., device serial number)</li>
                                    <li><strong>IMEI:</strong> International Mobile Equipment Identity (15 digits, found on device or SIM card)</li>
                                    <li><strong>ICCID:</strong> Integrated Circuit Card Identifier (SIM card number, optional)</li>
                                    <li><strong>Model:</strong> Device model (default: FMM13A)</li>
                                    <li><strong>Firmware Version:</strong> Current firmware version (optional)</li>
                                    <li><strong>Tenant:</strong> Select the tenant/organization this device belongs to</li>
                                </ul>
                            </li>
                            <li>Click <strong>"Save"</strong> to add the device</li>
                        </ol>

                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Tip:</strong> The IMEI is critical for device communication. Ensure it matches exactly what's printed on your device or SIM card.
                        </div>

                        <h6 class="mt-4">Step 3: Configure Device Settings</h6>
                        <p>After adding a device, you may need to configure it:</p>
                        <ol>
                            <li>Click on the device in the list to view details</li>
                            <li>Configure device-specific settings:
                                <ul>
                                    <li><strong>Data Transmission Interval:</strong> How often the device sends location updates</li>
                                    <li><strong>GPS Settings:</strong> GPS update frequency and accuracy requirements</li>
                                    <li><strong>IO Element Mapping:</strong> Map device IO elements to meaningful labels (see below)</li>
                                </ul>
                            </li>
                        </ol>

                        <h6 class="mt-4">Teltonika FMM13A Specific Configuration</h6>
                        <p>The Teltonika FMM13A is a compact GPS tracker with multiple IO inputs. Here's how to configure it:</p>

                        <h6 class="mt-3">Device Connection Setup</h6>
                        <ol>
                            <li><strong>Insert SIM Card:</strong> Ensure a compatible GSM SIM card is inserted</li>
                            <li><strong>Power Connection:</strong> Connect to vehicle power (12V/24V) or use internal battery</li>
                            <li><strong>GPS Antenna:</strong> Ensure GPS antenna has clear view of sky</li>
                            <li><strong>Configure via SMS:</strong> Send configuration commands via SMS (see device manual)</li>
                            <li><strong>Or Configure via Web:</strong> Use Teltonika Configurator tool</li>
                        </ol>

                        <h6 class="mt-3">Data Server Configuration</h6>
                        <p>Configure the device to send data to DragNet Portal:</p>
                        <ol>
                            <li>Server IP/URL: <code>portal.dragnet.ca</code></li>
                            <li>Port: <code>5027</code> (default Teltonika port, adjust if needed)</li>
                            <li>Protocol: <code>TCP</code></li>
                            <li>Data Protocol: <code>Codec8</code> or <code>Codec8E</code> (recommended)</li>
                        </ol>

                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Important:</strong> Ensure your server firewall allows incoming connections on the configured port.
                        </div>

                        <h6 class="mt-4">IO Element Configuration</h6>
                        <p>Teltonika devices support multiple IO (Input/Output) elements. Configure them in DragNet Portal:</p>
                        <ol>
                            <li>Go to <strong>Devices</strong> → Select your device → <strong>IO Labels</strong> tab</li>
                            <li>Add labels for each IO element:
                                <ul>
                                    <li><strong>Digital Inputs:</strong> Ignition, door sensors, panic buttons, etc.</li>
                                    <li><strong>Analog Inputs:</strong> Fuel level, temperature, pressure sensors, etc.</li>
                                    <li><strong>Digital Outputs:</strong> Relay controls, buzzer, etc.</li>
                                    <li><strong>Analog Outputs:</strong> Variable controls</li>
                                </ul>
                            </li>
                            <li>Map each IO ID to a meaningful label (e.g., "IO ID 1" → "Ignition Status")</li>
                        </ol>

                        <h6 class="mt-4">Device Status Monitoring</h6>
                        <p>Monitor your device health in real-time:</p>
                        <ul>
                            <li><strong>Online/Offline Status:</strong> Shows if device is currently connected</li>
                            <li><strong>Last Seen:</strong> Timestamp of last data received</li>
                            <li><strong>GSM Signal:</strong> Cellular signal strength (0-31, higher is better)</li>
                            <li><strong>Battery Level:</strong> Internal battery percentage (if applicable)</li>
                            <li><strong>External Voltage:</strong> Vehicle power supply voltage</li>
                        </ul>

                        <h6 class="mt-4">Troubleshooting Device Connection</h6>
                        <div class="card mt-3">
                            <div class="card-body">
                                <h6>Device Not Appearing Online</h6>
                                <ul>
                                    <li>Verify SIM card has active data plan</li>
                                    <li>Check device power connection</li>
                                    <li>Verify server IP/port configuration in device</li>
                                    <li>Check firewall rules allow incoming connections</li>
                                    <li>Review device logs in Admin → Logs tab</li>
                                </ul>

                                <h6 class="mt-3">No GPS Data Received</h6>
                                <ul>
                                    <li>Ensure GPS antenna has clear view of sky</li>
                                    <li>Check device is in area with GPS coverage</li>
                                    <li>Verify GPS is enabled in device configuration</li>
                                    <li>Allow 5-10 minutes for first GPS fix after power-on</li>
                                </ul>

                                <h6 class="mt-3">Incorrect Location Data</h6>
                                <ul>
                                    <li>Check GPS antenna connection</li>
                                    <li>Verify device firmware is up to date</li>
                                    <li>Review device accuracy settings</li>
                                    <li>Check for interference from vehicle electronics</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assets & Vehicles -->
            <div id="help-assets" class="help-section" style="display: none;">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0"><i class="fas fa-truck me-2"></i>Assets & Vehicles</h4>
                    </div>
                    <div class="card-body">
                        <h5>Managing Assets and Vehicles</h5>
                        <p>Assets represent vehicles, equipment, or other tracked items in your fleet. Link devices to assets for better organization and reporting.</p>

                        <h6 class="mt-4">Creating an Asset</h6>
                        <ol>
                            <li>Navigate to <strong>Assets</strong> from the main menu</li>
                            <li>Click <strong>"Add Asset"</strong></li>
                            <li>Fill in asset information:
                                <ul>
                                    <li><strong>Name:</strong> Descriptive name (e.g., "Delivery Truck #5")</li>
                                    <li><strong>Vehicle ID:</strong> License plate, VIN, or internal ID</li>
                                    <li><strong>Device:</strong> Select the telematics device to link (optional)</li>
                                    <li><strong>Status:</strong> Active, Inactive, or Maintenance</li>
                                </ul>
                            </li>
                            <li>Click <strong>"Save"</strong></li>
                        </ol>

                        <h6 class="mt-4">Linking Devices to Assets</h6>
                        <p>Linking a device to an asset provides:</p>
                        <ul>
                            <li>Better organization in reports and dashboards</li>
                            <li>Asset-specific alerts and notifications</li>
                            <li>Historical tracking by asset name</li>
                            <li>Easier identification on maps</li>
                        </ul>

                        <h6 class="mt-4">Asset Status Management</h6>
                        <ul>
                            <li><strong>Active:</strong> Asset is in use and being tracked</li>
                            <li><strong>Inactive:</strong> Asset is not currently in use (temporarily disabled)</li>
                            <li><strong>Maintenance:</strong> Asset is undergoing maintenance or repair</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Alerts & Notifications -->
            <div id="help-alerts" class="help-section" style="display: none;">
                <div class="card mb-4">
                    <div class="card-header bg-warning text-white">
                        <h4 class="mb-0"><i class="fas fa-bell me-2"></i>Alerts & Notifications</h4>
                    </div>
                    <div class="card-body">
                        <h5>Configuring Alerts</h5>
                        <p>Set up intelligent alerts to be notified of important events in real-time.</p>

                        <h6 class="mt-4">Alert Types</h6>
                        <ul>
                            <li><strong>Device Offline:</strong> Device stops sending data</li>
                            <li><strong>Ignition On/Off:</strong> Vehicle ignition status changes</li>
                            <li><strong>Speed Violation:</strong> Vehicle exceeds speed limit</li>
                            <li><strong>Idle Time:</strong> Vehicle idles for extended period</li>
                            <li><strong>Low Voltage:</strong> Vehicle battery voltage drops</li>
                            <li><strong>Low Battery:</strong> Device battery level is low</li>
                            <li><strong>Geofence Entry/Exit:</strong> Vehicle enters or leaves a defined area</li>
                        </ul>

                        <h6 class="mt-4">Alert Severity Levels</h6>
                        <ul>
                            <li><strong>Info:</strong> Informational alerts (e.g., ignition on)</li>
                            <li><strong>Warning:</strong> Important events requiring attention</li>
                            <li><strong>Critical:</strong> Urgent issues requiring immediate action</li>
                        </ul>

                        <h6 class="mt-4">Managing Alerts</h6>
                        <ol>
                            <li>Navigate to <strong>Alerts</strong> from the main menu</li>
                            <li>View all active alerts with filtering options</li>
                            <li>Acknowledge alerts by clicking the acknowledge button</li>
                            <li>Assign alerts to team members for follow-up</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Geofences -->
            <div id="help-geofences" class="help-section" style="display: none;">
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h4 class="mb-0"><i class="fas fa-map-marked-alt me-2"></i>Geofences</h4>
                    </div>
                    <div class="card-body">
                        <h5>Creating Geofences</h5>
                        <p>Geofences are virtual boundaries that trigger alerts when vehicles enter or exit defined areas.</p>

                        <h6 class="mt-4">Creating a Geofence</h6>
                        <ol>
                            <li>Navigate to <strong>Geofences</strong> from the main menu</li>
                            <li>Click <strong>"Add Geofence"</strong></li>
                            <li>Select geofence type:
                                <ul>
                                    <li><strong>Circle:</strong> Define center point and radius</li>
                                    <li><strong>Rectangle:</strong> Define corner coordinates</li>
                                    <li><strong>Polygon:</strong> Draw custom shape on map</li>
                                </ul>
                            </li>
                            <li>Configure rules:
                                <ul>
                                    <li>Entry alerts (notify when vehicle enters)</li>
                                    <li>Exit alerts (notify when vehicle leaves)</li>
                                    <li>Speed limits within geofence</li>
                                    <li>Time restrictions</li>
                                </ul>
                            </li>
                            <li>Click <strong>"Save"</strong></li>
                        </ol>

                        <h6 class="mt-4">Geofence Best Practices</h6>
                        <ul>
                            <li>Use descriptive names for easy identification</li>
                            <li>Set appropriate buffer zones for accurate detection</li>
                            <li>Configure entry/exit delays to reduce false alerts</li>
                            <li>Link geofences to specific assets or device groups</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Reports -->
            <div id="help-reports" class="help-section" style="display: none;">
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <h4 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Reports & Analytics</h4>
                    </div>
                    <div class="card-body">
                        <h5>Generating Reports</h5>
                        <p>Access comprehensive reports and analytics for your fleet operations.</p>

                        <h6 class="mt-4">Available Reports</h6>
                        <ul>
                            <li><strong>Vehicle Activity:</strong> Daily/weekly/monthly usage summaries</li>
                            <li><strong>Route History:</strong> Complete trip logs with maps</li>
                            <li><strong>Speed Analysis:</strong> Speed violations and average speeds</li>
                            <li><strong>Idle Time:</strong> Vehicle idle duration reports</li>
                            <li><strong>Fuel Consumption:</strong> Fuel usage estimates (if fuel sensor connected)</li>
                            <li><strong>Maintenance Alerts:</strong> Service reminders based on mileage/hours</li>
                        </ul>

                        <h6 class="mt-4">Exporting Reports</h6>
                        <p>Reports can be exported in various formats:</p>
                        <ul>
                            <li>PDF for printing and archiving</li>
                            <li>CSV/Excel for data analysis</li>
                            <li>Email delivery for scheduled reports</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- User Management -->
            <div id="help-users" class="help-section" style="display: none;">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-users me-2"></i>User Management</h4>
                    </div>
                    <div class="card-body">
                        <h5>Managing Users</h5>
                        <p>Control access to the platform with role-based user management.</p>

                        <h6 class="mt-4">User Roles</h6>
                        <ul>
                            <li><strong>Guest:</strong> Read-only access, view-only permissions</li>
                            <li><strong>ReadOnly:</strong> Can view all data but cannot make changes</li>
                            <li><strong>Operator:</strong> Can manage devices, assets, and view reports</li>
                            <li><strong>Administrator:</strong> Full access including user management and settings</li>
                            <li><strong>TenantOwner:</strong> Complete control over tenant and all resources</li>
                        </ul>

                        <h6 class="mt-4">Adding Users</h6>
                        <ol>
                            <li>Navigate to <strong>Admin</strong> → <strong>Users</strong> tab (Administrators only)</li>
                            <li>Click <strong>"Add User"</strong></li>
                            <li>Enter user email address</li>
                            <li>Select appropriate role</li>
                            <li>Assign to tenant</li>
                            <li>Click <strong>"Save"</strong></li>
                        </ol>

                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> User authentication is handled via SSO (Single Sign-On). Users will receive an email invitation to set up their account.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Administration -->
            <div id="help-admin" class="help-section" style="display: none;">
                <div class="card mb-4">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0"><i class="fas fa-cog me-2"></i>Administration</h4>
                    </div>
                    <div class="card-body">
                        <h5>Administrative Functions</h5>
                        <p>The Admin panel provides comprehensive management tools for your platform.</p>

                        <h6 class="mt-4">Tenant Management</h6>
                        <p>Create and manage multiple tenants (organizations) within the platform:</p>
                        <ol>
                            <li>Navigate to <strong>Admin</strong> → <strong>Tenants</strong> tab</li>
                            <li>Add, edit, or delete tenants</li>
                            <li>Configure tenant-specific settings</li>
                        </ol>

                        <h6 class="mt-4">Device Management</h6>
                        <p>Centralized device administration:</p>
                        <ul>
                            <li>Add, edit, and remove devices</li>
                            <li>Bulk device operations</li>
                            <li>Device configuration templates</li>
                            <li>Device health monitoring</li>
                        </ul>

                        <h6 class="mt-4">Telematics Logs</h6>
                        <p>View real-time telematics data logs:</p>
                        <ul>
                            <li>Filter by device, date, or data type</li>
                            <li>Search logs for specific events</li>
                            <li>Export logs for analysis</li>
                            <li>Monitor data ingestion in real-time</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Email Configuration -->
            <div id="help-email" class="help-section" style="display: none;">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0"><i class="fas fa-envelope me-2"></i>Email Configuration</h4>
                    </div>
                    <div class="card-body">
                        <h5>Configuring Email Settings</h5>
                        <p>Set up email relay providers to send notifications and alerts via email.</p>

                        <h6 class="mt-4">Supported Email Providers</h6>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h6>SMTP Providers</h6>
                                <ul>
                                    <li>SMTP (Generic/Custom)</li>
                                    <li>SMTP.com</li>
                                    <li>SMTP2GO</li>
                                    <li>Gmail</li>
                                    <li>Outlook/Office 365</li>
                                    <li>Yahoo Mail</li>
                                    <li>Zoho Mail</li>
                                    <li>ProtonMail</li>
                                    <li>FastMail</li>
                                    <li>Mail.com</li>
                                    <li>AOL Mail</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>API Providers</h6>
                                <ul>
                                    <li>SendGrid</li>
                                    <li>Mailgun</li>
                                    <li>Amazon SES</li>
                                    <li>Postmark</li>
                                    <li>SparkPost</li>
                                    <li>Mailjet</li>
                                    <li>Mandrill (Mailchimp)</li>
                                    <li>Sendinblue (Brevo)</li>
                                    <li>Pepipost</li>
                                    <li>Postal</li>
                                </ul>
                            </div>
                        </div>

                        <h6 class="mt-4">Configuring SMTP</h6>
                        <ol>
                            <li>Navigate to <strong>Admin</strong> → <strong>Email Integration</strong> tab</li>
                            <li>Select your email provider</li>
                            <li>Enter SMTP settings:
                                <ul>
                                    <li><strong>SMTP Host:</strong> Server address (e.g., smtp.gmail.com)</li>
                                    <li><strong>SMTP Port:</strong> Port number (587 for TLS, 465 for SSL, 25 for unencrypted)</li>
                                    <li><strong>Encryption:</strong> TLS, SSL, or None</li>
                                    <li><strong>Username:</strong> Your email account username</li>
                                    <li><strong>Password:</strong> Your email account password or app password</li>
                                </ul>
                            </li>
                            <li>Click <strong>"Save Email Settings"</strong></li>
                            <li>Test the configuration by clicking <strong>"Send Test Email"</strong></li>
                        </ol>

                        <h6 class="mt-4">Email Logs</h6>
                        <p>Monitor all email sending attempts:</p>
                        <ul>
                            <li>View success/failure status</li>
                            <li>Check error messages for failed sends</li>
                            <li>Enable debug logging for detailed information</li>
                            <li>Filter logs by status, provider, or recipient</li>
                        </ul>

                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Security Note:</strong> For Gmail and other providers, you may need to generate an "App Password" instead of using your regular password. Check your provider's documentation.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Settings -->
            <div id="help-maps" class="help-section" style="display: none;">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-map me-2"></i>Map Settings</h4>
                    </div>
                    <div class="card-body">
                        <h5>Configuring Map Provider</h5>
                        <p>Choose your preferred map provider for the live tracking map.</p>

                        <h6 class="mt-4">Available Map Providers</h6>
                        <ul>
                            <li><strong>OpenStreetMap:</strong> Free, open-source maps</li>
                            <li><strong>CartoDB:</strong> Professional mapping service</li>
                            <li><strong>Esri World Street Map:</strong> High-quality street maps</li>
                            <li><strong>Esri World Imagery:</strong> Satellite imagery</li>
                            <li><strong>Esri World Topographic:</strong> Topographic maps</li>
                            <li><strong>Stamen Terrain:</strong> Terrain visualization</li>
                            <li><strong>Stamen Toner:</strong> High-contrast black and white</li>
                            <li><strong>Stamen Watercolor:</strong> Artistic watercolor style</li>
                            <li><strong>OpenTopoMap:</strong> Topographic maps</li>
                            <li><strong>CyclOSM:</strong> Cycling-focused maps</li>
                            <li><strong>Transport Map:</strong> Public transport focused</li>
                        </ul>

                        <h6 class="mt-4">Changing Map Settings</h6>
                        <ol>
                            <li>Navigate to <strong>Admin</strong> → <strong>Settings</strong> tab</li>
                            <li>Select your preferred map provider</li>
                            <li>Set default zoom level (1-20)</li>
                            <li>Set default center coordinates (latitude, longitude)</li>
                            <li>Click <strong>"Save Settings"</strong></li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Troubleshooting -->
            <div id="help-troubleshooting" class="help-section" style="display: none;">
                <div class="card mb-4">
                    <div class="card-header bg-warning text-white">
                        <h4 class="mb-0"><i class="fas fa-tools me-2"></i>Troubleshooting</h4>
                    </div>
                    <div class="card-body">
                        <h5>Common Issues and Solutions</h5>

                        <h6 class="mt-4">Device Not Connecting</h6>
                        <div class="card mb-3">
                            <div class="card-body">
                                <p><strong>Problem:</strong> Device shows as offline or not receiving data</p>
                                <p><strong>Solutions:</strong></p>
                                <ul>
                                    <li>Verify SIM card has active data plan and sufficient credit</li>
                                    <li>Check device power connection and battery level</li>
                                    <li>Verify server IP address and port in device configuration</li>
                                    <li>Check firewall rules allow incoming connections on configured port</li>
                                    <li>Review device logs in Admin → Logs tab for error messages</li>
                                    <li>Test device connectivity by sending test command via SMS (if supported)</li>
                                </ul>
                            </div>
                        </div>

                        <h6 class="mt-4">No GPS Data</h6>
                        <div class="card mb-3">
                            <div class="card-body">
                                <p><strong>Problem:</strong> Device connects but no location data received</p>
                                <p><strong>Solutions:</strong></p>
                                <ul>
                                    <li>Ensure GPS antenna has clear view of sky (not blocked by metal/roof)</li>
                                    <li>Wait 5-10 minutes after power-on for first GPS fix</li>
                                    <li>Check GPS is enabled in device configuration</li>
                                    <li>Verify device is in area with GPS coverage</li>
                                    <li>Check for interference from vehicle electronics</li>
                                </ul>
                            </div>
                        </div>

                        <h6 class="mt-4">Incorrect Location Data</h6>
                        <div class="card mb-3">
                            <div class="card-body">
                                <p><strong>Problem:</strong> Device shows wrong location or inaccurate coordinates</p>
                                <p><strong>Solutions:</strong></p>
                                <ul>
                                    <li>Check GPS antenna connection and placement</li>
                                    <li>Verify device firmware is up to date</li>
                                    <li>Review GPS accuracy settings in device configuration</li>
                                    <li>Check for signal interference</li>
                                    <li>Allow time for GPS to acquire satellite lock</li>
                                </ul>
                            </div>
                        </div>

                        <h6 class="mt-4">Email Not Sending</h6>
                        <div class="card mb-3">
                            <div class="card-body">
                                <p><strong>Problem:</strong> Test emails or alerts not being sent</p>
                                <p><strong>Solutions:</strong></p>
                                <ul>
                                    <li>Check email settings in Admin → Email Integration</li>
                                    <li>Verify SMTP credentials are correct</li>
                                    <li>Check email logs for error messages</li>
                                    <li>Enable debug logging for detailed information</li>
                                    <li>Verify firewall allows outbound SMTP connections</li>
                                    <li>For Gmail, ensure "Less secure app access" is enabled or use App Password</li>
                                </ul>
                            </div>
                        </div>

                        <h6 class="mt-4">Getting Additional Help</h6>
                        <p>If you continue to experience issues:</p>
                        <ul>
                            <li>Check the device manufacturer's documentation</li>
                            <li>Review platform logs in Admin → Logs tab</li>
                            <li>Contact your system administrator</li>
                            <li>Review error messages in email logs (Admin → Email Integration → Email Logs)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    function waitForDependencies(callback) {
        if (typeof jQuery !== 'undefined' && typeof bootstrap !== 'undefined') {
            callback();
        } else {
            setTimeout(function() { waitForDependencies(callback); }, 100);
        }
    }
    
    waitForDependencies(function() {
        $(document).ready(function() {
            // Handle navigation clicks
            $('.help-nav-item').on('click', function(e) {
                e.preventDefault();
                const section = $(this).data('section');
                
                // Update active state
                $('.help-nav-item').removeClass('active');
                $(this).addClass('active');
                
                // Hide all sections
                $('.help-section').hide();
                
                // Show selected section
                $('#help-' + section).show();
                
                // Scroll to top of content
                $('html, body').animate({
                    scrollTop: $('.col-md-9').offset().top - 20
                }, 300);
            });
            
            // Handle hash navigation on page load
            if (window.location.hash) {
                const hash = window.location.hash.substring(1);
                const navItem = $('.help-nav-item[data-section="' + hash + '"]');
                if (navItem.length) {
                    navItem.click();
                }
            }
        });
    });
})();
</script>

<style>
.help-section {
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.help-nav-item {
    cursor: pointer;
    transition: background-color 0.2s;
}

.help-nav-item:hover {
    background-color: #f8f9fa;
}

.help-nav-item.active {
    background-color: #e7f3ff;
    font-weight: bold;
}

.card-header h4, .card-header h5 {
    margin: 0;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../views/layout.php';
?>

