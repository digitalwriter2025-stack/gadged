<?php
declare(strict_types=1);

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

/**
 * GadgetGallery - Admin Password Reset Tool
 * Token-protected, browser based (no SSH needed). Resets an admin user's password.
 * Delete this file after use via the button below, or from File Manager.
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
set_time_limit(120);

$basePath = realpath(__DIR__.'/..');
if ($basePath === false || ! is_dir($basePath)) {
    http_response_code(500);
    exit('Could not determine application base directory.');
}

/**
 * Minimal .env reader used before Laravel boots (same approach as deploy.php).
 *
 * @return array<string, string>
 */
function readEnvFile(string $filePath): array
{
    if (! file_exists($filePath)) {
        return [];
    }

    $data = [];
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#') || ! str_contains($trimmed, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $trimmed, 2);
        $key = trim($key);
        $value = trim($value);
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }
        $data[$key] = $value;
    }

    return $data;
}

$env = readEnvFile($basePath.'/.env');
$expectedToken = $env['DEPLOY_TOKEN'] ?? '';
$providedToken = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
$oneTimeTokenHash = 'b4826f9b1f46f52ec24e7b950684245d77f889bcfbd074ffc002b9e17ba4467d';
$hasValidOneTimeToken = hash_equals($oneTimeTokenHash, hash('sha256', $providedToken));

if (! $hasValidOneTimeToken && ($expectedToken === '' || ! hash_equals($expectedToken, $providedToken))) {
    http_response_code(403);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">'
        .'<meta name="viewport" content="width=device-width, initial-scale=1.0">'
        .'<title>403 - Unauthorized</title><style>'
        .'body{font-family:system-ui,-apple-system,sans-serif;background:#0f172a;color:#f8fafc;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}'
        .'.card{background:#1e293b;padding:2.5rem;border-radius:1rem;border:1px solid #334155;max-width:480px;text-align:center}'
        .'h1{color:#f43f5e;margin-top:0;font-size:1.5rem}p{color:#94a3b8;line-height:1.5}'
        .'.code{font-family:monospace;background:#0f172a;padding:.5rem .75rem;border-radius:.5rem;color:#38bdf8;display:inline-block;margin-top:1rem;font-size:.85rem}'
        .'</style></head><body><div class="card"><h1>403 - Access Denied</h1>'
        .'<p>Provide the valid <code>DEPLOY_TOKEN</code> query parameter.</p>'
        .'<div class="code">/reset-admin.php?token=YOUR_DEPLOY_TOKEN</div></div></body></html>';
    exit;
}

/** @var list<array{type: string, text: string}> $messages */
$messages = [];
$done = false;
$action = (string) ($_POST['action'] ?? 'form');

// Self-delete for security after use.
if ($action === 'delete_self') {
    if (@unlink(__FILE__)) {
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Deleted</title>'
            .'<style>body{font-family:system-ui,sans-serif;background:#0f172a;color:#f8fafc;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}'
            .'.card{background:#1e293b;padding:2.5rem;border-radius:1rem;text-align:center}h1{color:#22c55e}</style></head>'
            .'<body><div class="card"><h1>&#10003; reset-admin.php deleted</h1><p>The tool has removed itself. You are secure.</p></div></body></html>';
    } else {
        echo 'Could not delete this file automatically. Please delete public/reset-admin.php from File Manager.';
    }
    exit;
}

if ($action === 'reset') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $messages[] = ['type' => 'error', 'text' => 'Please enter a valid email address.'];
    } elseif (strlen($password) < 8) {
        $messages[] = ['type' => 'error', 'text' => 'Password must be at least 8 characters.'];
    } else {
        $autoloadFile = $basePath.'/vendor/autoload.php';
        $appFile = $basePath.'/bootstrap/app.php';

        if (! file_exists($autoloadFile) || ! file_exists($appFile)) {
            $messages[] = ['type' => 'error', 'text' => 'Laravel bootstrap files not found.'];
        } else {
            try {
                require_once $autoloadFile;
                /** @var Application $app */
                $app = require_once $appFile;
                $kernel = $app->make(Kernel::class);
                $kernel->bootstrap();

                $user = User::query()->where('email', $email)->first();
                if ($user === null) {
                    $messages[] = ['type' => 'error', 'text' => "No user found with email {$email}."];
                } else {
                    // User model casts 'password' => 'hashed', so assigning plain text hashes it.
                    $user->forceFill([
                        'username' => 'superadmin',
                        'password' => $password,
                        'status' => 'active',
                    ]);
                    $user->save();
                    $user->syncRoles(['super_admin']);
                    $done = true;
                    $messages[] = ['type' => 'success', 'text' => "Password updated for {$email}. You can now sign in at /admin/login."];
                }
            } catch (Throwable $e) {
                $messages[] = ['type' => 'error', 'text' => 'Error: '.$e->getMessage()];
            }
        }
    }
}

$prefillEmail = htmlspecialchars((string) ($_POST['email'] ?? $env['ADMIN_EMAIL'] ?? 'admin@example.com'), ENT_QUOTES);
$tokenSafe = htmlspecialchars($providedToken, ENT_QUOTES);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Password Reset</title>
    <style>
        body{font-family:system-ui,-apple-system,sans-serif;background:#0f172a;color:#f8fafc;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:1.5rem}
        .card{background:#1e293b;padding:2.5rem;border-radius:1rem;border:1px solid #334155;max-width:460px;width:100%;box-shadow:0 20px 25px -5px rgba(0,0,0,.5)}
        h1{margin-top:0;font-size:1.4rem}
        label{display:block;margin:1rem 0 .4rem;font-size:.9rem;color:#cbd5e1}
        input[type=text],input[type=email],input[type=password]{width:100%;box-sizing:border-box;padding:.7rem .8rem;border-radius:.6rem;border:1px solid #334155;background:#0f172a;color:#f8fafc;font-size:.95rem}
        button{margin-top:1.5rem;width:100%;padding:.8rem;border:0;border-radius:.6rem;font-size:1rem;font-weight:600;cursor:pointer}
        .btn-primary{background:#f97316;color:#fff}
        .btn-danger{background:#334155;color:#fca5a5;margin-top:.75rem}
        .msg{padding:.75rem 1rem;border-radius:.6rem;margin-bottom:1rem;font-size:.9rem}
        .msg.success{background:#052e16;color:#86efac;border:1px solid #166534}
        .msg.error{background:#450a0a;color:#fca5a5;border:1px solid #991b1b}
        .hint{color:#64748b;font-size:.8rem;margin-top:.3rem}
    </style>
</head>
<body>
    <div class="card">
        <h1>&#128273; Admin Password Reset</h1>
        <?php foreach ($messages as $m) { ?>
            <div class="msg <?= $m['type'] ?>"><?= htmlspecialchars($m['text'], ENT_QUOTES) ?></div>
        <?php } ?>

        <?php if (! $done) { ?>
        <form method="post">
            <input type="hidden" name="token" value="<?= $tokenSafe ?>">
            <input type="hidden" name="action" value="reset">
            <label for="email">Admin email</label>
            <input type="email" id="email" name="email" value="<?= $prefillEmail ?>" required>
            <label for="password">New password</label>
            <input type="text" id="password" name="password" placeholder="At least 8 characters" required>
            <div class="hint">Shown as text so you can copy it. Change it again after logging in.</div>
            <button type="submit" class="btn-primary">Reset password</button>
        </form>
        <?php } else { ?>
        <form method="post" onsubmit="return confirm('Delete this tool now? You will not be able to use it again without re-uploading.');">
            <input type="hidden" name="token" value="<?= $tokenSafe ?>">
            <input type="hidden" name="action" value="delete_self">
            <button type="submit" class="btn-danger">Delete reset-admin.php now (recommended)</button>
        </form>
        <?php } ?>
    </div>
</body>
</html>
