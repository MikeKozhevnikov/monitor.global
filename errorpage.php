<?php

/*

========================================================
 Monitor Global
--------------------------------------------------------
 2007-2009, Mike Kozhevnikov
========================================================
 File: errorpage.php
========================================================
 Назначение: Cтраницы с ошибками 400,401,403,404 и 500
========================================================

*/

error_reporting(55);

define ( 'ROOT_DIR', dirname ( __FILE__ ) );

include_once(ROOT_DIR . "/include/config.inc.php");
include_once(ROOT_DIR . "/include/functions.inc.php");
include_once(ROOT_DIR . "/include/class.log.php");

$time_start = getmicrotime();

$page = intval($_GET['page']);
if ($page!=400 && $page!=401 && $page!=403 && $page!=404 && $page!=500)
  {
  $page = NULL;
  exit();
  }

if ($config['accesslogtxt'])
  {
  $logger = new txtLogger("access");
  unset($logger);
  }

$lang = language_cookie(); 

$header = file_get_contents (ROOT_DIR . '/template/' . $lang . '/header.html');
$footer = file_get_contents (ROOT_DIR . '/template/' . $lang . '/footer.html');
$menu   = file_get_contents (ROOT_DIR . '/template/' . $lang . '/menu.html');
include_once(ROOT_DIR . "/language/" . $lang . ".php");

switch ($page)
  {
  case 400:
    $pageinfo=$language['error400title'];
    $pagetext=$language['error400info'];
    break;
  case 401:
    $pageinfo=$language['error401title'];
    $pagetext=$language['error401info'];
    break;
  case 403:
    $pageinfo=$language['error403title'];
    $pagetext=$language['error403info'];
    break;
  case 404:
    $pageinfo=$language['error404title'];
    $pagetext=$language['error404info'];
    break;                                                 
  case 500:
    $pageinfo=$language['error500title'];
    $pagetext=$language['error500info'];
    break;                    
  }

$javascript.= '<script src="/template/js/standart.js" type="text/javascript"></script>';
$title = 'monitor.global - ' . $pageinfo;

$main = '<h1>monitor<span>.global</span> -> ' . $pageinfo . '</h1>
<br><br>' . $pagetext . '<br><br><br><br><br><br><br><br><br><br><br><br><div class="divider"></div>';  

$loadinfo = loadinfo($time_start); 
$footer   = str_replace("{LOADINFO}",$loadinfo['full'],$footer);
$header   = str_replace("{JAVASCRIPT}",$javascript,$header);
$header   = str_replace("{TITLE}",$title,$header);
$output   = $header . $menu . $main . $footer;

echo $output;

?>