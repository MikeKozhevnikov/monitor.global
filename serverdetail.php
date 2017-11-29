<?php

/*

============================================================
 Monitor Global
------------------------------------------------------------
 2007-2009, Mike Kozhevnikov
============================================================
 File: serverdetail.php
============================================================
 Назначение: Страница с выбором готовых вариантов графиков
============================================================

*/

error_reporting(55);
error_reporting ( E_ALL ^ E_NOTICE );
define ( 'ROOT_DIR', dirname ( __FILE__ ) );

include_once(ROOT_DIR . "/include/config.inc.php");
include_once(ROOT_DIR . "/include/class.db.php");
include_once(ROOT_DIR . "/include/functions.inc.php");
include_once(ROOT_DIR . "/include/class.log.php");

$time_start = getmicrotime();

if ($config['accesslogtxt'])
  $logger = new txtLogger("access");

echo $a;

$serverid  =  intval($_GET['serverid']);
$ajax 	   =  $_GET['ajax'];
if (!$serverid && $ajax != 'sandboxload')
  {
  if ($config['errorslogtxt'])
  {
  $logger = new txtLogger("errors");
  $request = 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"];
  if ($_SERVER["QUERY_STRING"])
    {$request.= '?' . $_SERVER["QUERY_STRING"];}

  $logger->txtlog("\nREASON: Отсутствует serverid" . 
  		  		  "\nREQUEST: " . $request . 
		  		  "\nHTTP_USER_AGENT: " . $_SERVER['HTTP_USER_AGENT'] . 
	 	  		  "\nREMOTE_ADDR: " . $_SERVER['REMOTE_ADDR'] . 
		  		  "\nREMOTE_PORT: " . $_SERVER['REMOTE_PORT'] . 
		  		  "\nHTTP_REFERER: " .$_SERVER['HTTP_REFERER']);
  unset($logger);
  }
  
  rideSite("http://monitor.webcodes.club/index.php", "<br>Сейчас Вы будете перенаправлены на главную страницу");
  exit;
  }
$lang = language_cookie();  
include_once(ROOT_DIR . "/language/" . $lang . ".php");

if ($ajax)
  {
  if ($ajax == 'current')
    {
    $output.= '<center>';
    $output.= '<b>' . $language['stats_this_day'] . ':</b><br>';
    $output.= '<a href="statsdaybuilddata.php?periodtype=day&serverid=' . $serverid . '&period=' . date("Y-m-d") . '&imagetype=big"  onclick="return hs.expand(this)"><img src="statsdaybuilddata.php?periodtype=day&serverid=' . $serverid . '&period=' . date("Y-m-d") . '" alt="' . $language['stats_of_day'] . '" title="' . $language['clicktoenlarge'] . '" /></a>';
    $output.= '<center><font size="1" color="gray">&uArr; ' . $language['clicktoenlarge2'] . ' &uArr;</font><center>';
    $output.= '<br>';
    $output.= '<b>' . $language['stats_of_week'] . ':</b><br>';
    $output.= '<a href="statsdaybuilddata.php?periodtype=week&serverid=' . $serverid . '&period=' . date("Y-m-d") . '&priority=current&imagetype=big"  onclick="return hs.expand(this)"><img src="statsdaybuilddata.php?periodtype=week&serverid=' . $serverid . '&period=' . date("Y-m-d") . '&priority=current" alt="' . $language['stats_of_week'] . '" title="' . $language['clicktoenlarge'] . '" /></a>';
    $output.= '<center><font size="1" color="gray">&uArr; ' . $language['clicktoenlarge2'] . ' &uArr;</font><center>';
    $output.= '<br>';

    $output.= '<b>' . $language['stats_of_month'] . ':</b><br>';
    $output.= '<a href="statsdaybuilddata.php?periodtype=month&serverid=' . $serverid . '&period=' . date("Y-m-d") . '&priority=current&imagetype=big"  onclick="return hs.expand(this)"><img src="statsdaybuilddata.php?periodtype=month&serverid=' . $serverid . '&period=' . date("Y-m-d") . '&priority=current" alt="' . $language['stats_of_month'] . '" title="' . $language['clicktoenlarge'] . '" /></a>';
    $output.= '<center><font size="1" color="gray">&uArr; ' . $language['clicktoenlarge2'] . ' &uArr;</font><center>';
    $output.= '<br>';
    $output.= '</center>';
    echo $output;
    exit();
    }
  if ($ajax == 'currellast')
    {
    $output.= '<center>';
    $output.= '<b>' . $language['stats_of_day_rel_last_day'] . ':</b><br>';
    $output.= '<a href="statsdaybuilddata.php?periodtype=day&serverid=' . $serverid . '&serveridrel=' . $serverid . '&period=' . date("Y-m-d") . '&precision=relatively&periodrel=' . getPastDayDate(date("Y-m-d")) . '&imagetype=big"  onclick="return hs.expand(this)"><img src="statsdaybuilddata.php?periodtype=day&serverid=' . $serverid . '&serveridrel=' . $serverid . '&period=' . date("Y-m-d") . '&precision=relatively&periodrel=' . getPastDayDate(date("Y-m-d")) . '" alt="' . $language['stats_of_day_rel_last_day2'] . '" title="' . $language['clicktoenlarge'] . '" /></a>';
    $output.= '<center><font size="1" color="gray">&uArr; ' . $language['clicktoenlarge2'] . ' &uArr;</font><center>';
    $output.= '<br>';

    $output.= '<b>' . $language['stats_of_week_rel_last_week'] . ':</b><br>';
    $output.= '<a href="statsdaybuilddata.php?periodtype=week&serverid=' . $serverid . '&serveridrel=' . $serverid . '&period=' . date("Y-m-d") . '&precision=relatively&periodrel=' . getPastWeekDate(date("Y-m-d")) . '&imagetype=big"  onclick="return hs.expand(this)"><img src="statsdaybuilddata.php?periodtype=week&serverid=' . $serverid . '&serveridrel=' . $serverid . '&period=' . date("Y-m-d") . '&precision=relatively&periodrel=' . getPastWeekDate(date("Y-m-d")) . '" alt="' . $language['stats_of_week_rel_last_week2'] . '" title="' . $language['clicktoenlarge'] . '" /></a>';
    $output.= '<center><font size="1" color="gray">&uArr; ' . $language['clicktoenlarge2'] . ' &uArr;</font><center>';
    $output.= '<br>';

    $output.= '<b>' . $language['stats_of_month_rel_last_month'] . ':</b><br>';
    $output.= '<a href="statsdaybuilddata.php?periodtype=month&serverid=' . $serverid . '&serveridrel=' . $serverid . '&period=' . date("Y-m-d") . '&precision=relatively&periodrel=' . getPastMonthDate(date("Y-m-d")) . '&priority=all&imagetype=big"  onclick="return hs.expand(this)"><img src="statsdaybuilddata.php?periodtype=month&serverid=' . $serverid . '&serveridrel=' . $serverid . '&period=' . date("Y-m-d") . '&precision=relatively&periodrel=' . getPastMonthDate(date("Y-m-d")) . '&priority=all" alt="' . $language['stats_of_month_rel_last_month2'] . '" title="' . $language['clicktoenlarge'] . '" /></a>';
    $output.= '<center><font size="1" color="gray">&uArr; ' . $language['clicktoenlarge2'] . ' &uArr;</font><center>';
    $output.= '<br>';
    $output.= '</center>';
    echo $output;    
    exit();
    }
  if ($ajax == 'currentonoff')
    {
    $output.='<br><center>';

    $output.='<br><b>' . $language['onoffservermonth'] . ':</b><br>';
	
    $output.= '<a href="statsdaybuilddata.php?charttype=onoff&periodtype=month&serverid=' . $serverid . '&period=' . date("Y-m-d") . '&priority=current&imagetype=big"  onclick="return hs.expand(this)">	<img src="statsdaybuilddata.php?charttype=onoff&periodtype=month&serverid=' . $serverid . '&period=' . date("Y-m-d") . '&priority=current" alt="' . $language['onoffservermonth2'] . '" title="' . $language['clicktoenlarge'] . '" /></a>';
    $output.= '<center><font size="1" color="gray">&uArr; ' . $language['clicktoenlarge2'] . ' &uArr;</font><center>';
    $output.='</center>';
    echo $output;
    exit();
    }  
  
  exit();    
  }


$header  =  file_get_contents (ROOT_DIR . '/template/' . $lang . '/header.html');
$footer  =  file_get_contents (ROOT_DIR . '/template/' . $lang . '/footer.html');
$menu    =  file_get_contents (ROOT_DIR . '/template/' . $lang . '/menu.html');

$header_script1 = '<script language="javascript" src="/ajax/ajax_init.js"></script>';
$header_script2 = '<script language="javascript" src="/ajax/ajax_serverdetail.js"></script>';
$header_script3 = '<script type="text/javascript" src="/template/highslide/highslide.js"></script>
<link rel="stylesheet" type="text/css" href="/template/highslide/highslide.css" />
<!--[if lt IE 7]>
<link rel="stylesheet" type="text/css" href="/template/highslide/highslide-ie6.css" />
<![endif]-->';

$header_script4 = "<script type=\"text/javascript\">
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
$javascript.= $header_script1 . $header_script2 . $header_script3 . $header_script4;
$db_sql = New db_sql($config['dbName'],$config['hostname'],$config['dbUname'],$config['dbPasswort']);

$query = $db_sql->sql_query("
                              SELECT id, catid, name, description, url 
                              FROM " . $config['servers_table'] . " 
                              WHERE enable=1 
                              AND id=". $serverid . " 
                              LIMIT 1;"
                           );
			     
if (!$query1 = $db_sql->fetch_array ($query))
  {
  header("Location: " . $config['serverhost'] . "index.php");
  exit;
  }  
  
$query = $db_sql->sql_query("
                              SELECT name 
                              FROM " . $config['category_table'] . " 
                              WHERE id=" . $query1['catid'] ." 
                              LIMIT 1;"
                           );
		     
$query2 = $db_sql->fetch_array ($query);
$title = $language['serverstatstitle']  . $query1['name'];
$main = '<h1>monitor<span>.global</span> -> Cтатистика</h1><br>';

$main.= '<center><b><h2>' . $query1['name'] . '</h2></b></center>'; 
 
$main.=  $language['category'] . ': <a class="decor" href="serverlist.php?catid=' . $query1['catid'] . '">' . $query2['name'] . '</a><br>';

$main.= $language['description'] . ': ' . $query1['description'] . '<br>';

$main.= 'Url: <a class="decor" href="' . $query1['url'] . '">' . $query1['url'] . '</a><br>';

$monthData = returnMonthData(date("Y-m-d"));

$query = $db_sql->sql_query("
                              SELECT max, average, date 
                              FROM " . $config['statslog_table'] . " 
                              WHERE serverid = '" . $query1['id'] ."' 
                              AND date < '" . $monthData['monthendrel'] . "' 
                              AND date >= '" . $monthData['monthbeginrel'] . "'
                              ORDER BY date DESC 
                              LIMIT 32;"
                           );
		     
$i    	    = 1;
$max_week   = 0;
$max_month  = 0; 
$sum_week   = 0;
$j_week     = 0;
$sum_month  = 0;
$j_month    = 0;

while ($query3 = $db_sql->fetch_array($query))
  {
  if ($i<=3)
    {
    if ($query3['max'] && !$max_day)
      {$max_day = $query3['max'];}
    if ($query3['average'] && !$average_day)
      {$average_day = $query3['average'];}
    }
  if ($i<=7)
    {
    // max
    if($query3['max'] > $max_week)
      {$max_week = $query3['max'];}
    // average
    if($query3['average'])
        {
        $sum_week+=$query3['average'];
        $j_week++;
        }
    }
    // max
    if($query3['max'] > $max_month)
      {$max_month = $query3['max'];}
    // average
    if($query3['average'])
      {
      $sum_month+=$query3['average'];
      $j_month++;
      }    
  $i++;
  }
$average_week = (int)($sum_week/$j_week); 
$average_month = (int)($sum_month/$j_month);

$main.= sprintf($language['averageonlinelastday'],$average_day,$average_week,$average_month);

//$main.= 'Средний онлайн за последний день - <font color="green"><b>' . $average_day . '</b></font>; неделю - <font color="green"><b>' . $average_week . '</b></font>; месяц - <font color="green"><b>' . $average_month . '</b></font><br>';


$main.= sprintf($language['maxonlinelastday'],$max_day,$max_week,$max_month);

//$main.= 'Максимальный онлайн за последний день - <font color="green"><b>' . $max_day . '</b></font>; неделю - <font color="green"><b>' . $max_week . '</b></font>; месяц - <font color="green"><b>' . $max_month . '</b></font><br>';

$query = $db_sql->sql_query("
                              SELECT max, date 
                              FROM " . $config['statslog_table'] . " 
                              WHERE serverid=" . $query1['id'] ." 
                              ORDER BY max DESC, date ASC 
                              LIMIT 1;"
                           );
			     
$query3 = $db_sql->fetch_array ($query);

$main.= sprintf($language['maxofmaxserver'],$query3['date'],$query3['max']);

//$main.= 'Максимальный онлайн за весь период был зарегистрирован  <b>' . $query3['date'] . '</b> и составил - <font color="green"><b>' . $query3['max'] . '</b></font><br>';
          
$main.= $language['startmonitorforserver'] . ': <b>' . getLogStartTime($query1['id']) . '</b><br>';

$main.= '<br>';

$main.= '<div id="msg" ><a onClick="javascript:server_detail_current(' . $serverid . ')" class="decor">' . $language['dataofthisdayweekmonth'] . '</a></div>';

$main.= '<div id="msg2"><a onClick="javascript:server_detail_currellast(' . $serverid . ')" class="decor">' . $language['dataofthisrellastdayweekmonth'] . '</a></div>';

$main.= '<div id="msg3"><a onClick="javascript:server_detail_currentonoff(' . $serverid . ')" class="decor">' . $language['dataonoffoflastdayweekmonth'] . '</a></div>';

$main.= '<div id="msg4"><a target="_blank" href="sandbox.php" class="decor">' . $language['sandboxlinktext'] . '</a></div>';

$main.= '<div id="sandbox"></div><div id="current"></div><div id="currellast"></div></div><div id="currentonoff"></div>';

$main.= '<div class="divider"></div><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>';


$loadinfo = loadinfo($time_start);
$header   = str_replace("{JAVASCRIPT}",$javascript,$header);
$header   = str_replace("{TITLE}",$title,$header);
$footer   = str_replace("{LOADINFO}",$loadinfo['full'],$footer);
$output   = $header . $menu . $main . $footer;

echo $output;

$db_sql->closeSQL();

?>