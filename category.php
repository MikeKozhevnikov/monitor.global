<?php

/*

====================================================
 Monitor Global
----------------------------------------------------
 2007-2009, Mike Kozhevnikov
====================================================
 File: category.php
====================================================
 Назначение: Страница с выбором категорий сервисов
====================================================

*/

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

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

$title  = 'monitor.global -> список категорий -> cистема мониторинга статистики интернет-сервисов';
$header = file_get_contents (ROOT_DIR . '/template/' . $lang . '/header.html');
$footer = file_get_contents (ROOT_DIR . '/template/' . $lang . '/footer.html');
$menu   = file_get_contents (ROOT_DIR . '/template/' . $lang . '/menu.html');
include_once(ROOT_DIR . "/language/" . $lang . ".php");

$main = '<h1>monitor<span>.global</span> -> Список категорий</h1><br>';	
$header_script1 = '<script src="./include/scriptaculous/lib/prototype.js" type="text/javascript"></script>
<script src="./include/scriptaculous/src/scriptaculous.js" type="text/javascript"></script>';

$header_script2 = "<script type=\"text/javascript\">
function changeOpacity(id){
	\$opacityStatus = $('status'+id);
	if(\$opacityStatus.value==0){
		 new Effect.Opacity('el'+id, {duration:0.9, from:1.0, to:0.5});
		\$opacityStatus.value=1; 
	} else {
		new Effect.Opacity('el'+id, {duration:0.9, from:0.5, to:1});
		\$opacityStatus.value=0; 
	}
}

</script>";

$javascript = 0;
$javascript= '<script src="/template/js/standart.js" type="text/javascript"></script>';
$javascript.= $header_script1 . $header_script2;

$db_sql = New db_sql($config['dbName'],$config['hostname'],$config['dbUname'],$config['dbPasswort']);

$query = $db_sql->sql_query("SELECT * 
							 FROM " . $config['category_table'] . " 
			     			 LIMIT 100;");
			     
$main.='<table width="100%"><tr>';    
$main.='';
$main.='';
$main.='';
$check = 0;
$id = 1;

$query2 = $db_sql->sql_query("SELECT catid, COUNT(catid) as number 
	  		      			  FROM " . $config['servers_table'] . " 
			      			  GROUP BY catid 
			      			  LIMIT 100;");

while ($query3 = $db_sql->fetch_array ($query2))
  {$servernumb[$query3['catid']] = $query3['number'];}

while ($query1 = $db_sql->fetch_array ($query))
  {
  if ($query1['cat_image'])
    {$img_temp = 'template/images/category/' . $query1['cat_image'];}
  else
    {$img_temp = 'template/images/category/default.jpg';}             

  $main.= '<input type="hidden" id="status' . $id .'" value="0" >';
  if (!$check)
    $main.='<tr><td width="40%"><a class="catlink" href="./serverlist.php?catid=' . $query1['id'] .'"><div id="el' . $id .'" onmouseover="javascript:changeOpacity(' . $id .')" onmouseout="javascript:changeOpacity(' . $id .')" >';
  else
    $main.='<td width="40%"><a class="catlink" href="./serverlist.php?catid=' . $query1['id'] .'"><div id="el' . $id .'" onmouseover="javascript:changeOpacity(' . $id .')" onmouseout="javascript:changeOpacity(' . $id .')">';    
  $main.= '<p><img align="top" class="captioned_image_usual" src="' . $img_temp . '">';
  $main.= '<b><h2>' .$query1['name']. '</h2></b>';

  if ($query1['offurl'])
    {
    $main.= '<br>Official Server Url: ';
    $main.= $query1['offurl']; 
    } 

  $main.= '<br>'; 
  $main.= $language['categorydescriptionserver'] . ': ' . $query1['description']; 
  $main.= '<br>';
  $main.= $language['categoryvalueofservers'] . ': ' . $servernumb[$query1['id']]; 
  $main.= '</p>';
  if (!$check)
    $main.='</div></a></td>';  
  else  
    $main.='</div></a></td></tr>';
  if ($check == 0)
    $check = 1;
  else 
    $check = 0;
  $id++;    
}

$main.= '</table>';
$main.= '<div class="divider"></div><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>';

$db_sql->closeSQL();

$loadinfo = loadinfo($time_start);  
$footer   = str_replace("{LOADINFO}",$loadinfo['full'],$footer);
$header   = str_replace("{JAVASCRIPT}",$javascript,$header);
$header   = str_replace("{TITLE}",$title,$header);
$output   = $header . $menu . $main . $footer;

echo $output;

?>