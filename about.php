<?php

/*

===================================================
 Monitor Global
---------------------------------------------------
 2007-2009, Mike Kozhevnikov
===================================================
 File: about.php
===================================================
 Назначение: Страница c информацией о системе
===================================================

*/

error_reporting(55);

define ( 'ROOT_DIR', dirname ( __FILE__ ) );

include_once(ROOT_DIR . "/include/config.inc.php");
include_once(ROOT_DIR . "/include/class.db.php");
include_once(ROOT_DIR . "/include/functions.inc.php");
include_once(ROOT_DIR . "/include/class.log.php");

$time_start = getmicrotime();

if ($config['accesslogjabber'])
  $jabberlogger = new jabberLogger("access");

if ($config['accesslogtxt'])
  $logger = new txtLogger("access");

$lang = language_cookie(); 

$header = file_get_contents (ROOT_DIR . '/template/' . $lang . '/header.html');
$footer = file_get_contents (ROOT_DIR . '/template/' . $lang . '/footer.html');
$menu   = file_get_contents (ROOT_DIR . '/template/' . $lang . '/menu.html');
$main   = file_get_contents (ROOT_DIR . '/template/' . $lang . '/about.html');
include_once(ROOT_DIR . "/language/" . $lang . ".php");

$javascript.= '<script src="/template/js/standart.js" type="text/javascript"></script>';
$title = $language['titleabout'];

$loadinfo = loadinfo($time_start); 
$header   = str_replace("{JAVASCRIPT}",$javascript,$header);
$header   = str_replace("{TITLE}",$title,$header);
$footer   = str_replace("{LOADINFO}",$loadinfo['full'],$footer);
$output   = $header . $menu . $main . $footer;

echo $output;

?>