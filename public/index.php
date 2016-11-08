<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

require '../vendor/autoload.php';

$config_path = __DIR__.\DIRECTORY_SEPARATOR.'..'.\DIRECTORY_SEPARATOR.'config';
$env = getenv('SLIM_ENV') ?: 'development';

$config = new Art4\YouthwebEvent\Config($config_path, $env);

$app = new \Slim\App($config->getAll());

$app->get('/', function (ServerRequestInterface $request, ResponseInterface $response)
{
	$response->getBody()->write("Hello World!");

	return $response;
});

$app->get('/hello/{name}', function (ServerRequestInterface $request, ResponseInterface $response)
{
	$name = $request->getAttribute('name');
	$response->getBody()->write("Hello, $name");

	return $response;
});

$app->run();
