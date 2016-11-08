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
				'cache_path' => ROOTPATH.'cache/twig/',
				'template_path' => ROOTPATH.'templates/',
			],
		],
	],
];
