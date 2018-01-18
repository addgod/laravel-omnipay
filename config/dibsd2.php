<?php

return [
    'public_route_prefix' => env('DIBSD2_PREFIX', '/dibsd2/'),
    'admin_route_prefix' => env('DIBSD2_ADMIN_PREFIX', '/admin/dibsd2/'),

    /*
    |--------------------------------------------------------------------------
    | Default Config Driver
    |--------------------------------------------------------------------------
    |
    | The dibs module, supports two driver modes. Array and database.
    | It is possible to store, merchant config, in either an array, so
    | in this config file, or it is possible to use the supplied model and
    | database driver for storing merchants.
    |
    | Supported: "array", "database"
    |
    */
    'driver' => 'array',

    /*
    |--------------------------------------------------------------------------
    | Default Merchant
    |--------------------------------------------------------------------------
    |
    | This is the default merchant to be used, if no other merchant is supplied.
    | If the driver used is array, you would need to supply the key in the array.
    | If the driver used is database, then it would be the id, on database level
    | of the merchant in the database, that needs to be used.
    |
    */
    'default_merchant' => 'default',

    'defaults' => array(
        'testMode'      => false,
    ),

    'merchants' => [
        'default' => [
            'key1'          => '', // MD5 Key 1
            'key2'          => '', // MD5 Key 2
            'merchantId'    => 0, // Merchant ID
            'username'      => '', // Username for a webuser
            'password'      => '', // Password for a webuser
            'lang'          => 'dk', // Language shown in the dibs window
            'currency'      => 'DKK', // Currency used for this account
            'payType'       => 'DK,V-DK,MC,VISA', // Possible values here https://tech.dibspayment.com/D2/Toolbox/Paytypes
        ],
    ],
];
