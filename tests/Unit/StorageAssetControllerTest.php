<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\Store\StorageAssetController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class StorageAssetControllerTest extends TestCase
{
    public function test_existing_storage_file_is_served(): void
    {
        $testDir = storage_path('app/public/test_assets');
        if (! is_dir($testDir)) {
            mkdir($testDir, 0775, true);
        }

        $testFile = $testDir.'/sample.txt';
        file_put_contents($testFile, 'Hello World Asset');

        try {
            $controller = new StorageAssetController;
            $response = $controller->show('test_assets/sample.txt');

            $this->assertSame(200, $response->getStatusCode());
            $this->assertSame('Hello World Asset', file_get_contents($response->getFile()->getPathname()));
            $this->assertTrue($response->headers->has('Cache-Control'));
        } finally {
            @unlink($testFile);
            @rmdir($testDir);
        }
    }

    public function test_path_traversal_is_blocked_with_404(): void
    {
        $this->expectException(HttpException::class);

        $controller = new StorageAssetController;
        $controller->show('../../.env');
    }

    public function test_non_existent_file_throws_404(): void
    {
        $this->expectException(HttpException::class);

        $controller = new StorageAssetController;
        $controller->show('does_not_exist/foo_bar_12345.webp');
    }
}
