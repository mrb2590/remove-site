<?php
/*
 * usage: php remove_site.php mysite.com
*/

// user must be root
if (posix_getuid() != 0) {
    echo "Run script as root\n";
    exit;
}

$cfg = require "config.php";

// validate site name
if (isset($argv[1])) {
    // file must exist in websites directory
    if (!file_exists($cfg['paths']['sites_dir']."/".$argv[1])) {
    	echo "Directory ".$cfg['paths']['sites_dir']."/".$argv[1]." does not exist!\n";
    	exit;
    } else {
    	$siteName = $argv[1];
    }
} else {
    echo "Invalid usage.\n";
    echo "Usage: php remove_site.php mysite.com\n";
    exit;
}

function delTree($dir) { 
	$files = array_diff(scandir($dir), array('.','..')); 
	foreach ($files as $file) { 
		(is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file"); 
	} 
	return rmdir($dir); 
}

// remove websites directory and all contents
if (file_exists($cfg['paths']['sites_dir']."/".$siteName)) {
	delTree($cfg['paths']['sites_dir']."/".$siteName);
}

//remove vhost file
unlink($cfg['paths']['sites_avail_dir']."/".$siteName.".conf");

// remove log files
if (file_exists($cfg['paths']['apache_log_dir']."/".$siteName)) {
	delTree($cfg['paths']['apache_log_dir']."/".$siteName);
}

// disable site and reload apache
echo shell_exec("a2dissite ".$siteName);
echo shell_exec("/etc/init.d/apache2 reload");