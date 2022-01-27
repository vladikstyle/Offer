<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '256M');

/**
 * Check PHP version
 */
if (PHP_VERSION_ID < 70100) {
    http_response_code(500);
    die('Required PHP version is 7.1+. Your PHP version is ' . phpversion());
}

/**
 * Require helpers
 */
require_once(__DIR__ . '/helpers.php');
