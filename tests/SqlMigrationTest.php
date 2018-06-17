<?php

namespace Tests;

use CreateUsersTable;
use Illuminate\Support\Facades\DB;

class SqlMigrationTest extends TestCase
{
    protected $basePath = 'tests/database/migrations/2018_06_15_000000_create_users_table';

    public function testUp()
    {
        $migration = new CreateUsersTable();

        DB::shouldReceive('connection')
            ->andReturnSelf()
            ->shouldReceive('unprepared')
            ->with(file_get_contents($this->basePath.'.up.sql'));

        $migration->up();
    }

    public function testDown()
    {
        $migration = new CreateUsersTable();

        DB::shouldReceive('connection')
            ->andReturnSelf()
            ->shouldReceive('unprepared')
            ->with(file_get_contents($this->basePath.'.down.sql'));

        $migration->down();
    }
}
