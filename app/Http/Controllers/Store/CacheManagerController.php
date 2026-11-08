<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;

class CacheManagerController extends Controller
{
    /**
     * Clear and rebuild application caches via web without SSH access.
     */
    public function __invoke(Request $request): Response|JsonResponse
    {
        $expectedToken = (string) config('app.deploy_token');
        $providedToken = (string) ($request->query('token') ?? $request->input('token') ?? '');

        // If token is missing or does not match
        if ($expectedToken === '' || ! hash_equals($expectedToken, $providedToken)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized: Invalid or missing token.',
                ], 403);
            }

            return response($this->renderTokenPromptHtml($providedToken !== ''), 403);
        }

        // Execute cache clearing and rebuilding commands
        $outputs = [];

        Artisan::call('optimize:clear');
        $outputs['optimize_clear'] = trim(Artisan::output());

        Artisan::call('config:cache');
        $outputs['config_cache'] = trim(Artisan::output());

        Artisan::call('route:cache');
        $outputs['route_cache'] = trim(Artisan::output());

        Artisan::call('view:cache');
        $outputs['view_cache'] = trim(Artisan::output());

        Artisan::call('event:cache');
        $outputs['event_cache'] = trim(Artisan::output());

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'All caches cleared and rebuilt successfully without SSH!',
                'timestamp' => now()->toDateTimeString(),
                'details' => $outputs,
            ]);
        }

        return response($this->renderSuccessHtml($outputs));
    }

    /**
     * Render the sleek authentication prompt HTML.
     */
    private function renderTokenPromptHtml(bool $hasInvalidAttempt): string
    {
        $errorNotice = $hasInvalidAttempt
            ? '<div class="alert alert-danger">The token you entered is invalid. Please try again.</div>'
            : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cache Manager - GadgetGallery</title>
    <style>
        :root {
            --bg: #0b0f19;
            --card-bg: #151d30;
            --border: #24324f;
            --text: #f8fafc;
            --muted: #94a3b8;
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --danger: #ef4444;
        }
        * { box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
        }
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 1rem;
            padding: 2.25rem;
            max-width: 460px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
        }
        .icon {
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
        }
        h1 {
            font-size: 1.5rem;
            margin: 0 0 0.5rem 0;
            font-weight: 700;
        }
        p {
            color: var(--muted);
            font-size: 0.92rem;
            line-height: 1.5;
            margin: 0 0 1.5rem 0;
        }
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            margin-bottom: 1.25rem;
            text-align: left;
        }
        .alert-danger {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            text-align: left;
        }
        label {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--muted);
        }
        input[type="password"], input[type="text"] {
            width: 100%;
            padding: 0.75rem 1rem;
            background: #0b0f19;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            color: var(--text);
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s;
        }
        input:focus {
            border-color: var(--primary);
        }
        button {
            padding: 0.8rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 0.5rem;
        }
        button:hover {
            background: var(--primary-hover);
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">&#9881;&#65039;</div>
        <h1>Clear & Rebuild Cache</h1>
        <p>No SSH required. Enter your secret deployment token to clear application caches.</p>
        {$errorNotice}
        <form method="POST">
            <div>
                <label for="token">Deployment Token</label>
                <input type="password" id="token" name="token" required placeholder="Enter DEPLOY_TOKEN" autofocus>
            </div>
            <button type="submit">&#128260; Clean & Rebuild Cache</button>
        </form>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Render the successful cache clearing result HTML.
     *
     * @param  array<string, string>  $outputs
     */
    private function renderSuccessHtml(array $outputs): string
    {
        $timestamp = now()->toDateTimeString();
        $detailsHtml = '';

        foreach ($outputs as $key => $output) {
            $formattedKey = ucwords(str_replace('_', ' ', $key));
            $escapedOutput = htmlspecialchars($output ?: 'OK');
            $detailsHtml .= <<<HTML
            <div class="log-item">
                <strong>&#9989; {$formattedKey}</strong>
                <pre>{$escapedOutput}</pre>
            </div>
HTML;
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cache Cleared Successfully - GadgetGallery</title>
    <style>
        :root {
            --bg: #0b0f19;
            --card-bg: #151d30;
            --border: #24324f;
            --text: #f8fafc;
            --muted: #94a3b8;
            --success: #10b981;
            --primary: #3b82f6;
        }
        * { box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 2rem 1rem;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            max-width: 640px;
            width: 100%;
        }
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .header {
            text-align: center;
            margin-bottom: 1.75rem;
        }
        .icon {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }
        h1 {
            font-size: 1.6rem;
            margin: 0 0 0.5rem 0;
            color: var(--success);
        }
        p {
            color: var(--muted);
            margin: 0;
            font-size: 0.95rem;
        }
        .log-item {
            margin-bottom: 1rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 0.75rem;
        }
        .log-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        strong {
            font-size: 0.9rem;
            display: block;
            margin-bottom: 0.25rem;
        }
        pre {
            background: #080c14;
            color: #93c5fd;
            padding: 0.6rem 0.8rem;
            border-radius: 0.4rem;
            font-size: 0.8rem;
            margin: 0;
            overflow-x: auto;
            border: 1px solid var(--border);
        }
        .actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.75rem;
        }
        .btn {
            flex: 1;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            border: none;
        }
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        .btn-secondary {
            background: #1e293b;
            color: var(--text);
            border: 1px solid var(--border);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <div class="icon">&#9989;</div>
                <h1>Cache Cleared & Rebuilt!</h1>
                <p>Application caches (config, route, view, event) updated successfully at {$timestamp}</p>
            </div>
            <div class="logs">
                {$detailsHtml}
            </div>
            <div class="actions">
                <a href="/" class="btn btn-primary" target="_blank">Open Website &rarr;</a>
                <a href="/clear-cache" class="btn btn-secondary">&#128260; Clean Again</a>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
