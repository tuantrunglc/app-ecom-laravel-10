<?php
/**
 * Storage Link Creator for Shared Hosting - Method 2 (All in public_html)
 */
echo "<h2>🔗 Laravel Storage Link Creator</h2>";
echo "<p>Tạo symbolic link cho storage trong shared hosting</p>";

// Đường dẫn cho Method 2 (Tất cả trong public_html)
$target = './storage/app/public';
$link = './public/storage';

echo "<h3>Method 2: Tất cả trong public_html</h3>";

// Kiểm tra target directory
if (is_dir($target)) {
    echo "✅ Target directory exists: $target<br>";
    
    // Kiểm tra link đã tồn tại chưa
    if (!file_exists($link)) {
        // Thử tạo symlink
        if (@symlink($target, $link)) {
            echo "✅ Storage link created successfully: $link -> $target<br>";
        } else {
            echo "❌ Symlink failed, trying copy method...<br>";
            
            // Fallback: Copy directory
            if (copy_directory($target, $link)) {
                echo "✅ Storage directory copied successfully<br>";
            } else {
                echo "❌ Failed to copy storage directory<br>";
            }
        }
    } else {
        echo "ℹ️ Storage link already exists<br>";
        
        // Kiểm tra link có hoạt động không
        if (is_link($link)) {
            echo "✅ Existing symlink is working<br>";
        } elseif (is_dir($link)) {
            echo "ℹ️ Directory copy exists<br>";
        }
    }
    
    // Kiểm tra permissions
    echo "<h4>📁 Directory Permissions:</h4>";
    echo "Target ($target): " . substr(sprintf('%o', fileperms($target)), -4) . "<br>";
    if (file_exists($link)) {
        echo "Link ($link): " . substr(sprintf('%o', fileperms($link)), -4) . "<br>";
    }
    
} else {
    echo "❌ Target directory not found: $target<br>";
    echo "Please check if storage/app/public directory exists<br>";
}

echo "<hr>";
echo "<h3>📝 Next Steps</h3>";
echo "<ol>";
echo "<li>Test storage access: <a href='./public/storage/' target='_blank'>./public/storage/</a></li>";
echo "<li>Check file permissions: storage (775), public (755)</li>";
echo "<li>Upload some test images to storage/app/public/</li>";
echo "<li><strong>DELETE THIS FILE after testing!</strong></li>";
echo "</ol>";

echo "<hr>";
echo "<p><strong>⚠️ SECURITY WARNING:</strong> Please DELETE this file after use!</p>";

/**
 * Copy directory recursively (fallback if symlink doesn't work)
 */
function copy_directory($src, $dst) {
    if (!is_dir($src)) return false;
    
    if (!is_dir($dst)) {
        if (!mkdir($dst, 0755, true)) {
            return false;
        }
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
