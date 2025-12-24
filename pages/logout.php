<?php

/**
 * Logout Page
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

$config = $GLOBALS['config'];
session_start_custom($config['session']);
session_destroy_custom();

redirect('/login.php');

