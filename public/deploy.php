<?php
declare(strict_types=1);
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;

/**
 * GadgetGallery - Shared Hosting Deployment & Maintenance Tool
 * Allows running database imports, migrations, cache clearing, and storage symlinks without SSH.
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
set_time_limit(300);

$basePath = realpath(__DIR__.'/..');
if ($basePath === false || ! is_dir($basePath)) {
    http_response_code(500);
    exit('Could not determine application base directory.');
}

// Helper to parse .env file before Laravel is booted
function parseEnvFile(string $filePath): array
{
    if (! file_exists($filePath)) {
        return [];
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $data = [];
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }
        if (str_contains($trimmed, '=')) {
            [$key, $value] = explode('=', $trimmed, 2);
            $key = trim($key);
            $value = trim($value);
            // Remove wrapping quotes
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }
            $data[$key] = $value;
        }
    }

    return $data;
}

$env = parseEnvFile($basePath.'/.env');
$expectedToken = $env['DEPLOY_TOKEN'] ?? '';
$providedToken = (string) ($_GET['token'] ?? $_POST['token'] ?? '');

// Verify Authentication Token
if ($expectedToken === '' || ! hash_equals($expectedToken, $providedToken)) {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>403 - Unauthorized Access</title>
        <style>
            body { font-family: system-ui, -apple-system, sans-serif; background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
            .card { background: #1e293b; padding: 2.5rem; border-radius: 1rem; border: 1px solid #334155; max-width: 480px; text-align: center; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5); }
            h1 { color: #f43f5e; margin-top: 0; font-size: 1.75rem; }
            p { color: #94a3b8; font-size: 0.95rem; line-height: 1.5; }
            .code { font-family: monospace; background: #0f172a; padding: 0.5rem 0.75rem; border-radius: 0.5rem; color: #38bdf8; display: inline-block; margin-top: 1rem; font-size: 0.85rem; }
        </style>
    </head>
    <body>
        <div class="card">
            <h1>403 - Access Denied</h1>
            <p>You must provide the valid <code>DEPLOY_TOKEN</code> query parameter to access this deployment tool.</p>
            <div class="code">/deploy.php?token=YOUR_DEPLOY_TOKEN</div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$action = (string) ($_GET['action'] ?? $_POST['action'] ?? 'run');

// Handle Deleting deploy.php for security
if ($action === 'delete_deploy') {
    $selfFile = __FILE__;
    $rootDeploy = $basePath.'/deploy.php';
    $deletedSelf = @unlink($selfFile);
    $deletedRoot = file_exists($rootDeploy) ? @unlink($rootDeploy) : true;

    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Deploy Tool Deleted</title>
        <style>
            body { font-family: system-ui, sans-serif; background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
            .card { background: #1e293b; padding: 2rem; border-radius: 1rem; text-align: center; max-width: 450px; border: 1px solid #22c55e; }
            h2 { color: #22c55e; margin-top: 0; }
            a { display: inline-block; margin-top: 1rem; padding: 0.6rem 1.2rem; background: #3b82f6; color: white; text-decoration: none; border-radius: 0.5rem; }
        </style>
    </head>
    <body>
        <div class="card">
            <h2>deploy.php Successfully Deleted!</h2>
            <p>The deployment scripts have been permanently removed from the server for security.</p>
            <a href="/">Go to Homepage &rarr;</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Log of actions performed
$logs = [];

function addLog(string $title, string $status, string $message, ?string $output = null): void
{
    global $logs;
    $logs[] = [
        'title' => $title,
        'status' => $status, // 'success', 'warning', 'error', 'info'
        'message' => $message,
        'output' => $output,
    ];
}

// Step 1: Check Environment & Permissions
$writableDirs = [
    'storage' => $basePath.'/storage',
    'storage/framework' => $basePath.'/storage/framework',
    'storage/logs' => $basePath.'/storage/logs',
    'bootstrap/cache' => $basePath.'/bootstrap/cache',
];

$permissionIssues = [];
foreach ($writableDirs as $name => $path) {
    if (! is_dir($path)) {
        @mkdir($path, 0775, true);
    }
    if (! is_writable($path)) {
        $permissionIssues[] = $name;
    }
}

if (! empty($permissionIssues)) {
    addLog('Permissions Check', 'warning', 'Folders not writable: '.implode(', ', $permissionIssues).'. Please ensure chmod 755 or 775.');
} else {
    addLog('Permissions Check', 'success', 'All essential directories (storage, bootstrap/cache) are writable.');
}

// Step 2: Database Connection and Import
$dbHost = $env['DB_HOST'] ?? '127.0.0.1';
$dbPort = $env['DB_PORT'] ?? '3306';
$dbName = $env['DB_DATABASE'] ?? '';
$dbUser = $env['DB_USERNAME'] ?? '';
$dbPass = $env['DB_PASSWORD'] ?? '';

$pdo = null;
if ($dbName !== '' && $dbUser !== '') {
    try {
        $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => true,
        ]);
        addLog('Database Connection', 'success', "Connected successfully to database '{$dbName}' on {$dbHost}.");

        // Check if database is empty and dump.sql exists
        $dumpFile = $basePath.'/database/dump.sql';
        $tableCount = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '{$dbName}'")->fetchColumn();

        if (file_exists($dumpFile)) {
            $forceImport = isset($_GET['force_import']) || isset($_POST['force_import']);
            if ($tableCount === 0 || $forceImport) {
                $sqlContent = file_get_contents($dumpFile);
                if ($sqlContent !== false && trim($sqlContent) !== '') {
                    $pdo->exec('SET FOREIGN_KEY_CHECKS=0;');
                    $pdo->exec($sqlContent);
                    $pdo->exec('SET FOREIGN_KEY_CHECKS=1;');
                    $newCount = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '{$dbName}'")->fetchColumn();
                    addLog('Database Import', 'success', "Bundled dump.sql was imported successfully ({$newCount} tables created).");
                }
            } else {
                addLog('Database Import', 'info', "Database already contains {$tableCount} tables. Skipped dump.sql import (use force import if needed).");
            }
        }
    } catch (Throwable $e) {
        addLog('Database Connection / Import', 'error', 'Database error: '.$e->getMessage());
    }
} else {
    addLog('Database Configuration', 'warning', 'Database credentials not found in .env.');
}

// Step 3: Boot Laravel & Run Artisan Commands
$autoloadFile = $basePath.'/vendor/autoload.php';
$appFile = $basePath.'/bootstrap/app.php';

if (file_exists($autoloadFile) && file_exists($appFile)) {
    try {
        require_once $autoloadFile;
        /** @var Application $app */
        $app = require_once $appFile;
        $kernel = $app->make(Kernel::class);
        $kernel->bootstrap();

        if ($action === 'clear_cache' || $action === 'run') {
            // 1. Optimize Clear
            Artisan::call('optimize:clear');
            $clearOutput = trim(Artisan::output());
            addLog('Clear Caches', 'success', 'All application caches cleared successfully.', $clearOutput);

            // 2. Storage Link & Media Sync
            $storagePublic = $basePath.'/public/storage';
            $storageTarget = $basePath.'/storage/app/public';
            if (! file_exists($storagePublic)) {
                try {
                    Artisan::call('storage:link');
                    $linkOutput = trim(Artisan::output());
                    addLog('Storage Symlink', 'success', 'Storage linked to public/storage.', $linkOutput);
                } catch (Throwable $e) {
                    // Fallback using symlink function
                    if (function_exists('symlink') && is_dir($storageTarget)) {
                        @symlink($storageTarget, $storagePublic);
                        addLog('Storage Symlink', 'success', 'Storage symlinked via PHP fallback.');
                    } else {
                        addLog('Storage Symlink', 'warning', 'Could not create symlink: '.$e->getMessage().'.');
                    }
                }
            } else {
                addLog('Storage Link', 'info', 'public/storage exists.');
            }

            // If public/storage is a physical directory (not a symlink), sync any media from storage/app/public
            if (is_dir($storagePublic) && ! is_link($storagePublic) && is_dir($storageTarget)) {
                $syncCount = 0;
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($storageTarget, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );
                foreach ($iterator as $item) {
                    $subPath = substr($item->getPathname(), strlen($storageTarget) + 1);
                    $dest = $storagePublic.DIRECTORY_SEPARATOR.$subPath;
                    if ($item->isDir()) {
                        if (! is_dir($dest)) {
                            @mkdir($dest, 0775, true);
                        }
                    } elseif ($item->isFile() && ! file_exists($dest)) {
                        $dir = dirname($dest);
                        if (! is_dir($dir)) {
                            @mkdir($dir, 0775, true);
                        }
                        @copy($item->getPathname(), $dest);
                        $syncCount++;
                    }
                }
                if ($syncCount > 0) {
                    addLog('Storage Media Sync', 'success', "Synced {$syncCount} media files from storage/app/public to public/storage.");
                }
            }

            // 3. Database Migrations
            if ($pdo !== null && $action === 'run') {
                try {
                    Artisan::call('migrate', ['--force' => true]);
                    $migrateOutput = trim(Artisan::output());
                    addLog('Database Migrations', 'success', 'Migrations checked and executed.', $migrateOutput);
                } catch (Throwable $e) {
                    addLog('Database Migrations', 'warning', 'Migration note: '.$e->getMessage());
                }
            }

            // 4. Rebuild Production Caches
            try {
                Artisan::call('config:cache');
                Artisan::call('route:cache');
                Artisan::call('view:cache');
                Artisan::call('event:cache');
                addLog('Production Optimization', 'success', 'Config, Route, View, and Event caches generated for live Linux server.');
            } catch (Throwable $e) {
                addLog('Production Optimization', 'warning', 'Optimization warning: '.$e->getMessage());
            }
        }
    } catch (Throwable $e) {
        addLog('Laravel Application Boot', 'error', 'Error executing Artisan commands: '.$e->getMessage());
    }
} else {
    addLog('Laravel Boot', 'error', 'Vendor autoload or bootstrap/app.php is missing.');
}

// Render Dashboard UI
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GadgetGallery Deployment & Cache Manager</title>
    <style>
        :root {
            --bg: #0f172a;
            --card-bg: #1e293b;
            --border: #334155;
            --text: #f8fafc;
            --text-muted: #94a3b8;
            --accent: #38bdf8;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --info: #3b82f6;
        }
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 2rem 1rem;
            min-height: 100vh;
        }
        .container {
            max-width: 820px;
            margin: 0 auto;
        }
        .header {
            background: var(--card-bg);
            padding: 1.75rem 2rem;
            border-radius: 1rem;
            border: 1px solid var(--border);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .header h1 {
            margin: 0;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .header .badge {
            background: #0284c7;
            font-size: 0.8rem;
            padding: 0.25rem 0.6rem;
            border-radius: 9999px;
            font-weight: normal;
        }
        .btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            margin-bottom: 1.5rem;
        }
        .btn {
            padding: 0.6rem 1.1rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s ease;
        }
        .btn-primary { background: #2563eb; color: white; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-secondary { background: #334155; color: white; }
        .btn-secondary:hover { background: #475569; }
        .btn-warning { background: #d97706; color: white; }
        .btn-warning:hover { background: #b45309; }
        .btn-danger { background: #dc2626; color: white; }
        .btn-danger:hover { background: #b91c1c; }
        .btn-success { background: #059669; color: white; }
        .btn-success:hover { background: #047857; }

        .card {
            background: var(--card-bg);
            border-radius: 0.75rem;
            border: 1px solid var(--border);
            margin-bottom: 1rem;
            overflow: hidden;
        }
        .log-item {
            padding: 1.1rem 1.25rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }
        .log-item:last-child { border-bottom: none; }
        .log-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .log-title {
            font-weight: 600;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .status-badge {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.2rem 0.5rem;
            border-radius: 0.25rem;
            font-weight: 700;
        }
        .status-success { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); }
        .status-warning { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); }
        .status-error { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); }
        .status-info { background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.4); }
        .log-msg { color: var(--text-muted); font-size: 0.9rem; line-height: 1.4; }
        pre {
            background: #090d16;
            color: #93c5fd;
            padding: 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.8rem;
            overflow-x: auto;
            margin: 0.5rem 0 0 0;
            border: 1px solid #1e293b;
        }
        .footer {
            margin-top: 2rem;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>GadgetGallery Deployer <span class="badge">Live Helper</span></h1>
                <div style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem;">
                    PHP <?= PHP_VERSION ?> &bull; Environment: <?= htmlspecialchars($env['APP_ENV'] ?? 'unknown') ?>
                </div>
            </div>
            <a href="/" class="btn btn-success" target="_blank">Open Website &rarr;</a>
        </div>

        <div class="btn-group">
            <a href="?token=<?= urlencode($providedToken) ?>&action=run" class="btn btn-primary">
                &#9654; Full Deploy (DB + Cache + Link)
            </a>
            <a href="?token=<?= urlencode($providedToken) ?>&action=clear_cache" class="btn btn-secondary">
                &#128260; Clear & Rebuild Cache
            </a>
            <a href="?token=<?= urlencode($providedToken) ?>&action=run&force_import=1" class="btn btn-warning" onclick="return confirm('Force re-import database/dump.sql? Existing data might be overwritten.');">
                &#128450; Force Re-import Database
            </a>
            <a href="?token=<?= urlencode($providedToken) ?>&action=delete_deploy" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete deploy.php from the server? This will prevent further web deployment runs.');">
                &#128465; Delete deploy.php
            </a>
        </div>

        <div class="card">
            <?php foreach ($logs as $item) { ?>
                <div class="log-item">
                    <div class="log-header">
                        <div class="log-title">
                            <?php if ($item['status'] === 'success') { ?>&#9989;<?php } elseif ($item['status'] === 'warning') { ?>&#9888;&#65039;<?php } elseif ($item['status'] === 'error') { ?>&#10060;<?php } else { ?>&#8505;&#65039;<?php } ?>
                            <?= htmlspecialchars($item['title']) ?>
                        </div>
                        <span class="status-badge status-<?= $item['status'] ?>"><?= $item['status'] ?></span>
                    </div>
                    <div class="log-msg"><?= htmlspecialchars($item['message']) ?></div>
                    <?php if (! empty($item['output'])) { ?>
                        <pre><?= htmlspecialchars($item['output']) ?></pre>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>

        <div class="footer">
            GadgetGallery &bull; Shared Hosting Assistant &bull; Always keep your <code>DEPLOY_TOKEN</code> private.
        </div>
    </div>
</body>
</html>
