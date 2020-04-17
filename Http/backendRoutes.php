<?php

use Illuminate\Routing\Router;
/** @var Router $router */

$router->group(['prefix' =>'/icommerceepayco'], function (Router $router) {
    $router->bind('epaycoconfig', function ($id) {
        return app('Modules\Icommerceepayco\Repositories\EpaycoconfigRepository')->find($id);
    });
    $router->get('epaycoconfigs', [
        'as' => 'admin.icommerceepayco.epaycoconfig.index',
        'uses' => 'EpaycoconfigController@index',
        'middleware' => 'can:icommerceepayco.epaycoconfigs.index'
    ]);
    $router->get('epaycoconfigs/create', [
        'as' => 'admin.icommerceepayco.epaycoconfig.create',
        'uses' => 'EpaycoconfigController@create',
        'middleware' => 'can:icommerceepayco.epaycoconfigs.create'
    ]);
    $router->post('epaycoconfigs', [
        'as' => 'admin.icommerceepayco.epaycoconfig.store',
        'uses' => 'EpaycoconfigController@store',
        'middleware' => 'can:icommerceepayco.epaycoconfigs.create'
    ]);
    $router->get('epaycoconfigs/{epaycoconfig}/edit', [
        'as' => 'admin.icommerceepayco.epaycoconfig.edit',
        'uses' => 'EpaycoconfigController@edit',
        'middleware' => 'can:icommerceepayco.epaycoconfigs.edit'
    ]);


    $router->put('epaycoconfigs', [
        'as' => 'admin.icommerceepayco.epaycoconfig.update',
        'uses' => 'EpaycoconfigController@update',
        'middleware' => 'can:icommerceepayco.epaycoconfigs.edit'
    ]);
    

    $router->delete('epaycoconfigs/{epaycoconfig}', [
        'as' => 'admin.icommerceepayco.epaycoconfig.destroy',
        'uses' => 'EpaycoconfigController@destroy',
        'middleware' => 'can:icommerceepayco.epaycoconfigs.destroy'
    ]);
// append

});
