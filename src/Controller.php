<?php

namespace Art4\YouthwebEvent;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Config class
 */
class Controller
{
	/**
	 * Interop\Container\ContainerInterface $container
	 */
	private $container;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function getIndex(ServerRequestInterface $request, ResponseInterface $response, $args)
	{
		return $this->container->view->render($response, 'index.twig', []);
	}

	public function getHelloName(ServerRequestInterface $request, ResponseInterface $response, $args)
	{
		$name = $request->getAttribute('name');
		$response->getBody()->write("Hello, $name");

		return $response;
	}
}
