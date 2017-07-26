<?php

namespace Dartika\MultiTenancy\Console\Commands;

use Dartika\MultiTenancy\Models\Tenant;
use Illuminate\Console\Command;

class TenantListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List active tenants';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tenants = Tenant::all();

        $arrayTenants = [];
        foreach ($tenants as $tenant) {
            $arrayTenants[] = [$tenant->name, $tenant->subdomain, $tenant->dbdatabase];
        }

        $this->table(['Name', 'Subdomain', 'Database'], $arrayTenants);
    }
}
