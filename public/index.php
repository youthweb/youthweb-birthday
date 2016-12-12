<?php

require '../vendor/autoload.php';

define('PUBLICPATH', __DIR__.DIRECTORY_SEPARATOR);
define('ROOTPATH', realpath(__DIR__.'/../').DIRECTORY_SEPARATOR);

$config_path = ROOTPATH . 'config';
$env = getenv('SLIM_ENV') ?: 'development';

$config = new Art4\YouthwebEvent\Config($config_path, $env);

$app = new \Slim\App($config->getAll());

$container = $app->getContainer();

// Register component on container
$container['view'] = function ($container)
{
	$view = new \Slim\Views\Twig(
		$container['settings']['views']['twig']['template_path'],
		$container['settings']['views']['twig']['environment']
	);

	// Instantiate and add Slim specific extension
	$basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
	$view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

	return $view;
};

// Add routes to app
foreach ($container['settings']['routes'] as $pattern => $target)
{
	foreach ($target as $method => $callable)
	{
		$app->map([$method], $pattern, $callable);
	}
}

$app->run();
