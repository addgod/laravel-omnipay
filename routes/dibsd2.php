<?php

Route::group([
    'namespace'     => 'Addgod\DibsD2\app\Http\Controllers',
    'middleware'    => 'web',
    'prefix'        => config('dibsd2.route_prefix'),
], function() {
    Route::post('/purchase', 'DibsD2Controller@purchace')->name('dibsd2.purchase');
    Route::post('/complete/purchase', 'DibsD2Controller@completePurchase')->name('dibsd2.complete.purchase');
    Route::post('/authorize', 'DibsD2Controller@authorize')->name('dibsd2.authorize');
    Route::post('/re-authorize', 'DibsD2Controller@reAuthorize')->name('dibsd2.re-authorize');
    Route::post('/complete/authorize', 'DibsD2Controller@completeAuthorize')->name('dibsd2.complete.authorize');
    Route::post('/capture', 'DibsD2Controller@capture')->name('dibsd2.capture');
    Route::post('/void', 'DibsD2Controller@void')->name('dibsd2.void');
    Route::post('/refund', 'DibsD2Controller@refund')->name('dibsd2.refund');
    Route::post('/callback', 'DibsD2Controller@callback')->name('dibsd2.callback');
});