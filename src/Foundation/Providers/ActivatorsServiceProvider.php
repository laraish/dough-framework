<?php

namespace Laraish\Dough\Foundation\Providers;

use Laraish\Dough\Support\ServiceProvider;

class ActivatorsServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function boot()
    {
        // Register the activators
        $activators = (array)$this->app->config('activators');
        if ( ! empty($activators)) {
            foreach ($activators as $activatorClassName) {
                $this->registerActivator($activatorClassName);
            }
        }
    }

    /**
     * Register the Activator.
     *
     * @param string $activatorClassName
     */
    protected function registerActivator($activatorClassName)
    {
        if ( ! class_exists($activatorClassName)) {
            return;
        }

        register_activation_hook($this->app->mainPluginFilePath(), function () use ($activatorClassName) {
            $this->app->call([$activatorClassName, 'handle']);
        });
    }
}