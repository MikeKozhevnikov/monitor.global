<?php

/*

===============================================================
 Monitor Global
---------------------------------------------------------------
 2007-2009, Mike Kozhevnikov
===============================================================
 File: review.php
===============================================================
 Назначение: Страница для отображения записей-постов-новостей 
===============================================================

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

$reviewid = intval($_GET['reviewid']);
if (!$reviewid)
  {
  header("Location: " . $config['serverhost'] . "index.php");
  exit;
  }  

$lang = language_cookie(); 

$header = file_get_contents (ROOT_DIR . '/template/' . $lang . '/header.html');
$footer = file_get_contents (ROOT_DIR . '/template/' . $lang . '/footer.html');
$menu   = file_get_contents (ROOT_DIR . '/template/' . $lang . '/menu.html');
include_once(ROOT_DIR . "/language/" . $lang . ".php");

$header_script1 = '<script type="text/javascript" src="../template/highslide/highslide.js"></script>
<link rel="stylesheet" type="text/css" href="../template/highslide/highslide.css" />
<!--[if lt IE 7]>
<link rel="stylesheet" type="text/css" href="../template/highslide/highslide-ie6.css" />
<![endif]-->';

$header_script2 = "<script type=\"text/javascript\">
//<![CDATA[
hs.registerOverlay({
	html: '<div class=\"closebutton\" onclick=\"return hs.close(this)\" title=\"Close\"></div>',
	position: 'top right',
	fade: 2 // fading the semi-transparent overlay looks bad in IE
});


hs.graphicsDir = '../template/highslide/graphics/';
hs.wrapperClassName = 'borderless';
//]]>
</script>";

$javascript.= '<script src="/template/js/standart.js" type="text/javascript"></script>';
$javascript.= $header_script1 . $header_script2;

$db_sql = New db_sql($config['dbName'],$config['hostname'],$config['dbUname'],$config['dbPasswort']);

$query = $db_sql->sql_query("
							SELECT * 
       	 		            FROM " . $config['reviews_table'] . " 
			                WHERE id=". $reviewid . " 
			                LIMIT 1;");
			     
if (!$query1 = $db_sql->fetch_array ($query))
  {
  header("Location: " . $config['serverhost'] . "index.php");
  exit;
  }  

$query = $db_sql->sql_query("
							SELECT name 
       	 		            FROM " . $config['category_table'] . " 
			                WHERE id='" . $query1['catid'] ."' 
			                LIMIT 1;
			                ");
			     
if($db_sql->num_rows($query) != 0)
  {
  $query2 = $db_sql->fetch_array ($query);
  $title = $language['reviewtitle']  . $query2['name'] . ' -> '  . $query1['name'];
  $main = '<h1>' . $language['reviewtitle2'] . $query2['name'] . ' -> '  . $query1['name'] . '</h1><br>';
  $main.= $language['category'] . ': <a class="decor" href="./serverlist.php?catid=' . $query1['catid'] . '">' . $query2['name'] . '</a><br>';
  }
else
  {
  $title = $language['reviewtitle']  . $query1['name'];
  $main = '<h1>' . $language['reviewtitle2']  . $query1['name'] . '</h1><br>';
  }  

$main.= $query1['post'];
$main.= '<center><a id="thumb1" href="../media/review/1/test.jpg" class="highslide" onclick="return hs.expand(this)">
	<img src="../media/review/1/thumb_test.jpg" alt="Highslide JS"
		title="Нажмите, чтобы увеличить" /></a></center>';

$main.= '<div class="divider"></div>';

$loadinfo = loadinfo($time_start);  
$header   = str_replace("{JAVASCRIPT}",$javascript,$header);
$header   = str_replace("{TITLE}",$title,$header);
$footer   = str_replace("{LOADINFO}",$loadinfo['full'],$footer);
$output   = $header . $menu . $main . $footer;

echo $output;

?>