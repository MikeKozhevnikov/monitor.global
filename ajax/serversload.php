<?php

/*

================================================
 Monitor Global
------------------------------------------------
 2007-2009, Mike Kozhevnikov
================================================
 File: /ajax/serverload.php
================================================
 Назначение: Загрузка списка серверов для AJAX
================================================

*/

error_reporting(55);

$root = str_replace("\ajax", "", dirname ( __FILE__ ));
$root = str_replace("/ajax", "", $root);
define ( 'ROOT_DIR', $root );

include_once(ROOT_DIR . "/include/config.inc.php");
include_once(ROOT_DIR . "/include/class.db.php");
include_once(ROOT_DIR . "/include/functions.inc.php");

$catid = intval($_GET['catid']);

if(!catid)
  {
  echo "";
  exit;
  }

$db_sql = New db_sql($config['dbName'],$config['hostname'],$config['dbUname'],$config['dbPasswort']);

$query = $db_sql->sql_query("SELECT id,name 
       	 		     		 FROM " . $config['servers_table'] . " 
			     			 WHERE catid=" . $catid .  "
			     			 AND enable = 1
			     			 ORDER BY name
			     			 LIMIT 100;");
			     
while ($query1 = $db_sql->fetch_array ($query))
{
$output.= '<option value="' . $query1['id'] . '">' . $query1['name'] . '</option>';
}

$db_sql->closeSQL();	

echo $output;

?>