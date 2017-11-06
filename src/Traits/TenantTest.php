<?php

namespace Dartika\Traits;

trait TenantTest {
    /**
     * Override artisan command to use tenant migration path
     *
     * @param  string  $command
     * @param  array  $parameters
     * @return int
     */
    public function artisan($command, $parameters = [])
    {
        if (str_before($command, ':') === 'migrate' && !isset($parameters['--path'])) {
            $parameters['--path'] = config('laravel-mtenancy.migrations_path');
        }

        return parent::artisan($command, $parameters);
    }
}
