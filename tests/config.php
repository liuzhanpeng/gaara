<?php

return [
	'default' => 'test1',

	'gates' => [

		'test1' => [
			'user_provider' => [
				'driver' => 'model',
				'params' => [],
			],
			'authenticator' => [
				'driver' => 'session',
				'params' => [],
			],
			'authorizator' => [
				'driver' => 'acl',
				'params' => [],
			],
			'resource_provider' => [
				'driver' => '',
				'params' => [],
			],
			'event' => []
		],

		'test2' => []
	]
];
