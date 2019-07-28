<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

$router->get('/ping', function () {
    return ['status' => 'OK'];
});


$router->group(['prefix' => '/v1'], function() use ($router) {
	$router->post('/containers', 'ContainersApi@createContainers');
	$router->get('/containers', 'ContainersApi@listContainers');
	$router->get('/containers/{containerId}', 'ContainersApi@showContainerById');
});
