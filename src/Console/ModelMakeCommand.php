<?php

namespace SqlMigrations\Console;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Console\Command;

class ModelMakeCommand extends Command
{
    /**
     * Create a migration file for the model.
     *
     * @return void
     */
    protected function createMigration()
    {
        $table = Str::plural(Str::snake(class_basename($this->argument('name'))));

        $with = [
            'name' => "create_{$table}_table",
            '--create' => $table,
        ];

        if ($this->option('sql')) {
            $with['--sql'] = true;
        }

        $this->call('make:migration', $with);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        $options = parent::getOptions();
        $options[] = ['sql', 'M', InputOption::VALUE_NONE, 'Create a plain SQL migration.'];

        return $options;
    }
}
