<?php

namespace Laraish\Dough\Foundation\Providers;

use Laraish\Dough\Support\ServiceProvider;

class DeactivatorsServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function boot()
    {
        // Register the deactivators
        $deactivators = (array)$this->app->config('deactivators');
        if ( ! empty($deactivators)) {
            foreach ($deactivators as $deactivatorClassName) {
                $this->registerDeactivator($deactivatorClassName);
            }
        }
    }

    /**
     * Register the deactivator.
     *
     * @param string $deactivatorClassName
     */
    protected function registerDeactivator($deactivatorClassName)
    {
        if ( ! class_exists($deactivatorClassName)) {
            return;
        }

        register_deactivation_hook($this->app->mainPluginFilePath(), function () use ($deactivatorClassName) {
            $this->app->call([$deactivatorClassName, 'handle']);
        });
    }
}