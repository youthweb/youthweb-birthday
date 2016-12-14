# youthweb-event

A boilerplate for a Youthweb event website.

Build with Slim, Twig and Bootstrap.

## Requirements

- PHP >=5.6
- Composer
- npm

## Configuration

Create the file `config/development/config.php` with this minimal configuration

```php
<?php

return [
	'settings' => [
		'displayErrorDetails' => true,
		'database' => [
			'active' => 'default',
			'default' => [
				'type'        => 'pdo',
				'connection'  => [
					'dsn'        => 'sqlite:'.ROOTPATH.'cache'.DS.'mydb.sq3',
					'username'   => '',
					'password'   => '',
					'persistent' => false,
				],
			],
		],
		'youthweb_client' => [
			'client_id'     => '{client_id}',
			'client_secret' => '{client_secret}',
			'redirect_url'  => 'https://example.com/callback_url',
		],
	],
];

```

## Installation

Clone the repository or download and unzip the code into a folder. Run inside the folder:

```
php composer.phar install
npm install
npm run-script grunt
php cli doctrine:migrations:status
```

This installs all dependencies and creates the `style.css`.

Now point apache/nginx to `public/index.php` or use the PHP built in server:

```
php -S localhost:8080 -t public/
```

You can now access the website under http://localhost:8080

## License

GPL3
