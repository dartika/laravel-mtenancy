<?php

namespace Dartika\MultiTenancy\Models;

use Illuminate\Database\Eloquent\Model;
use Dartika\MultiTenancy\Exceptions\TenantInactiveException;

use Artisan;
use Carbon\Carbon;
use DB;
use File;
use Log;

class Tenant extends Model
{
    protected $table = 'tenants';
    
    protected $fillable = [
        'name', 'subdomain', 'dbhost', 'dbdatabase', 'dbusername', 'dbpassword'
    ];

    public function isActive()
    {
        return $this === app()->make('tenantManager')->tenant();
    }

    public function setActive()
    {
        $this->setDatabase();
        $this->setPublicPath();
        $this->setLogFile();

        app()->make('tenantManager')->setActive($this);
    }

    protected function setDatabase()
    {
        config([
            'database.connections.tenant.username' => $this->dbusername,
            'database.connections.tenant.password' => $this->dbpassword,
            'database.connections.tenant.database' => $this->dbdatabase
        ]);

        DB::setDefaultConnection('tenant');
    }

    protected function setPublicPath()
    {
        config(['filesystems.disks.local.root' => $this->path('/public/files')]);
    }

    protected function setLogFile()
    {
        if (!app()->runningInConsole()) {
            Log::useDailyFiles($this->path('/logs/log_' . $this->name . '.log'));
        }
    }

    public function path($path = '')
    {
        return storage_path('app/tenants/' . $this->name) . $path;
    }

    public function erase()
    {
        DB::statement("DROP DATABASE IF EXISTS " . $this->dbdatabase);
        DB::statement("GRANT USAGE ON *.* TO '" . $this->dbusername . "'@'%' IDENTIFIED BY 'password'"); // if don't exists, create to drop
        DB::statement("DROP USER '" . $this->dbusername . "'@'%'");

        File::deleteDirectory($this->path());

        $this->delete();
    }

    public function backup()
    {
        File::makeDirectory($this->path('/backups'), 0775, true, true);

        $backupFile = $this->path('/backups/bk_' . $this->name . '_' . Carbon::now() . '.sql');
        $command = sprintf('mysqldump -u %s -p%s %s -h %s  --skip-comments > \'%s\' 2>/dev/null', $this->dbusername, $this->dbpassword, $this->dbdatabase, env('DB_HOST', 'localhost'), $backupFile);
        
        exec($command);

        return file_exists($backupFile);
    }

    public function migrate($options = [])
    {
        $this->checkTenantIsActive();

        $options['--path'] = config('laravel-mtenancy.migrations_path');
        
        return Artisan::call('migrate', $options);
    }

    public function migrateFresh($options = [])
    {
        $this->checkTenantIsActive();

        $options['--path'] = config('laravel-mtenancy.migrations_path');
        
        return Artisan::call('migrate:refresh', $options);
    }

    public function rollbackMigration($options = [])
    {
        $this->checkTenantIsActive();

        $options['--path'] = config('laravel-mtenancy.migrations_path');

        return Artisan::call('migrate:rollback', $options);
    }

    protected function checkTenantIsActive()
    {
        if (!$this->isActive()) {
            throw new TenantInactiveException("Tenant '{$this->name}' is not active");
        }
    }
}
