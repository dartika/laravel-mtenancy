<?php

namespace Dartika\MultiTenancy\Console\Commands;

use Dartika\MultiTenancy\Models\Tenant;
use Illuminate\Console\Command;

class TenantDeleteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:delete {tenant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete tenant';

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
            if ($this->confirm('Delete "' . $tenant->name . '"" Tenant? (this will erase all data, backups, files, ...)')) {
                $tenant->erase();
                
                $this->info('Tenant deleted!');
            } else {
                $this->comment('Tenant not deleted');
            }
        } else {
            $this->error('Error: Tenant not found');
        }
    }
}
