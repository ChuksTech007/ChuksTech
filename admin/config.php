<?php
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'iportfolio_db');

// Admin credentials — change these before going live
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123');

// Absolute base path for uploads
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', '/iPortfolio/admin/uploads/');
