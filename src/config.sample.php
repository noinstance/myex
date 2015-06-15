<?php

$origin = [
	'hostname' => 'localhost',
	'port' => 3306,
	'username' => 'root',
	'password' => '1234'
];

$destiny = [
	'server1' => [
		'ssh' => [
			'hostname' => 'server1.com',
			'port' => 22,
			'username' => 'user',
			'password' => 'pass',
			'key' => false
		],
		'db' => [
			'hostname' => 'localhost',
			'port' => 3306,
			'username' => 'root',
			'password' => '1234'
		]
	],
	'server2' => [
		'ssh' => [
			'hostname' => 'server2.com',
			'port' => 22,
			'username' => 'user',
			'password' => 'pass',
			'key' => false
		],
		'db' => [
			'hostname' => 'localhost',
			'port' => 3306,
			'username' => 'root',
			'password' => '1234'
		]
	]
];

define('ORIGIN', serialize($origin));
define('DESTINY', serialize($destiny));
define('BACKUPDIR', dirname(dirname(__FILE__)) . '/backup/');