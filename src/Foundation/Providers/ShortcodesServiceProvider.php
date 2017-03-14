<?php

namespace Laraish\Dough\Foundation\Providers;

use Laraish\Dough\Support\ServiceProvider;

class ShortcodesServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function boot()
    {
        // Register the shortcodes
        $shortcodes = (array)$this->app->config('shortcodes');

        if ( ! empty($shortcodes)) {
            foreach ($shortcodes as $shortcodeClassName) {
                $this->registerShortcode($shortcodeClassName);
            }
        }
    }

    /**
     * Register the shortcode.
     *
     * @param string $shortcodeClassName
     */
    protected function registerShortcode($shortcodeClassName)
    {
        if ( ! class_exists($shortcodeClassName)) {
            return;
        }

        add_action('init', function () use ($shortcodeClassName) {
            add_shortcode($shortcodeClassName::TAG, function () use ($shortcodeClassName) {
                return $this->app->call($shortcodeClassName . '@handle', func_get_args());
            });
        });
    }
}