<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportDatabaseDumpCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_skips_the_import_when_the_database_already_has_tables(): void
    {
        $this->artisan('db:import-dump', ['--path' => 'missing.sql'])
            ->expectsOutputToContain('skipping the dump import')
            ->assertSuccessful();
    }
}
