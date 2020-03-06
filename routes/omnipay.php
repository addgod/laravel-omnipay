<?php

Route::group([
    'namespace'     => 'Addgod\Omnipay\app\Http\Controllers',
    'middleware'    => 'bindings',
    'prefix'        => config('omnipay.public_route_prefix'),
], function() {
    Route::get('/purchase/{transaction}', 'OmnipayController@purchase')->name('omnipay.purchase');
    Route::get('/authorize/{transaction}', 'OmnipayController@authorize')->name('omnipay.authorize');
    Route::get('/re-authorize/{transaction}', 'OmnipayController@reAuthorize')->name('omnipay.re-authorize');
    Route::any('/complete/purchase/{transaction}', 'OmnipayController@completePurchase')->name('omnipay.complete.purchase');
    Route::any('/complete/authorize/{transaction}', 'OmnipayController@completeAuthorize')->name('omnipay.complete.authorize');
    Route::any('/notify/{transaction}', 'OmnipayController@notify')->name('omnipay.notify');
});

Route::group([
    'namespace'     => 'Addgod\Omnipay\app\Http\Controllers',
    'middleware'    => ['web', 'admin'],
    'prefix'        => config('omnipay.admin_route_prefix'),
], function() {
    Route::get('/capture/{transaction}', 'OmnipayController@capture')->name('omnipay.capture');
    Route::get('/void/{transaction}', 'OmnipayController@void')->name('omnipay.void');
    Route::get('/refund/{transaction}/{amount?}', 'OmnipayController@refund')->name('omnipay.refund');
});
