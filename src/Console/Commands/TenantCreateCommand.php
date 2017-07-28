<?php

namespace Dartika\MultiTenancy\Console\Commands;

use Dartika\MultiTenancy\Models\Tenant;
use Dartika\MultiTenancy\TenantCreator;
use Illuminate\Console\Command;

class TenantCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create {tenant?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new tenant';

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

        if (!$tenantName) {
            $tenantName = $this->ask('Please type the tenant\'s name');
        }

        $tenantName = preg_replace("/[^_a-zA-Z0-9]+/", "", $tenantName); // clean

        $existingTenant = Tenant::where('name', $tenantName)->orWhere('subdomain', $tenantName)->first();

        if (!$existingTenant || $this->confirm('Tenant\'s name already in use, overwrite? (this will erase all data, backups, files, ...)')) {
            if ($existingTenant) {
                $existingTenant->erase();
            }

            $tenant = TenantCreator::create($tenantName);

            if (!$tenant) {
                return $this->error('Error: Unknow error at Tenant creation');
            }

            event(new \Dartika\MultiTenancy\Events\TenantCreated($tenant, $this));

            return $this->info('Tenant created!');
        } else {
            return $this->error('Error: Tenant\'s name already in use');
        }
    }
}
