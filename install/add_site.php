#!/usr/bin/php
<?php

function recursiveCopyDir($source, $target) {
    if (!file_exists($target)) {
        mkdir($target);
    } else if (!is_dir($target) || !is_writable($target)) {
        echo "ERROR: Unable to create target directory: '$target'!\n";
        return;
    }
    $dir = opendir($source);
    if (!$dir) {
        echo "ERROR: Unable to read source directory: '$source'\n";
        return;
    }
    while(false !== ($file = readdir($dir))) {
        if ('.' == $file || '..' == $file || '.svn' == $file) {
            continue;
        }
        if(is_dir($source.$file)) {
            recursiveCopyDir($source.$file.'/', $target.$file.'/');
        } else {
            copy($source.$file, $target.$file);
        }
    }
    closedir($dir);
}

echo "\nInstalling new site\n";

if ($_SERVER['argc'] < 2) {
	echo "Usage: ".__FILE__." <target_dir> [<locale> [<locale>...]]\n";
	exit(1);
}
$commonDir = dirname(dirname(__FILE__)).'/';
$dir = $_SERVER['argv'][1];

if (file_exists($dir)) {
	echo "ERROR: The specified directory already exists!\n";
	exit(1);
}
if (!file_exists(dirname($dir)) || !is_dir(dirname($dir)) || !mkdir($dir)) {
	echo "ERROR: The specified directory can't be created!\n";
	exit(1);
}
$dir = realpath($dir).'/';
mkdir($dir.'cache', 0777);
mkdir($dir.'configs');
mkdir($dir.'configs/cms');
mkdir($dir.'configs/smarty');
mkdir($dir.'cron');
mkdir($dir.'locale');
mkdir($dir.'models');
mkdir($dir.'module');
mkdir($dir.'module/admin');
mkdir($dir.'module/box');
mkdir($dir.'module/db');
mkdir($dir.'module/db/interface');
mkdir($dir.'module/db/Doctrine');
mkdir($dir.'module/db/generic');
mkdir($dir.'module/doc');
mkdir($dir.'module/utility');
mkdir($dir.'module/virtual');
mkdir($dir.'public_html');
mkdir($dir.'public_html/images');
mkdir($dir.'public_html/images/upload', 0777);
mkdir($dir.'system');
mkdir($dir.'template');
mkdir($dir.'template/box');
mkdir($dir.'template/doc');
mkdir($dir.'template/misc');
mkdir($dir.'template/page');

copy($commonDir.'public_html/get_doc.php', $dir.'public_html/get_doc.php');
copy($commonDir.'public_html/get_admin.php', $dir.'public_html/get_admin.php');
copy($commonDir.'public_html/debug.css', $dir.'public_html/debug.css');

copy($commonDir.'system/settings.sample.xml', $dir.'system/settings.sample.xml');
copy($commonDir.'system/paths.sample.php', $dir.'system/paths.sample.php');

$dirh = opendir($commonDir.'public_html/');
while(false !== ($file = readdir($dirh))) {
    if ('.' == $file || '..' == $file || '.svn' == $file || !is_dir($commonDir.'public_html/'.$file)) {
        continue;
    }
    recursiveCopyDir($commonDir.'public_html/'.$file.'/', $dir.'public_html/'.$file.'/');
}

$adminLangs = array();

array_push($_SERVER['argv'], 'hu_HU');
array_push($_SERVER['argv'], 'en_US');

for($i=2; $i<count($_SERVER['argv']); $i++) {
	if (in_array($_SERVER['argv'][$i], $adminLangs)) {
		continue;
	}
	mkdir($dir.'locale/'.$_SERVER['argv'][$i]);
	mkdir($dir.'locale/'.$_SERVER['argv'][$i].'/LC_MESSAGES');
	$localePath = realpath($commonDir.'locale/'.$_SERVER['argv'][$i].'/LC_MESSAGES').'/';
	$targetPath = $dir.'locale/'.$_SERVER['argv'][$i].'/LC_MESSAGES/';
	if (!file_exists($localePath.'YapepAdmin.po')) {
		echo "WARNING: Can't find PO file for ".$_SERVER['argv'][$i]." locale. The admin interface won't be available in that language.\n";
		continue;
	}
	$adminLangs[] = $_SERVER['argv'][$i];
	copy($localePath.'YapepAdmin.po', $targetPath.'YapepAdmin.po');
	echo "Converting PO file for ".$_SERVER['argv'][$i]."\n";
	exec("msgfmt ".$targetPath."YapepAdmin.po -o ".$targetPath."YapepAdmin.mo");
}
echo "\nInstallation finished. Please set up the paths.php and settings.xml files.\n\n";
?>