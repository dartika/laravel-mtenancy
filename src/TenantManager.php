<?php

namespace Dartika\MultiTenancy;

use Dartika\MultiTenancy\Models\Tenant;

class TenantManager
{
    protected $app;
    protected $activeTenant;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function setActive(Tenant $tenant)
    {
        $this->activeTenant = $tenant;
        event(new \Dartika\MultiTenancy\Events\TenantActivated($tenant));
    }

    public function tenant()
    {
        return $this->activeTenant;
    }
}
