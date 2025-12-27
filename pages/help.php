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
                    <a href="#trips" class="list-group-item list-group-item-action help-nav-item" data-section="trips">
                        <i class="fas fa-route me-2"></i>Trips & Route History
                    </a>
                    <a href="#device-groups" class="list-group-item list-group-item-action help-nav-item" data-section="device-groups">
                        <i class="fas fa-layer-group me-2"></i>Device Groups
                    </a>
                    <a href="#alert-rules" class="list-group-item list-group-item-action help-nav-item" data-section="alert-rules">
                        <i class="fas fa-sliders-h me-2"></i>Alert Rules
                    </a>
                    <a href="#pwa" class="list-group-item list-group-item-action help-nav-item" data-section="pwa">
                        <i class="fas fa-mobile-alt me-2"></i>PWA Installation
                    </a>
                    <?php if (has_role('Developer')): ?>
                    <a href="#migrations" class="list-group-item list-group-item-action help-nav-item" data-section="migrations">
                        <i class="fas fa-database me-2"></i>Database Migrations
                    </a>
                    <?php endif; ?>
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
                        <h5>Welcome to Dragnet <span style="color: #d4af37; font-weight: 700;">Intel</span>ematics</h5>
                        <p>Dragnet <span style="color: #d4af37; font-weight: 700;">Intel</span>ematics is a comprehensive telematics and asset tracking platform designed to help you monitor and manage your fleet, vehicles, and equipment in real-time. Just the facts, ma'am.</p>
                        
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
                        <p>Dragnet <span style="color: #d4af37; font-weight: 700;">Intel</span>ematics supports Teltonika FMM13A and other compatible telematics devices. Follow these steps to add and configure your device:</p>

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
                        <p>Configure the device to send data to Dragnet <span style="color: #d4af37; font-weight: 700;">Intel</span>ematics:</p>
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
                        <p>Teltonika devices support multiple IO (Input/Output) elements. Configure them in Dragnet <span style="color: #d4af37; font-weight: 700;">Intel</span>ematics:</p>
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
                        <p>The platform supports numerous alert types organized by category:</p>
                        
                        <h6 class="mt-3">Device Status</h6>
                        <ul>
                            <li><strong>Device Offline:</strong> Device stops sending data (configurable threshold)</li>
                            <li><strong>Device Online:</strong> Device comes back online after being offline</li>
                        </ul>
                        
                        <h6 class="mt-3">Ignition</h6>
                        <ul>
                            <li><strong>Ignition On:</strong> Vehicle ignition is turned on</li>
                            <li><strong>Ignition Off:</strong> Vehicle ignition is turned off</li>
                        </ul>
                        
                        <h6 class="mt-3">Driving</h6>
                        <ul>
                            <li><strong>Speed Violation:</strong> Vehicle exceeds specified speed limit (configurable threshold)</li>
                            <li><strong>Idle Time:</strong> Vehicle idles for extended period (configurable threshold)</li>
                        </ul>
                        
                        <h6 class="mt-3">Vehicle Health</h6>
                        <ul>
                            <li><strong>Low Voltage:</strong> Vehicle battery voltage drops below threshold</li>
                            <li><strong>Low Battery:</strong> Device battery level is low</li>
                        </ul>
                        
                        <h6 class="mt-3">Geofencing</h6>
                        <ul>
                            <li><strong>Geofence Entry:</strong> Vehicle enters a defined geofence area</li>
                            <li><strong>Geofence Exit:</strong> Vehicle leaves a defined geofence area</li>
                        </ul>
                        
                        <h6 class="mt-3">Vehicle Status</h6>
                        <ul>
                            <li><strong>Door Open:</strong> Vehicle door opened</li>
                            <li><strong>Door Closed:</strong> Vehicle door closed</li>
                        </ul>
                        
                        <h6 class="mt-3">Security</h6>
                        <ul>
                            <li><strong>Panic Button:</strong> Emergency panic button activated</li>
                            <li><strong>Tow Detection:</strong> Vehicle being towed or lifted detected</li>
                        </ul>
                        
                        <h6 class="mt-3">Safety</h6>
                        <ul>
                            <li><strong>Impact Detection:</strong> Vehicle impact or collision detected (configurable G-force threshold)</li>
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
                            <li>View all active alerts with filtering options:
                                <ul>
                                    <li>Filter by alert type</li>
                                    <li>Filter by severity (Info, Warning, Critical)</li>
                                    <li>Filter by device or asset</li>
                                    <li>Filter by acknowledgment status</li>
                                    <li>Filter by date range</li>
                                </ul>
                            </li>
                            <li>Acknowledge alerts by clicking the acknowledge button</li>
                            <li>Assign alerts to team members for follow-up</li>
                            <li>View alert details including metadata and timestamps</li>
                        </ol>
                        
                        <h6 class="mt-4">User Alert Subscriptions</h6>
                        <p>Users can subscribe to receive notifications for specific alerts:</p>
                        <ol>
                            <li>Go to your <strong>Profile</strong> or <strong>Settings</strong> page</strong></li>
                            <li>Navigate to <strong>Alert Subscriptions</strong> section</li>
                            <li>Select alert types you want to receive</li>
                            <li>Optionally filter by specific devices or assets</li>
                            <li>Choose notification methods (push notifications, email)</li>
                            <li>Save your subscriptions</li>
                        </ol>
                    </div>
                </div>
            </div>
            
            <!-- Alert Rules -->
            <div id="help-alert-rules" class="help-section" style="display: none;">
                <div class="card mb-4">
                    <div class="card-header bg-warning text-white">
                        <h4 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Alert Rules</h4>
                    </div>
                    <div class="card-body">
                        <h5>Configuring Alert Rules</h5>
                        <p>Alert rules allow you to configure automated alert generation based on specific conditions and thresholds.</p>
                        
                        <h6 class="mt-4">Creating Alert Rules</h6>
                        <ol>
                            <li>Navigate to <strong>Admin</strong> → <strong>Alert Rules</strong> (Operator+ required)</li>
                            <li>Click <strong>"Add Alert Rule"</strong></li>
                            <li>Configure the rule:
                                <ul>
                                    <li><strong>Name:</strong> Descriptive name for the rule</li>
                                    <li><strong>Description:</strong> Optional description</li>
                                    <li><strong>Alert Type:</strong> Select the type of alert to generate</li>
                                    <li><strong>Severity:</strong> Set alert severity (Info, Warning, Critical)</li>
                                    <li><strong>Threshold:</strong> For applicable alert types, set threshold value and unit</li>
                                    <li><strong>Conditions:</strong> Additional conditions (JSON format)</li>
                                    <li><strong>Actions:</strong> Actions to take when alert is triggered</li>
                                </ul>
                            </li>
                            <li>Associate with devices, device groups, or assets</li>
                            <li>Configure notification recipients</li>
                            <li>Enable or disable the rule</li>
                            <li>Click <strong>"Save"</strong></li>
                        </ol>
                        
                        <h6 class="mt-4">Alert Rule Features</h6>
                        <ul>
                            <li><strong>Threshold Configuration:</strong> Set thresholds for speed, idle time, voltage, etc.</li>
                            <li><strong>Device Association:</strong> Apply rules to specific devices</li>
                            <li><strong>Group Association:</strong> Apply rules to device groups for bulk configuration</li>
                            <li><strong>Asset Association:</strong> Apply rules to assets (all devices linked to asset)</li>
                            <li><strong>Enable/Disable:</strong> Temporarily disable rules without deleting</li>
                            <li><strong>Notification Recipients:</strong> Configure who receives notifications</li>
                        </ul>
                        
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Tip:</strong> Use device groups to apply alert rules to multiple devices at once, making fleet-wide configuration easier.
                        </div>
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

                        <h6 class="mt-4">Geofence Analytics</h6>
                        <p>View detailed analytics for each geofence:</p>
                        <ol>
                            <li>Navigate to <strong>Geofences</strong> page</li>
                            <li>Click the analytics icon (chart) on any geofence</li>
                            <li>Or click <strong>"View Analytics"</strong> in the map popup</li>
                            <li>View analytics including:
                                <ul>
                                    <li><strong>Visit Statistics:</strong> Number of entries/exits per device</li>
                                    <li><strong>Dwell Time:</strong> Time devices spend inside geofence</li>
                                    <li><strong>Currently Inside:</strong> Real-time list of devices currently inside</li>
                                    <li><strong>Event History:</strong> Complete history of entry/exit events</li>
                                </ul>
                            </li>
                            <li>Filter by date range for historical analysis</li>
                        </ol>
                        
                        <h6 class="mt-4">Geofence Best Practices</h6>
                        <ul>
                            <li>Use descriptive names for easy identification</li>
                            <li>Set appropriate buffer zones for accurate detection</li>
                            <li>Configure entry/exit delays to reduce false alerts</li>
                            <li>Link geofences to specific assets or device groups</li>
                            <li>Use polygon geofences for irregular areas</li>
                            <li>Review analytics regularly to optimize geofence placement</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Trips & Route History -->
            <div id="help-trips" class="help-section" style="display: none;">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0"><i class="fas fa-route me-2"></i>Trips & Route History</h4>
                    </div>
                    <div class="card-body">
                        <h5>Trip Detection and Management</h5>
                        <p>The platform automatically detects trips based on ignition status and stores complete route information for playback and analysis.</p>
                        
                        <h6 class="mt-4">How Trip Detection Works</h6>
                        <p>Trips are automatically detected when:</p>
                        <ul>
                            <li><strong>Trip Start:</strong> Ignition turns on (device starts moving)</li>
                            <li><strong>Trip End:</strong> Ignition turns off (device stops moving)</li>
                        </ul>
                        <p>During a trip, the system stores waypoints (GPS coordinates) at regular intervals, creating a complete route history.</p>
                        
                        <h6 class="mt-4">Viewing Trips</h6>
                        <ol>
                            <li>Navigate to <strong>Trips</strong> from the main menu</li>
                            <li>Filter trips by:
                                <ul>
                                    <li>Device</li>
                                    <li>Asset</li>
                                    <li>Date range</li>
                                </ul>
                            </li>
                            <li>View trip list with:
                                <ul>
                                    <li>Start and end times</li>
                                    <li>Distance traveled</li>
                                    <li>Duration</li>
                                    <li>Maximum speed</li>
                                    <li>Average speed</li>
                                </ul>
                            </li>
                            <li>Click on a trip to view details and route playback</li>
                        </ol>
                        
                        <h6 class="mt-4">Trip Statistics</h6>
                        <p>Each trip includes comprehensive statistics:</p>
                        <ul>
                            <li><strong>Distance:</strong> Total distance traveled (in kilometers)</li>
                            <li><strong>Duration:</strong> Total trip duration</li>
                            <li><strong>Maximum Speed:</strong> Highest speed reached during trip</li>
                            <li><strong>Average Speed:</strong> Average speed throughout trip</li>
                            <li><strong>Idle Time:</strong> Time spent idle (speed < 5 km/h)</li>
                            <li><strong>Start/End Locations:</strong> GPS coordinates of trip start and end</li>
                        </ul>
                        
                        <h6 class="mt-4">Route Playback</h6>
                        <p>View complete route history on the map:</p>
                        <ul>
                            <li>See the exact path taken during the trip</li>
                            <li>View waypoints along the route</li>
                            <li>Analyze speed and heading at each point</li>
                            <li>Identify stops and idle periods</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Device Groups -->
            <div id="help-device-groups" class="help-section" style="display: none;">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-layer-group me-2"></i>Device Groups</h4>
                    </div>
                    <div class="card-body">
                        <h5>Organizing Devices into Groups</h5>
                        <p>Device groups allow you to organize devices for bulk operations, making fleet management more efficient.</p>
                        
                        <h6 class="mt-4">Creating Device Groups</h6>
                        <ol>
                            <li>Navigate to <strong>Admin</strong> → <strong>Device Groups</strong> (Operator+ required)</li>
                            <li>Click <strong>"Add Device Group"</strong></li>
                            <li>Fill in group information:
                                <ul>
                                    <li><strong>Name:</strong> Descriptive name (e.g., "Delivery Fleet", "Service Vehicles")</li>
                                    <li><strong>Description:</strong> Optional description</li>
                                    <li><strong>Color:</strong> Color for visualization on maps and charts</li>
                                    <li><strong>Active Status:</strong> Enable or disable the group</li>
                                </ul>
                            </li>
                            <li>Add devices to the group (can be done after creation)</li>
                            <li>Click <strong>"Save"</strong></li>
                        </ol>
                        
                        <h6 class="mt-4">Managing Group Membership</h6>
                        <p>Add or remove devices from groups:</p>
                        <ul>
                            <li>From the group detail page, add devices individually</li>
                            <li>From the device detail page, add device to groups</li>
                            <li>Devices can belong to multiple groups</li>
                            <li>Group membership can be changed at any time</li>
                        </ul>
                        
                        <h6 class="mt-4">Using Device Groups</h6>
                        <p>Device groups are used for:</p>
                        <ul>
                            <li><strong>Bulk Alert Rules:</strong> Apply alert rules to all devices in a group</li>
                            <li><strong>Geofence Associations:</strong> Associate geofences with device groups</li>
                            <li><strong>Reporting:</strong> Generate reports for specific groups</li>
                            <li><strong>Visualization:</strong> Color-code devices on maps and charts</li>
                            <li><strong>Organization:</strong> Better organization of large fleets</li>
                        </ul>
                        
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Tip:</strong> Create groups based on vehicle type, department, or region to simplify fleet management.
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- PWA Installation -->
            <div id="help-pwa" class="help-section" style="display: none;">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-mobile-alt me-2"></i>PWA Installation</h4>
                    </div>
                    <div class="card-body">
                        <h5>Installing as a Progressive Web App</h5>
                        <p>Dragnet Intelematics can be installed as a native-like app on your device, providing offline access, push notifications, and an app-like experience.</p>
                        
                        <h6 class="mt-4">Installation Methods</h6>
                        
                        <h6 class="mt-3">iOS (Safari)</h6>
                        <ol>
                            <li>Open the portal in Safari</li>
                            <li>Tap the <strong>Share</strong> button (square with arrow)</li>
                            <li>Scroll down and tap <strong>"Add to Home Screen"</strong></li>
                            <li>Tap <strong>"Add"</strong> to confirm</li>
                            <li>The app icon will appear on your home screen</li>
                        </ol>
                        
                        <h6 class="mt-3">Android (Chrome/Firefox)</h6>
                        <ol>
                            <li>Open the portal in Chrome or Firefox</li>
                            <li>You may see an install prompt automatically</li>
                            <li>Or tap the menu (three dots) → <strong>"Install app"</strong> or <strong>"Add to Home screen"</strong></li>
                            <li>Tap <strong>"Install"</strong> to confirm</li>
                            <li>The app will be installed and appear in your app drawer</li>
                        </ol>
                        
                        <h6 class="mt-3">Windows (Edge/Chrome)</h6>
                        <ol>
                            <li>Open the portal in Edge or Chrome</li>
                            <li>Look for the install icon in the address bar</li>
                            <li>Or go to menu → <strong>"Apps"</strong> → <strong>"Install this site as an app"</strong></li>
                            <li>Click <strong>"Install"</strong> to confirm</li>
                            <li>The app will be added to your Start menu</li>
                        </ol>
                        
                        <h6 class="mt-3">macOS (Safari/Chrome)</h6>
                        <ol>
                            <li>Open the portal in Safari or Chrome</li>
                            <li>In Safari: File → <strong>"Add to Dock"</strong></li>
                            <li>In Chrome: Menu → <strong>"Install Dragnet Intelematics..."</strong></li>
                            <li>The app will be added to your Dock or Applications folder</li>
                        </ol>
                        
                        <h6 class="mt-4">PWA Features</h6>
                        <p>Once installed, the PWA provides:</p>
                        <ul>
                            <li><strong>Offline Access:</strong> View cached pages and data when offline</li>
                            <li><strong>Push Notifications:</strong> Receive real-time alerts and notifications</li>
                            <li><strong>GPS Access:</strong> Request location permissions for enhanced features</li>
                            <li><strong>App-like Experience:</strong> Standalone window without browser UI</li>
                            <li><strong>Fast Loading:</strong> Cached resources load instantly</li>
                            <li><strong>Home Screen Icon:</strong> Custom icon on your device</li>
                        </ul>
                        
                        <h6 class="mt-4">Enabling Push Notifications</h6>
                        <ol>
                            <li>After installing the PWA, you may be prompted to enable notifications</li>
                            <li>Or go to <strong>Settings</strong> → <strong>Notifications</strong></li>
                            <li>Click <strong>"Enable Push Notifications"</strong></li>
                            <li>Allow browser notifications when prompted</li>
                            <li>You will now receive push notifications for alerts</li>
                        </ol>
                        
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> Push notifications require a secure connection (HTTPS) and may need to be enabled in your browser settings.
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (has_role('Developer')): ?>
            <!-- Database Migrations -->
            <div id="help-migrations" class="help-section" style="display: none;">
                <div class="card mb-4">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0"><i class="fas fa-database me-2"></i>Database Migrations</h4>
                    </div>
                    <div class="card-body">
                        <h5>Managing Database Migrations</h5>
                        <p>The migration system allows you to manage database schema changes in a controlled, versioned manner.</p>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Developer Only:</strong> Database migrations are only accessible to users with the Developer role.
                        </div>
                        
                        <h6 class="mt-4">Accessing Migrations</h6>
                        <ol>
                            <li>Navigate to <strong>Admin</strong> → <strong>Database Migrations</strong> tab</li>
                            <li>The system automatically scans for applied migrations on page load</li>
                            <li>View migration status for all files in <code>database/migrations/</code></li>
                        </ol>
                        
                        <h6 class="mt-4">Migration Status</h6>
                        <p>Each migration file shows one of the following statuses:</p>
                        <ul>
                            <li><strong>Applied (Success):</strong> Migration has been successfully applied</li>
                            <li><strong>Pending:</strong> Migration file exists but hasn't been applied</li>
                            <li><strong>Failed:</strong> Migration was attempted but failed (check error message)</li>
                            <li><strong>Detected:</strong> Migration was auto-detected as applied (schema matches)</li>
                        </ul>
                        
                        <h6 class="mt-4">Auto-Scanning</h6>
                        <p>The system automatically detects applied migrations by:</p>
                        <ul>
                            <li>Checking if tables created by migrations exist</li>
                            <li>Checking if columns added by migrations exist</li>
                            <li>Checking if indexes created by migrations exist</li>
                            <li>Comparing database schema with migration SQL</li>
                        </ul>
                        <p>This happens automatically when you access the Migrations tab.</p>
                        
                        <h6 class="mt-4">Applying Migrations</h6>
                        <ol>
                            <li>Find a migration with <strong>Pending</strong> status</li>
                            <li>Click <strong>"Apply"</strong> button</li>
                            <li>Review the confirmation message</li>
                            <li>Click <strong>"Confirm"</strong> to apply</li>
                            <li>The migration will execute and status will update</li>
                        </ol>
                        
                        <h6 class="mt-4">Viewing Migration SQL</h6>
                        <p>To view the SQL content of any migration:</p>
                        <ol>
                            <li>Click the eye icon (👁️) next to the migration</li>
                            <li>A modal will display the complete SQL content</li>
                            <li>Review the SQL before applying if needed</li>
                        </ol>
                        
                        <h6 class="mt-4">Purging Migrations</h6>
                        <p>Remove migration tracking records:</p>
                        <ul>
                            <li><strong>Purge Individual:</strong> Remove tracking for a single migration</li>
                            <li><strong>Purge All Successful:</strong> Remove all successful migration records</li>
                        </ul>
                        <div class="alert alert-warning mt-2">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> Purging does not undo the migration, it only removes the tracking record. The database changes remain.
                        </div>
                        
                        <h6 class="mt-4">Schema Comparison</h6>
                        <p>Compare the live database with the <code>database/schema.sql</code> seed file:</p>
                        <ol>
                            <li>View differences in the Schema Comparison section</li>
                            <li>See missing or extra tables, columns, and indexes</li>
                            <li>Click <strong>"Update Schema File"</strong> to sync <code>schema.sql</code> with live database</li>
                            <li>A backup of the old schema.sql will be created automatically</li>
                        </ol>
                        
                        <h6 class="mt-4">Best Practices</h6>
                        <ul>
                            <li>Always review migration SQL before applying</li>
                            <li>Test migrations in a development environment first</li>
                            <li>Keep migration files in chronological order</li>
                            <li>Use descriptive filenames (e.g., <code>add_user_table.sql</code>)</li>
                            <li>Keep <code>schema.sql</code> in sync with live database</li>
                            <li>Back up database before applying migrations in production</li>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>

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
                            <li><strong>Distance Report:</strong> Total distance traveled by asset/device over a date range</li>
                            <li><strong>Idle Time Report:</strong> Vehicle idle duration analysis with detailed breakdown</li>
                            <li><strong>Violations Report:</strong> Speed violations and other rule violations</li>
                            <li><strong>Fuel Consumption:</strong> Fuel usage estimates (if fuel sensor connected)</li>
                            <li><strong>Activity Summary:</strong> Overall activity statistics including trips, distance, and time</li>
                            <li><strong>Device Health:</strong> Device health metrics and diagnostics</li>
                        </ul>
                        
                        <h6 class="mt-4">Report Filtering</h6>
                        <p>All reports support filtering by:</p>
                        <ul>
                            <li><strong>Date Range:</strong> Select start and end dates</li>
                            <li><strong>Asset:</strong> Filter by specific asset/vehicle</li>
                            <li><strong>Device:</strong> Filter by specific device</li>
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
                            <li><strong>Guest (Level 0):</strong> Read-only access, view-only permissions</li>
                            <li><strong>ReadOnly (Level 1):</strong> Can view all data but cannot make changes</li>
                            <li><strong>Operator (Level 2):</strong> Can manage devices, assets, alerts, and view reports</li>
                            <li><strong>Administrator (Level 3):</strong> Full access including user management and settings</li>
                            <li><strong>TenantOwner (Level 4):</strong> Complete control over tenant and all resources</li>
                            <li><strong>Developer (Level 5):</strong> Top-level role with all capabilities including database migrations and schema management</li>
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

