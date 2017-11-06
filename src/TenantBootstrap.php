<?php

namespace Dartika\MultiTenancy;

use Dartika\MultiTenancy\Models\Tenant;
use Illuminate\Http\Request;

class TenantBootstrap
{
    public static function boot($app)
    {
        if (!$app->runningInConsole()) {
            $subdomain = self::getTenantSubdomain($app->request);
            $tenant = Tenant::whereSubdomain($subdomain)->first();

            if(!$tenant) {
                abort(404);
            }

            return $tenant->setActive();
        }
    }

    protected static function getTenantSubdomain(Request $request)
    {
        $subdomain = str_before($request->getHost(), '.'); // TODO: Check subdomain vs domain (empty subdomain). domain config var?

        if ($broadcastChannel = $request->get('channel_name')) {  // broadcasting
            preg_match('/^private-private-(.*?)\./', $broadcastChannel, $match);

            if (isset($match[1])) {
                $subdomain = $match[1];
            }
        }

        return $subdomain;
    }
}
