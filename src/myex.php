<?php

// 3rd party
set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');
require_once('Net/SSH2.php');
require_once('Net/SCP.php');

// requires
require_once('config.php');
require_once('lib/required.php');
Required::import([ 'logger', 'stopwatch', 'wordpress', 'target' ]);

// options
$shortopts = 'd::u::p::D::f::w::s::r::zh';
$options = getopt($shortopts);

if(isset($options['h'])) {
	Logger::log('Usage is:');
	Logger::log('  -d=value   destiny database name');
	Logger::log('  -u=value   destiny user name');
	Logger::log('  -p=value   destiny password');	
	Logger::log('  -D=value   origin database name, defaults to -d value');
	Logger::log('  -f=value   backup filename, defaults to database name and timestamp');
	Logger::log('  -w=dir     wordpress dir; reads from wp-config.php');
	Logger::log('  -s=value   remote server ');
	Logger::log('  -r=v1,v2   replaces v1 for v2 in the file; useful for absolute urls');
	Logger::log('  -z         gzip output');
	Logger::log('  -h         print the help screen');
	exit(0);
}

// go
Logger::timestamp();
Logger::hr();
$stopwatch = new Stopwatch();
$stopwatch->start();

// get config server
$origin =  unserialize(ORIGIN);

// init target db
$target = new Target();

// get origin db
$originDB = new stdClass;
$originDB->hostname = $origin['hostname'];
$originDB->port = $origin['port'];
$originDB->username = $origin['username'];
$originDB->password = $origin['password'];

// get db to export
// try wordpress
if(isset($options['w'])) {

	try {
		$wpRoot = !empty($options['w']) ? $options['w'] : __DIR__;
		$wpdb = Wordpress::getDatabaseConnectionObject($wpRoot);

		// target db values
		$target->setDatabase($wpdb->database);
		$target->setUsername($wpdb->username);
		$target->setPassword($wpdb->password);
	} catch (Exception $e) {
		Logger::log('!!! Error reading from wordpress: ' . $e->getMessage());
		exit(0);
	}

} else {
	
	try {

		// target db values
		$target->setDatabase($options['d']);
		$target->setUsername($options['u']);
		$target->setPassword($options['p']);	
	} catch (Exception $e) {
		Logger::log('!!! Error reading options: ' . $e->getMessage());
		exit(0);
	}	
}

// origin db is set? if not, assumes same as target
try {
	$originDB->database = !empty($options['D']) ? $options['D'] : $target->getDatabase();
} catch (Exception $e) {
	Logger::log('!!! Error getting origin db: ' . $e->getMessage());
	exit(0);
}

// more options
try {
	$gzip = isset($options['z']) ? true : false;
	$replace = !empty($options['r']) ? explode(',', $options['r']) : false;
	$remoteServer = !empty($options['s']) ? $options['s'] : false;
	$filename = !empty($options['f']) ? $options['f'] : $originDB->database . date('-mdY-hms') . '.sql';

} catch (Exception $e) {
	Logger::log('!!! Error reading options: ' . $e->getMessage());
	exit(0);
}

// get backup
$cmd = "mysqldump" .
	" --host=" . $originDB->hostname .
	" --port=" . $originDB->port . 
	" --user=" . $originDB->username .
	" --password=" . $originDB->password .
	" " . $originDB->database . " > " . BACKUPDIR . $filename;
Logger::log('- Running mysqldump... ', false);

try {
	exec($cmd);
	Logger::log('Done! File created: ' . $filename);
	Logger::br();
} catch(Exception $e) {
	Logger::log('!!! error running mysqldump' . $e->getMessage());
	exit(0);
}

// replacements?
try {
	if($replace != false && count($replace) === 2) {
		Logger::log('- Replacing strings... ', false);
		$content = file_get_contents(BACKUPDIR . $filename);
		$content = str_replace($replace[0], $replace[1], $content);
		file_put_contents(BACKUPDIR . $filename, $content);
		$content = null;

		Logger::log('Done!');
		Logger::br();
	}
} catch(Exception $e) {
	Logger::log('!!! error replacing strings' . $e->getMessage());
	Logger::dump('replace', $replace);
	exit(0);
}

// gzip it
try {
	if($gzip) {
		Logger::log('- Zipping...', false);

		$cmd = "gzip --best " . BACKUPDIR . $filename;
		exec($cmd);
		$filename .= '.gz';

		Logger::log('Done!');
		Logger::br();
	}
} catch(Exception $e) {
	Logger::log('!!! error gzipping' . $e->getMessage());
	exit(0);
}

// upload scripts to remote server and run
try {
	if($remoteServer) {
		$destiny =  unserialize(DESTINY);
		$remote = $destiny[$remoteServer];

		Logger::log('- Uploading to ' . $remoteServer . '... ', false);

		// connection
		$ssh = new Net_SSH2($remote['ssh']['hostname'], $remote['ssh']['port']);
		
		// auth
		if($remote['ssh']['key'] == true) {
			// todo
		} else {
			if (!$ssh->login($remote['ssh']['username'], $remote['ssh']['password'])) {
			    throw new Exception('Login Failed');
			}
		}

		// prepare to send files
		$scp = new Net_SCP($ssh);

		// send db creation script
		$createScriptName = 'create-' . $filename;
		if (!$scp->put($createScriptName, $target->getCreateScript())) {
	        throw new Exception("Failed to send create script");
	    }

		// send backup file		
		if (!$scp->put($filename, BACKUPDIR . $filename, NET_SCP_LOCAL_FILE)) {
	        throw new Exception("Failed to send backup file");
	    }
	    Logger::log('Done!');
	    Logger::br();

	    // run on the server
	    $cmd = [];

		// unzip the file on the server if needed
		if($gzip) {
			$cmd[] = "gunzip " . $filename;
			$filename = str_replace('.sql.gz', '.sql', $filename);
		}

		// create db and import backup
		$mysqlConnect = "mysql" .
			" --host=" . $remote['db']['hostname'] .
			" --port=" . $remote['db']['port'] .
			" -u" . $remote['db']['username'] .
			" -p" . $remote['db']['password'];

		$cmd[] = $mysqlConnect . " < " . $createScriptName;
		$cmd[] = $mysqlConnect . " " . $target->getDatabase() . " < " . $filename;

		// cleanup after yourself
		$cmd[] = "rm " . $filename;
		$cmd[] = "rm " . $createScriptName;

		try {
			Logger::log('- Running commands on the remote server... ', false);
			
			foreach ($cmd as $command) {
				$ssh->exec($command);
			}
			
			Logger::log('Done!');
	    	Logger::br();
		} catch(Exception $e) {
			throw $e;			
		}		

  		// bye
  		$ssh->exec('exit');
  		unset($ssh);
	}
} catch(Exception $e) {
	Logger::log('!!! error uploading to remote server: ' . $e->getMessage());
	exit(0);
}

$stopwatch->stop();
Logger::timestamp();
Logger::log('Done in ' . $stopwatch->elapsed() . '!');
Logger::hr();

exit();


// mysqldump --host=localhost --port=3306 --user=root --password=absolutament0 lumeta > test.sql

// if ($mysqli->connect_errno) {
//     Logger::log("Failed to connect to MySQL: " . $mysqli->connect_error);
// }
