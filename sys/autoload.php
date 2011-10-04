<?php

/**
 * This file is part of YAPEP.
 *
 * @package 	YAPEP
 * @subpackage	Utility
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2007 Zsolt Szeberényi. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * Autoloader function
 *
 * @param		string $className
 */
function yapepLoad($className) {
	if ('Doctrine' == substr ($className, 0, 8) && !class_exists ('Doctrine')) {
		$config = sys_ApplicationConfiguration::getInstance ();
		$libdir = $config->getPath ('lib');
		if (file_exists ($libdir . 'Doctrine/Doctrine.compile.php')) {
			require_once $libdir . 'Doctrine/Doctrine.compile.php';
		} else {
			require_once $libdir . 'Doctrine/Doctrine.php';
		}
		addAutoloadLoader (array ('Doctrine', 'autoload'));
		return false;
	}
	$className = preg_replace ('@(^_+|\.|/)@', '', $className);
	$classPath = str_replace ('_', '/', $className . '.php');
	if (file_exists (PROJECT_PATH . $classPath)) {
		require_once PROJECT_PATH . $classPath;
		return true;
	}
	if (file_exists (SYS_PATH . $classPath)) {
		require_once SYS_PATH . $classPath;
		return true;
	}
	return false;
}

function modelLoad($className) {
	$className = str_replace (array ('.', '/'), '', $className);
	if (file_exists(CACHE_DIR.'dbSchema/Doctrine/'.$className.'.php')) {
		require_once CACHE_DIR.'dbSchema/Doctrine/'.$className.'.php';
		return true;
	}//*/
/*	if (file_exists(PROJECT_PATH.'models_orig/'.$className.'.php')) {
        require_once PROJECT_PATH.'models_orig/'.$className.'.php';
        return true;
    }
	if (file_exists(SYS_PATH.'models_orig/'.$className.'.php')) {
		require_once SYS_PATH.'models_orig/'.$className.'.php';
		return true;
	}//*/
	return false;
}

function autoloadFallback($className) {
	$backtrace=debug_backtrace();
	if (count($backtrace)>3 && ($backtrace[2]['function'] == 'class_exists' || $backtrace[2]['function'] == 'interface_exists') && strstr($backtrace[3]['class'], 'Doctrine')) {
		return false;
	}
	eval ('class ' . $className . ' {public function __call($m, $a){throw new sys_exception_SiteException(\'Class ' . $className . ' not found!\', 500);}}');
	throw new sys_exception_SiteException ('Class ' . $className . ' not found!', 500);
}

function addAutoloadLoader($function) {
	spl_autoload_register ($function);
}

spl_autoload_register ('yapepLoad');
spl_autoload_register ('modelLoad');
?>