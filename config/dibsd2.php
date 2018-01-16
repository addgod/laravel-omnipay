<?php

return [
    'route_prefix' => env('DIBSD2_PREFIX', '/dibsd2/'),

    'account' => 'default',

    'defaults' => array(
        'testMode' => false,
    ),

    'accounts' => [
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
