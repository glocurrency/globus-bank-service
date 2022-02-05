<?php

namespace GloCurrency\GlobusBank;

use Illuminate\Support\ServiceProvider;
use GloCurrency\GlobusBank\Config;
use BrokeYourBike\GlobusBank\Interfaces\ConfigInterface;

class GlobusBankServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerMigrations();
        $this->registerCommands();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->configure();
        $this->bindConfig();
    }

    /**
     * Setup the configuration for GlobusBank.
     *
     * @return void
     */
    protected function configure()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/globus_bank.php', 'services.globus_bank'
        );
    }

    /**
     * Bind the GlobusBank logger interface to the GlobusBank logger.
     *
     * @return void
     */
    protected function bindConfig()
    {
        $this->app->bind(ConfigInterface::class, Config::class);
    }

    /**
     * Register the package migrations.
     *
     * @return void
     */
    protected function registerMigrations()
    {
        if (GlobusBank::$runsMigrations && $this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                // TODO: is this a valid path?
                __DIR__.'/../config/globus_bank.php' => $this->app->configPath('globus_bank.php'),
            ], 'globus-bank-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => $this->app->databasePath('migrations'),
            ], 'globus-bank-migrations');
        }
    }

    /**
     * Register the package's commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {}
    }
}
