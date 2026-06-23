<?php
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Load .env for local development (Render injects env vars at OS level)
$_envFile = __DIR__ . '/../.env';
if (file_exists($_envFile)) {
    foreach (file($_envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $_line) {
        $_line = trim($_line);
        if ($_line === '' || $_line[0] === '#') continue;
        [$_k, $_v] = array_map('trim', explode('=', $_line, 2)) + [1 => ''];
        if ($_k && getenv($_k) === false) { putenv("$_k=$_v"); $_ENV[$_k] = $_v; }
    }
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'iportfolio_db');

// Admin credentials, change these before going live
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123');

// Absolute base path for uploads (local fallback, not used on Render)
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', '/admin/uploads/');

/**
 * Upload an image file to Cloudinary and return its secure_url.
 * Returns null if credentials are missing or the upload fails.
 */
function uploadToCloudinary(string $tmpFile): ?string {
    $cloudName = getenv('CLOUDINARY_CLOUD_NAME');
    $apiKey    = getenv('CLOUDINARY_API_KEY');
    $apiSecret = getenv('CLOUDINARY_API_SECRET');

    if (!$cloudName || !$apiKey || !$apiSecret) return null;

    $timestamp = time();
    $folder    = 'portfolio';

    // Signature: alphabetically sorted params string + api_secret
    $signature = hash('sha256', "folder={$folder}&timestamp={$timestamp}{$apiSecret}");

    $ch = curl_init("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => [
            'file'      => new CURLFile($tmpFile),
            'api_key'   => $apiKey,
            'timestamp' => $timestamp,
            'folder'    => $folder,
            'signature' => $signature,
        ],
    ]);
    $result = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($result, true);
    return $data['secure_url'] ?? null;
}
