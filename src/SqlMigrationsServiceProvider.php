<?php

namespace SqlMigrations;

use Illuminate\Support\ServiceProvider;
use SqlMigrations\Console\MigrateMakeCommand;
use SqlMigrations\Console\ModelMakeCommand;

class SqlMigrationsServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->extendMigrateMakeCommand();
    }

    /**
     * Extend MigrateMake command.
     *
     * @return void
     */
    protected function extendMigrateMakeCommand()
    {
        $migrateMakeAbstract = 'Illuminate\Database\Console\Migrations\MigrateMakeCommand';
        $modelMakeAbstract = 'Illuminate\Foundation\Console\ModelMakeCommand';

        $appMajor = explode('.', $this->app->version())[0];
        if ($appMajor < 9) {
            $migrateMakeAbstract = 'command.migrate.make';
            $modelMakeAbstract = 'command.model.make';
        }

        $this->app->extend($migrateMakeAbstract, function ($command, $app) {
            return new MigrateMakeCommand(
                $app['migration.creator'],
                $app['composer']
            );
        });

        $this->app->extend($modelMakeAbstract, function ($command, $app) {
            return new ModelMakeCommand(
                $app['files']
            );
        });
    }
}
