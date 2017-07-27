<?php

namespace Dartika\MultiTenancy;

use Dartika\MultiTenancy\TenantManager;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Input\ArgvInput;

class TenantServiceProvider extends ServiceProvider
{
    protected $commands = [
        \Dartika\MultiTenancy\Console\Commands\TenantListCommand::class,
        \Dartika\MultiTenancy\Console\Commands\TenantCreateCommand::class,
        \Dartika\MultiTenancy\Console\Commands\TenantDeleteCommand::class,
        \Dartika\MultiTenancy\Console\Commands\TenantMigrateCommand::class,
        \Dartika\MultiTenancy\Console\Commands\TenantRollbackCommand::class,
        \Dartika\MultiTenancy\Console\Commands\TenantBackupCommand::class,
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['tenant']->boot();

        $this->registerConsoleCommands();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('tenant', function ($app) {
            return new TenantManager($app);
        });
    }

    private function registerConsoleCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }
}
