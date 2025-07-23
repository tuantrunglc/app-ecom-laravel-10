<?php
echo "<h1>🔍 Laravel Shared Hosting Diagnostic</h1>";
echo "<hr>";

echo "<h2>📋 Environment Check</h2>";
echo "<strong>PHP Version:</strong> " . PHP_VERSION . "<br>";
echo "<strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "<strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "<strong>Current Directory:</strong> " . __DIR__ . "<br>";

echo "<h2>📁 File Structure Check</h2>";
$required_dirs = ['app', 'bootstrap', 'config', 'public', 'storage', 'vendor'];
foreach($required_dirs as $dir) {
    $exists = is_dir(__DIR__ . '/' . $dir);
    $status = $exists ? "✅" : "❌";
    echo "<strong>$dir/:</strong> $status " . ($exists ? "EXISTS" : "MISSING") . "<br>";
}

echo "<h2>📄 Required Files Check</h2>";
$required_files = ['.env', 'artisan', 'composer.json', 'public/index.php'];
foreach($required_files as $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    $status = $exists ? "✅" : "❌";
    echo "<strong>$file:</strong> $status " . ($exists ? "EXISTS" : "MISSING") . "<br>";
}

echo "<h2>🔑 Laravel Configuration</h2>";
if (file_exists(__DIR__ . '/.env')) {
    $env_content = file_get_contents(__DIR__ . '/.env');
    $app_key_found = strpos($env_content, 'APP_KEY=') !== false;
    $db_configured = strpos($env_content, 'DB_DATABASE=') !== false;
    
    echo "<strong>APP_KEY:</strong> " . ($app_key_found ? "✅ SET" : "❌ NOT SET") . "<br>";
    echo "<strong>Database Config:</strong> " . ($db_configured ? "✅ CONFIGURED" : "❌ NOT CONFIGURED") . "<br>";
} else {
    echo "<strong>.env file:</strong> ❌ NOT FOUND<br>";
}

echo "<h2>📦 Composer Check</h2>";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "<strong>Composer autoload:</strong> ✅ AVAILABLE<br>";
    require_once __DIR__ . '/vendor/autoload.php';
    echo "<strong>Vendor loaded:</strong> ✅ SUCCESS<br>";
} else {
    echo "<strong>Composer autoload:</strong> ❌ NOT FOUND<br>";
}

echo "<h2>🗄️ Database Test</h2>";
if (file_exists(__DIR__ . '/.env')) {
    // Parse .env file
    $env_lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env_vars = [];
    foreach($env_lines as $line) {
        if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
            list($key, $value) = explode('=', $line, 2);
            $env_vars[trim($key)] = trim($value);
        }
    }
    
    if (isset($env_vars['DB_HOST']) && isset($env_vars['DB_DATABASE'])) {
        try {
            $pdo = new PDO(
                "mysql:host={$env_vars['DB_HOST']};dbname={$env_vars['DB_DATABASE']}", 
                $env_vars['DB_USERNAME'], 
                $env_vars['DB_PASSWORD']
            );
            echo "<strong>Database Connection:</strong> ✅ SUCCESS<br>";
        } catch(PDOException $e) {
            echo "<strong>Database Connection:</strong> ❌ FAILED - " . $e->getMessage() . "<br>";
        }
    } else {
        echo "<strong>Database Config:</strong> ❌ INCOMPLETE<br>";
    }
}

echo "<h2>🌐 URL Test</h2>";
echo "<strong>Current URL:</strong> " . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "<br>";

echo "<hr>";
echo "<h2>🚀 Next Steps</h2>";
echo "<ol>";
echo "<li>Upload <code>index_for_public_html.php</code> as <code>index.php</code> to public_html/</li>";
echo "<li>Upload <code>.htaccess_main_fixed</code> as <code>.htaccess</code> to public_html/</li>";
echo "<li>Configure <code>.env</code> file with correct database settings</li>";
echo "<li>Test Laravel application: <a href='./'>Go to Homepage</a></li>";
echo "<li><strong>DELETE this diagnostic file after testing!</strong></li>";
echo "</ol>";

echo "<p><strong>⚠️ SECURITY WARNING:</strong> DELETE this file after diagnosis!</p>";
?>
