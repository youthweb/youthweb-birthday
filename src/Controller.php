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
			'members' => [
				[
					'message' => 'Lorem ipsum dolor sit amet, eu sit eius justo adipiscing, cum eros tempor epicurei ex. Ea tota perfecto sit. Illum epicuri eloquentiam ad vim, dignissim cotidieque ea sit, ut eam everti vivendum principes. Ut eius velit ignota usu, agam diceret consectetuer pro cu. An sit mollis mentitum consetetur, has sint liber officiis te. Eam no decore appetere democritum.',
				],
				[
					'message' => 'Ex has nibh illud. Nobis doctus qui et. Ius nostro salutatus honestatis at, sit ignota verterem voluptatum cu. Usu impetus mediocrem ex, pro sale admodum ad.

At mea nulla nullam timeam, cu deserunt vulputate vim. Per ut affert accumsan perfecto. Cibo mutat periculis ne qui, cu sea dico nonumes detraxit. Mel id putant moderatius, congue molestie an per. Te eam latine quaestio, at qui probo efficiantur, comprehensam vituperatoribus in eam.',
				],
				[
					'message' => 'Lorem ipsum dolor sit amet, eu sit eius justo adipiscing, cum eros tempor epicurei ex. Ea tota perfecto sit. Illum epicuri eloquentiam ad vim, dignissim cotidieque ea sit, ut eam everti vivendum principes. Ut eius velit ignota usu, agam diceret consectetuer pro cu. An sit mollis mentitum consetetur, has sint liber officiis te. Eam no decore appetere democritum.',
				],
				[
					'message' => 'Ex has nibh illud. Nobis doctus qui et. Ius nostro salutatus honestatis at, sit ignota verterem voluptatum cu. Usu impetus mediocrem ex, pro sale admodum ad.

At mea nulla nullam timeam, cu deserunt vulputate vim. Per ut affert accumsan perfecto. Cibo mutat periculis ne qui, cu sea dico nonumes detraxit. Mel id putant moderatius, congue molestie an per. Te eam latine quaestio, at qui probo efficiantur, comprehensam vituperatoribus in eam.',
				],
				[
					'message' => 'Ex has nibh illud. Nobis doctus qui et. Ius nostro salutatus honestatis at, sit ignota verterem voluptatum cu. Usu impetus mediocrem ex, pro sale admodum ad.

At mea nulla nullam timeam, cu deserunt vulputate vim. Per ut affert accumsan perfecto. Cibo mutat periculis ne qui, cu sea dico nonumes detraxit. Mel id putant moderatius, congue molestie an per. Te eam latine quaestio, at qui probo efficiantur, comprehensam vituperatoribus in eam.',
				],
				[
					'message' => 'Aeterno pericula pri in, ad sea voluptaria conclusionemque, ubique epicuri eos eu. In ludus adipisci consetetur nec. Pro vivendum patrioque mediocritatem te, eam ad nullam hendrerit. Eam no consequat percipitur, ne facilis consequuntur vis. Ei nulla facilis incorrupte nec, ex vix veritus prodesset. Per falli contentiones eu, accusam corpora has cu. Quot delectus salutandi ex vel.

Laudem probatus adipisci et ius. In eos sumo regione adipiscing, pri amet illum voluptatum id, porro dicta vituperatoribus ea nec. Ex per novum tation concludaturque. Est suscipit periculis no. Vidit instructior ex vel, vel ei etiam aperiri.',
				],
				[
					'message' => 'Laudem probatus adipisci et ius. In eos sumo regione adipiscing, pri amet illum voluptatum id, porro dicta vituperatoribus ea nec. Ex per novum tation concludaturque. Est suscipit periculis no. Vidit instructior ex vel, vel ei etiam aperiri.',
				],
				[
					'message' => 'Lorem ipsum dolor sit amet, eu sit eius justo adipiscing, cum eros tempor epicurei ex. Ea tota perfecto sit. Illum epicuri eloquentiam ad vim, dignissim cotidieque ea sit, ut eam everti vivendum principes. Ut eius velit ignota usu, agam diceret consectetuer pro cu. An sit mollis mentitum consetetur, has sint liber officiis te. Eam no decore appetere democritum.',
				],
			],
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

		$em = $this->container['em'];

		$member = $em->getRepository(Model\MemberModel::class)->findOneBy([
			'user_id' => $me->get('data.id')
		]);

		// Create new member
		if ($member === null)
		{
			$member = new Model\MemberModel;
			$member->setUserId($me->get('data.id'));
			$member->setUsername($me->get('data.attributes.username'));
			$member->setName($me->get('data.attributes.first_name') . ' ' . $me->get('data.attributes.last_name'));
			$member->setMemberSince(new \DateTime($me->get('data.attributes.created_at')));
			$member->setBirthday(new \DateTime($me->get('data.attributes.birthday')));
			$member->setPictureUrl($me->get('data.attributes.picture_url'));
			$member->setDescriptionMotto($me->get('data.attributes.description_motto'));
			$member->setCreatedAt(time());

			$em->persist($member);
			$em->flush();
		}

		$response = $response->withHeader('Location', '/');

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
