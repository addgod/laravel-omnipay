<?php
namespace Addgod\DibsD2;


class Facade extends \Illuminate\Support\Facades\Facade
{

    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor() { return 'dibsd2'; }

}