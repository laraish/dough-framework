<?php

namespace Laraish\Dough\Support;

class ServiceProvider
{
    /**
     * The application instance.
     *
     * @var \Laraish\Dough\Application
     */
    protected $app;

    /**
     * Create a new service provider instance.
     *
     * @param  \Laraish\Dough\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }
}
