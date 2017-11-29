<?php

/*

===================================================================================
 Monitor Global
-----------------------------------------------------------------------------------
 2007-2009, Mike Kozhevnikov
===================================================================================
 File: pregmatchtest.php
===================================================================================
 Назначение: Страница для тестирования функции preg_match с заданными параметрами
===================================================================================

*/

error_reporting(55);

define ( 'ROOT_DIR', dirname ( __FILE__ ) );

include_once(ROOT_DIR . "/include/functions.inc.php");

if ($_POST['action']!='do')
	{
	echo '<form name="form1" action="pregmatchtest.php" method="post"><br>';
	echo 'Url: <input type="text" value="" name="url" style="width:300px"><br>';
	echo 'start: <input type="text" value="" name="tagstart" style="width:300px"><br>';
	echo 'stop: <input type="text" value="" name="tagstop" style="width:300px"><br>';
	echo 'Кодировка: <select name="encode" size=2>
	      <option value="win">win</option>
	      <option value="utf">utf</option>       
	      </select>';
	echo '<input type="hidden" name="action" value="do">';
	echo '<input type="submit" value="GO">';
	echo '</form>';
	}
else
	{
	$serverdata = file_get_contents($_POST['url']);
	if ($_POST['encode']=='utf')
	  $serverdata = utf8_win($serverdata);

	$query['tagstart'] = $_POST['tagstart'];
	$query['tagstop']  = $_POST['tagstop'];

	preg_match("/<span class=\"statsTopHi\">(\d*,\d*,\d*)<\/span>/", $serverdata,$m);
	var_dump($m);

	// Если числовое значение онлайна не найдено
	if ($m[1])
		{
		$online = $m[1];
		$online = str_replace(".", "", $online);
		$online = str_replace(" ", "", $online);
		$online = str_replace(",", "", $online);

		$online = intval($online);
		$serv_onoff = 'ON';
		}
	else
		{
		$online = '-';
		$serv_onoff = 'OFF';
		}  
	echo 'Server is ';
	echo $serv_onoff;     
	echo '<br>';   
	echo 'Online: ';
	echo $online;

	}

?>