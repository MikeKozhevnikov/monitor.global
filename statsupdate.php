<?php

/*

=======================================================
 Monitor Global
-------------------------------------------------------
 2007-2009, Mike Kozhevnikov
=======================================================
 File: statsupdate.php
=======================================================
 Назначение: Скрипт ежедневного обновления статистики
=======================================================

*/
error_reporting(55);

define ( 'ROOT_DIR', dirname ( __FILE__ ) );

// устанавливаем максимальное время выполнения скрипта
set_time_limit(120);

include_once(ROOT_DIR . "/include/config.inc.php");
include_once(ROOT_DIR . "/include/class.db.php");
include_once(ROOT_DIR . "/include/functions.inc.php");
include_once(ROOT_DIR . "/include/class.log.php");

if (isset($_GET['time']))
  {$time = intval($_GET['time']);}
else
  {$time = time();}  

$dateNow = date ("Y-m-d", $time);//date("Y-m-d");
$timeNext = $time + 60*60*24; 
echo '<b>' . $dateNow . '</b>';
echo '<br>';
echo $timeNext-356*24*60*60;
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

$logger = new txtLogger("statsupdate");
$logger->txtlog("Начинаем выполнение statsupdate.php");

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>';

$time_start = getmicrotime();

// подключаемся к базе данных
$db_sql = New db_sql($config['dbName'],$config['hostname'],$config['dbUname'],$config['dbPasswort']);

echo '<br>-----------------------------------------------------<br>Обновление старых данных<br>';  
// пересчет ранних данных за день 
// берем из базы данные для пересчета

$query1 = $db_sql->sql_query("
                              SELECT * 
                              FROM " . $config['statslog_table'] . "
			                        WHERE date < '" . $dateNow . "' AND (max IS NULL OR average IS NULL) 
			                        ORDER BY date DESC
			                        LIMIT 4000;");
 
while($query = $db_sql->fetch_array($query1))
  {
  $max=0;
  for ($i=0;$i<=23;$i++)
    {
    if($query[time . $i] > $max)
    $max = $query[time . $i];
    }
  $sum=0;
  $j=0;
  for ($i=0;$i<=23;$i++)
    {
    if($query[time . $i] != -2 && $query[time . $i] != -1)
      {
      $sum+=$query[time . $i];
      $j++;
      }
    }
  $average = (int)($sum/$j);
  
  // записываем данные о max и average в statslog за прошедший день
  
  $query2 = $db_sql->sql_query("UPDATE " . $config['statslog_table'] . " 
  	    			SET max = '" . $max ."', average = '" . $average . "' 
				WHERE id = '" . $query['id'] . "' 
				LIMIT 1;");
				
  // обновляем средний онлайн за прошедший день в servers
  
  $query2 = $db_sql->sql_query("UPDATE " . $config['servers_table'] . " 
  	    			SET onlineday = '" . $average . "' 
				WHERE id = '" . $query['serverid'] . "' 
				LIMIT 1;"); 
				
  echo '----------';
  echo 'id=' . $query['id'] . '<br>';
  echo $query2;

  echo 'max= ' . $max  . '<br>';
  echo 'average= ' . $average  . '<br>';
  echo 'Запрос - max= ' . $max  . '<br>';
  }
  
  
$loadinfo = loadinfo($time_start);   
$logger->txtlog("Скрипт statsupdate.php успешно выполнен. " . $loadinfo['full']);
echo '<br><center>' . $loadinfo['full'] . '</center><br>';

$db_sql->closeSQL();			      

?>