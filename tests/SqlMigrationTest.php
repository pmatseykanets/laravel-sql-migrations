<?php

namespace Tests;

use CreateUsersTable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SqlMigrationTest extends TestCase
{
    public function testUp()
    {
        $migration = new CreateUsersTable();

        DB::shouldReceive('connection')
            ->andReturnSelf()
            ->shouldReceive('unprepared')
            ->withArgs(function($sql) {
                return Str::contains($sql, [
                    'CREATE',
                    'users',
                    'id',
                    'name',
                    'email',
                    'password',
                    'remember_token',
                    'created_at',
                    'updated_at',
                    'users_email_idx'
                ]);
            });
        $migration->up();
    }

    public function testDown()
    {
        $migration = new CreateUsersTable();

        DB::shouldReceive('connection')
            ->andReturnSelf()
            ->shouldReceive('unprepared')
            ->withArgs(function($sql) {
                return Str::contains($sql, [
                    'DROP',
                    'users'
                ]);
            });

        $migration->down();
    }
}
