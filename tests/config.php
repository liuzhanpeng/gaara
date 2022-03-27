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
		'accessor' => [
			'driver' => 'generic',
			'permission_provider' => 'xxx'
		],
	],
];
