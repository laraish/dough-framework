<?php

namespace Laraish\Dough\Foundation\Providers;

use Laraish\Dough\Support\ServiceProvider;

class UninstallersServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function boot()
    {
        // Register the uninstallers
        $uninstallers = (array) $this->app->config('uninstallers');

        if (!empty($uninstallers)) {
            foreach ($uninstallers as $uninstallerClassName) {
                $this->registerUninstaller($uninstallerClassName);
            }
        }
    }

    /**
     * Register the uninstaller.
     *
     * @param string $uninstallerClassName
     */
    protected function registerUninstaller($uninstallerClassName)
    {
        if (!class_exists($uninstallerClassName)) {
            return;
        }

        $uninstallerClassName::$app = $this->app;

        register_uninstall_hook($this->app->mainPluginFilePath(), [$uninstallerClassName, 'uninstall']);
    }
}
