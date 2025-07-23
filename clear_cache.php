<?php
/**
 * Cache Clear Tool for Shared Hosting
 * Thay thế cho: php artisan cache:clear, config:clear, route:clear, view:clear
 * 
 * Cách sử dụng:
 * 1. Upload file này vào thư mục gốc Laravel (cùng cấp với artisan)
 * 2. Truy cập: https://yourdomain.com/clear_cache.php (hoặc domain/path/to/laravel/clear_cache.php)
 * 3. Xóa file này sau khi hoàn thành
 */

echo "<h2>🧹 Laravel Cache Cleaner</h2>";
echo "<p>Xóa cache Laravel trong shared hosting</p>";

$basePath = __DIR__;
$cleared = [];
$errors = [];

// 1. Application Cache
$cachePath = $basePath . '/storage/framework/cache/data';
if (is_dir($cachePath)) {
    if (deleteDirectory($cachePath)) {
        $cleared[] = "✅ Application cache cleared";
        // Recreate the directory
        mkdir($cachePath, 0755, true);
    } else {
        $errors[] = "❌ Failed to clear application cache";
    }
} else {
    $cleared[] = "ℹ️ Application cache directory not found";
}

// 2. Config Cache
$configCachePath = $basePath . '/bootstrap/cache/config.php';
if (file_exists($configCachePath)) {
    if (unlink($configCachePath)) {
        $cleared[] = "✅ Config cache cleared";
    } else {
        $errors[] = "❌ Failed to clear config cache";
    }
} else {
    $cleared[] = "ℹ️ Config cache file not found";
}

// 3. Route Cache
$routeCachePath = $basePath . '/bootstrap/cache/routes-v7.php';
if (file_exists($routeCachePath)) {
    if (unlink($routeCachePath)) {
        $cleared[] = "✅ Route cache cleared";
    } else {
        $errors[] = "❌ Failed to clear route cache";
    }
} else {
    $cleared[] = "ℹ️ Route cache file not found";
}

// Also check for other route cache files
$routeFiles = glob($basePath . '/bootstrap/cache/routes*.php');
foreach ($routeFiles as $file) {
    if (unlink($file)) {
        $cleared[] = "✅ Route cache file cleared: " . basename($file);
    }
}

// 4. View Cache
$viewCachePath = $basePath . '/storage/framework/views';
if (is_dir($viewCachePath)) {
    $viewFiles = glob($viewCachePath . '/*.php');
    $deletedCount = 0;
    foreach ($viewFiles as $file) {
        if (unlink($file)) {
            $deletedCount++;
        }
    }
    if ($deletedCount > 0) {
        $cleared[] = "✅ View cache cleared ($deletedCount files)";
    } else {
        $cleared[] = "ℹ️ No view cache files to clear";
    }
} else {
    $errors[] = "❌ View cache directory not found";
}

// 5. Session files (optional)
$sessionPath = $basePath . '/storage/framework/sessions';
if (is_dir($sessionPath)) {
    $sessionFiles = glob($sessionPath . '/*');
    $deletedCount = 0;
    foreach ($sessionFiles as $file) {
        if (is_file($file) && unlink($file)) {
            $deletedCount++;
        }
    }
    if ($deletedCount > 0) {
        $cleared[] = "✅ Session files cleared ($deletedCount files)";
    } else {
        $cleared[] = "ℹ️ No session files to clear";
    }
}

// 6. Log files (optional - only if too large)
$logPath = $basePath . '/storage/logs';
if (is_dir($logPath)) {
    $logFiles = glob($logPath . '/*.log');
    $largeFiles = [];
    foreach ($logFiles as $file) {
        // Clear log files larger than 10MB
        if (filesize($file) > 10 * 1024 * 1024) {
            $largeFiles[] = basename($file);
            file_put_contents($file, ''); // Empty the file instead of deleting
        }
    }
    if (!empty($largeFiles)) {
        $cleared[] = "✅ Large log files cleared: " . implode(', ', $largeFiles);
    }
}

echo "<h3>Results:</h3>";
foreach ($cleared as $message) {
    echo "<p>$message</p>";
}

if (!empty($errors)) {
    echo "<h3>Errors:</h3>";
    foreach ($errors as $error) {
        echo "<p style='color: red;'>$error</p>";
    }
}

echo "<hr>";
echo "<h3>📝 Manual Steps</h3>";
echo "<p>Nếu có lỗi, bạn có thể xóa thủ công các thư mục/file sau:</p>";
echo "<ul>";
echo "<li><code>storage/framework/cache/data/*</code></li>";
echo "<li><code>storage/framework/views/*.php</code></li>";
echo "<li><code>bootstrap/cache/config.php</code></li>";
echo "<li><code>bootstrap/cache/routes*.php</code></li>";
echo "<li><code>storage/framework/sessions/*</code> (optional)</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>⚠️ QUAN TRỌNG:</strong> Hãy XÓA file này sau khi hoàn thành!</p>";
echo "<p>File này có thể tạo lỗ hổng bảo mật nếu để lại trên server.</p>";

/**
 * Delete directory recursively
 */
function deleteDirectory($dir) {
    if (!is_dir($dir)) return false;
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isDir()) {
            rmdir($file->getRealPath());
        } else {
            unlink($file->getRealPath());
        }
    }
    
    return rmdir($dir);
}
?>
