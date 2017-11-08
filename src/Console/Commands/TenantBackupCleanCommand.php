<?php

namespace Dartika\MultiTenancy\Console\Commands;

use Dartika\MultiTenancy\Models\Tenant;

class TenantBackupCleanCommand extends TenantBaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:backup-clean {tenant?}
                {--days= : Number of days to keep backups (15 days by default).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean Tenant\'s backup';

    public function handleTenantCommand(Tenant $tenant)
    {
        $tenant->cleanBackups($this->option('days') ?? 15);
        $this->info('"' . $tenant->name . '" Backup\'s cleaned!');
    }
}
