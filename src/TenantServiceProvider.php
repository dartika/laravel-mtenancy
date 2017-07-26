<?php

namespace Dartika\MultiTenancy;

use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Input\ArgvInput;

use Dartika\MultiTenancy\Models\Tenant;

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
        $this->registerConsoleCommands();

        $tenant = null;

        if ($this->app->runningInConsole()) {
            $tenantname = (new ArgvInput())->getParameterOption('--tenant');
            if ($tenantname) {
                $tenant = Tenant::whereName($tenantname)->first();
            } else {
                if ($this->app->environment('testing')) {
                    // temporal tenant for testing
                    $tenant = new Tenant([
                        'name' => 'testing',
                        'subdomain' => 'testing',
                        'dbhost' => config('database.connections.tests.host'),
                        'dbdatabase' => config('database.connections.tests.database'),
                        'dbusername' => config('database.connections.tests.username'),
                        'dbpassword' => config('database.connections.tests.password'),
                    ]);

                    $tenant->setActive();
                    $tenant->migrate();
                } else {
                    $tenant = null;
                }
            }
        } else {
            $subdomain = explode('.', $_SERVER['HTTP_HOST'])[0];

            if (\Request::get('channel_name')) {  // broadcasting
                preg_match('/^private-private-(.*?)\./', \Request::get('channel_name'), $match);

                if (isset($match[1])) {
                    $subdomain = $match[1];
                }
            }

            if (!$this->app->environment('testing')) {  // testing
                $tenant = Tenant::whereSubdomain($subdomain)->first();
            }
        }

        if ($tenant) {
            $tenant->setActive();
        } else {
            if (!$this->app->runningInConsole() && !$this->app->environment('testing')) {
                abort(404);
            }
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    private function registerConsoleCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }
}
