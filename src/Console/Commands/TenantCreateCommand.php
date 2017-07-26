<?php

namespace Dartika\MultiTenancy\Console\Commands;

use Dartika\MultiTenancy\Models\Tenant;
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

            $tenant = Tenant::generate($tenantName);

            if ($tenant) {
                // prompts
                $departmentName = $this->ask('Enter the default Department\'s name.', 'Administrators');
                $adminEmail = $this->ask('Enter the default admin user email.');
                $adminPassword = $this->secret('Enter the default admin user password.', 'wopr');

                $tenant->setActive();
                $tenant->migrate();

                // specific code, delete or delegate it
                $user = \App\Models\User::create([
                    'email' => $adminEmail,
                    'password' => bcrypt($adminPassword),
                    'department_id' => \App\Models\Department::create(['name' => $departmentName])->id
                ]);

                $user->attachRole(\App\Models\Role::where('name', 'admin')->first());
            } else {
                $this->error('Error: Unknow error at Tenant creation');
            }

            $this->info('Tenant created!');
        } else {
            $this->error('Error: Tenant\'s name already in use');
        }
    }
}
