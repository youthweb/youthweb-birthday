<?php

return [
	'settings' => [
		'httpVersion' => '1.1',
		'responseChunkSize' => 4096,
		'outputBuffering' => 'append',
		'determineRouteBeforeAppMiddleware' => false,
		'displayErrorDetails' => false,
		'addContentLengthHeader' => true,
		'routerCacheFile' => false,
		'database' => [
			'type' => 'sqlite',
		],
		'youthweb_client' => [
			'client_id'     => '{client_id}',
			'client_secret' => '{client_secret}',
			'redirect_url'  => 'http://localhost:8080/auth',
		],
		'views' => [
			'twig' => [
				'template_path' => ROOTPATH.'templates'.DS,
				'environment' => [
					'auto_reload' => false,
					'cache_path' => ROOTPATH.'cache'.DS.'twig'.DS,
				],
			],
		],
		'cachepool' => [
			'cache_path' => ROOTPATH.'cache'.DS.'cachepool'.DS,
		],
		'routes' => [
			'/' => [
				'GET' => '\Art4\YouthwebEvent\Controller:getIndex',
			],
			'/auth' => [
				'GET' => '\Art4\YouthwebEvent\Controller:getAuth',
			],
			'/join' => [
				'GET' => '\Art4\YouthwebEvent\Controller:getJoin',
			],
		],
	],
];
