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
        \Dartika\MultiTenancy\Console\Commands\TenantBackupCleanCommand::class,
        \Dartika\MultiTenancy\Console\Commands\TenantTinkerCommand::class,
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/laravel-mtenancy.php' => config_path('laravel-mtenancy.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../database/migrations/create_tenants_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_tenants_table.php'),
        ], 'migrations');

        $this->registerConsoleCommands();

        TenantBootstrap::boot($this->app);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ .' /../config/laravel-mtenancy.php', 'laravel-mtenancy');

        $this->app->singleton('tenantManager', function ($app) {
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
