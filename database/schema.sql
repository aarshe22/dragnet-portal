-- Dragnet Intelematics Database Schema
-- Auto-generated from live database
-- Generated: 2025-12-27 00:17:37

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tenant_id` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `type` enum('device_offline','ignition_on','ignition_off','speed_violation','idle_time','low_voltage','low_battery','geofence_entry','geofence_exit') NOT NULL,
  `severity` enum('info','warning','critical') DEFAULT '\'warning\'',
  `message` text DEFAULT 'NULL',
  `acknowledged` tinyint(1) DEFAULT 0,
  `acknowledged_by` int(10) unsigned DEFAULT 'NULL',
  `acknowledged_at` timestamp DEFAULT 'NULL',
  `assigned_to` int(10) unsigned DEFAULT 'NULL',
  `metadata` longtext DEFAULT 'NULL',
  `created_at` timestamp NOT NULL DEFAULT 'current_timestamp()'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alert_rules`
--

CREATE TABLE `alert_rules` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tenant_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT 'NULL',
  `alert_type` varchar(50) NOT NULL,
  `severity` enum('info','warning','critical') DEFAULT '\'warning\'',
  `enabled` tinyint(1) DEFAULT 1,
  `threshold_value` decimal(10,2) DEFAULT 'NULL',
  `threshold_unit` varchar(20) DEFAULT 'NULL',
  `conditions` longtext DEFAULT 'NULL',
  `actions` longtext DEFAULT 'NULL',
  `notification_recipients` longtext DEFAULT 'NULL',
  `created_at` timestamp NOT NULL DEFAULT 'current_timestamp()',
  `updated_at` timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alert_rule_devices`
--

CREATE TABLE `alert_rule_devices` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `rule_id` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT 'current_timestamp()'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alert_rule_groups`
--

CREATE TABLE `alert_rule_groups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `rule_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT 'current_timestamp()'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tenant_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `vehicle_id` varchar(100) DEFAULT 'NULL',
  `device_id` int(10) unsigned DEFAULT 'NULL',
  `status` enum('active','inactive','maintenance') DEFAULT '\'active\'',
  `created_at` timestamp NOT NULL DEFAULT 'current_timestamp()',
  `updated_at` timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `tenant_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned DEFAULT 'NULL',
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT 'NULL',
  `entity_id` int(10) unsigned DEFAULT 'NULL',
  `details` longtext DEFAULT 'NULL',
  `ip_address` varchar(45) DEFAULT 'NULL',
  `user_agent` text DEFAULT 'NULL',
  `created_at` timestamp NOT NULL DEFAULT 'current_timestamp()'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tenant_id` int(10) unsigned NOT NULL,
  `asset_id` int(10) unsigned DEFAULT 'NULL',
  `device_uid` varchar(100) NOT NULL,
  `imei` varchar(20) NOT NULL,
  `iccid` varchar(20) DEFAULT 'NULL',
  `model` varchar(50) DEFAULT '\'FMM13A\'',
  `device_type` varchar(50) DEFAULT '\'vehicle\'',
  `firmware_version` varchar(50) DEFAULT 'NULL',
  `last_seen` timestamp DEFAULT 'NULL',
  `gsm_signal` int(11) DEFAULT 'NULL',
  `external_voltage` decimal(5,2) DEFAULT 'NULL',
  `internal_battery` int(11) DEFAULT 'NULL',
  `status` enum('online','offline','moving','idle','parked') DEFAULT '\'offline\'',
  `created_at` timestamp NOT NULL DEFAULT 'current_timestamp()',
  `updated_at` timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `device_geofence_state`
--

CREATE TABLE `device_geofence_state` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tenant_id` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `geofence_id` int(10) unsigned NOT NULL,
  `is_inside` tinyint(1) DEFAULT 0,
  `entry_time` timestamp DEFAULT 'NULL',
  `last_seen_inside` timestamp DEFAULT 'NULL',
  `last_telemetry_id` bigint(20) unsigned DEFAULT 'NULL',
  `updated_at` timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `device_groups`
--

CREATE TABLE `device_groups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tenant_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT 'NULL',
  `color` varchar(7) DEFAULT '\'#007bff\'',
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT 'current_timestamp()',
  `updated_at` timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `device_group_members`
--

CREATE TABLE `device_group_members` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `group_id` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT 'current_timestamp()'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `device_io_labels`
--

CREATE TABLE `device_io_labels` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tenant_id` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned DEFAULT 'NULL',
  `io_id` int(11) NOT NULL,
  `io_type` enum('digital_input','analog_input','digital_output','analog_output') NOT NULL,
  `label` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT 'current_timestamp()',
  `updated_at` timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `tenant_id` int(10) unsigned DEFAULT 'NULL',
  `recipient` varchar(255) NOT NULL,
  `subject` varchar(500) DEFAULT 'NULL',
  `provider` varchar(50) DEFAULT 'NULL',
  `status` enum('pending','sent','failed','bounced') DEFAULT '\'pending\'',
  `error_message` text DEFAULT 'NULL',
  `response_data` longtext DEFAULT 'NULL',
  `debug_data` longtext DEFAULT 'NULL',
  `created_at` timestamp NOT NULL DEFAULT 'current_timestamp()',
  `sent_at` timestamp DEFAULT 'NULL'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `geofences`
--

CREATE TABLE `geofences` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tenant_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('polygon','circle','rectangle') NOT NULL,
  `coordinates` longtext NOT NULL,
  `rules` longtext DEFAULT 'NULL',
  `actions` longtext DEFAULT 'NULL',
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT 'current_timestamp()',
  `updated_at` timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `geofence_devices`
--

CREATE TABLE `geofence_devices` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `geofence_id` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT 'current_timestamp()'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `geofence_events`
--

CREATE TABLE `geofence_events` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `tenant_id` int(10) unsigned NOT NULL,
  `geofence_id` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `event_type` enum('entry','exit') NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  `lat` decimal(10,8) NOT NULL,
  `lon` decimal(11,8) NOT NULL,
  `speed` decimal(5,2) DEFAULT 'NULL',
  `heading` int(11) DEFAULT 'NULL',
  `telemetry_id` bigint(20) unsigned DEFAULT 'NULL',
  `created_at` timestamp NOT NULL DEFAULT 'current_timestamp()'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `geofence_groups`
--

CREATE TABLE `geofence_groups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `geofence_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT 'current_timestamp()'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `filename` varchar(255) NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT 'current_timestamp()',
  `applied_by` int(10) unsigned DEFAULT 'NULL',
  `execution_time` decimal(10,3) DEFAULT 'NULL',
  `error_message` text DEFAULT 'NULL',
  `status` enum('success','failed','partial') DEFAULT '\'success\''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `push_subscriptions`
--

CREATE TABLE `push_subscriptions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL,
  `endpoint` text NOT NULL,
  `p256dh_key` text NOT NULL,
  `auth_key` text NOT NULL,
  `platform` varchar(50) DEFAULT 'NULL',
  `user_agent` text DEFAULT 'NULL',
  `created_at` timestamp NOT NULL DEFAULT 'current_timestamp()',
  `updated_at` timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tenant_id` int(10) unsigned DEFAULT 'NULL',
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT 'NULL',
  `created_at` timestamp NOT NULL DEFAULT 'current_timestamp()',
  `updated_at` timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `telemetry`
--

CREATE TABLE `telemetry` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `device_id` int(10) unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  `lat` decimal(10,8) NOT NULL,
  `lon` decimal(11,8) NOT NULL,
  `altitude` decimal(8,2) DEFAULT 'NULL',
  `speed` decimal(5,2) DEFAULT 'NULL',
  `heading` int(11) DEFAULT 'NULL',
  `satellites` tinyint(4) DEFAULT 'NULL',
  `hdop` decimal(4,2) DEFAULT 'NULL',
  `ignition` tinyint(1) DEFAULT 0,
  `rpm` int(11) DEFAULT 'NULL',
  `vehicle_speed` decimal(5,2) DEFAULT 'NULL',
  `fuel_level` decimal(5,2) DEFAULT 'NULL',
  `engine_load` decimal(5,2) DEFAULT 'NULL',
  `odometer` decimal(10,2) DEFAULT 'NULL',
  `gsm_signal` int(11) DEFAULT 'NULL',
  `battery_voltage` decimal(5,2) DEFAULT 'NULL',
  `external_voltage` decimal(5,2) DEFAULT 'NULL',
  `internal_battery_level` int(11) DEFAULT 'NULL',
  `temperature` decimal(5,2) DEFAULT 'NULL',
  `io_payload` longtext DEFAULT 'NULL',
  `created_at` timestamp NOT NULL DEFAULT 'current_timestamp()'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tenants`
--

CREATE TABLE `tenants` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `region` varchar(50) DEFAULT '\'us-east\'',
  `created_at` timestamp NOT NULL DEFAULT 'current_timestamp()',
  `updated_at` timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trips`
--

CREATE TABLE `trips` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `tenant_id` int(10) unsigned NOT NULL,
  `device_id` int(10) unsigned NOT NULL,
  `asset_id` int(10) unsigned DEFAULT 'NULL',
  `start_time` timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  `end_time` timestamp DEFAULT 'NULL',
  `start_lat` decimal(10,8) NOT NULL,
  `start_lon` decimal(11,8) NOT NULL,
  `end_lat` decimal(10,8) DEFAULT 'NULL',
  `end_lon` decimal(11,8) DEFAULT 'NULL',
  `start_address` text DEFAULT 'NULL',
  `end_address` text DEFAULT 'NULL',
  `distance_km` decimal(10,2) DEFAULT 0.00,
  `duration_minutes` int(11) DEFAULT 0,
  `max_speed` decimal(5,2) DEFAULT 'NULL',
  `avg_speed` decimal(5,2) DEFAULT 'NULL',
  `idle_time_minutes` int(11) DEFAULT 0,
  `fuel_consumed` decimal(8,2) DEFAULT 'NULL',
  `start_odometer` decimal(10,2) DEFAULT 'NULL',
  `end_odometer` decimal(10,2) DEFAULT 'NULL',
  `created_at` timestamp NOT NULL DEFAULT 'current_timestamp()',
  `updated_at` timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trip_waypoints`
--

CREATE TABLE `trip_waypoints` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `trip_id` bigint(20) unsigned NOT NULL,
  `telemetry_id` bigint(20) unsigned DEFAULT 'NULL',
  `sequence` int(11) NOT NULL,
  `lat` decimal(10,8) NOT NULL,
  `lon` decimal(11,8) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  `speed` decimal(5,2) DEFAULT 'NULL',
  `heading` int(11) DEFAULT 'NULL',
  `altitude` decimal(8,2) DEFAULT 'NULL',
  `created_at` timestamp NOT NULL DEFAULT 'current_timestamp()'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tenant_id` int(10) unsigned NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` enum('Guest','ReadOnly','Operator','Administrator','TenantOwner','Developer') DEFAULT '\'Guest\'',
  `sso_provider` varchar(50) DEFAULT 'NULL',
  `sso_subject` varchar(255) DEFAULT 'NULL',
  `last_login` timestamp DEFAULT 'NULL',
  `created_at` timestamp NOT NULL DEFAULT 'current_timestamp()',
  `updated_at` timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_alert_subscriptions`
--

CREATE TABLE `user_alert_subscriptions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL,
  `tenant_id` int(10) unsigned NOT NULL,
  `alert_type` varchar(50) DEFAULT 'NULL',
  `asset_id` int(10) unsigned DEFAULT 'NULL',
  `device_id` int(10) unsigned DEFAULT 'NULL',
  `severity` enum('info','warning','critical') DEFAULT 'NULL',
  `enabled` tinyint(1) DEFAULT 1,
  `push_notifications` tinyint(1) DEFAULT 1,
  `email_notifications` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT 'current_timestamp()',
  `updated_at` timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_invites`
--

CREATE TABLE `user_invites` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tenant_id` int(10) unsigned NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `role` enum('Guest','ReadOnly','Operator','Administrator','TenantOwner') DEFAULT '\'Guest\'',
  `invited_by` int(10) unsigned DEFAULT 'NULL',
  `expires_at` timestamp NOT NULL DEFAULT 'current_timestamp()' on update current_timestamp(),
  `accepted_at` timestamp DEFAULT 'NULL',
  `created_at` timestamp NOT NULL DEFAULT 'current_timestamp()'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD KEY `acknowledged_by` (`acknowledged_by`);
ALTER TABLE `alerts`
  ADD KEY `assigned_to` (`assigned_to`);
ALTER TABLE `alerts`
  ADD KEY `idx_acknowledged` (`acknowledged`);
ALTER TABLE `alerts`
  ADD KEY `idx_alerts_acknowledged` (`tenant_id`,`acknowledged`,`created_at`);
ALTER TABLE `alerts`
  ADD KEY `idx_alerts_device` (`device_id`,`created_at`);
ALTER TABLE `alerts`
  ADD KEY `idx_alerts_severity` (`tenant_id`,`severity`,`acknowledged`);
ALTER TABLE `alerts`
  ADD KEY `idx_alerts_tenant_created` (`tenant_id`,`created_at`);
ALTER TABLE `alerts`
  ADD KEY `idx_alerts_type` (`tenant_id`,`type`,`created_at`);
ALTER TABLE `alerts`
  ADD KEY `idx_created` (`created_at`);
ALTER TABLE `alerts`
  ADD KEY `idx_device` (`device_id`);
ALTER TABLE `alerts`
  ADD KEY `idx_severity` (`severity`);
ALTER TABLE `alerts`
  ADD KEY `idx_tenant` (`tenant_id`);
ALTER TABLE `alerts`
  ADD KEY `idx_type` (`type`);
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `alert_rules`
--
ALTER TABLE `alert_rules`
  ADD KEY `idx_alert_type` (`alert_type`);
ALTER TABLE `alert_rules`
  ADD KEY `idx_enabled` (`enabled`);
ALTER TABLE `alert_rules`
  ADD KEY `idx_tenant` (`tenant_id`);
ALTER TABLE `alert_rules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `alert_rule_devices`
--
ALTER TABLE `alert_rule_devices`
  ADD KEY `idx_device` (`device_id`);
ALTER TABLE `alert_rule_devices`
  ADD KEY `idx_rule` (`rule_id`);
ALTER TABLE `alert_rule_devices`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `alert_rule_devices`
  ADD UNIQUE KEY `uk_rule_device` (`rule_id`,`device_id`);

--
-- Indexes for table `alert_rule_groups`
--
ALTER TABLE `alert_rule_groups`
  ADD KEY `idx_group` (`group_id`);
ALTER TABLE `alert_rule_groups`
  ADD KEY `idx_rule` (`rule_id`);
ALTER TABLE `alert_rule_groups`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `alert_rule_groups`
  ADD UNIQUE KEY `uk_rule_group` (`rule_id`,`group_id`);

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD KEY `idx_assets_device` (`device_id`);
ALTER TABLE `assets`
  ADD KEY `idx_assets_tenant` (`tenant_id`,`status`);
ALTER TABLE `assets`
  ADD KEY `idx_device` (`device_id`);
ALTER TABLE `assets`
  ADD KEY `idx_status` (`status`);
ALTER TABLE `assets`
  ADD KEY `idx_tenant` (`tenant_id`);
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD KEY `idx_action` (`action`);
ALTER TABLE `audit_log`
  ADD KEY `idx_created` (`created_at`);
ALTER TABLE `audit_log`
  ADD KEY `idx_tenant` (`tenant_id`);
ALTER TABLE `audit_log`
  ADD KEY `idx_user` (`user_id`);
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD KEY `idx_asset` (`asset_id`);
ALTER TABLE `devices`
  ADD KEY `idx_devices_asset_id` (`asset_id`);
ALTER TABLE `devices`
  ADD KEY `idx_devices_imei` (`imei`);
ALTER TABLE `devices`
  ADD KEY `idx_devices_last_seen` (`last_seen`);
ALTER TABLE `devices`
  ADD KEY `idx_devices_tenant_status` (`tenant_id`,`status`);
ALTER TABLE `devices`
  ADD KEY `idx_last_seen` (`last_seen`);
ALTER TABLE `devices`
  ADD KEY `idx_status` (`status`);
ALTER TABLE `devices`
  ADD KEY `idx_tenant` (`tenant_id`);
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `devices`
  ADD UNIQUE KEY `uk_device_uid` (`device_uid`);
ALTER TABLE `devices`
  ADD UNIQUE KEY `uk_imei` (`imei`);

--
-- Indexes for table `device_geofence_state`
--
ALTER TABLE `device_geofence_state`
  ADD KEY `idx_geofence` (`geofence_id`);
ALTER TABLE `device_geofence_state`
  ADD KEY `idx_is_inside` (`is_inside`);
ALTER TABLE `device_geofence_state`
  ADD KEY `idx_tenant_device` (`tenant_id`,`device_id`);
ALTER TABLE `device_geofence_state`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `device_geofence_state`
  ADD UNIQUE KEY `uk_device_geofence` (`device_id`,`geofence_id`);

--
-- Indexes for table `device_groups`
--
ALTER TABLE `device_groups`
  ADD KEY `idx_active` (`active`);
ALTER TABLE `device_groups`
  ADD KEY `idx_tenant` (`tenant_id`);
ALTER TABLE `device_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `device_group_members`
--
ALTER TABLE `device_group_members`
  ADD KEY `idx_device` (`device_id`);
ALTER TABLE `device_group_members`
  ADD KEY `idx_group` (`group_id`);
ALTER TABLE `device_group_members`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `device_group_members`
  ADD UNIQUE KEY `uk_group_device` (`group_id`,`device_id`);

--
-- Indexes for table `device_io_labels`
--
ALTER TABLE `device_io_labels`
  ADD KEY `idx_device` (`device_id`);
ALTER TABLE `device_io_labels`
  ADD KEY `idx_tenant` (`tenant_id`);
ALTER TABLE `device_io_labels`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `device_io_labels`
  ADD UNIQUE KEY `uk_tenant_device_io` (`tenant_id`,`device_id`,`io_id`,`io_type`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD KEY `idx_created` (`created_at`);
ALTER TABLE `email_logs`
  ADD KEY `idx_provider` (`provider`);
ALTER TABLE `email_logs`
  ADD KEY `idx_recipient` (`recipient`);
ALTER TABLE `email_logs`
  ADD KEY `idx_status` (`status`);
ALTER TABLE `email_logs`
  ADD KEY `idx_tenant` (`tenant_id`);
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `geofences`
--
ALTER TABLE `geofences`
  ADD KEY `idx_active` (`active`);
ALTER TABLE `geofences`
  ADD KEY `idx_geofences_active` (`tenant_id`,`active`);
ALTER TABLE `geofences`
  ADD KEY `idx_tenant` (`tenant_id`);
ALTER TABLE `geofences`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `geofence_devices`
--
ALTER TABLE `geofence_devices`
  ADD KEY `idx_device` (`device_id`);
ALTER TABLE `geofence_devices`
  ADD KEY `idx_geofence` (`geofence_id`);
ALTER TABLE `geofence_devices`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `geofence_devices`
  ADD UNIQUE KEY `uk_geofence_device` (`geofence_id`,`device_id`);

--
-- Indexes for table `geofence_events`
--
ALTER TABLE `geofence_events`
  ADD KEY `fk_geofence_events_geofence_id` (`geofence_id`);
ALTER TABLE `geofence_events`
  ADD KEY `idx_device_geofence` (`device_id`,`geofence_id`);
ALTER TABLE `geofence_events`
  ADD KEY `idx_event_type` (`event_type`);
ALTER TABLE `geofence_events`
  ADD KEY `idx_telemetry` (`telemetry_id`);
ALTER TABLE `geofence_events`
  ADD KEY `idx_tenant_geofence` (`tenant_id`,`geofence_id`);
ALTER TABLE `geofence_events`
  ADD KEY `idx_timestamp` (`timestamp`);
ALTER TABLE `geofence_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `geofence_groups`
--
ALTER TABLE `geofence_groups`
  ADD KEY `idx_geofence` (`geofence_id`);
ALTER TABLE `geofence_groups`
  ADD KEY `idx_group` (`group_id`);
ALTER TABLE `geofence_groups`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `geofence_groups`
  ADD UNIQUE KEY `uk_geofence_group` (`geofence_id`,`group_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD UNIQUE KEY `filename` (`filename`);
ALTER TABLE `migrations`
  ADD KEY `idx_applied_at` (`applied_at`);
ALTER TABLE `migrations`
  ADD KEY `idx_filename` (`filename`);
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `push_subscriptions`
--
ALTER TABLE `push_subscriptions`
  ADD KEY `idx_user` (`user_id`);
ALTER TABLE `push_subscriptions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD KEY `idx_tenant` (`tenant_id`);
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `settings`
  ADD UNIQUE KEY `uk_tenant_setting` (`tenant_id`,`setting_key`);

--
-- Indexes for table `telemetry`
--
ALTER TABLE `telemetry`
  ADD KEY `idx_device_timestamp` (`device_id`,`timestamp`);
ALTER TABLE `telemetry`
  ADD KEY `idx_ignition` (`ignition`);
ALTER TABLE `telemetry`
  ADD KEY `idx_location` (`lat`,`lon`);
ALTER TABLE `telemetry`
  ADD KEY `idx_telemetry_device_timestamp` (`device_id`,`timestamp`);
ALTER TABLE `telemetry`
  ADD KEY `idx_telemetry_fuel` (`device_id`,`fuel_level`,`timestamp`);
ALTER TABLE `telemetry`
  ADD KEY `idx_telemetry_ignition` (`device_id`,`ignition`,`timestamp`);
ALTER TABLE `telemetry`
  ADD KEY `idx_telemetry_location` (`lat`,`lon`);
ALTER TABLE `telemetry`
  ADD KEY `idx_telemetry_speed` (`device_id`,`speed`,`timestamp`);
ALTER TABLE `telemetry`
  ADD KEY `idx_telemetry_timestamp` (`timestamp`);
ALTER TABLE `telemetry`
  ADD KEY `idx_timestamp` (`timestamp`);
ALTER TABLE `telemetry`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tenants`
--
ALTER TABLE `tenants`
  ADD KEY `idx_name` (`name`);
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trips`
--
ALTER TABLE `trips`
  ADD KEY `fk_trips_asset_id` (`asset_id`);
ALTER TABLE `trips`
  ADD KEY `idx_device_start` (`device_id`,`start_time`);
ALTER TABLE `trips`
  ADD KEY `idx_end_time` (`end_time`);
ALTER TABLE `trips`
  ADD KEY `idx_start_time` (`start_time`);
ALTER TABLE `trips`
  ADD KEY `idx_tenant_device` (`tenant_id`,`device_id`);
ALTER TABLE `trips`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trip_waypoints`
--
ALTER TABLE `trip_waypoints`
  ADD KEY `fk_trip_waypoints_telemetry_id` (`telemetry_id`);
ALTER TABLE `trip_waypoints`
  ADD KEY `idx_trip_sequence` (`trip_id`,`sequence`);
ALTER TABLE `trip_waypoints`
  ADD KEY `idx_trip_timestamp` (`trip_id`,`timestamp`);
ALTER TABLE `trip_waypoints`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD KEY `idx_email` (`email`);
ALTER TABLE `users`
  ADD KEY `idx_tenant` (`tenant_id`);
ALTER TABLE `users`
  ADD KEY `idx_users_email` (`email`);
ALTER TABLE `users`
  ADD KEY `idx_users_sso` (`sso_provider`,`sso_subject`);
ALTER TABLE `users`
  ADD KEY `idx_users_tenant` (`tenant_id`,`role`);
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `users`
  ADD UNIQUE KEY `uk_tenant_email` (`tenant_id`,`email`);

--
-- Indexes for table `user_alert_subscriptions`
--
ALTER TABLE `user_alert_subscriptions`
  ADD KEY `idx_alert_type` (`alert_type`);
ALTER TABLE `user_alert_subscriptions`
  ADD KEY `idx_asset` (`asset_id`);
ALTER TABLE `user_alert_subscriptions`
  ADD KEY `idx_device` (`device_id`);
ALTER TABLE `user_alert_subscriptions`
  ADD KEY `idx_tenant` (`tenant_id`);
ALTER TABLE `user_alert_subscriptions`
  ADD KEY `idx_user` (`user_id`);
ALTER TABLE `user_alert_subscriptions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_invites`
--
ALTER TABLE `user_invites`
  ADD KEY `idx_accepted` (`accepted_at`);
ALTER TABLE `user_invites`
  ADD KEY `idx_email` (`email`);
ALTER TABLE `user_invites`
  ADD KEY `idx_expires` (`expires_at`);
ALTER TABLE `user_invites`
  ADD KEY `idx_tenant` (`tenant_id`);
ALTER TABLE `user_invites`
  ADD KEY `idx_token` (`token`);
ALTER TABLE `user_invites`
  ADD KEY `invited_by` (`invited_by`);
ALTER TABLE `user_invites`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `user_invites`
  ADD UNIQUE KEY `token` (`token`);

-- --------------------------------------------------------

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `alert_rules`
--
ALTER TABLE `alert_rules`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `alert_rule_devices`
--
ALTER TABLE `alert_rule_devices`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `alert_rule_groups`
--
ALTER TABLE `alert_rule_groups`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `device_geofence_state`
--
ALTER TABLE `device_geofence_state`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `device_groups`
--
ALTER TABLE `device_groups`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `device_group_members`
--
ALTER TABLE `device_group_members`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `device_io_labels`
--
ALTER TABLE `device_io_labels`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `geofences`
--
ALTER TABLE `geofences`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `geofence_devices`
--
ALTER TABLE `geofence_devices`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `geofence_events`
--
ALTER TABLE `geofence_events`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `geofence_groups`
--
ALTER TABLE `geofence_groups`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `push_subscriptions`
--
ALTER TABLE `push_subscriptions`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `telemetry`
--
ALTER TABLE `telemetry`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trips`
--
ALTER TABLE `trips`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trip_waypoints`
--
ALTER TABLE `trip_waypoints`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_alert_subscriptions`
--
ALTER TABLE `user_alert_subscriptions`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_invites`
--
ALTER TABLE `user_invites`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alerts`
--
ALTER TABLE `alerts`
  ADD CONSTRAINT `alerts_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;
ALTER TABLE `alerts`
  ADD CONSTRAINT `alerts_ibfk_2` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE;
ALTER TABLE `alerts`
  ADD CONSTRAINT `alerts_ibfk_3` FOREIGN KEY (`acknowledged_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `alerts`
  ADD CONSTRAINT `alerts_ibfk_4` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `alert_rules`
--
ALTER TABLE `alert_rules`
  ADD CONSTRAINT `fk_alert_rules_tenant_id` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `alert_rule_devices`
--
ALTER TABLE `alert_rule_devices`
  ADD CONSTRAINT `fk_alert_rule_devices_device_id` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE;
ALTER TABLE `alert_rule_devices`
  ADD CONSTRAINT `fk_alert_rule_devices_rule_id` FOREIGN KEY (`rule_id`) REFERENCES `alert_rules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `alert_rule_groups`
--
ALTER TABLE `alert_rule_groups`
  ADD CONSTRAINT `fk_alert_rule_groups_group_id` FOREIGN KEY (`group_id`) REFERENCES `device_groups` (`id`) ON DELETE CASCADE;
ALTER TABLE `alert_rule_groups`
  ADD CONSTRAINT `fk_alert_rule_groups_rule_id` FOREIGN KEY (`rule_id`) REFERENCES `alert_rules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `assets_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `devices`
--
ALTER TABLE `devices`
  ADD CONSTRAINT `devices_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;
ALTER TABLE `devices`
  ADD CONSTRAINT `devices_ibfk_2` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `device_geofence_state`
--
ALTER TABLE `device_geofence_state`
  ADD CONSTRAINT `fk_device_geofence_state_device_id` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE;
ALTER TABLE `device_geofence_state`
  ADD CONSTRAINT `fk_device_geofence_state_geofence_id` FOREIGN KEY (`geofence_id`) REFERENCES `geofences` (`id`) ON DELETE CASCADE;
ALTER TABLE `device_geofence_state`
  ADD CONSTRAINT `fk_device_geofence_state_tenant_id` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `device_groups`
--
ALTER TABLE `device_groups`
  ADD CONSTRAINT `fk_device_groups_tenant_id` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `device_group_members`
--
ALTER TABLE `device_group_members`
  ADD CONSTRAINT `fk_device_group_members_device_id` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE;
ALTER TABLE `device_group_members`
  ADD CONSTRAINT `fk_device_group_members_group_id` FOREIGN KEY (`group_id`) REFERENCES `device_groups` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `device_io_labels`
--
ALTER TABLE `device_io_labels`
  ADD CONSTRAINT `device_io_labels_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;
ALTER TABLE `device_io_labels`
  ADD CONSTRAINT `device_io_labels_ibfk_2` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD CONSTRAINT `email_logs_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `geofences`
--
ALTER TABLE `geofences`
  ADD CONSTRAINT `geofences_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `geofence_devices`
--
ALTER TABLE `geofence_devices`
  ADD CONSTRAINT `fk_geofence_devices_device_id` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE;
ALTER TABLE `geofence_devices`
  ADD CONSTRAINT `fk_geofence_devices_geofence_id` FOREIGN KEY (`geofence_id`) REFERENCES `geofences` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `geofence_events`
--
ALTER TABLE `geofence_events`
  ADD CONSTRAINT `fk_geofence_events_device_id` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE;
ALTER TABLE `geofence_events`
  ADD CONSTRAINT `fk_geofence_events_geofence_id` FOREIGN KEY (`geofence_id`) REFERENCES `geofences` (`id`) ON DELETE CASCADE;
ALTER TABLE `geofence_events`
  ADD CONSTRAINT `fk_geofence_events_telemetry_id` FOREIGN KEY (`telemetry_id`) REFERENCES `telemetry` (`id`) ON DELETE SET NULL;
ALTER TABLE `geofence_events`
  ADD CONSTRAINT `fk_geofence_events_tenant_id` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `geofence_groups`
--
ALTER TABLE `geofence_groups`
  ADD CONSTRAINT `fk_geofence_groups_geofence_id` FOREIGN KEY (`geofence_id`) REFERENCES `geofences` (`id`) ON DELETE CASCADE;
ALTER TABLE `geofence_groups`
  ADD CONSTRAINT `fk_geofence_groups_group_id` FOREIGN KEY (`group_id`) REFERENCES `device_groups` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `migrations`
--

--
-- Constraints for table `push_subscriptions`
--
ALTER TABLE `push_subscriptions`
  ADD CONSTRAINT `push_subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `settings`
--

--
-- Constraints for table `telemetry`
--
ALTER TABLE `telemetry`
  ADD CONSTRAINT `telemetry_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tenants`
--

--
-- Constraints for table `trips`
--
ALTER TABLE `trips`
  ADD CONSTRAINT `fk_trips_asset_id` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE SET NULL;
ALTER TABLE `trips`
  ADD CONSTRAINT `fk_trips_device_id` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE;
ALTER TABLE `trips`
  ADD CONSTRAINT `fk_trips_tenant_id` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trip_waypoints`
--
ALTER TABLE `trip_waypoints`
  ADD CONSTRAINT `fk_trip_waypoints_telemetry_id` FOREIGN KEY (`telemetry_id`) REFERENCES `telemetry` (`id`) ON DELETE SET NULL;
ALTER TABLE `trip_waypoints`
  ADD CONSTRAINT `fk_trip_waypoints_trip_id` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_alert_subscriptions`
--
ALTER TABLE `user_alert_subscriptions`
  ADD CONSTRAINT `fk_user_alert_subscriptions_asset_id` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE;
ALTER TABLE `user_alert_subscriptions`
  ADD CONSTRAINT `fk_user_alert_subscriptions_device_id` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE;
ALTER TABLE `user_alert_subscriptions`
  ADD CONSTRAINT `fk_user_alert_subscriptions_tenant_id` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;
ALTER TABLE `user_alert_subscriptions`
  ADD CONSTRAINT `fk_user_alert_subscriptions_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_invites`
--
ALTER TABLE `user_invites`
  ADD CONSTRAINT `user_invites_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;
ALTER TABLE `user_invites`
  ADD CONSTRAINT `user_invites_ibfk_2` FOREIGN KEY (`invited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

SET FOREIGN_KEY_CHECKS = 1;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;