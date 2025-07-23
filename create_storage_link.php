<?php
/**
 * Storage Link Creator for Shared Hosting
 * Thay thế cho: php artisan storage:link
 * 
 * Cách sử dụng:
 * 1. Upload file này vào public_html/
 * 2. Truy cập: https://yourdomain.com/create_storage_link.php
 * 3. Xóa file này sau khi hoàn thành
 */

echo "<h2>🔗 Laravel Storage Link Creator</h2>";
echo "<p>Tạo symbolic link cho storage trong shared hosting</p>";

// Đường dẫn cho Phương pháp 1 (Laravel core tách riêng)
$target1 = '../laravel_app/storage/app/public';
$link1 = './storage';

// Đường dẫn cho Phương pháp 2 (Tất cả trong public_html)
$target2 = './storage/app/public';
$link2 = './public/storage';

echo "<h3>Phương pháp 1: Laravel core tách riêng</h3>";
if (is_dir($target1)) {
    if (!file_exists($link1)) {
        if (symlink($target1, $link1)) {
            echo "✅ Storage link created successfully: $link1 -> $target1<br>";
        } else {
            echo "❌ Failed to create storage link<br>";
            // Fallback: Copy directory
            echo "Trying alternative method (copy)...<br>";
            if (copy_directory($target1, $link1)) {
                echo "✅ Storage directory copied successfully<br>";
            } else {
                echo "❌ Failed to copy storage directory<br>";
            }
        }
    } else {
        echo "ℹ️ Storage link already exists<br>";
    }
} else {
    echo "❌ Target directory not found: $target1<br>";
}

echo "<h3>Phương pháp 2: Tất cả trong public_html</h3>";
if (is_dir($target2)) {
    if (!file_exists($link2)) {
        if (symlink($target2, $link2)) {
            echo "✅ Storage link created successfully: $link2 -> $target2<br>";
        } else {
            echo "❌ Failed to create storage link<br>";
            // Fallback: Copy directory
            echo "Trying alternative method (copy)...<br>";
            if (copy_directory($target2, $link2)) {
                echo "✅ Storage directory copied successfully<br>";
            } else {
                echo "❌ Failed to copy storage directory<br>";
            }
        }
    } else {
        echo "ℹ️ Storage link already exists<br>";
    }
} else {
    echo "❌ Target directory not found: $target2<br>";
}

echo "<hr>";
echo "<p><strong>⚠️ QUAN TRỌNG:</strong> Hãy XÓA file này sau khi hoàn thành!</p>";
echo "<p>File này có thể tạo lỗ hổng bảo mật nếu để lại trên server.</p>";

/**
 * Copy directory recursively (fallback nếu symlink không work)
 */
function copy_directory($src, $dst) {
    if (!is_dir($src)) return false;
    
    if (!is_dir($dst)) {
        mkdir($dst, 0755, true);
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $item) {
        $target = $dst . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
        if ($item->isDir()) {
            if (!is_dir($target)) {
                mkdir($target, 0755, true);
            }
        } else {
            copy($item, $target);
        }
    }
    
    return true;
}
?>
