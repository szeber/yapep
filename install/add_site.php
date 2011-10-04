#!/usr/bin/php
<?php

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
	echo "ERROR: The specified directory can't be created!";
	exit(1);
}
$dir = realpath($dir).'/';
mkdir($dir.'cache', 0777);
mkdir($dir.'configs');
mkdir($dir.'cron');
mkdir($dir.'locale');
mkdir($dir.'models');
mkdir($dir.'module');
mkdir($dir.'module/admin');
mkdir($dir.'module/box');
mkdir($dir.'module/db');
mkdir($dir.'module/doc');
mkdir($dir.'module/utility');
mkdir($dir.'module/virtual');
mkdir($dir.'public_html');
mkdir($dir.'public_html/images');
mkdir($dir.'public_html/images/upload', 0777);
mkdir($dir.'system');
mkdir($dir.'template');

copy($commonDir.'public_html/get_doc.php', $dir.'public_html/get_doc.php');
copy($commonDir.'public_html/get_admin.php', $dir.'public_html/get_admin.php');

copy($commonDir.'system/settings.sample.xml', $dir.'system/settings.sample.xml');
copy($commonDir.'system/paths.sample.php', $dir.'system/paths.sample.php');

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