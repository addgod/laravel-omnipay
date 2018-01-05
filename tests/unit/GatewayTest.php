<?php

namespace Addgod\DibsD2\Tests\Unit;

use Orchestra\Testbench\TestCase;
use Addgod\DibsD2\GatewayManager;
use Addgod\DibsD2\ServiceProvider;
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
            'DibsD2' => 'Addgod\DibsD2\Facade'
        ];
    }

    public function testGateway()
    {
        DibsD2::setDefaultAccount('default');
    }


    public function testController()
    {
        $response = $this->post('dibsd2/purchase', [
            'returnUrl'     => url('/dibsd2/complete/purchase'),
            'amount'        => 100.00,
            'callbackUrl'   => url('/dibsd2/callback'),
            'currency'      => 'DKK',
            'orderid'       => 8,
            'cancelurl'     => url('/'),
        ]);

        $this->artisan('route:list');

        $response->assertStatus(302);
    }
}