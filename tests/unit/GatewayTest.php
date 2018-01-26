<?php

namespace Addgod\Omnipay\Tests\Unit;

use Orchestra\Testbench\TestCase;
use Addgod\Omnipay\GatewayManager;
use Addgod\Omnipay\ServiceProvider;
use Illuminate\Support\Facades\Facade;
use Omnipay\Common\GatewayFactory;
use Illuminate\Database\Eloquent\Model;

class GatewayTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
        if (! $this->app) {
            $this->refreshApplication();
        }

        $this->setUpTraits();

        foreach ($this->afterApplicationCreatedCallbacks as $callback) {
            call_user_func($callback);
        }

        Facade::clearResolvedInstances();

        Model::setEventDispatcher($this->app['events']);

        $this->setUpHasRun = true;
    }

    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Omnipay' => 'Addgod\Omnipay\Facade'
        ];
    }

    public function testGateway()
    {
        Omnipay::setDefaultMerchant('default');
    }
}
