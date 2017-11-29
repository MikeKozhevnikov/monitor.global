<?php

/*

=====================================================================
 Monitor Global
---------------------------------------------------------------------
 2007-2009, Mike Kozhevnikov
=====================================================================
 File: reviewlist.php
=====================================================================
 Назначение: Страница для отображения списка записей-постов-новостей
=====================================================================

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


if(isset($_GET['catid']))
  {$catid  = intval($_GET['catid']);}
else
  {$catid = -1;}

$lang = language_cookie(); 

$header = file_get_contents (ROOT_DIR . '/template/' . $lang . '/header.html');
$footer = file_get_contents (ROOT_DIR . '/template/' . $lang . '/footer.html');
$menu   = file_get_contents (ROOT_DIR . '/template/' . $lang . '/menu.html');
include_once(ROOT_DIR . "/language/" . $lang . ".php");

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
$javascript.= '<script src="/template/js/standart.js" type="text/javascript"></script>';
$javascript.= $header_script1 . $header_script2;
$db_sql = New db_sql($config['dbName'],$config['hostname'],$config['dbUname'],$config['dbPasswort']);
if ($catid>=0) 
  {
  if ($catid != 0)
    {
    $query = $db_sql->sql_query("
    							SELECT name 
    	   		                FROM " . $config['category_table'] . " 
			                    WHERE id=" . $catid ." 
			                    LIMIT 1;
			                    ");
			       
    $query1 = $db_sql->fetch_array ($query);
    $currentcatname = $query1['name'];
    }
  else
    {$currentcatname=$language['analytic'];}

  $title = $language['reviewtitle']  . $currentcatname;
  $main = '<h1>' . $language['reviewtitle'] . $currentcatname . '</h1><br>';
  
  $query = $db_sql->sql_query("
  								SELECT id, name 
  	                            FROM " . $config['reviews_table'] . "  
			                    WHERE catid = '" . $catid . "' 
			                    LIMIT 100;
			                    ");
			       
  if ($db_sql->num_rows($query) == 0)
    {$main.=$language['nomaterialsforthiscategory'];} 
  else
    {
    while ($query1 = $db_sql->fetch_array ($query))
      {
      $main.= '<a class="decor" href="./review.php?reviewid=' . $query1['id'] . '">' . $query1['name'] . '</a><br><br>';
      }
    }
  }  
else 
  {
  $currentcatname = $language['reviewlisth1'];
  $title = 'monitor.global -> '  . $currentcatname;
  $main = '<h1>monitor.global -> ' . $language['reviewlisth1'] . '</h1><br>';
 
  $query = $db_sql->sql_query("
  							SELECT * 
       	 		            FROM " . $config['category_table'] . " 
			                LIMIT 100;
			                ");
			     
  $main.='<table width="100%"><tr>';    
  $check = 1;
  $id = 0;

  $query2 = $db_sql->sql_query("
  								SELECT catid, COUNT(catid) as number 
	  		                    FROM " . $config['servers_table'] . " 
			                    GROUP BY catid 
			                    LIMIT 100;
			                    ");
			      
  while ($query3 = $db_sql->fetch_array ($query2))
    {$servernumb[$query3['catid']] = $query3['number'];}

  $main.='<input type="hidden" id="status0" value="0" ><tr><td width="40%"><a class="catlink" href="./reviewlist.php?catid=0"><div id="el0" onmouseover="javascript:changeOpacity(0)" onmouseout="javascript:changeOpacity(0)" ><p><img align="top" class="captioned_image_usual" src="template/images/category/analytics.gif"><br>Аналитика<br>Описание: прогнозы и обзоры рынка игрового ПО<br></p></div></a></td>';

  while ($query1 = $db_sql->fetch_array ($query))
    {
    $id++;
    $main.= '<input type="hidden" id="status' . $id .'" value="0" >';
    if (!$check)
      {$main.='<tr><td width="40%"><a class="catlink" href="./reviewlist.php?catid=' . $query1['id'] .'"><div id="el' . $id .'" onmouseover="javascript:changeOpacity(' . $id .')" onmouseout="javascript:changeOpacity(' . $id .')" >';}
    else
      {$main.='<td width="40%"><a class="catlink" href="./reviewlist.php?catid=' . $query1['id'] .'"><div id="el' . $id .'" onmouseover="javascript:changeOpacity(' . $id .')" onmouseout="javascript:changeOpacity(' . $id .')">';}    
  
    if ($query1['cat_image'])
      {$img_temp = 'template/images/category/' . $query1['cat_image'];}
    else
      {$img_temp = 'template/images/category/default.jpg';}     
    
    
    $main.= '<p><img align="top" class="captioned_image_usual" src="' . $img_temp . '">';
    $main.= '<br>';
    $main.= $query1['name'];
    $main.= '<br>';
    $main.= 'Описание: ' . $query1['description']; 
    $main.= '<br>'; 
    $main.= '</p>';
    if (!$check)
      $main.='</div></a></td>';  
    else  
      $main.='</div></a></td></tr>';
    if ($check == 0)
      $check = 1;
    else 
      $check = 0;
    }
  
  $main.= '</table>';
  $main.= '<div class="divider"></div>';
  }

$db_sql->closeSQL();
  
$loadinfo = loadinfo($time_start);  
$header   = str_replace("{JAVASCRIPT}",$javascript,$header);
$header   = str_replace("{TITLE}",$title,$header);
$footer   = str_replace("{LOADINFO}",$loadinfo['full'],$footer);
$output   = $header . $menu . $main . $footer;

echo $output;

?>