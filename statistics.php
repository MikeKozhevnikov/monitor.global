<?php

/*

=====================================================
 Monitor Global
-----------------------------------------------------
 2007-2009, Mike Kozhevnikov
=====================================================
 File: statictics.php
=====================================================
 Назначение: Страница со статистикой работы системы 
=====================================================

*/

error_reporting(55);

define ( 'ROOT_DIR', dirname ( __FILE__ ) );

include_once(ROOT_DIR . "/include/config.inc.php");
include_once(ROOT_DIR . "/include/class.db.php");
include_once(ROOT_DIR . "/include/functions.inc.php");
include_once(ROOT_DIR . "/include/class.log.php");

$time_start = getmicrotime();

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

$title  = 'monitor.global -> Cтатистика системы';


$lang   = language_cookie(); 

$header = file_get_contents (ROOT_DIR . '/template/' . $lang . '/header.html');
$footer = file_get_contents (ROOT_DIR . '/template/' . $lang . '/footer.html');
$menu   = file_get_contents (ROOT_DIR . '/template/' . $lang . '/menu.html');
include_once(ROOT_DIR . "/language/" . $lang . ".php");


$main.= '<h1>monitor<span>.global</span> -> Cтатистика системы</h1><br>';
	
$javascript.= '<script src="/template/js/standart.js" type="text/javascript"></script>';

$db_sql = New db_sql($config['dbName'],$config['hostname'],$config['dbUname'],$config['dbPasswort']);

$query  = $db_sql->sql_query("
							SELECT catid, COUNT(catid) as number 
	  		     			FROM " . $config['servers_table'] . " 
			     			GROUP BY catid 
			     			LIMIT 100;
			     			");
			      
$query1 = $db_sql->fetch_array ($query);

$query  = $db_sql->sql_query("
							SELECT COUNT(enable) as count, VERSION() as version 
       	 		     		FROM " . $config['servers_table'] . "
       	 		     		GROUP BY enable 
			     			LIMIT 2;
			     			");
			     
$query2        = $db_sql->fetch_array ($query);
$num_disable   = $query2['count'];
$mysql_version = $query2['version'];
$query2        = $db_sql->fetch_array ($query);
$num_enable    = $query2['count'];
unset($query2,$query);

$main.= sprintf($language['a1'],($num_enable + $num_disable),$num_enable,$num_disable);

$query = $db_sql->sql_query("
							SELECT name, serverid, date, max 
       	 		     		FROM " . $config['statslog_table'] . " 
			     			LEFT JOIN " . $config['servers_table'] . " 
			     			ON (" . $config['servers_table'] . ".id=serverid) 
			     			ORDER BY max DESC, date ASC 
			     			LIMIT 1;
			     			");

$query2 = $db_sql->fetch_array ($query);


$main.= sprintf($language['a2'],$query2['serverid'],$query2['name'],$query2['max']);

$query = $db_sql->sql_query("
							SELECT name, serverid, date, average 
       	 		     		FROM " . $config['statslog_table'] . " 
			     			LEFT JOIN " . $config['servers_table'] . " 
			     			ON (" . $config['servers_table'] . ".id=serverid) 
			     			ORDER BY average DESC, date ASC 
			     			LIMIT 1;
			     			");

$query2 = $db_sql->fetch_array ($query);

$main.= sprintf($language['statistic_maxofmaxofservers'],$query2['serverid'],$query2['name'],$query2['average']);

$mysql_info = getMySQLSize();

$main.= $language['mysqlveigth'] . ': <b><font color="green">' . $mysql_info[0] . '</font></b> ' . $language['kb'] .'. ' . $language['kolvostrings'] .': <b><font color="green">' . $mysql_info[1] . '</font></b><br><br>';

unset($mysql_info);

$main.= '<br><b>' . $language['serverPOinfo'] . '</b>:<br><br>';
$server_os = PHP_OS;
$server_uname = php_uname();
$main.= '<div id="box_ul"><ul><li>' . $language['OS'] . ': <b>' . $server_os . '</b></li><br>
	      		      <li>' . $language['servername'] . ': <b>' . $server_uname . '</b></li><br>
			      <li>' . $language['apacheversion'] . ': <b>' . substr($_SERVER['SERVER_SOFTWARE'],0,12) . '</b></li><br>';
$main.= '<li>' . $language['phpversion'] . ': <b>' . phpversion() . '</b></li><br>';
$main.= '<li>' . $language['mysqlversion'] . ': <b>' . $mysql_version . '</b></li></ul></div><br>';


$main.= '<div class="divider"></div><br><br><br><br><br><br><br><br><br><br><br><br><br><br>';

$loadinfo = loadinfo($time_start); 
$footer   = str_replace("{LOADINFO}",$loadinfo['full'],$footer);
$header   = str_replace("{JAVASCRIPT}",$javascript,$header);
$header   = str_replace("{TITLE}",$title,$header);
$output   = $header . $menu . $main . $footer;

echo $output;

$db_sql->closeSQL();

?>