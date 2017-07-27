<?php

namespace Dartika\MultiTenancy\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $table = 'tenants';
    
    protected $fillable = [
        'name', 'subdomain', 'dbhost', 'dbdatabase', 'dbusername', 'dbpassword'
    ];

    public function isActive()
    {
        return \DB::getDefaultConnection() === 'tenant' && config('database.connections.tenant.database') === $this->dbdatabase;
    }

    public function setActive()
    {
        $this->setDatabase();
        $this->setLogFile();

        \App::make('tenant')->setActive($this);
    }

    protected function setDatabase()
    {
        config([
            'database.connections.tenant.username' => $this->dbusername,
            'database.connections.tenant.password' => $this->dbpassword,
            'database.connections.tenant.database' => $this->dbdatabase,

            'filesystems.disks.tenant.root' => $this->path('/public/files'),

            'constants.broadcast_url' => $this->name . '.wopr.broadcastchannel.',
        ]);

        \DB::setDefaultConnection('tenant');
    }

    protected function setLogFile()
    {
        if (!app()->runningInConsole()) {
            \Log::useDailyFiles($this->path('/logs/log_' . $this->name . '.log'));
        }
    }

    public function path($path = '')
    {
        return storage_path('app/tenants/' . $this->name) . $path;
    }

    public function erase()
    {
        \DB::statement("DROP DATABASE IF EXISTS " . $this->dbdatabase);
        \DB::statement("GRANT USAGE ON *.* TO '" . $this->dbusername . "'@'%' IDENTIFIED BY 'password'"); // if don't exists, create to drop
        \DB::statement("DROP USER '" . $this->dbusername . "'@'%'");

        \File::deleteDirectory($this->path());

        $this->delete();
    }

    public function backup()
    {
        \File::makeDirectory($this->path('/backups'), 0775, true, true);

        $backupFile = $this->path('/backups/bk_' . $this->name . '_' . \Carbon\Carbon::now() . '.sql');
        $command = sprintf('mysqldump -u %s -p%s %s -h %s  --skip-comments > \'%s\' 2>/dev/null', $this->dbusername, $this->dbpassword, $this->dbdatabase, env('DB_HOST', 'localhost'), $backupFile);
        
        exec($command);

        return file_exists($backupFile);
    }

    public function migrate($seed = false)
    {
        return $this->doMigration($seed, false);
    }

    public function migrateAsDemo()
    {
        return $this->doMigration(true, true);
    }

    private function doMigration($seed, $refresh)
    {
        $migrationCommand = $refresh ? 'migrate:refresh' : 'migrate';

        if ($this->isActive()) {
            \Artisan::call($migrationCommand, array('--force' => true, '--path' => 'database/migrations/tenants', '--seed' => $seed));
            return true;
        } else {
            throw new \Exception("Error: Tenant is not active", 1);
        }
    }

    public function rollbackMigration($step = 1)
    {
        if ($this->isActive()) {
            \Artisan::call('migrate:rollback', array('--force' => true, '--step' => $step));
            return true;
        } else {
            throw new \Exception("Error: Tenant is not active", 1);
        }
    }
}
