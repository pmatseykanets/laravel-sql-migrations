<?php

namespace SqlMigrations;

use Illuminate\Support\ServiceProvider;
use SqlMigrations\Console\MigrateMakeCommand;

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
        $this->app->singleton('command.migrate.make', function ($app) {
            return new MigrateMakeCommand(
                $app['migration.creator'],
                $app['composer']
            );
        });
    }
}
