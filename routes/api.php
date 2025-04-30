<?php

$router->get('/', function () {
    return file_get_contents(base_path('index.php'));
});