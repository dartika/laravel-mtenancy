<?php

namespace Dartika\MultiTenancy\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class TenantActivated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tenant;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($tenant)
    {
        $this->tenant = $tenant;
    }
}
