<?php
namespace Addgod\Omnipay\Facades;


class Omnipay extends \Illuminate\Support\Facades\Facade
{

    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor() { return 'omnipay'; }

}
