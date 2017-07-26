<?php

namespace Dartika\MultiTenancy\Console\Commands;

use Dartika\MultiTenancy\Models\Tenant;

class TenantMigrateCommand extends TenantBaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:migrate {tenant?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate a/all tenant/s';

    public function handleTenantCommand(Tenant $tenant)
    {
        $tenant->migrate();
        $this->info('"' . $tenant->name . '" Migrated!');
    }
}
