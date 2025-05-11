<?php

$router->group(['prefix' => 'analytics'], function () use ($router) {
    $router->get('/streams', 'TwitchAnalytics\Http\Controllers\ApiController@getStreams');

    $router->get('/streams/enriched', 'TwitchAnalytics\Http\Controllers\ApiController@getEnriched');

    $router->get('/user', 'TwitchAnalytics\Controllers\UserController@getUser');

    $router->get('/topsofthetops', 'TwitchAnalytics\Http\Controllers\ApiController@getTopsOfTheTops');
});

$router->group(['prefix' => 'token'], function () use ($router) {
    $router->post('', 'TwitchAnalytics\Http\Controllers\ApiController@getToken');
});

$router->group(['prefix' => 'register'], function () use ($router) {
    $router->post('', 'TwitchAnalytics\Http\Controllers\ApiController@register');
});
