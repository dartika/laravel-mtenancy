<?php

namespace Dartika\MultiTenancy\Console\Commands;

use Dartika\MultiTenancy\Models\Tenant;

class TenantBackupCommand extends TenantBaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:backup {tenant?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tenant\'s backup';
    
    public function handleTenantCommand(Tenant $tenant)
    {
        $tenant->backup();
        $this->info('"' . $tenant->name . '" Backup!');
    }
}
