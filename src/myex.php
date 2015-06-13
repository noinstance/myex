<?php

// imports
require_once('lib/required.php');
Required::import([ 'logger', 'stopwatch', 'wordpress' ]);

// options
$shortopts = 'h::o::d::u::p::H::O::D::U::P::f::r::w::zh';
$options = getopt($shortopts);

if(isset($options['h'])) {
	Logger::log('Usage is:');
	Logger::log('  -h=value   origin hostname');
	Logger::log('  -o=value   origin port');
	Logger::log('  -d=value   origin database');
	Logger::log('  -u=value   origin username');
	Logger::log('  -p=value   origin password');
	// Logger::log('  -H=value   destiny hostname');
	// Logger::log('  -O=value   destiny port');
	// Logger::log('  -D=value   destiny database');
	// Logger::log('  -U=value   destiny username');
	// Logger::log('  -P=value   destiny password');
	Logger::log('  -r=v1,v2   replaces v1 for v2 in the file; useful for absolute urls');
	Logger::log('  -w=dir     wordpress dir; reads from wp-config.php');
	Logger::log('  -z         gzip output');
	// Logger::log('  -r         remote server ');
	Logger::log('  -h         print the help screen');
	exit(0);
}

// go
Logger::timestamp();
Logger::hr();
$stopwatch = new Stopwatch();
$stopwatch->start();

// get origin db
$originDB = new stdClass;

// try wordpress
if(isset($options['w'])) {

	try {
		$wpRoot = !empty($options['w']) ? $options['w'] : __DIR__;
		$originDB = Wordpress::getDatabaseConnectionObject($wpRoot);
	} catch (Exception $e) {
		Logger::log('!!! Error reading from wordpress: ' . $e->getMessage());
	}

	print_r($originDB);
	die;


} else {
	
	$originDB->hostname = !empty($options['h']) ? $options['h'] : 'localhost';
	$originDB->port = !empty($options['o']) ? $options['o'] : 3306;
	$originDB->database = $options['d'];
	$originDB->username = $options['u'];
	$originDB->password = $options['p'];	
}

// $targetDB = new stdClass;
// $targetDB->hostname = !empty($options['H']) ? $options['H'] : 'localhost';
// $targetDB->port = !empty($options['O']) ? $options['O'] : 3306;
// $targetDB->database = $options['D'];
// $targetDB->username = $options['U'];
// $targetDB->password = $options['P'];

$gzip = isset($options['z']) ? true : false;

$filename = !empty($options['f']) ? $options['f'] : $originDB->database . date('-mdY-hms');
$filename .= '.sql';

// get backup
$cmd = "mysqldump --host=$originDB->hostname --port=$originDB->port --user=$originDB->username --password=$originDB->password $originDB->database > $filename";
Logger::log('Running mysqldump');

try {
	exec($cmd);
	Logger::log('File created: ' . $filename);
} catch(Exception $e) {
	Logger::log('!!! error running mysqldump' . $e->getMessage());
}

// gzip it
if($gzip) {
	$cmd = "gzip --best $filename";
	exec($cmd);
}

$stopwatch->stop();
Logger::hr();
Logger::timestamp();
Logger::log('Done in ' . $stopwatch->elapsed() . '!');
Logger::hr();

exit();


// mysqldump --host=localhost --port=3306 --user=root --password=absolutament0 lumeta > test.sql

// if ($mysqli->connect_errno) {
//     Logger::log("Failed to connect to MySQL: " . $mysqli->connect_error);
// }
