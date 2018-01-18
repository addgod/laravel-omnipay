<?php

Route::group([
    'namespace'     => 'Addgod\DibsD2\app\Http\Controllers',
    'middleware'    => 'web',
    'prefix'        => config('dibsd2.public_route_prefix'),
], function() {
    Route::get('/purchase/{transaction}', 'DibsD2Controller@purchase')->name('dibsd2.purchase');
    Route::get('/authorize/{transaction}', 'DibsD2Controller@authorize')->name('dibsd2.authorize');
    Route::get('/re-authorize/{transaction}', 'DibsD2Controller@reAuthorize')->name('dibsd2.re-authorize');
    Route::post('/complete/purchase', 'DibsD2Controller@completePurchase')->name('dibsd2.complete.purchase');
    Route::post('/complete/authorize', 'DibsD2Controller@completeAuthorize')->name('dibsd2.complete.authorize');
    Route::post('/callback', 'DibsD2Controller@callback')->name('dibsd2.callback');
});

Route::group([
    'namespace'     => 'Addgod\DibsD2\app\Http\Controllers',
    'middleware'    => ['web', 'admin'],
    'prefix'        => config('dibsd2.admin_route_prefix'),
], function() {
    Route::get('/capture/{transaction}', 'DibsD2Controller@capture')->name('dibsd2.capture');
    Route::get('/void/{transaction}', 'DibsD2Controller@void')->name('dibsd2.void');
    Route::get('/refund/{transaction}/{amount?}', 'DibsD2Controller@refund')->name('dibsd2.refund');
});
