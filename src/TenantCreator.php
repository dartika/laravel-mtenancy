<?php

namespace Dartika\MultiTenancy;

use Dartika\MultiTenancy\Models\Tenant;
use Dartika\MultiTenancy\Exceptions\TenantCreationException;

class TenantCreator
{
    public static function create($name)
    {
        $dbhost = env('DB_HOST', 'localhost'); // TODO: Remove env call

        $tenant = Tenant::create([
            'name'       => $name,
            'subdomain'  => $name,
            'dbhost'     => $dbhost,
            'dbdatabase' => 'wopr_' . $name,
            'dbusername' => 'wopr_' . $name,
            'dbpassword' => str_random(40)
        ]);

        if (!$tenant) {
            throw new TenantCreationException("Tenant's creation fail to store on database");
        }

        self::createDatabase($tenant);
        self::createFolderStructure($tenant);
        
        $tenant->setActive();
        $tenant->migrate([ '--force' => true ]);

        return $tenant;
    }

    protected static function createDatabase(Tenant $tenant)
    {
        if (!self::databaseExists($tenant->dbdatabase)) {
            \DB::statement("CREATE DATABASE " . $tenant->dbdatabase);
            \DB::statement("CREATE USER '" . $tenant->dbusername . "'@'%' IDENTIFIED BY '" . $tenant->dbpassword . "'");
            \DB::statement("GRANT ALL PRIVILEGES ON " . $tenant->dbdatabase . ".* TO '" . $tenant->dbusername . "'@'%'");
            \DB::statement("FLUSH PRIVILEGES");
        } else {
            throw new TenantCreationException("Tenant's database already exists");
        }
    }

    protected static function databaseExists($database)
    {
        $db = \DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME =  ?", [ $database ]);
        return !empty($db);
    }

    protected static function createFolderStructure(Tenant $tenant)
    {
        if (!\File::exists($tenant->path())) {
            umask(0);
            \File::makeDirectory($tenant->path(), 0775, true, true);
            \File::makeDirectory($tenant->path('/public'), 0777, true, true);
            \File::makeDirectory($tenant->path('/logs'), 0777, true, true);
        } else {
            throw new TenantCreationException("Tenant's folder already exists");
        }
    }
}
