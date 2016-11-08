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
		'views' => [
			'twig' => [
				'template_path' => ROOTPATH.'templates/',
				'environment' => [
					'auto_reload' => true,
					'cache_path' => ROOTPATH.'cache/twig/',
				],
			],
		],
	],
];
