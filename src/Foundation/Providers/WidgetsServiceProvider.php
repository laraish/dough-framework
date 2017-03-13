<?php

namespace Laraish\Dough\Foundation\Providers;

use Laraish\Dough\Support\ServiceProvider;

class WidgetsServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function boot()
    {
        // Register the widgets
        $widgets = (array)$this->app->config('widgets');

        if ( ! empty($widgets)) {
            foreach ($widgets as $widgetClassName) {
                $this->registerWidget($widgetClassName);
            }
        }
    }

    /**
     * Register the widget.
     *
     * @param string $widgetClassName
     */
    protected function registerWidget($widgetClassName)
    {
        add_action('widgets_init', function () use ($widgetClassName) {
            register_widget($widgetClassName);
        });
    }
}