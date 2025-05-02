<?php


$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get('/gen-token', 'TwitchAnalytics\Http\Controllers\ApiController@genToken');
});


$router->get('foo', function () {
    return 'Hello World';
});

