<?php

$router->group(['prefix' => 'analytics', 'middleware' => 'auth.token'], function () use ($router) {
    $router->get('/streams', 'TwitchAnalytics\Controllers\StreamController@getStreams');
    $router->get('/streams/enriched', 'TwitchAnalytics\Controllers\EnrichedController@getEnriched');
    $router->get('/user', 'TwitchAnalytics\Controllers\UserController@getUser');
    $router->get('/topsofthetops', 'TwitchAnalytics\Http\Controllers\ApiController@getTopsOfTheTops');
});

$router->group(['prefix' => 'token'], function () use ($router) {
    $router->post('', 'TwitchAnalytics\Controllers\TokenController@getToken');
});

$router->group(['prefix' => 'register'], function () use ($router) {
    $router->post('', 'TwitchAnalytics\Controllers\RegisterController@registerUser');
});
