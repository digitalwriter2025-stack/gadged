<?php

namespace Tests\Feature\Database;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportDatabaseDumpCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_skips_the_import_when_the_database_already_has_data(): void
    {
        User::factory()->create();

        $this->artisan('db:import-dump', ['--path' => 'missing.sql'])
            ->expectsOutputToContain('skipping the dump import')
            ->assertSuccessful();
    }

    public function test_it_imports_over_an_empty_migrated_schema(): void
    {
        $this->artisan('db:import-dump', ['--path' => 'missing.sql'])
            ->expectsOutputToContain('Dump file not found')
            ->assertFailed();
    }
}
