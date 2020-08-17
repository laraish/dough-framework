<?php

namespace Laraish\Dough;

use Illuminate\Container\Container;
use Laraish\Dough\Support\ServiceProvider;

class Application extends Container
{
    /**
     * The Laravel framework version.
     *
     * @var string
     */
    const VERSION = '1.0.0';

    /**
     * The base path of this plugin.
     * @var
     */
    protected $basePath;

    /**
     * The full path of 'main plugin file' of the plugin.
     * @var string
     */
    protected $mainPluginFilePath;

    /**
     * The configuration of the plugin.
     * @var array
     */

    protected $config = [];

    /**
     * Indicates if the application has been bootstrapped before.
     *
     * @var bool
     */
    protected $hasBeenBootstrapped = false;

    /**
     * Indicates if the application has "booted".
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * All of the registered service providers.
     *
     * @var array
     */
    protected $serviceProviders = [];

    /**
     * Application constructor.
     *
     * @param $basePath
     * @param $mainPluginFilePath
     */
    public function __construct($basePath, $mainPluginFilePath = null)
    {
        $this->setBasePath($basePath);

        $this->setMainPluginFilePath($mainPluginFilePath ?: $this->basePath . DIRECTORY_SEPARATOR . 'plugin.php');

        $this->registerBaseBindings();
    }

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }

    /**
     * Set the base path for the application.
     *
     * @param  string $basePath
     *
     * @return $this
     */
    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '\/');

        return $this;
    }

    public function setMainPluginFilePath($mainPluginFilePath)
    {
        $this->mainPluginFilePath = $mainPluginFilePath;
    }

    /**
     * Register the basic bindings into the container.
     *
     * @return void
     */
    protected function registerBaseBindings()
    {
        static::setInstance($this);

        $this->instance('app', $this);

        $this->instance(Container::class, $this);

        $this->instance(static::class, $this);
    }

    /**
     * Get the specified configuration value.
     *
     * @param  string $key
     * @param  mixed $default
     *
     * @return mixed
     */
    public function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->config;
        }

        return isset($this->config[$key]) ? $this->config[$key] : $default;
    }

    /**
     * Load the building blocks of the plugin.
     */
    public function bootstrap()
    {
        if ($this->hasBeenBootstrapped) {
            return;
        }

        $this->loadConfiguration();

        $this->registerConfiguredProviders();

        $this->hasBeenBootstrapped = true;

        $this->boot();
    }

    /**
     * Load the configuration file.
     */
    protected function loadConfiguration()
    {
        $this->config = require_once $this->basePath . DIRECTORY_SEPARATOR . 'app.config.php';
    }

    /**
     * Register all of the configured providers.
     *
     * @return void
     */
    public function registerConfiguredProviders()
    {
        foreach ((array) $this->config('providers') as $provider) {
            $this->register($provider);
        }
    }

    /**
     * Register a service provider with the application.
     *
     * @param ServiceProvider|string $provider
     * @param bool $force
     *
     * @return ServiceProvider
     */
    public function register($provider, $force = false)
    {
        if (($registered = $this->getProvider($provider)) && !$force) {
            return $registered;
        }

        // If the given "provider" is a string, we will resolve it, passing in the
        // application instance automatically for the developer. This is simply
        // a more convenient way of specifying your service provider classes.
        if (is_string($provider)) {
            $provider = $this->resolveProvider($provider);
        }

        if (method_exists($provider, 'register')) {
            $provider->register();
        }

        $this->markAsRegistered($provider);

        // If the application has already booted, we will call this boot method on
        // the provider class so it has an opportunity to do its boot logic and
        // will be ready for any usage by this developer's application logic.
        if ($this->booted) {
            $this->bootProvider($provider);
        }

        return $provider;
    }

    /**
     * Get the registered service provider instance if it exists.
     *
     * @param  mixed $provider
     *
     * @return mixed
     */
    public function getProvider($provider)
    {
        $serviceProviderClassName = is_string($provider) ? $provider : get_class($provider);

        foreach ($this->serviceProviders as $serviceProvider) {
            if ($serviceProvider instanceof $serviceProviderClassName) {
                return $serviceProvider;
            }
        }

        return null;
    }

    /**
     * Resolve a service provider instance from the class name.
     *
     * @param  string $provider
     *
     * @return ServiceProvider
     */
    public function resolveProvider($provider)
    {
        return new $provider($this);
    }

    /**
     * Mark the given provider as registered.
     *
     * @param $provider
     *
     * @return void
     */
    protected function markAsRegistered($provider)
    {
        $this->serviceProviders[] = $provider;
    }

    /**
     * Boot the given service provider.
     *
     * @param ServiceProvider $provider
     *
     * @return mixed
     */
    protected function bootProvider(ServiceProvider $provider)
    {
        if (method_exists($provider, 'boot')) {
            return $this->call([$provider, 'boot']);
        }
    }

    public function boot()
    {
        if ($this->booted) {
            return;
        }

        array_walk($this->serviceProviders, function ($p) {
            $this->bootProvider($p);
        });

        $this->booted = true;
    }

    /**
     * Determine if the application has booted.
     *
     * @return bool
     */
    public function isBooted()
    {
        return $this->booted;
    }

    /**
     * Determine if the application has been bootstrapped before.
     *
     * @return bool
     */
    public function hasBeenBootstrapped()
    {
        return $this->hasBeenBootstrapped;
    }

    /**
     * Get the base path of the Laravel installation.
     *
     * @return string
     */
    public function basePath()
    {
        return $this->basePath;
    }

    /**
     * Get the main plugin file path.
     *
     * @return string
     */
    public function mainPluginFilePath()
    {
        return $this->mainPluginFilePath;
    }
}
