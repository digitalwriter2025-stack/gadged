<?php

namespace App\Console\Commands;

use Dotenv\Dotenv;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\PackageManifest;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Process\ExecutableFinder;
use ZipArchive;

/**
 * Builds a zip for shared hosting without SSH. Extract it in the domain's document
 * root, then open /deploy.php?token=DEPLOY_TOKEN once: that imports the bundled
 * database, runs migrations, links storage and rebuilds every cache.
 */
#[Signature('hosting:package
    {--update : Leave out .env, the database dump and uploaded media so the live site keeps its own}
    {--skip-build : Reuse the current public/build instead of running npm run build}
    {--output= : Zip path (default: hosting.zip, or hosting-update.zip with --update, in the project root)}
    {--composer= : Path to composer.phar when Composer is not on PATH}
    {--mysqldump= : Path to mysqldump when it is not on PATH}')]
#[Description('Build a zip for shared hosting without SSH (extract it, then open /deploy.php)')]
class PackageForHostingCommand extends Command
{
    /**
     * Project entries copied to the server. vendor/ comes from a separate --no-dev install.
     *
     * @var list<string>
     */
    private const INCLUDED_PATHS = [
        '.htaccess', 'app', 'artisan', 'bootstrap', 'composer.json', 'composer.lock',
        'config', 'database', 'deploy.php', 'lang', 'public', 'resources', 'routes', 'storage',
    ];

    /**
     * Tables whose rows belong to one environment; only their structure is exported.
     *
     * @var list<string>
     */
    private const TABLES_WITHOUT_DATA = [
        'cache', 'cache_locks', 'failed_jobs', 'job_batches', 'jobs', 'password_reset_tokens', 'sessions',
    ];

    public function handle(Filesystem $files): int
    {
        $isUpdate = (bool) $this->option('update');
        $outputPath = $this->option('output') ?: base_path($isUpdate ? 'hosting-update.zip' : 'hosting.zip');
        $environmentPath = base_path('.env.production');

        if (! $isUpdate && ($problems = $this->productionEnvironmentProblems($environmentPath)) !== []) {
            $this->components->error('Fix .env.production first (it becomes .env on the server):');
            $this->components->bulletList($problems);

            return self::FAILURE;
        }

        $stagingPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'hosting-'.Str::lower(Str::random(8));

        try {
            if (! $this->option('skip-build')) {
                $this->components->task('Building frontend assets', fn () => Process::path(base_path())
                    ->timeout(600)
                    ->run('npm run build')
                    ->throw());
            }

            if (! is_file(public_path('build/manifest.json'))) {
                throw new RuntimeException('public/build/manifest.json is missing. Run npm run build.');
            }

            $this->components->task('Installing production Composer packages', fn () => $this->installProductionDependencies($files, $stagingPath));

            $dumpPath = null;

            if (! $isUpdate) {
                $this->components->task('Exporting the database', function () use ($files, $stagingPath, &$dumpPath): void {
                    $dumpPath = $this->dumpDatabase($files, $stagingPath);
                });
            }

            $this->components->task('Writing '.basename($outputPath), fn () => $this->writeZip($files, $outputPath, $stagingPath, $dumpPath));
        } catch (RuntimeException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        } finally {
            $files->deleteDirectory($stagingPath);
        }

        $this->printNextSteps($outputPath, $isUpdate, $environmentPath);

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function productionEnvironmentProblems(string $environmentPath): array
    {
        if (! is_file($environmentPath)) {
            return ['.env.production does not exist.'];
        }

        $environment = Dotenv::parse(file_get_contents($environmentPath));

        return array_values(array_filter([
            ($environment['APP_ENV'] ?? '') !== 'production' ? 'APP_ENV must be production.' : null,
            ($environment['APP_DEBUG'] ?? '') !== 'false' ? 'APP_DEBUG must be false.' : null,
            blank($environment['APP_KEY'] ?? null) ? 'APP_KEY is empty.' : null,
            strlen($environment['DEPLOY_TOKEN'] ?? '') < 32 ? 'DEPLOY_TOKEN must be at least 32 random characters.' : null,
        ]));
    }

    /**
     * Installs vendor/ without dev packages and writes the matching package manifest, so the
     * server never tries to boot a dev-only provider (Boost, Pail, Collision, ...).
     */
    private function installProductionDependencies(Filesystem $files, string $stagingPath): void
    {
        $files->ensureDirectoryExists($stagingPath.'/bootstrap/cache');

        foreach (['composer.json', 'composer.lock'] as $file) {
            $files->copy(base_path($file), $stagingPath.'/'.$file);
        }

        // The optimized classmap scans the autoloaded folders, so they must exist here.
        foreach (['app', 'database'] as $directory) {
            $files->copyDirectory(base_path($directory), $stagingPath.'/'.$directory);
        }

        Process::path($stagingPath)
            ->timeout(900)
            ->run([
                ...$this->composerCommand(),
                'install', '--no-dev', '--no-scripts', '--optimize-autoloader',
                '--prefer-dist', '--no-interaction', '--no-progress',
            ])
            ->throw();

        (new PackageManifest($files, $stagingPath, $stagingPath.'/bootstrap/cache/packages.php'))->build();
    }

    /**
     * Composer runs on the PHP binary running artisan: the composer.bat shim may pick an
     * older PHP that fails the platform check and Laravel's own scripts.
     *
     * @return list<string>
     */
    private function composerCommand(): array
    {
        $composer = $this->option('composer') ?: (new ExecutableFinder)->find('composer');

        if ($composer === null) {
            throw new RuntimeException('Composer was not found. Pass --composer=/path/to/composer.phar.');
        }

        $phar = dirname($composer).DIRECTORY_SEPARATOR.'composer.phar';

        return [PHP_BINARY, is_file($phar) ? $phar : $composer];
    }

    private function dumpDatabase(Filesystem $files, string $stagingPath): string
    {
        $connection = config('database.connections.'.config('database.default'));
        $mysqldump = $this->option('mysqldump') ?: (new ExecutableFinder)->find('mysqldump', null, ['C:\\xampp\\mysql\\bin']);

        if ($mysqldump === null) {
            throw new RuntimeException('mysqldump was not found. Pass --mysqldump=/path/to/mysqldump.');
        }

        $arguments = [
            $mysqldump,
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--user='.$connection['username'],
            '--default-character-set=utf8mb4',
            '--single-transaction',
            '--skip-lock-tables',
            '--skip-add-locks',
            '--skip-comments',
            '--no-tablespaces',
        ];

        $ignoredTables = array_map(
            fn (string $table): string => '--ignore-table='.$connection['database'].'.'.$table,
            self::TABLES_WITHOUT_DATA,
        );

        $sql = collect([
            [...$arguments, '--no-data', $connection['database']],
            [...$arguments, '--no-create-info', ...$ignoredTables, $connection['database']],
        ])->map(fn (array $command): string => Process::env(['MYSQL_PWD' => (string) $connection['password']])
            ->timeout(300)
            ->run($command)
            ->throw()
            ->output()
        )->implode(PHP_EOL);

        // Newer MariaDB clients prepend a sandbox directive that MySQL and older MariaDB reject.
        $sql = preg_replace('/^\/\*M!999999.*\R/m', '', $sql);

        $files->put($dumpPath = $stagingPath.'/dump.sql', $sql);

        return $dumpPath;
    }

    private function writeZip(Filesystem $files, string $outputPath, string $stagingPath, ?string $dumpPath): void
    {
        $files->delete($outputPath);

        $zip = new ZipArchive;

        if ($zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Cannot create {$outputPath}.");
        }

        foreach (self::INCLUDED_PATHS as $path) {
            $absolutePath = base_path($path);

            if (is_file($absolutePath)) {
                $zip->addFile($absolutePath, $path);
            } elseif (is_dir($absolutePath)) {
                $this->addDirectoryToZip($zip, base_path(), $path, fn (string $relativePath): bool => $this->shouldShip($relativePath, $dumpPath === null));
            }
        }

        $this->addDirectoryToZip($zip, $stagingPath, 'vendor', fn (): bool => true);
        $zip->addFile($stagingPath.'/bootstrap/cache/packages.php', 'bootstrap/cache/packages.php');

        if ($dumpPath !== null) {
            $zip->addFile(base_path('.env.production'), '.env');
            $zip->addFile($dumpPath, 'database/dump.sql');
        }

        // Bundle uploaded media into public/storage as real files so they work immediately upon unzipping without symlinks
        $publicStorageSource = base_path('storage/app/public');
        if (is_dir($publicStorageSource)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($publicStorageSource, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($publicStorageSource) + 1));
                    $zip->addFile($file->getPathname(), 'public/storage/'.$relative);
                }
            }
        }

        if (! $zip->close()) {
            throw new RuntimeException("Writing {$outputPath} failed.");
        }
    }

    /**
     * @param  callable(string): bool  $shouldShip
     */
    private function addDirectoryToZip(ZipArchive $zip, string $root, string $directory, callable $shouldShip): void
    {
        $toRelativePath = fn (SplFileInfo $file): string => str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));

        $iterator = new RecursiveIteratorIterator(new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator($root.DIRECTORY_SEPARATOR.$directory, RecursiveDirectoryIterator::SKIP_DOTS),
            fn (SplFileInfo $file): bool => $file->isDir()
                ? ! $file->isLink() && $toRelativePath($file) !== 'public/storage'
                : $shouldShip($toRelativePath($file)),
        ));

        foreach ($iterator as $file) {
            $zip->addFile($file->getPathname(), $toRelativePath($file));
        }
    }

    /**
     * Local caches, logs and the public/storage link are never shipped: the server
     * rebuilds them. Uploaded media is shipped only with a full (non --update) package.
     */
    private function shouldShip(string $relativePath, bool $isUpdate): bool
    {
        if ($relativePath === 'public/hot') {
            return false;
        }

        if (str_starts_with($relativePath, 'bootstrap/cache/') || str_starts_with($relativePath, 'storage/')) {
            return basename($relativePath) === '.gitignore'
                || (! $isUpdate && str_starts_with($relativePath, 'storage/app/public/'));
        }

        return true;
    }

    private function printNextSteps(string $outputPath, bool $isUpdate, string $environmentPath): void
    {
        $this->components->info(sprintf('Created %s (%s MB).', $outputPath, round(filesize($outputPath) / 1048576, 1)));

        $deployUrl = '/deploy.php?token=DEPLOY_TOKEN';

        if (! $isUpdate) {
            $environment = Dotenv::parse(file_get_contents($environmentPath));
            $deployUrl = rtrim($environment['APP_URL'] ?? '', '/').'/deploy.php?token='.$environment['DEPLOY_TOKEN'];
        }

        $this->components->bulletList(array_filter([
            'In cPanel set the domain to PHP 8.4 (Select PHP Version / MultiPHP Manager).',
            'Upload the zip to the domain\'s document root and extract it there (it has no wrapper folder).',
            $isUpdate ? 'Your live .env, database and uploaded images are left untouched.' : null,
            'Open '.$deployUrl,
        ]));
    }
}
