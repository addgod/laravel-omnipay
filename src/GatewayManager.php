<?php

namespace Addgod\Omnipay;

use Omnipay\Common\GatewayFactory;
use Addgod\Omnipay\Models\Merchant;
use Illuminate\Foundation\Application;

class GatewayManager
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * The registered merchants
     * 
     * @var array
     */
    protected $merchants;

    /**
     * The chosen merchant
     * 
     * @var string
     */
    protected $merchant;

    /**
     * The default settings, applied to every gateway
     * 
     * @var array
     */
    protected $defaults;

    /**
     * The omnipay gateway facotry
     *
     * @var \Omnipay\Common\GatewayFactory
     */
    protected $factory;

    /**
     * Create a new Gateway manager instance.
     *
     * @param                                $app
     * @param \Omnipay\Common\GatewayFactory $factory
     * @param  array
     */
    public function __construct($app, GatewayFactory $factory, $defaults = [])
    {
        $this->app = $app;
        $this->factory = $factory;
        $this->defaults = $defaults;
        $this->merchant = $this->app['config']['omnipay.default_merchant'];
        if ($this->app['config']['driver'] === 'array') {
            $this->merchants = $this->app['config']['omnipay.merchants'];
        } else {
            $this->merchants = Merchant::all()->keyBy('id')->toArray();
        }
    }

    /**
     * Get a gateway
     *
     * @param  string  The gateway to retrieve (null=default)
     *
     * @return \Omnipay\Common\GatewayInterface
     */
    public function gateway()
    {
        $merchant = $this->merchants[$this->merchant];
        $gateway = $this->factory->create($merchant['gateway'], null, $this->app['request']);
        $gateway->initialize(array_merge($this->defaults, $merchant['config']));

        return $gateway;
    }

    /**
     * Get all registered merchants
     *
     * @return array
     */
    public function getMerchants()
    {
        return $this->merchants;
    }

    /**
     * Set the default merchant name.
     *
     * @param string $name
     *
     * @return void
     */
    public function setMerchant($identifier)
    {
        $this->merchant = $identifier;
    }

    /**
     * Dynamically call the gateway.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->gateway(), $method], $parameters);
    }
}
