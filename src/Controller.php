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

	/**
	 * Constructor
	 *
	 * @param Interop\Container\ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * Zeigt die Startseite an
	 *
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 *
	 * @return ResponseInterface $response
	 */
	public function getIndex(ServerRequestInterface $request, ResponseInterface $response, $args)
	{
		list($namespace, $request, $response) = $this->forgeCacheNamespace($request, $response);
		list($client, $request, $response) = $this->createClient($namespace, $request, $response);

		$current_user_data = $this->getUserdata($namespace);

		$em = $this->container['em'];

		$members = $em->getRepository(Model\MemberModel::class)->findAll();

		$this->container->view->render($response, 'index.twig', [
			'members_count' => count($members),
			'members' => $members,
			'current_user_data' => $current_user_data,
		]);

		return $response;
	}

	/**
	 * Startet den Login
	 *
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 *
	 * @return ResponseInterface $response
	 */
	public function getJoin(ServerRequestInterface $request, ResponseInterface $response, $args)
	{
		list($namespace, $request, $response) = $this->forgeCacheNamespace($request, $response);
		list($client, $request, $response) = $this->createClient($namespace, $request, $response);

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

		$cachepool = $this->container['cachepool'];

		$item = $cachepool->getItem($namespace . '.userdata');

		$item->set([
			'user_id' => $me->get('data.id'),
			'username' => $me->get('data.attributes.username'),
		]);

		$item->expiresAt(new \DateTime('+30 minutes'));

		$cachepool->save($item);

		$response = $response->withHeader('Location', '/');

		return $response;
	}

	/**
	 * Speichert einen neuen Glückwunsch
	 *
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 *
	 * @return ResponseInterface $response
	 */
	public function postJoin(ServerRequestInterface $request, ResponseInterface $response, $args)
	{
		list($namespace, $request, $response) = $this->forgeCacheNamespace($request, $response);
		list($client, $request, $response) = $this->createClient($namespace, $request, $response);

		$current_user_data = $this->getUserdata($namespace);

		if ( $current_user_data['is_logged_in'] === false )
		{
			$response = $response->withHeader('Location', '/join');

			return $response;
		}

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

		$em = $this->container['em'];
/*
		$member = $em->getRepository(Model\MemberModel::class)->findOneBy([
			'user_id' => $me->get('data.id')
		]);
*/
		$body = $request->getParsedBody();
		$message = (isset($body['message'])) ? strval($body['message']) : 'Kein Text';

		// Create new entry
		$member = new Model\MemberModel;
		$member->setUserId($me->get('data.id'));
		$member->setUsername($me->get('data.attributes.username'));
		$member->setName($me->get('data.attributes.first_name') . ' ' . $me->get('data.attributes.last_name'));
		$member->setMemberSince(new \DateTime($me->get('data.attributes.created_at')));
		$member->setPictureUrl($me->get('data.attributes.picture_url'));
		$member->setDescriptionMotto($message);
		$member->setCreatedAt(time());

		$em->persist($member);
		$em->flush();

		$response = $response->withHeader('Location', '/');

		return $response;
	}

	/**
	 * Loggt den User aus
	 *
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 *
	 * @return ResponseInterface $response
	 */
	public function getLogout(ServerRequestInterface $request, ResponseInterface $response, $args)
	{
		$response = $this->removeCookieToResponse($response, 'accesskey');

		$response = $response->withHeader('Location', '/');

		return $response;
	}

	/**
	 * Callback für OAuth2
	 *
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 *
	 * @return ResponseInterface $response
	 */
	public function getAuth(ServerRequestInterface $request, ResponseInterface $response, $args)
	{
		list($namespace, $request, $response) = $this->forgeCacheNamespace($request, $response);
		list($client, $request, $response) = $this->createClient($namespace, $request, $response);

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

	/**
	 * Zeigt einen Fehler bei der Authorization an
	 *
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 *
	 * @return ResponseInterface $response
	 */
	private function showUnauthorizedError(ServerRequestInterface $request, ResponseInterface $response, $args)
	{
		$this->container->view->render($response, 'errors/unauthorized.twig', []);

		return $response;
	}

	/**
	 * Get or create a cache namespace
	 *
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 *
	 * @return array Array with namespace, request and response
	 */
	private function forgeCacheNamespace(ServerRequestInterface $request, ResponseInterface $response)
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

		$namespace = str_replace('-', '', $namespace);

		return [$namespace, $request, $response];
	}

	/**
	 * Create a Youthweb-API client
	 *
	 * We can't put this into the container because of the cache_namespace creation
	 * @see https://github.com/youthweb/php-youthweb-api/issues/15
	 *
	 * @param string $namespace
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 *
	 * @return Youthweb\Api\Client
	 */
	private function createClient($namespace, ServerRequestInterface $request, ResponseInterface $response)
	{
		$config = $this->container['settings']['youthweb_client'];

		$config['cache_namespace'] = $namespace . '.';
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
	 * @param ResponseInterface $response
	 * @param string $name
	 * @param string $value
	 * @param DateTimeInterface $expires
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

	/**
	 * Removes a cookie
	 *
	 * $response = $this->removeCookieToResponse($response, 'name');
	 *
	 * @param ResponseInterface $response
	 * @param string $name
	 *
	 * @param ResponseInterface $response
	 */
	private function removeCookieToResponse(ResponseInterface $response, $name)
	{
		$expires = new \DateTime('-10 years');

		$expires->setTimezone(new \DateTimeZone('GMT'));

		$expires_at = '; Expires=' . $expires->format(\DateTime::COOKIE);

		$cookie_value = strval($name) . '=' . $expires_at;

		$response = $response->withAddedHeader('Set-Cookie', $cookie_value);

		return $response;
	}

	/**
	 * Get Userdata
	 *
	 * @param string $namespace
	 *
	 * @return array
	 */
	private function getUserdata($namespace)
	{
		// Check if user is logged in
		$current_user_data = [
			'is_logged_in' => false,
			'user_id' => 0,
			'username' => '',
		];

		$cachepool = $this->container['cachepool'];

		$item = $cachepool->getItem($namespace . '.userdata');

		if ( $item->isHit() )
		{
			$cached_user_data = $item->get();

			// User is logged in
			$current_user_data['is_logged_in'] = true;

			foreach ($current_user_data as $key => $value)
			{
				if ( is_array($cached_user_data) and array_key_exists($key, $cached_user_data) )
				{
					$current_user_data[$key] = $cached_user_data[$key];
				}
			}
		}

		return $current_user_data;
	}
}
