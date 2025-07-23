<?php
/**
 * Entry point for Laravel on Shared Hosting
 * Redirects all requests to public/ folder
 */

// Check if we're already in public folder
if (basename(__DIR__) === 'public') {
    // We're in public folder, load Laravel normally
    require_once __DIR__ . '/index.php';
    exit;
}

// We're in root, redirect to public folder
$public_path = __DIR__ . '/public/index.php';

if (file_exists($public_path)) {
    // Include Laravel's public index.php
    require_once $public_path;
} else {
    // Fallback: redirect to public folder
    $request_uri = $_SERVER['REQUEST_URI'];
    $public_url = '/public' . $request_uri;
    
    // Redirect to public folder
    header("Location: $public_url", true, 301);
    exit;
}
?>
