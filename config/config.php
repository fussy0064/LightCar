<?php
// On AWS, set these as real environment variables instead of editing this
// file (e.g. in Apache/PHP-FPM env, or a .env loaded before this file).
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'car_sales_db');

// Encryption key (AES-256-CBC). MUST be overridden via env var in production.
define('ENCRYPTION_KEY', getenv('ENCRYPTION_KEY') ?: 'b8f2d59160e1d88bbd6d34b4c730dc6516f1eb3c3f91da121b6a7a0b3687508e');
?>