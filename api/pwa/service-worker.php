<?php

/**
 * Service Worker
 */

header('Content-Type: application/javascript');

readfile(__DIR__ . '/../../public/service-worker.js');

