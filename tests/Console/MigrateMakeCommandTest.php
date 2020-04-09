<?php

namespace Tests\Console;

use Illuminate\Container\Container;
use Mockery;
use Tests\TestCase;
use Illuminate\Support\Str;
use SqlMigrations\Console\MigrateMakeCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class MigrateMakeCommandTest extends TestCase
{
    protected $basePath = 'tests/database/migrations/2018_06_16_000000_create_foo_table';

    public function tearDown(): void
    {
        array_map('unlink', glob($this->basePath.'.*'));
    }

    public function testSqlOption()
    {
        $command = new MigrateMakeCommand(
            $creator = Mockery::mock('Illuminate\Database\Migrations\MigrationCreator'),
            $composer = Mockery::mock('Illuminate\Support\Composer')
        );
        $container = new class extends Container {
            public function databasePath()
            {
                return 'tests/database';
            }
        };
        $command->setLaravel($container);

        $phpMigrationPath = $this->basePath.'.php';
        file_put_contents($phpMigrationPath, "<?php\n");

        $composer->shouldReceive('dumpAutoloads')->once();
        $creator->shouldReceive('create')->once()
            ->with('create_foo_table', 'tests/database/migrations', 'foo', true)
            ->andReturn($phpMigrationPath);

        $this->runCommand($command, ['name' => 'create_foo_table', '--sql' => null]);

        $this->assertFileExists($phpMigrationPath);
        $this->assertTrue(Str::contains(file_get_contents($this->basePath.'.php'), 'class CreateFooTable extends SqlMigration'));
        $this->assertFileExists($this->basePath.'.up.sql');
        $this->assertEquals(file_get_contents($this->basePath.'.up.sql'), "-- Run the migrations\n");
        $this->assertFileExists($this->basePath.'.down.sql');
        $this->assertEquals(file_get_contents($this->basePath.'.down.sql'), "-- Reverse the migrations\n");
    }

    protected function runCommand($command, $input = [])
    {
        return $command->run(new ArrayInput($input), new NullOutput());
    }
}
