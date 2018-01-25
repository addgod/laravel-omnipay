<?php

namespace Addgod\Omnipay;

use Omnipay\Common\GatewayFactory;
use Addgod\DibsD2\app\Models\Merchant;

class GatewayManager {

    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * The registered merchants
     */
    protected $merchants;

    /**
     * The default settings, applied to every gateway
     */
    protected $defaults;

    /**
     * Create a new Gateway manager instance.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @param  \Omnipay\Common\GatewayFactory $factory
     * @param  array
     */
    public function __construct($app, GatewayFactory $factory, $defaults = array())
    {
        $this->app = $app;
        $this->factory = $factory;
        $this->defaults = $defaults;
    }

    /**
     * Get a gateway
     *
     * @param  string  The gateway to retrieve (null=default)
     * @return \Omnipay\Common\GatewayInterface
     */
    public function gateway()
    {
        $merchant = $this->getDefaultMerchant();
        if(!isset($this->merchants[$merchant])){
            $gateway = $this->factory->create('DibsD2', null, $this->app['request']);
            $gateway->initialize($this->getConfig($merchant));
            $this->merchants[$merchant] = $gateway;
        }

        return $this->merchants[$merchant];
    }

    /**
     * Get the configuration, based on the config and the defaults.
     */
    protected function getConfig($id)
    {
        if ($this->app['config']['omnipay.driver'] === 'array') {
            return array_merge(
                $this->defaults,
                $this->app['config']->get('omnipay.merchants.'.$id, array())
            );
        } elseif ($this->app['config']['omnipay.driver'] === 'database') {
            return array_merge(
                $this->defaults,
                Merchant::findOrFail($id)->toConfig()
            );
        }
    }

    /**
     * Get the default merchant name.
     *
     * @return string
     */
    public function getDefaultMerchant()
    {
        return $this->app['config']['omnipay.default_merchant'];
    }

    /**
     * Set the default merchant name.
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultMerchant($name)
    {
        $this->app['config']['omnipay.default_merchant'] = $name;
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array(array($this->gateway(), $method), $parameters);
    }

}
