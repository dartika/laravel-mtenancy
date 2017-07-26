<?php

namespace Dartika\MultiTenancy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use SoftDeletes;
    
    protected $table = 'tenants';
    
    protected $fillable = [
        'name', 'subdomain', 'dbhost', 'dbdatabase', 'dbusername', 'dbpassword'
    ];

    public function path($path = '')
    {
        return base_path('/tenants/' . $this->name) . $path;
    }

    public function setActive()
    {
        config([
            'database.connections.tenant.username' => $this->dbusername,
            'database.connections.tenant.password' => $this->dbpassword,
            'database.connections.tenant.database' => $this->dbdatabase,

            'filesystems.disks.tenant.root' => $this->path('/public/files'),

            'constants.broadcast_url' => $this->name . '.wopr.broadcastchannel.',
        ]);
        
        \DB::setDefaultConnection('tenant');
        
        if (!app()->runningInConsole()) {
            \Log::useDailyFiles($this->path('/logs/log_' . $this->name . '.log'));
        }
    }

    public function isActive()
    {
        return \DB::getDefaultConnection() === 'tenant' && config('database.connections.tenant.database') === $this->dbdatabase;
    }

    public function backup()
    {
        \File::makeDirectory($this->path('/backups'), 0775, true, true);

        $backupFile = $this->path('/backups/bk_' . $this->name . '_' . \Carbon\Carbon::now() . '.sql');
        $command = sprintf('mysqldump -u %s -p%s %s -h %s  --skip-comments > \'%s\' 2>/dev/null', $this->dbusername, $this->dbpassword, $this->dbdatabase, env('DB_HOST', 'localhost'), $backupFile);
        
        exec($command);

        return file_exists($backupFile);
    }

    public static function generate($name)
    {
        $dbhost = env('DB_HOST', 'localhost');

        $tenant = self::create([
            'name'       => $name,
            'subdomain'  => $name,
            'dbhost'     => $dbhost,
            'dbdatabase' => 'wopr_' . $name,
            'dbusername' => 'wopr_' . $name,
            'dbpassword' => str_random(40)
        ]);

        if ($tenant) {
            $tenant->createDatabase();
            $tenant->createFolderStructure();
        } else {
            return false;
        }

        return $tenant;
    }

    public function createDatabase()
    {
        if (!$this->databaseExists()) {
            \DB::statement("CREATE DATABASE " . $this->dbdatabase);
            \DB::statement("CREATE USER '" . $this->dbusername . "'@'%' IDENTIFIED BY '" . $this->dbpassword . "'");
            \DB::statement("GRANT ALL PRIVILEGES ON " . $this->dbdatabase . ".* TO '" . $this->dbusername . "'@'%'");
            \DB::statement("FLUSH PRIVILEGES");
        } else {
            return false; // database exists (exception)
        }
    }

    public function databaseExists()
    {
        $db = \DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME =  ?", [ $this->dbdatabase ]);
        return !empty($db);
    }

    public function createFolderStructure()
    {
        if (!\File::exists($this->path())) {
            umask(0);
            \File::makeDirectory($this->path(), 0775, true, true);
            \File::makeDirectory($this->path() . '/public', 0777, true, true);
            \File::makeDirectory($this->path() . '/logs', 0777, true, true);
        } else {
            return false; // folder exists (exception)
        }
    }

    public function erase()
    {
        \DB::statement("DROP DATABASE IF EXISTS " . $this->dbdatabase);
        \DB::statement("GRANT USAGE ON *.* TO '" . $this->dbusername . "'@'%' IDENTIFIED BY 'password'"); // if don't exists, create to drop
        \DB::statement("DROP USER '" . $this->dbusername . "'@'%'");

        \File::deleteDirectory($this->path());

        $this->forceDelete();
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
            return false; // the tenant is not active, don't migrate (exception)
        }
    }

    public function rollbackMigration($step = 1)
    {
        if ($this->isActive()) {
            \Artisan::call('migrate:rollback', array('--force' => true, '--step' => $step));
            return true;
        } else {
            return false; // the tenant is not active, don't rollback (exception)
        }
    }
}
