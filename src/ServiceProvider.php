<?php
namespace Addgod\DibsD2;

use Omnipay\Common\GatewayFactory;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $configPath = __DIR__ . '/../config/dibsd2.php';
        $this->publishes([$configPath => config_path('dibsd2.php')]);

        $this->loadRoutesFrom(__DIR__. '/../routes/dibsd2.php');

        $this->loadMigrationsFrom(__DIR__.'/../migrations');

        $this->app->singleton('dibsd2',function ($app){
            $defaults = $app['config']->get('dibsd2.defaults', array());
            return new GatewayManager($app, new GatewayFactory, $defaults);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('dibsd2');
    }
}
