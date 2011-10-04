<?php
define('PROJECT_PATH', realpath(dirname(dirname(__FILE__))) . '/');

if(preg_match('@.dev$@', $_SERVER['SERVER_NAME'])) {
	define('SITE', 'dev');
	define('CLI', false);
	define('SYS_PATH', realpath(PROJECT_PATH.'../yapep').'/');
} elseif($_SERVER['SERVER_NAME']) {
	define('SITE', 'live');
	define('CLI', false);
	define('SYS_PATH', realpath(PROJECT_PATH.'../yapep').'/');
} else {
	define('CLI', true);
	if(strstr(PROJECT_PATH, '/dev/')) {
		define('SITE', 'dev');
		define('SYS_PATH', realpath(PROJECT_PATH.'../yapep').'/');
	} else {
		define('SITE', 'live');
		define('SYS_PATH', realpath(PROJECT_PATH.'../yapep').'/');
	}
}
define('CACHE_DIR', PROJECT_PATH . 'cache/');
