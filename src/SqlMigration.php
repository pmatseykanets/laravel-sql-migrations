<?php

namespace SqlMigrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

abstract class SqlMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->apply('up');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->apply('down');
    }

    /**
     * Apply the migration.
     *
     * @param $path
     * @param mixed $direction
     */
    public function apply($direction)
    {
        if (file_exists($path = $this->migrationFile($direction))) {
            DB::connection($this->getConnection())->unprepared(file_get_contents($path));
        }
    }

    public function migrationFile($direction)
    {
        return preg_replace(
            '/\.php$/',
            ".$direction.sql",
            (new \ReflectionObject($this))->getFileName()
        );
    }
}
