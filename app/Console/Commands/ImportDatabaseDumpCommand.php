<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Loads the committed database dump into a brand-new database, for hosts whose
 * database is only reachable from inside the app container. Safe to run on every
 * deploy: it does nothing once the database has a migrations table.
 */
#[Signature('db:import-dump
    {--path=database/dump/gadget_website.sql : Dump file, relative to the project root}')]
#[Description('Import the committed SQL dump when the database is still empty')]
class ImportDatabaseDumpCommand extends Command
{
    public function handle(): int
    {
        if (Schema::hasTable('migrations')) {
            $this->components->info('Database already has tables, skipping the dump import.');

            return self::SUCCESS;
        }

        $path = base_path((string) $this->option('path'));

        if (! is_file($path)) {
            $this->components->error("Dump file not found: {$path}");

            return self::FAILURE;
        }

        DB::unprepared((string) file_get_contents($path));

        $this->components->info('Imported '.basename($path).' into the empty database.');

        return self::SUCCESS;
    }
}
