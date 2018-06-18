<?php

namespace SqlMigrations\Console;

use Illuminate\Database\Console\Migrations\MigrateMakeCommand as BaseCommand;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;

class MigrateMakeCommand extends BaseCommand
{
    protected $stub = <<<'STUB'
<?php

use SqlMigrations\SqlMigration;

class DummyClass extends SqlMigration
{
}

STUB;

    public function __construct(MigrationCreator $creator, Composer $composer)
    {
        $this->signature .= '{--sql : Create a plain SQL migration}';

        parent::__construct($creator, $composer);
    }

    /**
     * Write the migration file to disk.
     *
     * @param  string $name
     * @param  string $table
     * @param  bool $create
     */
    protected function writeMigration($name, $table, $create)
    {
        if (! $this->option('sql')) {
            return parent::writeMigration($name, $table, $create);
        }

        $path = $this->creator->create($name, $this->getMigrationPath(), $table, $create);

        $this->replaceMigrationContent($path, $name);
        $this->createSqlMigrationStubs($path);

        $file = pathinfo($path, PATHINFO_FILENAME);

        $this->line("<info>Created Migration:</info> {$file}");
    }

    protected function replaceMigrationContent($path, $name)
    {
        $className = Str::studly($name);

        $stub = str_replace('DummyClass', $className, $this->stub);

        file_put_contents($path, $stub);
    }

    protected function createSqlMigrationStubs($path)
    {
        $basePath = preg_replace('/\.php$/', '', $path);

        file_put_contents("$basePath.up.sql", "-- Run the migrations\n");
        file_put_contents("$basePath.down.sql", "-- Reverse the migrations\n");
    }
}
