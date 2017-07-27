# Laravel Multi Tenancy

Multi tenancy package for laravel applications (multi-database)

### Available commands

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
$ php artisan tenant:migrate [tenant_name]
# Migrate tenant/s
```