<?php

/*

========================================================
 Monitor Global
--------------------------------------------------------
 2007-2009, Mike Kozhevnikov
========================================================
 File: serverlist.php
========================================================
 Назначение: Страница со cписком всех серверов системы 
========================================================

*/

error_reporting(55);

define ( 'ROOT_DIR', dirname ( __FILE__ ) );

include_once(ROOT_DIR . "/include/config.inc.php");
include_once(ROOT_DIR . "/include/class.db.php");
include_once(ROOT_DIR . "/include/functions.inc.php");
include_once(ROOT_DIR . "/include/class.log.php");

$time_start = getmicrotime();

// определяем переданные GET параментры     // возможные варианты            
$sort    = $_GET['sort'];     		        // 'averageonline' or 'abc' or 'daterec'
$sortdir = $_GET['sortdir'];  		        // 'asc' or 'desc'

if ($sort != 'averageonline' && $sort != 'abc' && $sort != 'daterec')
  {$sort = NULL;}
if ($sortdir != 'asc' && $sortdir != 'desc')
  {$sortdir = NULL;}  
  
if ($config['accesslogjabber'])
  {
  $jabberlogger = new jabberLogger("access");
  unset($jabberlogger);
  }

if ($config['accesslogtxt'])
  {
  $logger = new txtLogger("access");
  unset($logger);
  }

$catid = intval($_GET['catid']);

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

switch ($sort)
  {
  case 'averageonline':
    $sort_param1 = 'ORDER BY servers.onlineday';
    break;
  case 'abc':
    $sort_param1 = 'ORDER BY servers.name';
    break;
  case 'daterec':
    $sort_param1 = 'ORDER BY g.date';
    break;
  default:
    $sort_param1 = 'ORDER BY servers.name';
    break;        
  }

switch ($sortdir)
  {
  case 'asc':
    $sort_param2 = 'ASC';
    break;
  case 'desc':
    $sort_param2 = 'DESC';
    break;
  default:
    $sort_param2 = 'ASC';
    break;        
  }

  $sort_param_full = $sort_param1 . ' ' . $sort_param2;
  if ($sort_param2 == 'ASC')
    {$sort_param3 = 'DESC';}
  else                                                       
    {$sort_param3 = 'ASC';}

  
if ($catid) 
  {
  $query = $db_sql->sql_query("
  							SELECT name 
  	   		       			FROM " . $config['category_table'] . " 
			       			WHERE id=" . $catid ." 
			       			LIMIT 1;
			       			");
			       
      
  $query1 = $db_sql->fetch_array ($query);
  $currentcatname = $query1['name'];
  $title = $language['serverlist_serverlistofcategory']  . $currentcatname;
  $main.= '<h1>monitor<span>.global</span> -> ' . $currentcatname . '</h1><br>';
  
  $query = $db_sql->sql_query("SELECT servers.id, servers.catid, g.date, servers.name,
				                              servers.description, servers.url, servers.onlineday 
  	                           FROM " . $config['servers_table'] . " as servers
  	                           LEFT JOIN 	(SELECT serverid,date 
			       	           				FROM " . $config['statslog_table'] . " 
											ORDER BY date ASC) g 
			                   ON (servers.id=g.serverid) 
			                   WHERE servers.enable = '1' 
			                   AND servers.catid=" . $catid ." 
			                   GROUP BY g.serverid " . $sort_param_full . ";");
			       
  }
else 
  {
  $title = 'monitor.global -> '  . $language['listofallservers'];
  $main = '<h1>monitor<span>.global</span> -> Список всех серверов системы </h1><br>';

  $query = $db_sql->sql_query("SELECT id, name 
	                             FROM " . $config['category_table'] . " 
			                         LIMIT 100;");
  while($query1 = $db_sql->fetch_array ($query))
    $arrayX[$query1['id']] = $query1['name'];  
  
  $query = $db_sql->sql_query("
  								SELECT servers.id, servers.catid, g.date, servers.name,
  	   			                          servers.description, servers.url, servers.onlineday
								FROM " . $config['servers_table'] . " as servers  
			                    LEFT JOIN (SELECT serverid,date 
                                	FROM " . $config['statslog_table'] . " 
					                ORDER BY date ASC) g 
			                	ON (servers.id=g.serverid) 
			                    WHERE servers.enable = '1' 
			                    GROUP BY g.serverid " . $sort_param_full . ";
			                    ");
  }
  

  switch ($sort)
  {
  case 'averageonline':
    $main.= $language['sortby'] . ':&nbsp;<a class="decor" href="serverlist.php?catid=' . $catid . '&sort=abc&sortdir=asc" >' . $language['abc'] . '</a> | <img src="/template/images/' . strtolower($sort_param2) . '.gif" alt="" /> <a class="decor" href="serverlist.php?catid=' . $catid . '&sort=averageonline&sortdir=' . strtolower($sort_param3) . '" >' . $language['averageonline'] . '</a> | <a class="decor" href="serverlist.php?catid=' . $catid . '&sort=daterec&sortdir=asc" >' . $language['startmonitoring'] . '</a><br><br>';
    break;
  case 'abc':
    $main.= $language['sortby'] . ':&nbsp;<img src="/template/images/' . strtolower($sort_param2) . '.gif" alt="" /><a class="decor" href="serverlist.php?catid=' . $catid . '&sort=abc&sortdir=' . strtolower($sort_param3) . '" >' . $language['abc'] . '</a> | <a class="decor" href="serverlist.php?catid=' . $catid . '&sort=averageonline&sortdir=asc" >' . $language['averageonline'] . '</a> | <a class="decor" href="serverlist.php?catid=' . $catid . '&sort=daterec&sortdir=asc" >' . $language['startmonitoring'] . '</a><br><br>';
    break;
  case 'daterec':
    $main.= $language['sortby'] . ':&nbsp;<a class="decor" href="serverlist.php?catid=' . $catid . '&sort=abc&sortdir=asc" >' . $language['abc'] . '</a> | <a class="decor" href="serverlist.php?catid=' . $catid . '&sort=averageonline&sortdir=asc" >' . $language['averageonline'] . '</a> | <img src="/template/images/' . strtolower($sort_param2) . '.gif" alt="" /><a class="decor" href="serverlist.php?catid=' . $catid . '&sort=daterec&sortdir=' . strtolower($sort_param3) . '" >' . $language['startmonitoring'] . '</a><br><br>';
    break;
  default:
    $main.= $language['sortby'] . ':&nbsp;<img src="/template/images/' . strtolower($sort_param2) . '.gif" alt="" /><a class="decor" href="serverlist.php?catid=' . $catid . '&sort=abc&sortdir=' . strtolower($sort_param3) . '" >' . $language['abc'] . '</a> | <a class="decor" href="serverlist.php?catid=' . $catid . '&sort=averageonline&sortdir=asc" >' . $language['averageonline'] . '</a> | <a class="decor" href="serverlist.php?catid=' . $catid . '&sort=daterec&sortdir=asc" >' . $language['startmonitoring'] . '</a><br><br>';
    break;        
  }


$main.='<table width="100%"><tr>';    
while ($query1 = $db_sql->fetch_array ($query))
{
$main.= '<tr><td>';
$main.= '<input type="hidden" id="status' . $query1['id'] .'" value="0" >';
$main.= '<div id="el' . $query1['id'] . '" >';
$main.= '<div onmouseover="javascript:changeOpacity(' . $query1['id'] .')" onmouseout="javascript:changeOpacity(' . $query1['id'] .')"><a class="decor" href="./serverdetail.php?serverid=' .  $query1['id'] . '">' . $query1['name'] . '</a></div>';
if (!$catid)
  $main.= $language['category'] . ': <a class="decor" href="./serverlist.php?catid=' . $query1['catid'] . '">' . $arrayX[$query1['catid']] . '</a><br>';
$main.= $language['description'] . ': ' . $query1['description'] . '<br>';
$main.= 'Url: <a class="decor" href="http://' . $query1['url'] . '">' . $query1['url'] . '</a><br>';
$main.= $language['averageonlinelastday2'] . ' - <font color="green"><b>' . $query1['onlineday'] . '</b></font><br>';
$main.= $language['startmonitorforserver'] . ': <b>' . $query1['date'] . '</b><br>';
$main.= '<br><br></div></td></tr>';      
}


$main.= '</table>';
$main.= '<div class="divider"></div>';


$loadinfo = loadinfo($time_start);  
$header   = str_replace("{JAVASCRIPT}",$javascript,$header);
$header   = str_replace("{TITLE}",$title,$header);
$footer   = str_replace("{LOADINFO}",$loadinfo['full'],$footer);
$output   = $header . $menu . $main . $footer;

echo $output;

$db_sql->closeSQL();

?>