<?php

namespace Softworx\RocXolid\Providers;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

/**
 * rocXolid configuration service provider.
 *
 * @author softworx <hello@softworx.digital>
 * @package Softworx\RocXolid
 * @version 1.0.0
 */
class ConfigurationServiceProvider extends IlluminateServiceProvider
{
    /**
     * @var array $config_files Configuration files to be published and loaded.
     */
    private $config_files = [
        'rocXolid.main' => '/../../config/main.php',
        'rocXolid.main' => '/../../config/validation.php',
    ];

    /**
     * Register configuration provider for rocXolid core package.
     *
     * @return \Illuminate\Support\ServiceProvider
     */
    public function register(): IlluminateServiceProvider
    {
        $this
            ->configure();

        return $this;
    }

    /**
     * Set configuration files for loading.
     *
     * @return \Illuminate\Support\ServiceProvider
     */
    private function configure(): IlluminateServiceProvider
    {
        foreach ($this->config_files as $key => $path) {
            $path = realpath(__DIR__ . $path);

            if (file_exists($path)) {
                $this->mergeConfigFrom($path, $key);
            }
        }

        return $this;
    }
}
