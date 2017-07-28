<?php

namespace Dartika\MultiTenancy\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class TenantCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tenant;
    public $command;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($tenant, $command)
    {
        //
        $this->tenant = $tenant;
        $this->command = $command;
    }
}
