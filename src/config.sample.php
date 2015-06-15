<?php

$origin = [
	'hostname' => 'localhost',				// local db server host
	'port' => 3306,							// local db server port
	'username' => 'root',					// local db server user with permission to run mysqldump
	'password' => '1234'					// local db server user's password
];

$destiny = [
	'server1' => [							// you can have as many remote servers as you want
		'ssh' => [							
			'hostname' => 'server1.com',	// remote server ssh hostname (or ip)
			'port' => 22,					// remote server ssh port
			'username' => 'user',			// remote server login user
			'password' => 'pass',			// remote server login password
			// 'key' => false 				// not implemented
		],
		'db' => [
			'hostname' => 'localhost',		// remote server database hostname
			'port' => 3306,					// remote server database port
			'username' => 'root',			// remote server user with permission to create users and databases
			'password' => '1234'			// remote server user's password
		]
	],
	// 'server2' => [						
	// 	'ssh' => [
	// 		'hostname' => 'server2.com',
	// 		'port' => 22,
	// 		'username' => 'user',
	// 		'password' => 'pass',
	// 		'key' => false
	// 	],
	// 	'db' => [
	// 		'hostname' => 'localhost',
	// 		'port' => 3306,
	// 		'username' => 'root',
	// 		'password' => '1234'
	// 	]
	// ]
];

define('ORIGIN', serialize($origin));
define('DESTINY', serialize($destiny));
define('BACKUPDIR', dirname(dirname(__FILE__)) . '/backup/');