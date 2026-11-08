<?php

declare(strict_types=1);

/**
 * Root deploy.php proxy - delegates to public/deploy.php
 */
$publicDeploy = __DIR__.'/public/deploy.php';

if (file_exists($publicDeploy)) {
    require $publicDeploy;
} else {
    http_response_code(404);
    echo 'deploy.php not found in public directory.';
}
