<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StorageAssetController extends Controller
{
    /**
     * Fallback controller to serve public media when symlinks are unavailable on shared hosting.
     */
    public function show(string $path): BinaryFileResponse
    {
        $storageDir = realpath(storage_path('app/public'));
        $targetFile = realpath(storage_path('app/public/'.ltrim($path, '/')));

        if (
            $storageDir === false ||
            $targetFile === false ||
            ! str_starts_with($targetFile, $storageDir) ||
            ! is_file($targetFile)
        ) {
            abort(404);
        }

        $response = response()->file($targetFile);
        $response->setCache([
            'public' => true,
            'max_age' => 604800, // 7 days
        ]);

        return $response;
    }
}
