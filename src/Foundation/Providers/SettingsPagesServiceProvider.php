<?php

namespace Laraish\Dough\Foundation\Providers;

use Laraish\Dough\Support\ServiceProvider;

class SettingsPagesServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function boot()
    {
        // Register the settingsPages
        $settingsPages = (array) $this->app->config('settings_pages');

        if (!empty($settingsPages)) {
            foreach ($settingsPages as $settingsPageClassName) {
                $this->registerSettingsPage($settingsPageClassName);
            }
        }
    }

    /**
     * Register the settings page.
     *
     * @param string $settingsPageClassName
     */
    protected function registerSettingsPage($settingsPageClassName)
    {
        add_action('wp_loaded', function () use ($settingsPageClassName) {
            if (is_admin()) {
                $this->app->call($settingsPageClassName . '@handle');
            }
        });
    }
}
