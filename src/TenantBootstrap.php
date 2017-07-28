<?php

namespace Dartika\MultiTenancy;

use Dartika\MultiTenancy\Models\Tenant;
use Illuminate\Http\Request;

class TenantBootstrap
{
    public static function boot($app)
    {
        $tenant = null;

        if ($app->runningInConsole()) {
            if ($app->environment('testing')) {
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
            $subdomain = self::getTenantSubdomain($app->request);

            if (!$app->environment('testing')) {  // testing
                $tenant = Tenant::whereSubdomain($subdomain)->first();
            }
        }

        if ($tenant) {
            $tenant->setActive();
        } else {
            if (!$app->runningInConsole() && !$app->environment('testing')) {
                abort(404);
            }
        }
    }

    protected static function getTenantSubdomain(Request $request)
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
}
