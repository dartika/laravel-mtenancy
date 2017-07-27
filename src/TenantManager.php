<?php

namespace Dartika\MultiTenancy;

use Dartika\MultiTenancy\Models\Tenant;
use Illuminate\Http\Request;

class TenantManager
{
    protected $app;
    protected $activeTenant;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function boot()
    {
        $tenant = null;

        if ($this->app->runningInConsole()) {
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
            }
        } else {
            $subdomain = $this->getTenantSubdomain($this->app->request);

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

    protected function getTenantSubdomain(Request $request)
    {
        $subdomain = explode('.', $_SERVER['HTTP_HOST'])[0];

        if ($broadcastChannel = $request->get('channel_name')) {  // broadcasting
            preg_match('/^private-private-(.*?)\./', $broadcastChannel, $match);

            if (isset($match[1])) {
                $subdomain = $match[1];
            }
        }

        return $subdomain;
    }

    public function getActive()
    {
        return $this->activeTenant;
    }

    public function setActive(Tenant $tenant)
    {
        $this->activeTenant = $tenant;
    }
}
