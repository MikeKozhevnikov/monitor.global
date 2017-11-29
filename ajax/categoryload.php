<?php

/*

=================================================
 Monitor Global
-------------------------------------------------
 2007-2009, Mike Kozhevnikov
=================================================
 File: /ajax/categoryload.php
=================================================
 Назначение: Загрузка списка категорий для AJAX
=================================================

*/

error_reporting(55);

$root = str_replace("\ajax", "", dirname ( __FILE__ ));
$root = str_replace("/ajax", "", $root);
define ( 'ROOT_DIR', $root );

include_once(ROOT_DIR . "/include/config.inc.php");
include_once(ROOT_DIR . "/include/class.db.php");
include_once(ROOT_DIR . "/include/functions.inc.php");

$db_sql = New db_sql($config['dbName'],$config['hostname'],$config['dbUname'],$config['dbPasswort']);

$query = $db_sql->sql_query("SELECT id,name 
       	 		     		 FROM " . $config['category_table'] . " 
			     			 LIMIT 100;");
			     
while ($query1 = $db_sql->fetch_array ($query))
  $output.= '<option value="' . $query1['id'] . '">' . $query1['name'] . '</option>';

$db_sql->closeSQL();	

echo $output;

?>