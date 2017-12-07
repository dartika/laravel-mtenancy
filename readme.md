# Laravel Multi Tenancy

Multi tenancy package for laravel applications (multi-database)

## Instalation

### Composer

Install this package with composer:

```
composer require dartika/laravel-mtenancy
```
### Service Provider

Add this provider to your config/app.php:

```
'providers' => [
    // ...
    Dartika\MultiTenancy\TenantServiceProvider::class,
]
```

### Database and Migrations

### Public Assets

To bind the public assets url to public tenant's files, use this in your nginx vhost:

```
server_name ~^(?<subdomain>\w+)\.yourdomain.com;

location /files/ {
    rewrite ^(.*?)$ /../storage/app/tenants/$subdomain/public/$1 break;
}
```

----------

#### Available commands

```sh
$ php artisan tenant:list
# List all tenants
```

```sh
$ php artisan tenant:create tenant_name
# Create tenant
```

```sh
$ php artisan tenant:delete tenant_name
# Delete tenant
```

```sh
$ php artisan tenant:migrate [tenant_name] [--secure]
# Migrate tenant/s (and backup tenant if --secure flag is enabled and there is a migrations)
```

```sh
$ php artisan tenant:backup [tenant_name]
# Backup tenant (to tenant/backups path)
```

```sh
$ php artisan tenant:backup-clean [tenant_name] [--days=15]
# Clean tenant backups keeping the latest "--days" backups 
```

```sh
$ php artisan tenant:tinker [tenant_name]
# Open tinker with tenant activated. (NOTE: Tinker must be available in project)
```
