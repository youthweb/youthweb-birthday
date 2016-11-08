<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

require '../vendor/autoload.php';

define('PUBLICPATH', __DIR__.DIRECTORY_SEPARATOR);
define('ROOTPATH', realpath(__DIR__.'/../').DIRECTORY_SEPARATOR);
define('');

$config_path = ROOTPATH . 'config';
$env = getenv('SLIM_ENV') ?: 'development';

$config = new Art4\YouthwebEvent\Config($config_path, $env);

$app = new \Slim\App($config->getAll());

$container = $app->getContainer();

// Register component on container
$container['view'] = function ($container)
{
	$view = new \Slim\Views\Twig($container['settings']['views']['twig']['template_path'], [
		'cache' => $container['settings']['views']['twig']['cache_path'],
	]);

	// Instantiate and add Slim specific extension
	$basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
	$view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

	return $view;
};

$app->get('/', function (ServerRequestInterface $request, ResponseInterface $response)
{
	return $this->view->render($response, 'index.twig', []);

	return $response;
});

$app->get('/hello/{name}', function (ServerRequestInterface $request, ResponseInterface $response)
{
	$name = $request->getAttribute('name');
	$response->getBody()->write("Hello, $name");

	return $response;
});

$app->run();
