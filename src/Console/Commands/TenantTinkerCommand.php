<?php

namespace Dartika\MultiTenancy\Console\Commands;

use Dartika\MultiTenancy\Models\Tenant;
use Illuminate\Console\Command;

class TenantTinkerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:tinker {tenant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tinker on tenant';

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
        $tenantName = $this->argument('tenant');

        $tenant = Tenant::where('name', $tenantName)->orWhere('subdomain', $tenantName)->first();

        if ($tenant) {
            $tenant->setActive();
            \Artisan::call('tinker');
        } else {
            $this->error('Error: Tenant not found');
        }
    }
}
