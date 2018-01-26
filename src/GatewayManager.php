<?php

namespace Addgod\Omnipay;

use Omnipay\Common\GatewayFactory;
use Addgod\Omnipay\app\Models\Merchant;

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
        if(!isset($this->merchants[$this->app['config']['omnipay.default_merchant']])){
            $gateway = $this->factory->create($merchant['gateway'], null, $this->app['request']);
            $gateway->initialize(array_merge($this->defaults, $merchant['config']));
            $this->merchants[$this->app['config']['omnipay.default_merchant']] = $gateway;
        }

        return $this->merchants[$this->app['config']['omnipay.default_merchant']];
    }

    /**
     * Get the default merchant.
     *
     * @return mixed
     */
    public function getDefaultMerchant()
    {
        if ($this->app['config']['omnipay.driver'] === 'array') {
            return $this->app['config']->get('omnipay.merchants.' . $this->app['config']['omnipay.default_merchant'], array());
        } elseif ($this->app['config']['omnipay.driver'] === 'database') {
            return Merchant::findOrFail($this->app['config']['omnipay.default_merchant'])->toArray();
        }
    }

    /**
     * Set the default merchant name.
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultMerchant($identifier)
    {
        $this->app['config']['omnipay.default_merchant'] = $identifier;
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
