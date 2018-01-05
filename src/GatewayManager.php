<?php

namespace Addgod\DibsD2;

use Omnipay\Common\GatewayFactory;

class GatewayManager {

    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * The registered accounts
     */
    protected $accounts;

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
        $account = $this->getDefaultAccount();
        if(!isset($this->accounts[$account])){
            $gateway = $this->factory->create('DibsD2', null, $this->app['request']);
            $gateway->initialize($this->getConfig($account));
            $this->accounts[$account] = $gateway;
        }

        return $this->accounts[$account];
    }

    /**
     * Get the configuration, based on the config and the defaults.
     */
    protected function getConfig($name)
    {
        return array_merge(
            $this->defaults,
            $this->app['config']->get('dibsd2.accounts.'.$name, array())
        );
    }

    /**
     * Get the default account name.
     *
     * @return string
     */
    public function getDefaultAccount()
    {
        return $this->app['config']['dibsd2.account'];
    }

    /**
     * Set the default account name.
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultAccount($name)
    {
        $this->app['config']['dibsd2.account'] = $name;
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
