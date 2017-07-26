<?php

namespace Dartika\MultiTenancy\Console\Commands;

use Dartika\MultiTenancy\Models\Tenant;
use Illuminate\Console\Command;

abstract class TenantBaseCommand extends Command
{
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach ($this->parseTenants() as $tenant) {
            $tenant->setActive();
            
            $this->handleTenantCommand($tenant);
        }
    }

    protected function parseTenants()
    {
        if ($this->argument('tenant')) {
            $tenant = Tenant::where('name', $this->argument('tenant'))->get();

            if (!$tenant->count()) {
                $this->error('Error: Tenant not found');
                return;
            } else {
                return $tenant;
            }
        } else {
            return Tenant::all();
        }
    }

    abstract protected function handleTenantCommand(Tenant $tenant);
}
