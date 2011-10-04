<?php

/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	Utility
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * Alias for printArr @see printArr
 *
 * @param mixed $variable
 * @param string $type
 */
function print_arr($variable, $type = 'print_arr') {
	printArr ($variable, $type);
}

/**
 * Prints the contents of a variable enclosed in 'xmp' tags either with 'print_r', 'var_export' or 'var_dump'
 *
 * @param mixed $variable
 * @param string $type The function to use either 'var_dump', 'var_export' or 'print_arr'
 */
function printArr($variable, $type = 'print_arr') {
	echo '<xmp>';
	if ($type === true || $type == 'var_dump') {
		var_dump ($variable);
	} elseif ($type == 'var_export') {
		var_export ($variable);
	} else {
		print_r ($variable);
	}
	echo '</xmp>';
}

/**
 * Recursively deletes a file or directory
 *
 * @param string $file
 */
function recursiveDelete($file, $delete = true) {
	if (is_dir ($file)) {
		$files = scandir ($file);
		foreach ( $files as $val ) {
			if ($val != '.' && $val != '..') {
				recursiveDelete ($file . '/' . $val);
			}
		}
		if ($delete) {
			rmdir ($file);
		}
	} elseif ($delete && is_file ($file)) {
		unlink ($file);
	}
}

/**
 * Converts a string to a valid docname and returns it
 *
 * @param string $string
 * @return string
 */
function convertStringToDocname($string) {
	$string = strtolower (iconv ('UTF-8', 'ASCII//TRANSLIT', $string));
	$string = trim($string);
	$string = str_replace (' ', '-', $string);
	$string = preg_replace ('/[^-_a-z0-9]/', '', $string);
	if (strlen($string) > 28) {
		$string = substr($string, 0, 28);
	}
	return $string;
}

/**
 * Converts a string to a valid and free docname in the given folder.
 *
 * It accepts a docname as valid that's used by the doc with the id specified in $excludeId.
 *
 * @param string $string
 * @param integer $folderId
 * @param integer $excludeId
 * @return string
 */
function makeValidDocnameFromString($localeId, $string, $folderId, $excludeId = 0) {
	$docname = convertStringToDocname($string);
	$docHandler = getPersistClass('Doc');
	$docname = $docHandler->findValidDocname($localeId, $docname, $folderId, $excludeId);
	return $docname;
}

/**
 * Retuns the persistance class specified by it's name and database type.
 * The database type is automatically determined from the connection.
 *
 * @param string $name
 * @param string $connectionName
 * @return module_db_DbModule
 */
function getPersistClass($name, $connectionName = 'site') {
	$conn = sys_LibFactory::getDbConnection ($connectionName);
    $type = $conn->getType ();
	if (strstr ($type . '_' . $name, '.') || strstr ($type . '_' . $name, '/')) {
		throw new sys_exception_SiteException ('Bad database type or persistance class name!', 901);
	}
	$className = 'module_db_' . $type . '_' . $name;
	if (!class_exists ($className)) {
		$className = 'module_db_generic_' . $name;
		if (!class_exists ($className)) {
			throw new sys_exception_ModuleException ('Missing persistence module: ' . $name, 500);
		}
	}
	return new $className ($connectionName);
}

/**
 * Removes magic quotes from the GET, POST and COOKIE arrays if magic_quotes_gpc is enabled
 *
 */
function removeMagicQuotes() {
	if (!get_magic_quotes_gpc ()) {
		return;
	}
	$_GET = stripslashesArr($_GET);
	$_POST = stripslashesArr($_POST);
	$_COOKIE = stripslashesArr($_COOKIE);
}

/**
 * Recursiveli runs stripslashes on $arr
 *
 * @param mixed $arr
 * @return mixed
 */
function stripslashesArr($arr) {
	if (!is_array($arr)) {
		return stripslashes($arr);
	}
	foreach($arr as &$val) {
		$val = stripslashesArr($val);
	}
	return $arr;
}

/**
 * Recursively runs strip_tags on $data
 *
 * @param mixed $data
 * @return mixed
 */
function recursiveStripTags($data) {
	if (is_array($data)) {
		foreach ($data as &$val) {
			$val = recursiveStripTags($val);
		}
		return $data;
	}
	return strip_tags($data);
}

/**
 * Sets the locale domain and path for gettext
 *
 * @param string $locale
 * @param string $domain The gettext domain
 * @param string $path The path to the locales
 */
function setupGettext($locale, $domain, $path) {
	putenv ("LC_ALL=" . $locale);
	setlocale (LC_ALL, $locale);

	bindtextdomain ($domain, $path);
	bind_textdomain_codeset ($domain, 'UTF-8');
	textdomain ($domain);
}

/**
 * Checks if an email address is sintacticaly valid
 *
 * Courtesy of Cal Henderson.
 *
 * @param string $email
 * @return boolean
 * @see http://www.iamcal.com/publish/articles/php/parsing_email/
 */
function checkEmailAddressValid($email) {
	$qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
	$dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
	$atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c'.
		'\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
	$quoted_pair = '\\x5c[\\x00-\\x7f]';
	$domain_literal = "\\x5b($dtext|$quoted_pair)*\\x5d";
	$quoted_string = "\\x22($qtext|$quoted_pair)*\\x22";
	$domain_ref = $atom;
	$sub_domain = "($domain_ref|$domain_literal)";
	$word = "($atom|$quoted_string)";
	$domain = "$sub_domain(\\x2e$sub_domain)*";
	$local_part = "$word(\\x2e$word)*";
	$addr_spec = "$local_part\\x40$domain";
	return (bool)preg_match("!^$addr_spec$!", $email);
}

/**
 * Returns the source of a template either from the project's or the system's template directory
 *
 * @param string $tpl_name
 * @param string $tpl_source
 * @param Smarty $smarty_obj
 * @return boolean True if the file is found, false otherwise
 */
function yapepGetTemplate($tpl_name, &$tpl_source, &$smarty_obj) {
	$fileName = yapepGetTemplateFileName ($tpl_name);
	if (!$fileName) {
		return false;
	}
	$tpl_source = file_get_contents ($fileName);
	if (false === $tpl_source) {
		return false;
	}
	return true;
}

/**
 * Returns the last modification date of a template file
 *
 * @param string $tpl_name
 * @param integer $tpl_timestamp
 * @param Smarty $smarty_obj
 * @return boolean True if the file is found, false otherwise
 */
function yapepGetTimestamp($tpl_name, &$tpl_timestamp, &$smarty_obj) {
	$fileName = yapepGetTemplateFileName ($tpl_name);
	if (!$fileName) {
		return false;
	}
	$tpl_timestamp = filemtime ($fileName);
	return (bool) $tpl_timestamp;
}

/**
 * Checks if a template file exists under either the project's or the system's directory structure.
 * Returns the full path and file name if the template is found, or false if it's not
 *
 * @param string $tpl_name
 * @return string
 */
function yapepGetTemplateFileName($tpl_name) {
	if (file_exists (PROJECT_PATH . 'template/' . $tpl_name) && strstr (realpath (PROJECT_PATH . 'template/' . $tpl_name), PROJECT_PATH . 'template/') && is_file (PROJECT_PATH . 'template/' . $tpl_name)) {
		return (PROJECT_PATH . 'template/' . $tpl_name);
	}
	if (file_exists (SYS_PATH . 'template/' . $tpl_name) && strstr (realpath (SYS_PATH . 'template/' . $tpl_name), SYS_PATH . 'template/') && is_file (SYS_PATH . 'template/' . $tpl_name)) {
		return (SYS_PATH . 'template/' . $tpl_name);
	}
	return false;
}

/**
 * Checks if the template is secure.
 *
 * Since we check the paths already, it always returns true
 *
 * @param string $tpl_name
 * @param Smarty $smarty_obj
 * @return boolean
 */
function yapepGetSecure($tpl_name, &$smarty_obj) {
	return true;
}

/**
 * Checks if a resource is trusted
 *
 * Since we only allow this method to be used with templates, it always returns false
 *
 * @param string $tpl_name
 * @param Smarty $smarty_obj
 * @return boolean
 */
function yapepGetTrusted($tpl_name, &$smarty_obj) {
	return false;
}