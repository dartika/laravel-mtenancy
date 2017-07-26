<?php

namespace Dartika\MultiTenancy\Console\Commands;

use Dartika\MultiTenancy\Models\Tenant;

class TenantRollbackCommand extends TenantBaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:rollback {tenant?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback a/all tenant/s';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->argument('tenant') || $this->confirm('Rolback selected Tenants?')) {
            parent::handle();
        } else {
            $this->comment('Nothing to rollback');
        }
    }

    public function handleTenantCommand(Tenant $tenant)
    {
        $tenant->rollbackMigration();
        $this->info('"' . $tenant->name . '" Rollback!');
    }
}
