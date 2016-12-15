<?php

namespace Art4\YouthwebEvent;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Controller class
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
		$em = $this->container['em'];

		$members = $em->getRepository(Model\MemberModel::class)->findAll();

		$this->container->view->render($response, 'index.twig', [
			'members_count' => count($members),
		]);

		return $response;
	}

	public function getJoin(ServerRequestInterface $request, ResponseInterface $response, $args)
	{
		list($client, $request, $response) = $this->createClient($request, $response);

		if ( ! $client->isAuthorized() )
		{
			$response = $response->withHeader('Location', $client->getAuthorizationUrl());

			return $response;
		}

		try
		{
			$me = $client->getResource('users')->showMe();
		}
		catch (\Exception $e)
		{
			// Prüfen, ob ein 401 Error vorliegt
			// @see https://github.com/youthweb/php-youthweb-api/issues/14
			if ( strval($e->getCode()) === '401' )
			{
				return $this->showUnauthorizedError($request, $response, $args);
			}

			throw $e;
		}

		$response->getBody()->write(sprintf('<p>Hallo %s %s!</p>', $me->get('data.attributes.first_name'), $me->get('data.attributes.last_name')));

		return $response;
	}

	public function getAuth(ServerRequestInterface $request, ResponseInterface $response, $args)
	{
		list($client, $request, $response) = $this->createClient($request, $response);

		$query = $request->getQueryParams();

		try
		{
			$client->authorize('authorization_code', [
				'code' => $query['code'] ?: '',
				'state' => $query['state'] ?: '',
			]);
		}
		catch (\InvalidArgumentException $e)
		{
			return $this->showUnauthorizedError($request, $response, $args);
		}

		$response = $response->withHeader('Location', '/join');

		return $response;
	}

	private function showUnauthorizedError(ServerRequestInterface $request, ResponseInterface $response, $args)
	{
		$this->container->view->render($response, 'errors/unauthorized.twig', []);

		return $response;
	}

	/**
	 * Create a Youthweb-API client
	 *
	 * We can't put this into the container because of the cache_namespace creation
	 * @see https://github.com/youthweb/php-youthweb-api/issues/15
	 *
	 * @return Youthweb\Api\Client
	 */
	private function createClient(ServerRequestInterface $request, ResponseInterface $response)
	{
		$cookie_params = $request->getCookieParams();

		if ( isset($cookie_params['accesskey']) and \Ramsey\Uuid\Uuid::isValid($cookie_params['accesskey']) )
		{
			$namespace = $cookie_params['accesskey'];
		}
		else
		{
			$namespace = strval(\Ramsey\Uuid\Uuid::uuid4());

			$response = $this->addCookieToResponse($response, 'accesskey', $namespace, new \DateTime('+1 hour'));
		}

		$config = $this->container['settings']['youthweb_client'];

		$config['cache_namespace'] = str_replace('-', '', $namespace) . '.';
		$config['scope'] = ['user:read'];

		$client = new \Youthweb\Api\Client(
			$config,
			[
				'cache_provider' => $this->container['cachepool'],
			]
		);

		return [$client, $request, $response];
	}

	/**
	 * Add a cookie to the response
	 *
	 * $response = $this->addCookieToResponse($response, 'name', 'value', new \DateTime('+1 hour'));
	 */
	private function addCookieToResponse(ResponseInterface $response, $name, $value, \DateTimeInterface $expires = null)
	{
		$expires_at = '';

		if ( $expires !== null )
		{
			$expires->setTimezone(new \DateTimeZone('GMT'));

			$expires_at = '; Expires=' . $expires->format(\DateTime::COOKIE);
		}

		$cookie_value = strval($name) . '=' . urlencode(strval($value)) . $expires_at;

		$response = $response->withAddedHeader('Set-Cookie', $cookie_value);

		return $response;
	}
}
