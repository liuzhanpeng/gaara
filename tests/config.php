<?php

use Psr\Container\ContainerInterface;

return [
	'user' => [
		'authenticator' => [
			'driver' => 'session',
			'key' => 'key',
			// 'session' => new Session(),
			// 'session' => function (?ContainerInterface $container) {
			// 	return new Session($container->get('component'));
			// }
		],
		'user_provider' => [
			'driver' => '',
		],
		'credential_validator' => [
			'driver' => 'password',
			'field' => 'password',
		],
		'accessor' => [
			'driver' => 'generic',
			'permission_provider' => 'xxx'
		],
	],
];
