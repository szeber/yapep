<?php
function testLoad($className) {
	$className = preg_replace ('@(^_+|\.|/)@', '', $className);
	$classPath = str_replace ('_', '/', $className . '.php');
	if (file_exists (SYS_PATH . 'test/' . $classPath)) {
		require_once SYS_PATH . 'test/' . $classPath;
		return true;
	}
	return false;
}

spl_autoload_register(testLoad);
?>