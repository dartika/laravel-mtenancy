<?php 

namespace Dartika\MultiTenancy\Facades;

use Illuminate\Support\Facades\Facade;

class TenantFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'tenantManager';
    }
}
