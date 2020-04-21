<?php

use Illuminate\Routing\Router;

$router->group(['prefix' => 'icommerceepayco'], function (Router $router) {
    
    $router->post('/confirmation', [
        'as' => 'icommerceepayco.api.epayco.confirmation',
        'uses' => 'IcommerceEpaycoApiController@confirmation',
    ]);

});