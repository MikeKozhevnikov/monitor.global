<?php

/*

=========================================
 Monitor Global
-----------------------------------------
 2007-2009, Mike Kozhevnikov
=========================================
 File: statsdaybuilddata.php
=========================================
 Назначение: Скрипт отрисовки графиков
=========================================

*/

// сообщать о всех ошибках
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

error_reporting(55);
// определяем корень нашего сайта
define ( 'ROOT_DIR', dirname ( __FILE__ ) );

// подключение конфигурационного файла
include_once(ROOT_DIR . "/include/config.inc.php");
// подключение класса доступа к бд
include_once(ROOT_DIR . "/include/class.db.php");
// подключение библиотеки функций
include_once(ROOT_DIR . "/include/functions.inc.php");
// подключение класса записи логов в файл
include_once(ROOT_DIR . "/include/class.log.php");

// делаем штамп времени, для последующего просчета скорости выполнения скрипта
$time_start = getmicrotime();

// если в конфиге прописан true для лог доступа на jabber - отправляем лог на jabber
if ($config['accesslogjabber'])
  {
  $jabberlogger = new jabberLogger("access");
  unset($jabberlogger);
  }

// если в конфиге прописан true для лог доступа в текстовый файл - пишем лог
if ($config['accesslogtxt'])
  {
  $logger = new txtLogger("access");
  unset($logger);
  }

// определяем переданные GET параментры   // возможные варианты           | где используется    | что означает
$serverid     =  $_GET['serverid']; 	    // '1'-'1000' 		              | everywhere          | ид первого сервера
$serveridrel  =  $_GET['serveridrel']; 	  // '1'-'1000' 		              | only relativity     | ид сравн. сервера
$periodtype   =  $_GET['periodtype'];     // 'day' or 'week' or 'month'   | everywhere          | тип периода: день\неделя\месяц
$imagetype    =  $_GET['imagetype']; 	    // 'big' or 'small' 		        | everywhere          | график: маленький\большой
$period       =  $_GET['period'];   	    // for example '2009-08-03' 	  | everywhere          | период для первого сервера
$periodrel    =  $_GET['periodrel'];   	  // for example '2009-08-03' 	  | only relativity     | период для сравн. сервера
$precision    =  $_GET['precision'];      // 'absolute'  or 'relatively'  | everywhere          | сравнить?
$valueofdata  =  $_GET['valueofdata'];	  // 'mean' or 'peak' 		        | only !day           | значения: средние\максимальные
$priority     =  $_GET['priority'];   	  // 'current' or 'all' 	        | only !day           | период заканчивается этой датой?
$charttype    =  $_GET['charttype'];      // 'online'  'onoff'            | only charttype      | онлайн или онлайн\оффлайн серв.
$libtype      =  $_GET['libtype'];        // 'php' or 'flash'             | everywhere          | тип графика: пхп или флэш
$givedata     =  $_GET['givedata'];       // "true" or "false"            | only libtype=flash  | выдать данные или график?

// проверяем все GET параметры
$serverid     =  intval($serverid);
$serveridrel  =  intval($serveridrel);

if ($givedata)
  {
  // если не "false" и  "true", делаем его NULL
  if ($givedata != "false" && $givedata != "true") 
    {$givedata = false;}
  else
    {
    // если "false" - делаем булевский false
    if ($givedata == "false")
      {$givedata = false;} 
    // если "true" - делаем булевский true
    if ($givedata == "true")
      {$givedata = true;}   
    }  
  }

if ($libtype)
  {
  if ($libtype != 'php' && $libtype != 'flash') 
    {$libtype = NULL;}
  }

if ($priority)
  {
  if ($priority != 'current' && $priority != 'all') 
    {$priority = NULL;}
  }
  
if ($precision)
  {  
  if ($precision != 'absolute' && $precision != 'relatively')
    {$precision = NULL;}
  }
  
if ($valueofdata)
  {  
  if ($valueofdata != 'mean' && $valueofdata != 'peak')
    {$valueofdata = NULL;}
  }  
  
if ($imagetype)
  {
  if ($imagetype != 'big' && $imagetype != 'small')
    {$imagetype = NULL;}
  }  

if ($periodtype)
  {
  if ($periodtype != 'day' && $periodtype != 'week' && $periodtype != 'month')
    {$periodtype = NULL;}       
  }
    
if ($period)
  {
  $period = substr($period, 0, 10);
  if ($period{4}!='-')
    {$period = NULL;}        
  }
  
if ($periodrel)
  {
  $periodrel = substr($periodrel, 0, 10);
  if ($periodrel{4}!='-')
    {$periodrel = NULL;}
  }

if ($charttype)
  {
  if ($charttype != 'online' && $charttype != 'onoff')
    {$charttype = NULL;}
  }  


// инициализируем подключение к базе данных
$db_sql = New db_sql($config['dbName'],$config['hostname'],$config['dbUname'],$config['dbPasswort']);

// проверяем язык и создаем cookies, если это необходимо
$lang = language_cookie();

// подключаем языковой файл
include_once(ROOT_DIR . "/language/" . $lang . ".php"); 

// производим начальные настройки для флэш графиков
if ($libtype == 'flash')
  {
  $flashWBig   =  980; 
  $flashHBig   =  322;
  $flashWSmall =  700;
  $flashHSmall =  280;
  $on_show     =  '"on-show":{"type": "grow-up", "cascade":0.6, "delay":0.5}';
  $on_show2    =  '"on-show":{"type": "grow-up", "cascade":0.6, "delay":1.5}';
  /*
   Варианты эффектов:
   "on-show":{"type": "pop", "delay":0.5, "cascade":0.6},
   "on-show":{"type": "grow-up"},
   "on-show":{"type": "drop", "cascade":0.6, "delay":0.5}
   "on-show":{"type": "explode", "cascade":0.6, "delay":0.5}
   "on-show":{"type": "fade-in", "cascade":0.6, "delay":0.5}
   "on-show":{"type": "mid-slide", "cascade":0.6, "delay":0.5}
   "on-show":{"type": "pop-up", "cascade":0.6, "delay":0.5}
   "on-show":{"type": "shrink-in", "cascade":1.6, "delay":0.2}
   "on-show":{"type": "pop-up", "cascade":1, "delay":0.5},
   "on-show":{"type": "explode", "cascade":0.6, "delay":0.5},   
  */
  }

// если тип графика - данные об онлайне\оффлайне сервера
if ($charttype == 'onoff')
  {
  // подключаем библиотеки jpgraph
  require_once (ROOT_DIR . "/include/jpgraph/jpgraph.php");
  require_once (ROOT_DIR . "/include/jpgraph/jpgraph_gantt.php");
  // производим отбор необходимых данных из базы за:
  // день
  if ($periodtype == 'day')
    {
    $query1 = $db_sql->sql_query("
									SELECT * 
									FROM " . $config['statslog_table'] . " 
									WHERE serverid=" . $serverid . " 
									AND date='" . $period . "' 
									LIMIT 1;"
								);
    }	
  // неделю  			    
  if ($periodtype == 'week')
    {
    $WeekData = returnWeekData($period);
    if ($priority == 'current')
      {
      $query1 = $db_sql->sql_query("
							       SELECT * 
									   FROM " . $config['statslog_table'] . " 
									   WHERE serverid=" . $serverid . " 
									   AND date<='" . $WeekData['weekendrel'] . "' 
									   AND date>='" . $WeekData['weekbeginrel'] . "' 
									   ORDER BY date DESC
									   LIMIT 7;"
									   );    
      }
    
  
    if ($priority == 'all')
      {
      $query1 = $db_sql->sql_query("
									SELECT * 
									FROM " . $config['statslog_table'] . " 
									WHERE serverid=" . $serverid . " 
									AND date<='" . $WeekData['weekendabs'] . "' 
									AND date>='" . $WeekData['weekbeginabs'] . "' 
									ORDER BY date DESC
									LIMIT 7;"
									);
      }
    }  
  // месяц  
  if ($periodtype == 'month') 
    {
    $MonthData = returnMonthData($period);
    if ($priority == 'current')
      {
      $query1 = $db_sql->sql_query("
									SELECT * 
									FROM " . $config['statslog_table'] . " 
									WHERE serverid=" . $serverid . " 
									AND date<='" . $MonthData['monthendrel'] . "' 
									AND date>='" . $MonthData['monthbeginrel'] . "' 
									ORDER BY date DESC
									LIMIT 31;"
									);    
      }
    if ($priority == 'all')
      {
      $query1 = $db_sql->sql_query("
									SELECT * 
									FROM " . $config['statslog_table'] . " 
									WHERE serverid=" . $serverid . " 
									AND date<='" . $MonthData['monthendabs'] . "' 
									AND date>='" . $MonthData['monthbeginabs'] . "' 
									ORDER BY date DESC
									LIMIT 31;"
									);
      }
    }
  
  // проверяем, если ли данные для вывода на график
  if($db_sql->num_rows($query1) == 0)
    $content = false;
  else
    $content = true; 
     
  $i=0; $j=0;
  while ($query = $db_sql->fetch_array ($query1))
    {
    $switch = false;
    $query['time24']=-1;
    for($k=0;$k<=24;$k++)
      {
      if ($query['time' . $k]>=0)
        {if (!$switch) {$start = $k . ':00'; $switch = true;}}
      else
        {
        if ($switch)
          {
	  if ($k == 24)
	    {$stop = 23 . ':59';}
	  else
	    {$stop = $k. ':00';}
        
	  $switch = false;
	  $arrayX[$j][0] = $i;
	  $arrayX[$j][1] = $query['date'];
	  $arrayX[$j][2] = $start;
	  $arrayX[$j][3] = $stop;    

	  ++$j;
 	  }
        }
      } 
    ++$i;
    }
    // определяем имя сервера
    $servername = get_servername($serverid);  
  
  $height = 0;
  if ($imagetype=='big')
    $width = 1200;
  else
    $width = 700;
  $graph = new GanttGraph($width,$height);
  $graph->SetMarginColor('blue:1.7');
  $graph->SetColor('white');
 
  $graph->SetBackgroundGradient('#F0F0F0','#F0F0F0',GRAD_HOR,BGRAD_MARGIN);
  $graph->scale->hour->SetBackgroundColor('lightyellow:1.5');
  $graph->scale->hour->SetFont(FF_FONT1);
  $graph->scale->day->SetBackgroundColor('lightyellow:1.5');
  $graph->scale->day->SetFont(FF_FONT1,FS_BOLD);
 
  switch ($periodtype) 
    {
    case 'month':
      $titledate = $language['for_a_month'] . $MonthData['monthandyear'];
      break;
    case 'week':
      if ($priority == 'current')
        {
	$titledate = $language['for_a_week'] . 
		     $WeekData['weekbeginendrelday'] . "." . 
		     $WeekData['weekbeginendrelmonth'] . "." . 
		     $WeekData['weekbeginendrelyear'];
        }
      if ($priority == 'all')
        {
        $titledate = $language['for_a_week'] . 
		     $WeekData['weekbeginendabsday'] . "." . 
		     $WeekData['weekbeginendabsmonth'] . "." . 
		     $WeekData['weekbeginendabsyear'];
        }
      break;
    case 'day':
      $titledate = $language['for_a_day'] . dateToNormal($period);
      break;
    }    
  
  $txt4 = new Text();
  $txt4->SetPos(90,6);
  $txt4->Set(WinToHTMLChars($servername . $language['online_offline1'] . $titledate));
  $txt4->SetFont(FF_VERDANA,FS_NORMAL,10);
  $txt4->SetColor('black');
  $graph->Add($txt4);
 
  $graph->ShowHeaders(GANTT_HHOUR);
 
  $graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);
  $graph->scale->week->SetFont(FF_FONT1);
  $graph->scale->hour->SetIntervall(1);
 
  $graph->scale->hour->SetStyle(HOURSTYLE_H24);
  $graph->scale->day->SetStyle(DAYSTYLE_SHORTDAYDATE3);

  if($db_sql->num_rows($query1) > 0)
    {$data = $arrayX;}
  else
    {
    $data = array(
    array(0,"        ", "00:00","00:00")); 
    }  

  $graph->hgrid->Show(true);
  $graph->hgrid->SetRowFillColor('gray@0.7','#E4DCCE@0.6');
 

    for($i=0; $i<count($data); ++$i) 
      {
      $bar = new GanttBar($data[$i][0],$data[$i][1],$data[$i][2],$data[$i][3],"",10);
      $bar->SetPattern(3,"blue@0.9");
      $bar->SetFillColor("blue@0.9");
      $graph->Add($bar);
      }
   
  if(!$content)
    {
    $txt3 = new Text();
    if ($imagetype=='big')
      {$txt3->SetPos(532,53);}
    else
      {$txt3->SetPos(232,53);}
    $txt3->Set(WinToHTMLChars($language['no_data_to_graph']));
    $txt3->SetFont(FF_VERDANA,FS_NORMAL,12);
    $txt3->SetColor('black');
    $graph->Add($txt3);
    }
      
  // находим время загрузки страницы и кол-во SQL запросов
  $loadinfo = loadinfo($time_start); 

  $txt1 = new Text();
  if ($imagetype=='big')
    {$txt1->SetPos(1062,7);}
  else
    {$txt1->SetPos(562,7);}
  $txt1->Set(WinToHTMLChars($loadinfo['image']));
  $txt1->SetFont(FF_VERDANA,FS_NORMAL,7);
  $txt1->SetColor('black');
  $graph->Add($txt1);

  $txt2 = new Text();
  switch ($periodtype) 
    {
    case 'month':
      $posY = 63 + $db_sql->num_rows($query1)/3*19;
      break;
    case 'week':
      $posY = 63;
      break;
    case 'day':
      $posY = 7 ;
      break;
    }      
  if ($imagetype=='big')
    {$posX = 1187;}
  else
    {$posX = 687;}
      
  if (!$content)
    $txt2->SetPos($posX,7);
  else
    $txt2->SetPos($posX,$posY);  
  $txt2->Set($language['monitorglobaltext1'] . $config['verison']);
  $txt2->SetFont(FF_VERDANA,FS_NORMAL,7);
  $txt2->SetColor('black');
  $txt2->SetAngle(90);
  $graph->Add($txt2);

  // генерируем картинку и выдаем в stream
  $graph->Stroke();
  
  // если в конфиге стоит true для записи логов longbuild - пишем лог
  if($config['longbuildtxtlog'])
    {
    $logger = new txtLogger("longbuild");
    unset($logger);  	
    }
  exit;
  }

//
//
// если выбранный период - ДЕНЬ
//
//

if ($periodtype == 'day')
  {
  
  $query1 = $db_sql->sql_query("SELECT * 
  	    			FROM " . $config['statslog_table'] . " 
  				WHERE date='" . $period . "' 
				AND serverid = '" . $serverid . "' 
  				LIMIT 1;");
  				
  $query = $db_sql->fetch_array($query1);
  
 				
  				
  //echo 'Пошло на if day';
 $max = 0;
 for ($i=0;$i<=23;$i++)
  {
  if (($query[time . $i]) == -1 || ($query[time . $i] == -2))
    {$arrayX[$i] = 0;}
  else
    {
    $value = intval($query[time . $i]);
    $arrayX[$i] = $value;
    }
  if ($query[time . $i] > $max )
    {$max = $query[time . $i];}  
  }
  if ($precision == 'relatively')
    {

    if ($serveridrel)
      $queryserverid = $serveridrel;
    else
      $queryserverid  = $serverid;    
    
     $query2 = $db_sql->sql_query("SELECT * 
     	       			   FROM " . $config['statslog_table'] . " 
				   WHERE date='" . $periodrel . "' 
				   AND serverid = '" . $queryserverid . "' 
				   LIMIT 1;");
				   
     $query3 = $db_sql->fetch_array($query2); 
    for ($i=0;$i<=23;$i++)
      {
      if (($query3[time . $i]) == -1 || ($query3[time . $i] == -2))
        $arrayX2[$i] = 0;
      else
        $arrayX2[$i] = $query3[time . $i];
      }

    // определяем имена серверов
    // если сервер один и тот же
    if ($serverid == $serveridrel)
      {
      $servername = get_servername($serverid);
      }
    // если их два
    else
      {
      $queryName = $db_sql->sql_query("
										SELECT id,name  
										FROM " . $config['servers_table'] . " 
										WHERE id='" . $serverid . "' 
										OR id='" . $serveridrel . "'
										LIMIT 2;"
										);
  				     
      for($i=0;$i<=1;$i++)
        {
        $queryName2 = $db_sql->fetch_array($queryName);          
        if ($serverid == $queryName2['id'])
          {$servername = $queryName2['name'];}
        elseif ($serveridrel == $queryName2['id'])  
	  {$servernamerel = $queryName2['name'];}
        }
      unset($queryName, $queryName2);
      }    
    
    if ($libtype == 'flash')
      {
      
      if ($givedata == false)
        {
        // подключаем библиотеку OFC
	require_once(ROOT_DIR . '/include/ofc/open-flash-chart-object.php');
        $requesturl = $_SERVER["PHP_SELF"];
        $requesturl.= '?' . $_SERVER["QUERY_STRING"]. '&givedata=true';
        if ($imagetype=='big')
          {echo open_flash_chart_object_str($flashWBig,$flashHBig,$requesturl,false,'');}
	else
          {echo open_flash_chart_object_str($flashWSmall,$flashHSmall,$requesturl,false,'');} 
	exit();
	}
      
      $arrayX_text        =   return_arrayX_to_text($arrayX);
      $arrayX2_text       =   return_arrayX_to_text($arrayX2);
      $loadinfo           =   loadinfo($time_start);
      $loadtime_title     =   WinToHTMLChars($loadinfo['image']);
      unset($loadinfo);
      if ($serverid == $serveridrel)
        {
	$title = WinToHTMLChars($servername . $language['stats_for_day']) . 
	         dateToNormal($period) . 
		 WinToHTMLChars(' vs ') . 
		 dateToNormal($periodrel);
		 
        $online_arrayX_title  =   WinToHTMLChars(  '<br>' . $servername . '<br>' . 
			      	  		   dateToNormal($period) . 
						   "<br>" . $language['onlinetext1'] . "#val#" . 
						   $language['mentext1']       );
						   
        $online_arrayX2_title =   WinToHTMLChars(  '<br>' . $servername . '<br>' . 
			      	  		   dateToNormal($periodrel) . 
						   "<br>" . $language['onlinetext1'] . "#val#". 
						   $language['mentext1']       );
        $arrayX_legend = dateToNormal($period);
        $arrayX2_legend = dateToNormal($periodrel);
	}
      else                                                         
        {                                                         
	$title = WinToHTMLChars($language['stats_for_day2'] . $servername) . ' (' .
	         dateToNormal($period) . ') vs ' . 
		 WinToHTMLChars($servernamerel) . ' (' . 
		 dateToNormal($periodrel) . ')';
		 
        $online_arrayX_title  =   WinToHTMLChars(  '<br>' . $servername . '<br>' . 
			      	  		   dateToNormal($period) . 
						   "<br>" . $language['onlinetext1'] . "#val#" . 
						   $language['mentext1']       );
        $online_arrayX2_title =   WinToHTMLChars(  '<br>' . $servernamerel . '<br>' . 
			      	  		   dateToNormal($periodrel) . 
						   "<br>" . $language['onlinetext1'] . "#val#" . 
						   $language['mentext1']       );
						   
	$arrayX_legend = WinToHTMLChars($servername) . ' (' . dateToNormal($period). ')';
        $arrayX2_legend = WinToHTMLChars($servernamerel) . ' (' . dateToNormal($periodrel). ')';	
	}  

      $version_title        =   $language['monitorglobaltext1'] . $config['verison'];
      $hour_title           =   returnDayArray('JAVA',true);
      $bar_colour           =  '#D54C78';
      $bar2_colour          =  '#5555ff';
      $x_axis_colour        =  '#909090'; 
      $bg_colour            =  '#EFEFEF';//'#F8F8F8';//'#F8F8D8'; 
      $y_axis_colour        =  '#5555f'; 
      $y_axis_grid_colour   =  '#DDDDDD';//'#eeeeee';
      $x_axis_grid_colour   =  '#DDDDDD';//'#eeeeee';
      
$output = '{ 
"title": { "text": "' . $title . '"}, 
"elements": 
[
 	    {
	     "type": "bar_3d", 
	     "tip": "' . $online_arrayX_title . '", 
	     "colour": "' . $bar_colour . '", 
	     "text":      "' . $arrayX_legend . '",	  
	     "line-style": { "style": "dash", "on": 4, "off": 4 }, 
	     "dot-style": { "type": "hollow-dot", "width": 1, "size": 2 }, 
	     "values": [ ' . $arrayX_text . ' ], 
	     "fill-alpha": 0.5, 
	     "width": 5, 
	     ' . $on_show . ' 
	    },
	    
	    {
	     "type": "bar_3d", 
	     "tip": "' . $online_arrayX2_title . '", 
	     "colour": "' . $bar2_colour . '",
	     "text":      "' . $arrayX2_legend . '",	      
	     "line-style": { "style": "dash", "on": 4, "off": 4 }, 
	     "dot-style": { "type": "hollow-dot", "width": 1, "size": 2 }, 
	     "values": [ ' . $arrayX2_text . ' ], 
	     "fill-alpha": 0.5, 
	     "width": 5, 
	     ' . $on_show2 . ' 
	    },
	    

	      	{
			"type":      "line",
			"colour":    "#736AFF",
			"font-size": 1,
			"width":     1,
			"dot-style": {
				"type":"solid-dot", "colour":"' . $bg_colour . '", "dot-size": 20,
				"tip":"' . $loadtime_title . '" },
			"values" : [{"x":21, "y": "' . intval($max*1.3) . '" } ]
		},
		{
			"type":      "line",
			"colour":    "#736AFF",
			"font-size": 1,
			"width":     1,
			"dot-style": {
				"type":"solid-dot", "colour":"' . $bg_colour . '", "dot-size": 20,
				"tip":"' . $version_title . '" },
			"values" : [{"x":23, "y": "' . intval($max*1.3) . '" } ]
		}	
		
],

"bg_colour": "' . $bg_colour . '",
"x_axis": { "3d": 5, 
	    "colour": "' . $x_axis_colour . '", 
	    "grid-colour": "' . $x_axis_grid_colour . '", 
	    "offset": true,
  
	        "labels": {

      "rotate": 0,

      "labels": [ ' . $hour_title . ' ]
    } 
	  }, 

"y_axis": { "min": 0, 
	    "max": ' . intval($max*1.1) . ', 
	    "steps": 1, 
	    "grid-colour": "' . $y_axis_grid_colour . '", 
	    "colour": "' . $y_axis_colour . '" 
	  }, 

  "tooltip":{
    "mouse": 2,
    "stroke":1
  } 	  
}';    
  /*
  	  Работает, но не очень
  	  	    {
	  "type":"tags",
	  "font":"Verdana",
	  "font-size":10,
	  "colour":"#000000",
	  "pad-x":0,
	  "pad-y":100,
	  "rotate":0,
	  "align-x":"left",                    
	  "align-y":"center",
	  "text":"$#y#",
	  "values":[
		    {"x":23,"y":2250, "text":"'.$loadtime_title.'" }
		    ]
	},
	    
	    Работает!!!!
	  {
			"type":      "line",
			"colour":    "#736AFF",
			"font-size": 1,
			"width":     1,
			"dot-style": {
				"type":"solid-dot", "colour":"' . $bg_colour . '", "dot-size": 6,
				"tip":"#val#<br>#x_label#" },
			"values" : [{"x":23, "y": "' . intval($max*1.1) . '" } ]
		}
  */     

    // выводим data для swf файла
    echo $output;
    
    // если в конфиге стоит true для записи логов longbuild - пишем лог  
    if ($config['longbuildtxtlog'])
      {
      $logger = new txtLogger("longbuild");
      unset($logger);  	
      }
    }
    else
    {  
    
	// подключаем библиотеки pChart
	include_once(ROOT_DIR . "/include/pChart/pChart/pData.class");   
	include_once(ROOT_DIR . "/include/pChart/pChart/pChart.class");  
	
	// Dataset definition 
         $DataSet = new pData;
	 $DataSet->AddPoint($arrayX2,"Serie1");
	 $DataSet->AddPoint($arrayX,"Serie2");
	
         $DayArray = returnDayArray();
	 $DataSet->AddPoint($DayArray,"Serie3");

	 $DataSet->SetAbsciseLabelSerie("Serie3");
	 $DataSet->AddSerie("Serie1");
	 $DataSet->AddSerie("Serie2"); 
	 $DataSet->SetSerieName($periodrel,"Serie1");
	 $DataSet->SetSerieName($period,"Serie2");

	
	 // Initialise the graph
	 
	 if ($imagetype == 'big')
	   {
	   $Test = new pChart(1200,394);
	   $Test->setGraphArea(85,51,1165,342);
	   $Test->drawFilledRoundedRectangle(7,7,1192,386,5,240,240,240);
	   $Test->drawRoundedRectangle(8,8,1191,385,8,394,394,394);
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",11);
	   }
	 else
	   {
	   $Test = new pChart(700,230);
	   $Test->setGraphArea(50,30,680,200);
	   $Test->drawFilledRoundedRectangle(7,7,693,223,5,240,240,240);
	   $Test->drawRoundedRectangle(5,5,695,225,5,230,230,230);
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",8);
	   }  

	 $Test->drawGraphArea(255,255,255,TRUE);
	 $Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2,TRUE);
	 $Test->drawGrid(4,TRUE,230,230,230,50);
	
	 if ($imagetype == 'big')
	   {
	   	 // Draw the 0 line
	 $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",10);
	 $Test->drawTreshold(0,143,55,72,TRUE,TRUE);
	   }
	 else
	   {
	   	 // Draw the 0 line
	 $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",6);
	 $Test->drawTreshold(0,143,55,72,TRUE,TRUE);
	   }  
	 
         if ($serverid == $serveridrel)
           {
	   $title = WinToHTMLChars($servername . $language['stats_for_day']) . dateToNormal($period) . WinToHTMLChars(' vs ') . dateToNormal($periodrel);
	   }
         else
	   {
	   $title = WinToHTMLChars($language['stats_for_day2'] . $servername) . ' (' . dateToNormal($period) . ') vs ' . WinToHTMLChars($servernamerel) . ' (' . dateToNormal($periodrel) . ')';
	   }  
	
	 // Draw the bar graph
	 $Test->drawBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),TRUE);
	 
	 if ($imagetype == 'big')
	   {
           $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",13);
	   $Test->writeValues($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie1");
	   $Test->writeValues($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie2");
	   // Finish the graph
           $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",13);
	   $Test->drawLegend(1041,60,$DataSet->GetDataDescription(),255,255,255);
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",17);
	   $Test->drawTitle(85,37,$title,85,85,85,994);	   
	   }
	 else
	   {
           $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",9); 
	   $Test->writeValues($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie1");
	   $Test->writeValues($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie2");
	   // Finish the graph
           $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",8);
	   $Test->drawLegend(596,35,$DataSet->GetDataDescription(),255,255,255); 
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",10);
	   
	   $Test->drawTitle(50,22,$title,50,50,50,580);	   
	   } 
	 
	 // находим время загрузки страницы и кол-во SQL запросов
	 $loadinfo = loadinfo($time_start);
	 if ($imagetype == 'big')
	   {
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",12);
	   $Test->drawTitle(994,35,WinToHTMLChars($loadinfo['image']),50,50,50,1157);
	   $Test->drawTitle(1345,497,$language['monitorglobaltext1'] . $config['verison'],50,50,50,1165,"",90);
	   
	   }
	 else
	   {
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",7);
	   $Test->drawTitle(580,21,WinToHTMLChars($loadinfo['image']),50,50,50,675);
	   $Test->drawTitle(785,290,$language['monitorglobaltext1'] . $config['verison'],50,50,50,680,"",90);
	   }
	 
	 // генерируем картинку и выдаем в stream
	 $Test->Stroke();

	 // если в конфиге стоит true для записи логов longbuild - пишем лог
         if ($config['longbuildtxtlog'])
           {
    	   $logger = new txtLogger("longbuild");
    	   unset($logger);  	
	   }
      }  
    }
  else                         
    {
    // определяем имя сервера
    $servername = get_servername($serverid);    
    
    if ($libtype == 'flash')
      {
      if ($givedata == false)
        {
        // подключаем библиотеку OFC
	require_once(ROOT_DIR . '/include/ofc/open-flash-chart-object.php');
        $requesturl = $_SERVER["PHP_SELF"];
        $requesturl.= '?' . $_SERVER["QUERY_STRING"]. '&givedata=true';
        if ($imagetype=='big')
          {echo open_flash_chart_object_str($flashWBig,$flashHBig,$requesturl,false,'');}
	else
          {echo open_flash_chart_object_str($flashWSmall,$flashHSmall,$requesturl,false,'');} 
	exit();
	}
	
      $arrayX_text        =   return_arrayX_to_text($arrayX);
      $loadinfo           =   loadinfo($time_start);
      $loadtime_title     =   WinToHTMLChars($loadinfo['image']);
      unset($loadinfo);
      $title              =   WinToHTMLChars($servername) . WinToHTMLChars($language['stats_for_day']) . 
      			      dateToNormal($period);
      			      
      $online_title       =   WinToHTMLChars($language['onlinetext1'] . "#val#" . 
      			      $language['mentext1']);
      $version_title      =   $language['monitorglobaltext1'] . $config['verison'];
      $hour_title         =   returnDayArray('JAVA',true);
      $bar_colour         =  '#D54C78';
      $x_axis_colour      =  '#909090'; 
      $bg_colour          =  '#EFEFEF';//'#F8F8F8';//'#F8F8D8'; 
      $y_axis_colour      =  '#5555f'; 
      $y_axis_grid_colour =  '#DDDDDD';//'#eeeeee';
      $x_axis_grid_colour =  '#DDDDDD';//'#eeeeee';
      
    
$output = '{ 
"title": { "text": "' . $title . '" }, 
"elements": 
[
 	    {
	     "type": "bar_3d", 
	     "tip": "' . $online_title . '", 
	     "colour": "' . $bar_colour . '", 
	     "line-style": { "style": "dash", "on": 4, "off": 4 }, 
	     "dot-style": { "type": "hollow-dot", "width": 1, "size": 2 }, 
	     "values": [ ' . $arrayX_text . ' ], 
	     "fill-alpha": 0.5, 
	     "width": 5, 
	     ' . $on_show . ' 
	    },
	    
            	 {
			"type":      "line",
			"colour":    "#736AFF",
			"font-size": 1,
			"width":     1,
			"dot-style": {
				"type":"solid-dot", "colour":"' . $bg_colour . '", "dot-size": 17,
				"tip":"' . $loadtime_title . '" },
			"values" : [{"x":21, "y": "' . intval($max*1.22) . '" } ]
		},
		{
			"type":      "line",
			"colour":    "#736AFF",
			"font-size": 1,
			"width":     1,
			"dot-style": {
				"type":"solid-dot", "colour":"' . $bg_colour . '", "dot-size": 17,
				"tip":"' . $version_title . '" },
			"values" : [{"x":23, "y": "' . intval($max*1.22) . '" } ]
		}

],

"bg_colour": "' . $bg_colour . '",
"x_axis": { "3d": 5, 
	    "colour": "' . $x_axis_colour . '", 
	    "grid-colour": "' . $x_axis_grid_colour . '", 
	    "offset": true,
	        "labels": {

      "rotate": 0,

      "labels": [ ' . $hour_title . ' ]
    } 
	  }, 

"y_axis": { "min": 0, 
	    "max": ' . intval($max*1.1) . ', 
	    "steps": 1, 
	    "grid-colour": "' . $y_axis_grid_colour . '", 
	    "colour": "' . $y_axis_colour . '" 
	  }, 

  "tooltip":{
    "mouse": 2,
    "stroke":1
  } 	  
}';    
       
    // выводим data для swf файла
    echo $output;
    
    // если в конфиге стоит true для записи логов longbuild - пишем лог  
    if ($config['longbuildtxtlog'])
      {
      $logger = new txtLogger("longbuild");
      unset($logger);  	
      }
    }
    else
    {     
	 // подключаем библиотеки pChart
	 include_once(ROOT_DIR . "/include/pChart/pChart/pData.class");   
	 include_once(ROOT_DIR . "/include/pChart/pChart/pChart.class");  
	 // Dataset definition 
	 $DataSet = new pData;
	 $DataSet->AddPoint($arrayX, "Serie1");
	 
  	 $DayArray = returnDayArray();
  	 
	 $DataSet->AddPoint($DayArray,"Serie3");
	 $DataSet->AddSerie("Serie1");
	 $DataSet->SetAbsciseLabelSerie("Serie3");
	 $DataSet->SetSerieName(WinToHTMLChars("Онлайн"),"Serie1");

	
	 // Initialise the graph
	 
	 if ($imagetype == 'big')
	   {
	   $Test = new pChart(1200,394);
	   $Test->setGraphArea(85,51,1165,342);
	   $Test->drawFilledRoundedRectangle(7,7,1192,386,5,240,240,240);
	   $Test->drawRoundedRectangle(8,8,1191,385,8,394,394,394);
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",11);
	   }
	 else
	   {
	   $Test = new pChart(700,230);
	   $Test->setGraphArea(50,30,680,200);
	   $Test->drawFilledRoundedRectangle(7,7,693,223,5,240,240,240);
	   $Test->drawRoundedRectangle(5,5,695,225,5,230,230,230);
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",8);
	   }  

	 $Test->drawGraphArea(255,255,255,TRUE);
	 $Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2,TRUE);
	 $Test->drawGrid(4,TRUE,230,230,230,50);
	
	 if ($imagetype == 'big')
	   {
	 $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",10);
	 // Draw the 0 line
	 $Test->drawTreshold(0,143,55,72,TRUE,TRUE);
	   }
	 else
	   {
	 $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",6);
	 // Draw the 0 line
	 $Test->drawTreshold(0,143,55,72,TRUE,TRUE);
	   }  
	 

	
	 // Draw the bar graph
	 $Test->drawBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),TRUE);
	 
	 if ($imagetype == 'big')
	   {
           $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",13);
	   $Test->writeValues($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie1"); 
	   // Finish the graph
           $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",13);
	   $Test->drawLegend(1041,60,$DataSet->GetDataDescription(),255,255,255);
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",17);
	   $Test->drawTitle(85,37,
	   		    WinToHTMLChars($servername) . 
	   		    WinToHTMLChars($language['stats_for_day']). 
			    dateToNormal ($period),
			    85,85,85,994);	   
	   }
	 else
	   {
           $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",9); 
           $Test->writeValues($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie1");
	   // Finish the graph
           $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",8);
	   $Test->drawLegend(596,35,$DataSet->GetDataDescription(),255,255,255); 
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",10);
	   $Test->drawTitle(50,22,
	   		    WinToHTMLChars($servername) . 
			    WinToHTMLChars($language['stats_for_day']). 
			    dateToNormal ($period),
			    50,50,50,580);	   
	   } 
	 
	 // находим время загрузки страницы и кол-во SQL запросов
	 $loadinfo = loadinfo($time_start);
	 if ($imagetype == 'big')
	   {
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",12);
	   $Test->drawTitle(994,35,WinToHTMLChars($loadinfo['image']),50,50,50,1157);
	   $Test->drawTitle(1345,497,$language['monitorglobaltext1'] . $config['verison'],50,50,50,1165,"",90);
	   
	   }
	 else
	   {
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",7);
	   $Test->drawTitle(580,21,WinToHTMLChars($loadinfo['image']),50,50,50,675);
	   $Test->drawTitle(785,290,$language['monitorglobaltext1'] . $config['verison'],50,50,50,680,"",90);
	   }
	 
 
	 // генерируем картинку и выдаем в stream
	 $Test->Stroke();

	 // если в конфиге стоит true для записи логов longbuild - пишем лог
  	 if($config['longbuildtxtlog'])
    	   {
    	   $logger = new txtLogger("longbuild");
    	   unset($logger);  	
    	   }
      }	 
    }  


  exit;
  }

//
//
//
// если выбранный период - НЕДЕЛЯ
//
//  
//
  
if ($periodtype == 'week')
  {
  $weekdata = returnWeekData($period);
  if ($priority == 'current' && $precision != 'relatively')
    {
    
    $query1 = $db_sql->sql_query("
								SELECT * 
								FROM " . $config['statslog_table'] . " 
								WHERE date <= '" . $weekdata['weekendrel'] . "' 
								AND date >= '" . $weekdata['weekbeginrel'] . "' 
								AND serverid = '" . $serverid . "' 
								LIMIT 7;"
								);
				  
    $arrayX = getWeekDataArray($weekdata['weekbeginrel']);
    }
  else
    {
    
    $query1 = $db_sql->sql_query("
								SELECT * 
								FROM " . $config['statslog_table'] . " 
								WHERE date <= '" . $weekdata['weekendabs'] . "' 
								AND date >= '" . $weekdata['weekbeginabs'] . "' 
								AND serverid = '" . $serverid . "' 
								LIMIT 7;"
								);
				  
    $arrayX = getWeekDataArray($weekdata['weekbeginabs']);
    }

  $max = 0;
  while ($query = $db_sql->fetch_array($query1))
    {
      if ($valueofdata == 'peak')
        {
        if (!$query['max'])
	  {$arrayX[$query['date']] = intval(getMax());}  
        else
 	  {$arrayX[$query['date']] = intval($query['max']);}
 	if ($arrayX[$query['date']]>$max)
 	  {$max = $arrayX[$query['date']]; }
	}  
      else
        {  
        if (!$query['average'])
	  {$arrayX[$query['date']] = intval(getAverage());}
        else
	  {$arrayX[$query['date']] = intval($query['average']);}
	if ($arrayX[$query['date']]>$max)
 	  {$max = $arrayX[$query['date']]; }    
	
	}

   }
//  foreach ($arrayX as $val)echo $val . ' |';
  if ($precision == 'relatively')
    {
    $weekdatarel = returnWeekData($periodrel);
    
    if ($serveridrel)
      $queryserverid = $serveridrel;
    else
      $queryserverid  = $serverid; 
    
    $query1 = $db_sql->sql_query("
								SELECT * 
								FROM " . $config['statslog_table'] . " 
								WHERE date <='" . $weekdatarel['weekendabs'] . "' 
								AND date >= '" . $weekdatarel['weekbeginabs'] . "' 
								AND serverid = '" . $queryserverid . "' 
								LIMIT 7;"
								);
				  
    $arrayX2 = getWeekDataArray($weekdatarel['weekbeginabs']);     

   //   if($db_sql->num_rows($query1) == 0)
   //     echo 'ПИЗДЕЦ!';
    while ($query = $db_sql->fetch_array($query1))
      {
      if ($valueofdata == 'peak')
        {
        if (!$query['max'])
	  {$arrayX2[$query['date']] = getMax();}  
        else
 	  {$arrayX2[$query['date']] = $query['max'];}
 	}  
      else
        {  
        if (!$query['average'])
	  {$arrayX2[$query['date']] = getAverage();}
        else
	  {$arrayX2[$query['date']] = $query['average'];}  
	}
      }

    // определяем имена серверов
    // если сервер один и тот же
    if ($serverid == $serveridrel)
      {
      $servername = get_servername($serverid);
      }
    // если сервера разные
    else
      {
      $queryName = $db_sql->sql_query("
										SELECT id,name  
										FROM " . $config['servers_table'] . " 
										WHERE id='" . $serverid . "' 
										OR id='" . $serveridrel . "'
										LIMIT 2;"
										);
  				     
      for($i=0;$i<=1;$i++)
        {
        $queryName2 = $db_sql->fetch_array($queryName);          
        if ($serverid == $queryName2['id'])
          {$servername = $queryName2['name'];}
        elseif ($serveridrel == $queryName2['id'])  
	  {$servernamerel = $queryName2['name'];}
        }
      unset($queryName, $queryName2);
      }

    if ($libtype == 'flash')
      {
      if ($givedata == false)
        {
        // подключаем библиотеку OFC
	require_once(ROOT_DIR . '/include/ofc/open-flash-chart-object.php');
        $requesturl = $_SERVER["PHP_SELF"];
        $requesturl.= '?' . $_SERVER["QUERY_STRING"]. '&givedata=true';
        if ($imagetype=='big')
          {echo open_flash_chart_object_str($flashWBig,$flashHBig,$requesturl,false,'');}
	else
          {echo open_flash_chart_object_str($flashWSmall,$flashHSmall,$requesturl,false,'');} 	  
	exit();
	}
 
      if ($serverid == $serveridrel)
        {
	$title = $servername . 
	         WinToHTMLChars($language['stats_for_week']) . 
		 $weekdata['weekbeginendabsday'] . "." . 
		 $weekdata['weekbeginendabsmonth'] .  "." . 
		 $weekdata['weekbeginendabsyear'] . 
		 WinToHTMLChars(' vs ') . 
		 $weekdatarel['weekbeginendabsday'] .  "." . 
		 $weekdatarel['weekbeginendabsmonth'] .  "." . 
		 $weekdatarel['weekbeginendabsyear'];
		 
        $online_arrayX_title  =   WinToHTMLChars(  '<br>' . $servername . '<br>' . 
						   $weekdata['weekbeginendabsday'] .   "." . 
						   $weekdata['weekbeginendabsmonth'] . "." . 
						   $weekdata['weekbeginendabsyear'] . "<br>" . 
						   $language['onlinetext1'] . "#val#" . 
						   $language['mentext1'] );

						   
        $online_arrayX2_title =   WinToHTMLChars(  '<br>' . $servername . '<br>' . 
			      	  		   $weekdatarel['weekbeginendabsday'] . "." . 
						   $weekdatarel['weekbeginendabsmonth'] . "." . 
						   $weekdatarel['weekbeginendabsyear'] . "<br>" . 
						   $language['onlinetext1'] . "#val#" . 
						   $language['mentext1'] );
							    
        $arrayX_legend  = $weekdata['weekbeginendabsday'] . "." . 
		          $weekdata['weekbeginendabsmonth'] . "." . 
			  $weekdata['weekbeginendabsyear'];
			 
        $arrayX2_legend = $weekdatarel['weekbeginendabsday'] . "." . 
			  $weekdatarel['weekbeginendabsmonth'] . "." . 
			  $weekdatarel['weekbeginendabsyear'];
	}
      else
        {
	$title = WinToHTMLChars($language['stats_for_week2']) . 
	         $servername . ' (' . 
		 $weekdata['weekbeginendabsday'] . "." . 
		 $weekdata['weekbeginendabsmonth'] . "." . 
		 $weekdata['weekbeginendabsyear'] . 
		 WinToHTMLChars(') vs ') . 
		 $servernamerel . ' (' . 
		 $weekdatarel['weekbeginendabsday'] . "." . 
		 $weekdatarel['weekbeginendabsmonth'] . "." . 
		 $weekdatarel['weekbeginendabsyear'] . ')';
		 
        $online_arrayX_title  =   WinToHTMLChars(  '<br>' . $servername . '<br>' . 
			      	  		   $weekdata['weekbeginendabsday'] . "." . 
						   $weekdata['weekbeginendabsmonth'] . "." . 
						   $weekdata['weekbeginendabsyear'] . "<br>" . 
						   $language['onlinetext1'] . "#val#" . 
						   $language['mentext1']);
					
					
						   
        $online_arrayX2_title =   WinToHTMLChars(  '<br>' . $servernamerel . '<br>' . 
			      	  		   $weekdatarel['weekbeginendabsday'] . "." . 
						   $weekdatarel['weekbeginendabsmonth'] . "." . 
						   $weekdatarel['weekbeginendabsyear'] . "<br>" . 
						   $language['onlinetext1'] . "#val#" . 
						   $language['mentext1']);
						   
	$arrayX_legend  = WinToHTMLChars($servername) . ' (' . 
		          $weekdata['weekbeginendabsday'] . "." . 
			  $weekdata['weekbeginendabsmonth'] . "." . 
			  $weekdata['weekbeginendabsyear']. ')';
			 
        $arrayX2_legend = WinToHTMLChars($servernamerel) . ' (' . 
			  $weekdatarel['weekbeginendabsday'] . "." . 
			  $weekdatarel['weekbeginendabsmonth'] . "." . 
			  $weekdatarel['weekbeginendabsyear']. ')';	
	}  
	
      $WeekArray = returnWeekArray('JAVA');
      $arrayX_text        =  return_arrayX_to_text($arrayX);
      $arrayX2_text       =  return_arrayX_to_text($arrayX2);      
      $loadinfo           =  loadinfo($time_start);
      $loadtime_title     =  WinToHTMLChars($loadinfo['image']);
      unset($loadinfo);
      $version_title      =  $language['monitorglobaltext1'] . $config['verison'];
      $WeekArray          =  return_arrayX_to_text2($WeekArray);
      $bar_colour         =  '#D54C78';
      $bar2_colour        =  '#5555ff';
      $x_axis_colour      =  '#909090'; 
      $bg_colour          =  '#EFEFEF';//'#F8F8F8';//'#F8F8D8'; 
      $y_axis_colour      =  '#5555f'; 
      $y_axis_grid_colour =  '#DDDDDD';//'#eeeeee';
      $x_axis_grid_colour =  '#DDDDDD';//'#eeeeee';
      
$output = '{ 
"title": { "text": "' . $title . '" }, 
"elements": 
[
 	    {
	     "type": "bar_3d", 
	     "tip": "' . $online_arrayX_title . '", 
	     "colour": "' . $bar_colour . '",
	     "text":      "' . $arrayX_legend . '",	      
	     "line-style": { "style": "dash", "on": 4, "off": 4 }, 
	     "dot-style": { "type": "hollow-dot", "width": 1, "size": 2 }, 
	     "values": [ ' . $arrayX_text . ' ], 
	     "fill-alpha": 0.5, 
	     "width": 5, 
	     ' . $on_show . '
	    },
	    
	    {
	     "type": "bar_3d", 
	     "tip": "' . $online_arrayX2_title . '", 
	     "colour": "' . $bar2_colour . '",
	     "text":      "' . $arrayX2_legend . '",	      
	     "line-style": { "style": "dash", "on": 4, "off": 4 }, 
	     "dot-style": { "type": "hollow-dot", "width": 1, "size": 2 }, 
	     "values": [ ' . $arrayX2_text . ' ], 
	     "fill-alpha": 0.5, 
	     "width": 5, 
	     ' . $on_show2 . '
	    },	    
	    
            	 {
			"type":      "line",
			"colour":    "#736AFF",
			"font-size": 1,
			"width":     1,
			"dot-style": {
				"type":"solid-dot", "colour":"' . $bg_colour . '", "dot-size": 4,
				"tip":"' . $loadtime_title . '" },
			"values" : [{"x":5, "y": "' . intval($max*1.1) . '" } ]
		},
		{
			"type":      "line",
			"colour":    "#736AFF",
			"font-size": 1,
			"width":     1,
			"dot-style": {
				"type":"solid-dot", "colour":"' . $bg_colour . '", "dot-size": 4,
				"tip":"' . $version_title . '" },
			"values" : [{"x":6, "y": "' . intval($max*1.1) . '" } ]
		}

],

"bg_colour": "' . $bg_colour . '",
"x_axis": { "3d": 5, 
	    "colour": "' . $x_axis_colour . '", 
	    "grid-colour": "' . $x_axis_grid_colour . '", 
	    "offset": true,
	        "labels": {

      "rotate": 0,

      "labels": [ ' . $WeekArray . ' ]
    } 
	  }, 

"y_axis": { "min": 0, 
	    "max": ' . intval($max*1.1) . ', 
	    "steps": 1, 
	    "grid-colour": "' . $y_axis_grid_colour . '", 
	    "colour": "' . $y_axis_colour . '" 
	  }, 

  "tooltip":{
    "mouse": 2,
    "stroke":1
  } 	  
}';    
       
    // выводим data для swf файла
    echo $output;
    
    // если в конфиге стоит true для записи логов longbuild - пишем лог  
    if ($config['longbuildtxtlog'])
      {
      $logger = new txtLogger("longbuild");
      unset($logger);  	
      }
    }
    else
    {       
	// подключаем библиотеки pChart
	include_once(ROOT_DIR . "/include/pChart/pChart/pData.class");   
	include_once(ROOT_DIR . "/include/pChart/pChart/pChart.class");  

	 // Dataset definition 
	 $DataSet = new pData;
 	 
         $WeekArray = returnWeekArray();

	 $DataSet->AddPoint($arrayX,"Serie1");
	 $DataSet->AddPoint($arrayX2,"Serie2");
	 $DataSet->AddPoint($WeekArray,"Serie3");
	 $DataSet->AddSerie("Serie1");
	 $DataSet->AddSerie("Serie2");	
	 $DataSet->SetAbsciseLabelSerie("Serie3");
	 $DataSet->SetSerieName($period,"Serie1");
	 $DataSet->SetSerieName($periodrel,"Serie2");
	
	 // Initialise the graph
	 
	 if ($imagetype == 'big')
	   {
	   $Test = new pChart(1200,394);
	   $Test->setGraphArea(85,51,1165,342);
	   $Test->drawFilledRoundedRectangle(7,7,1192,386,5,240,240,240);
	   $Test->drawRoundedRectangle(8,8,1191,385,8,394,394,394);
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",13);
	   }
	 else
	   {
	   $Test = new pChart(700,230);
	   $Test->setGraphArea(50,30,680,200);
	   $Test->drawFilledRoundedRectangle(7,7,693,223,5,240,240,240);
	   $Test->drawRoundedRectangle(5,5,695,225,5,230,230,230);
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",8);
	   }  
	 
	 
	 $Test->drawGraphArea(255,255,255,TRUE);
	 $Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2,TRUE);
	 $Test->drawGrid(4,TRUE,230,230,230,50);
	 // Draw the 0 line
	 if ($imagetype == 'big')
	   {
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",10);	   
	   }
	 else
	   {
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",6);	   
	   }  
	 

	 $Test->drawTreshold(0,143,55,72,TRUE,TRUE);
	
	 // Draw the bar graph
	 $Test->drawBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),TRUE);
	
	
	if ($serverid == $serveridrel)
	  {
	  $title = $servername . 
	  	   WinToHTMLChars($language['stats_for_week']) . 
		   $weekdata['weekbeginendabsday'] . "." . 
		   $weekdata['weekbeginendabsmonth'] . "." . 
		   $weekdata['weekbeginendabsyear'] . 
		   WinToHTMLChars(' vs ') . 
		   $weekdatarel['weekbeginendabsday'] . "." . 
		   $weekdatarel['weekbeginendabsmonth'] . "." . 
		   $weekdatarel['weekbeginendabsyear'];
	  }
	else
	  {
	  $title = WinToHTMLChars($language['stats_for_week2']) . 
	  	   $servername . ' (' . 
		   $weekdata['weekbeginendabsday'] . "." . 
		   $weekdata['weekbeginendabsmonth'] . "." . 
		   $weekdata['weekbeginendabsyear'] . 
		   WinToHTMLChars(') vs ') . 
		   $servernamerel . ' (' . 
		   $weekdatarel['weekbeginendabsday'] . "." . 
		   $weekdatarel['weekbeginendabsmonth'] . "." . 
		   $weekdatarel['weekbeginendabsyear'] . ')';
	  }
	
	if ($imagetype =='big')
	  {
	  $titlePosition  = imageftbbox(17,0,"./include/pChart/Fonts/tahoma.ttf",$title);
          $titleTextWidth = $titlePosition[2]-$titlePosition[0];
	  if ($titleTextWidth > 976)
	    {$istitletoobig = true;}
	  else
	    {$istitletoobig = false;}
	  }
	else
	  {         

	  $titlePosition  = imageftbbox(10,0,"./include/pChart/Fonts/tahoma.ttf",$title);
          $titleTextWidth = $titlePosition[2]-$titlePosition[0];
   	  if ($titleTextWidth > 569)
	    {$istitletoobig = true;}
	  else
	    {$istitletoobig = false;}
	  }  

//	echo $titleTextWidth;
//	echo intval($istitletoobig);
	
	 if ($imagetype == 'big')
	   {
           $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",18);
	   $Test->writeValues($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie1");
	   $Test->writeValues($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie2");
	   // Finish the graph
           $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",13);
	   $Test->drawLegend(1041,60,$DataSet->GetDataDescription(),255,255,255);
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",17);
//	   if ($priority == 'all')
	   if ($istitletoobig)
	     {$Test->drawTitle(17,37,$title,50,50,50,1157);}
	   else
	     {$Test->drawTitle(17,37,$title,50,50,50,993);}  
  //	   else
	  //   {$Test->drawTitle(17,37,$title,50,50,50,993);}
	   }
	 else
	   {
           $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",11); 
	   $Test->writeValues($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie1");
	   $Test->writeValues($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie2");
	   // Finish the graph
           $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",8);
	   $Test->drawLegend(596,35,$DataSet->GetDataDescription(),255,255,255); 
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",10);	   
	   if ($istitletoobig)
	     {$Test->drawTitle(10,22,$title,50,50,50,675);}
  	   else
	     {$Test->drawTitle(10,22,$title,50,50,50,579);}
	   }
	 
	 // находим время загрузки страницы и кол-во SQL запросов
	 $loadinfo = loadinfo($time_start); 
	 if ($imagetype == 'big')
	   {
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",12);
	   if (!$istitletoobig)
	     {$Test->drawTitle(994,39,WinToHTMLChars($loadinfo['image']),50,50,50,1157);}
	   $Test->drawTitle(1345,497,$language['monitorglobaltext1'] . $config['verison'],50,50,50,1165,"",90);
	   }
	 else
	   {
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",7);
	   if (!$istitletoobig)
	     {$Test->drawTitle(580,23,WinToHTMLChars($loadinfo['image']),50,50,50,675);}
	   $Test->drawTitle(785,290,$language['monitorglobaltext1'] . $config['verison'],50,50,50,680,"",90);	   
	   }	   
	 
	 // генерируем картинку и выдаем в stream
	 $Test->Stroke();

	 // если в конфиге стоит true для записи логов longbuild - пишем лог   
	 if($config['longbuildtxtlog'])
    	   {
    	   $logger = new txtLogger("longbuild");
    	   unset($logger);  	
    	   }
      }
    }
  else                         
    {
    // определяем имя сервера
    $servername = get_servername($serverid);	 

   if ($priority == 'all')
     {
     $title = WinToHTMLChars($servername) . 
     	      WinToHTMLChars($language['stats_for_week']) . 
	      $weekdata['weekbeginendabsday'] . "." . 
	      $weekdata['weekbeginendabsmonth'] . "." . 
	      $weekdata['weekbeginendabsyear'];
     }
   else
     {
     $title = WinToHTMLChars($servername) .
     	      WinToHTMLChars($language['stats_for_week']) . 
	      $weekdata['weekbeginendrelday'] . "." . 
	      $weekdata['weekbeginendrelmonth'] . "." . 
	      $weekdata['weekbeginendrelyear'];
     }


    if ($libtype == 'flash')
      {

      if ($givedata == false)
        {
        // подключаем библиотеку OFC
	require_once(ROOT_DIR . '/include/ofc/open-flash-chart-object.php');
        $requesturl = $_SERVER["PHP_SELF"];
        $requesturl.= '?' . $_SERVER["QUERY_STRING"]. '&givedata=true';
        if ($imagetype=='big')
          {echo open_flash_chart_object_str($flashWBig,$flashHBig,$requesturl,false,'');}
	else
          {echo open_flash_chart_object_str($flashWSmall,$flashHSmall,$requesturl,false,'');} 
          
	exit();                                                                    
	}
      
   if ($priority == 'all')
     {
     $WeekArray = returnWeekArray('JAVA');
     }
   else
     {
     $WeekArray = returnWeekArrayRel($weekdata['weekbeginrel'],'JAVA');

     }      
      $arrayX_text        =  return_arrayX_to_text($arrayX);
      $loadinfo           =  loadinfo($time_start);
      $loadtime_title     =  WinToHTMLChars($loadinfo['image']);
      unset($loadinfo);
      $online_title       =  WinToHTMLChars(  $language['onlinetext1'] . "#val#" . $language['mentext1']);
      $version_title      =  $language['monitorglobaltext1'] . $config['verison'];
      $WeekArray          =  return_arrayX_to_text2($WeekArray);
      $bar_colour         =  '#D54C78';
      $x_axis_colour      =  '#909090'; 
      $bg_colour          =  '#EFEFEF';//'#F8F8F8';//'#F8F8D8'; 
      $y_axis_colour      =  '#5555f'; 
      $y_axis_grid_colour =  '#DDDDDD';//'#eeeeee';
      $x_axis_grid_colour =  '#DDDDDD';//'#eeeeee';
      
     
$output = '{ 
"title": { "text": "' . $title . '" }, 
"elements": 
[
 	    {
	     "type": "bar_3d", 
	     "tip": "' . $online_title . '", 
	     "colour": "' . $bar_colour . '", 
	     "line-style": { "style": "dash", "on": 4, "off": 4 }, 
	     "dot-style": { "type": "hollow-dot", "width": 1, "size": 2 }, 
	     "values": [ ' . $arrayX_text . ' ], 
	     "fill-alpha": 0.5, 
	     "width": 5, 
	     ' . $on_show . ' 
	    },
	    
            	 {
			"type":      "line",
			"colour":    "#736AFF",
			"font-size": 1,
			"width":     1,
			"dot-style": {
				"type":"solid-dot", "colour":"' . $bg_colour . '", "dot-size": 3,
				"tip":"' . $loadtime_title . '" },
			"values" : [{"x":5, "y": "' . intval($max*1.1) . '" } ]
		},
		{
			"type":      "line",
			"colour":    "#736AFF",
			"font-size": 1,
			"width":     1,
			"dot-style": {
				"type":"solid-dot", "colour":"' . $bg_colour . '", "dot-size": 3,
				"tip":"' . $version_title . '" },
			"values" : [{"x":6, "y": "' . intval($max*1.1) . '" } ]
		}

],

"bg_colour": "' . $bg_colour . '",
"x_axis": { "3d": 5, 
	    "colour": "' . $x_axis_colour . '", 
	    "grid-colour": "' . $x_axis_grid_colour . '", 
	    "offset": true,
	        "labels": {

      "rotate": 0,

      "labels": [ ' . $WeekArray . ' ]
    } 
	  }, 

"y_axis": { "min": 0, 
	    "max": ' . intval($max*1.1) . ', 
	    "steps": 1, 
	    "grid-colour": "' . $y_axis_grid_colour . '", 
	    "colour": "' . $y_axis_colour . '" 
	  }, 

  "tooltip":{
    "mouse": 2,
    "stroke":1
  } 	  
}';    
       
    // выводим data для swf файла
    echo $output;
    
    // если в конфиге стоит true для записи логов longbuild - пишем лог  
    if ($config['longbuildtxtlog'])
      {
      $logger = new txtLogger("longbuild");
      unset($logger);  	
      }
    }
    else
    {  
	 // подключаем библиотеки pChart
	 include_once(ROOT_DIR . "/include/pChart/pChart/pData.class");   
	 include_once(ROOT_DIR . "/include/pChart/pChart/pChart.class");  
	 // Dataset definition 
	 $DataSet = new pData;
	 
   if ($priority == 'all')
     {
     $WeekArray = returnWeekArray();
     }
   else
     {
     $WeekArray = returnWeekArrayRel($weekdata['weekbeginrel']);
     } 	 

	 $DataSet->AddPoint($arrayX, "Serie1");
	 $DataSet->AddPoint($WeekArray,"Serie3");
	 $DataSet->AddSerie("Serie1");
	 $DataSet->SetAbsciseLabelSerie("Serie3");
	 $DataSet->SetSerieName(WinToHTMLChars('Онлайн'),"Serie1");
	
	 // Initialise the graph
	 
	 if ($imagetype == 'big')
	   {
	   $Test = new pChart(1200,394);
	   $Test->setGraphArea(85,51,1165,342);
	   $Test->drawFilledRoundedRectangle(7,7,1192,386,5,240,240,240);
	   $Test->drawRoundedRectangle(8,8,1191,385,8,394,394,394);
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",13);
	   }
	 else
	   {
	   $Test = new pChart(700,230);
	   $Test->setGraphArea(50,30,680,200);
	   $Test->drawFilledRoundedRectangle(7,7,693,223,5,240,240,240);
	   $Test->drawRoundedRectangle(5,5,695,225,5,230,230,230);
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",8);
	   }  
	 
	 $Test->drawGraphArea(255,255,255,TRUE);
	 $Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2,TRUE);
	 $Test->drawGrid(4,TRUE,230,230,230,50);
	 // Draw the 0 line
	 if ($imagetype == 'big')
	   {
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",10);	   
	   }
	 else
	   {
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",6);	   
	   }  
	 

	 $Test->drawTreshold(0,143,55,72,TRUE,TRUE);
	
	 // Draw the bar graph
	 $Test->drawBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),TRUE);
	
	 if ($imagetype == 'big')
	   {
           $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",18);
	   $Test->writeValues($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie1"); 
	   // Finish the graph
           $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",13);
	   $Test->drawLegend(1041,60,$DataSet->GetDataDescription(),255,255,255);
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",17);
	   $Test->drawTitle(85,37,$title,50,50,50,994);
	   }
	 else
	   {
           $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",11); 
           $Test->writeValues($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie1");
	   // Finish the graph
           $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",8);
	   $Test->drawLegend(596,35,$DataSet->GetDataDescription(),255,255,255); 
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",10);	   
	   $Test->drawTitle(50,22,$title,50,50,50,580);
	   }
	 
	 // находим время загрузки страницы и кол-во SQL запросов
	 $loadinfo = loadinfo($time_start); 
	 if ($imagetype == 'big')
	   {
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",12);
	   $Test->drawTitle(994,39,WinToHTMLChars($loadinfo['image']),50,50,50,1157);
	   $Test->drawTitle(1345,497,$language['monitorglobaltext1'] . $config['verison'],50,50,50,1165,"",90);
	   }
	 else
	   {
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",7);
	   $Test->drawTitle(580,23,WinToHTMLChars($loadinfo['image']),50,50,50,675);
	   $Test->drawTitle(785,290,$language['monitorglobaltext1'] . $config['verison'],50,50,50,680,"",90);	   
	   }	   
	 
	 // генерируем картинку и выдаем в stream
	 $Test->Stroke();

	 // если в конфиге стоит true для записи логов longbuild - пишем лог
  	 if($config['longbuildtxtlog'])
    	   {
    	   $logger = new txtLogger("longbuild");
    	   unset($logger);  	
    	   }
    }  
  }  
  exit;
  }

//
//
// если выбранный период - МЕСЯЦ
//
//   

if ($periodtype == 'month')
  {
  $monthdata = returnMonthData($period);
  if (($priority == 'current') && ($precision !='relatively'))
    {
    $query1 = $db_sql->sql_query("
								SELECT * 
								FROM " . $config['statslog_table'] . " 
								WHERE date <= '" . $monthdata['monthendrel'] . "' 
								AND date >= '" . $monthdata['monthbeginrel'] . "' 
								AND serverid = '" . $serverid . "' 
								LIMIT 40;"
								);
				  
    $arrayX = getMonthDataArray($monthdata['monthbeginrel'],0);
    }
  if (($priority == 'all') || (($priority == 'current') && ($precision =='relatively')))
    {  
    $query1 = $db_sql->sql_query("
								SELECT * 
								FROM " . $config['statslog_table'] . " 
								WHERE date <= '" . $monthdata['monthendabs'] . "' 
								AND date >= '" . $monthdata['monthbeginabs'] . "' 
								AND serverid = '" . $serverid . "' 
								LIMIT 40;"
								);
				  
    $arrayX = getMonthDataArray($monthdata['monthbeginabs'],$monthdata['daysonmonth']);
    }
  
  $max = 0;
  while ($query = $db_sql->fetch_array($query1))
    {
      if ($valueofdata == 'peak')
        {
        if (!$query['max'])
	  {$arrayX[$query['date']] = getMax();}  
        else
 	  {$arrayX[$query['date']] = $query['max'];}

	}  
      else
        {  
        if (!$query['average'])
	  {$arrayX[$query['date']] = getAverage();}
        else
	  {$arrayX[$query['date']] = $query['average'];}  
	}
   if ($arrayX[$query['date']] > $max)
     {$max = $arrayX[$query['date']];}	
   }   
  
  if ($precision == 'relatively')
    {
    $monthdatarel = returnMonthData($periodrel);
    
    if ($serveridrel)
      $queryserverid = $serveridrel;
    else
      $queryserverid  = $serverid;     
    
    $query1 = $db_sql->sql_query("
								SELECT * 
								FROM " . $config['statslog_table'] . " 
								WHERE date <='" . $monthdatarel['monthendabs'] . "' 
								AND date >= '" . $monthdatarel['monthbeginabs'] . "' 
								AND serverid = '" . $queryserverid . "' 
								LIMIT 32;"
								);
				  
    $arrayX2 = getMonthDataArray($monthdatarel['monthbeginabs'],$monthdata['daysonmonth']);
  //  $m=0;
//    if($db_sql->num_rows($query1) == 0)
 //     echo 'ПИЗДЕЦ!';
    while ($query = $db_sql->fetch_array($query1))
      {
      if ($valueofdata == 'peak')
        {
        if (!$query['max'])
	  {$arrayX2[$query['date']] = getMax();}  
        else
 	  {$arrayX2[$query['date']] = $query['max'];}
 	}  
      else
        {  
        if (!$query['average'])
	  {$arrayX2[$query['date']] = getAverage();}
        else
	  {$arrayX2[$query['date']] = $query['average'];}  
	}
  //    $m++;
      }
         $time1 = mktime (0,0,0,$period{5}.$period{6},$period{8}.$period{9},$period{0}.$period{1}.$period{2}.$period{3});
	 $time2 = mktime (0,0,0,$periodrel{5}.$periodrel{6},$periodrel{8}.$periodrel{9},$periodrel{0}.$periodrel{1}.$periodrel{2}.$periodrel{3});
	 $time1 = date("t",$time1); $time2 = date("t",$time2);
	 if ($time1 > $time2)
	   { $MonthArrayAbs = returnMonthArrayAbs($period); }
	 else
	   { $MonthArrayAbs = returnMonthArrayAbs($periodrel); } 
	    

    if ($serverid == $serveridrel)
      {
      // определяем имя сервера
      $servername = get_servername($serverid);
      }
    else
      {
      $queryName = $db_sql->sql_query("
										SELECT id,name  
										FROM " . $config['servers_table'] . " 
										WHERE id='" . $serverid . "' 
										OR id='" . $serveridrel . "'
										LIMIT 2;"
										);
  				     
      for($i=0;$i<=1;$i++)
        {
        $queryName2 = $db_sql->fetch_array($queryName);          
        if ($serverid == $queryName2['id'])
          {$servername = $queryName2['name'];}
        elseif ($serveridrel == $queryName2['id'])  
	  {$servernamerel = $queryName2['name'];}
        }
      unset($queryName, $queryName2);
      }

         if ($serverid == $serveridrel)
	   {
           $title = $servername . 
	   	    WinToHTMLChars($language['stats_for_month']) . 
		    $monthdata['monthandyear'] . ' vs ' . 
		    $monthdatarel['monthandyear'];
	   }
	 else                   
	   {
           $title = WinToHTMLChars($language['stats_for_month2']) . 
	   	    $servername . ' (' . 
		    $monthdata['monthandyear'] . ') vs ' . 
		    $servernamerel . ' (' . 
		    $monthdatarel['monthandyear'] . ')';	   
	   }


    if ($libtype == 'flash')
      {
      if ($givedata == false)
        {
        // подключаем библиотеку OFC
	require_once(ROOT_DIR . '/include/ofc/open-flash-chart-object.php');
        $requesturl = $_SERVER["PHP_SELF"];
        $requesturl.= '?' . $_SERVER["QUERY_STRING"]. '&givedata=true';
        if ($imagetype=='big')
          {echo open_flash_chart_object_str($flashWBig,$flashHBig,$requesturl,false,'');}
	else
          {echo open_flash_chart_object_str($flashWSmall,$flashHSmall,$requesturl,false,'');} 	  
	exit();
	}
 
      if ($serverid == $serveridrel)
        {
		 
        $online_arrayX_title  =   WinToHTMLChars(  '<br>' . $servername . '<br>' . 
						   $monthdata['monthandyear'] . 
						   "<br>" . $language['onlinetext1'] . "#val#" . 
						   $language['mentext1'] );
						   
						         
						   
        $online_arrayX2_title =   WinToHTMLChars(  '<br>' . $servername . '<br>' . 
			      	  		   $monthdatarel['monthandyear'] . 
						   "<br>" . $language['onlinetext1'] . "#val#" . 
						   $language['mentext1'] );
							    
        $arrayX_legend  = $monthdata['monthandyear'];
        $arrayX2_legend = $monthdatarel['monthandyear'];
	}
      else
        {
		 
        $online_arrayX_title  =   WinToHTMLChars(  '<br>' . $servername . '<br>' . 
			      	  		   $monthdata['monthandyear'] . 
						   "<br>" . $language['onlinetext1'] . "#val#" . 
						   $language['mentext1']       );
						   
        $online_arrayX2_title =   WinToHTMLChars(  '<br>' . $servernamerel . '<br>' . 
			      	  		   $monthdatarel['monthandyear'] . 
						   "<br>" . $language['onlinetext1'] . "#val#" . 
						   $language['mentext1']       );
						   
	$arrayX_legend  = WinToHTMLChars($servername) . ' (' . 
		          $monthdata['monthandyear']. ')';
			 
        $arrayX2_legend = WinToHTMLChars($servernamerel) . ' (' . 
			  $monthdatarel['monthandyear']. ')';	
	}  
	
      $arrayX_text        =  return_arrayX_to_text($arrayX);
      $arrayX2_text       =  return_arrayX_to_text($arrayX2);      
      $loadinfo           =  loadinfo($time_start);
      $loadtime_title     =  WinToHTMLChars($loadinfo['image']);
      unset($loadinfo);
      $version_title      =  $language['monitorglobaltext1'] . $config['verison'];
      $MonthArrayAbs      =  return_arrayX_to_text2($MonthArrayAbs);
      $bar_colour         =  '#D54C78';
      $bar2_colour        =  '#5555ff';
      $x_axis_colour      =  '#909090'; 
      $bg_colour          =  '#EFEFEF';//'#F8F8F8';//'#F8F8D8'; 
      $y_axis_colour      =  '#5555f'; 
      $y_axis_grid_colour =  '#DDDDDD';//'#eeeeee';
      $x_axis_grid_colour =  '#DDDDDD';//'#eeeeee';
      
$output = '{ 
"title": { "text": "' . $title . '" }, 
"elements": 
[
 	    {
	     "type": "bar_3d", 
	     "tip": "' . $online_arrayX_title . '", 
	     "colour": "' . $bar_colour . '",
	     "text":      "' . $arrayX_legend . '",	      
	     "line-style": { "style": "dash", "on": 4, "off": 4 }, 
	     "dot-style": { "type": "hollow-dot", "width": 1, "size": 2 }, 
	     "values": [ ' . $arrayX_text . ' ], 
	     "fill-alpha": 0.5, 
	     "width": 5, 
	     ' . $on_show . '
	    },
	    
	    {
	     "type": "bar_3d", 
	     "tip": "' . $online_arrayX2_title . '", 
	     "colour": "' . $bar2_colour . '",
	     "text":      "' . $arrayX2_legend . '",	      
	     "line-style": { "style": "dash", "on": 4, "off": 4 }, 
	     "dot-style": { "type": "hollow-dot", "width": 1, "size": 2 }, 
	     "values": [ ' . $arrayX2_text . ' ], 
	     "fill-alpha": 0.5, 
	     "width": 5, 
	     ' . $on_show2 . '
	    },	    
	    
            	 {
			"type":      "line",
			"colour":    "#736AFF",
			"font-size": 1,
			"width":     1,
			"dot-style": {
				"type":"solid-dot", "colour":"' . $bg_colour . '", "dot-size": 4,
				"tip":"' . $loadtime_title . '" },
			"values" : [{"x":5, "y": "' . intval($max*1.1) . '" } ]
		},
		{
			"type":      "line",
			"colour":    "#736AFF",
			"font-size": 1,
			"width":     1,
			"dot-style": {
				"type":"solid-dot", "colour":"' . $bg_colour . '", "dot-size": 4,
				"tip":"' . $version_title . '" },
			"values" : [{"x":6, "y": "' . intval($max*1.1) . '" } ]
		}

],

"bg_colour": "' . $bg_colour . '",
"x_axis": { "3d": 5, 
	    "colour": "' . $x_axis_colour . '", 
	    "grid-colour": "' . $x_axis_grid_colour . '", 
	    "offset": true,
	        "labels": {

      "rotate": 0,

      "labels": [ ' . $MonthArrayAbs . ' ]
    } 
	  }, 

"y_axis": { "min": 0, 
	    "max": ' . intval($max*1.1) . ', 
	    "steps": 1, 
	    "grid-colour": "' . $y_axis_grid_colour . '", 
	    "colour": "' . $y_axis_colour . '" 
	  }, 

  "tooltip":{
    "mouse": 2,
    "stroke":1
  } 	  
}';    
       
    // выводим data для swf файла
    echo $output;
    
    // если в конфиге стоит true для записи логов longbuild - пишем лог  
    if ($config['longbuildtxtlog'])
      {
      $logger = new txtLogger("longbuild");
      unset($logger);  	
      }
    }
    else
    {   
	 
  	 	 
	 // подключаем библиотеки pChart
	 include_once(ROOT_DIR . "/include/pChart/pChart/pData.class");   
	 include_once(ROOT_DIR . "/include/pChart/pChart/pChart.class");  
	 
	 // Dataset definition 
	 $DataSet = new pData;
	 $DataSet->AddPoint($arrayX2,"Serie1");
	 $DataSet->AddPoint($arrayX,"Serie2");

	 $DataSet->AddPoint($MonthArrayAbs,"Serie3");
	 $DataSet->AddSerie("Serie1");
	 $DataSet->AddSerie("Serie2");
	 $DataSet->SetAbsciseLabelSerie("Serie3");
	 $DataSet->SetSerieName($monthdatarel['monthandyear'],"Serie1");
	 $DataSet->SetSerieName($monthdata['monthandyear'],"Serie2");
	
	 // Initialise the graph
	 
	 if ($imagetype == 'big')
	   {
	   $Test = new pChart(1200,394);
	   $Test->setGraphArea(85,51,1165,342);
	   $Test->drawFilledRoundedRectangle(7,7,1192,382,5,240,240,240);
	   $Test->drawRoundedRectangle(5,5,1191,385,5,230,230,230);
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",13);
	   }
	 else
	   {
	   $Test = new pChart(700,230);
	   $Test->setGraphArea(50,30,680,200);
	   $Test->drawFilledRoundedRectangle(7,7,693,223,5,240,240,240);
	   $Test->drawRoundedRectangle(5,5,695,225,5,230,230,230);
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",8);
	   }  	 
	 
	 $Test->drawGraphArea(255,255,255,TRUE);
	 $Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2,TRUE);
	 $Test->drawGrid(4,TRUE,230,230,230,50);
	
	 // Draw the 0 line
 	 if ($imagetype == 'big')
	   {
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",10);
	   }
	 else
	   {
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",6);
	   }  	 

	 $Test->drawTreshold(0,143,55,72,TRUE,TRUE);
	
	 // Draw the bar graph
	 $Test->drawBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),TRUE);	
 	 
 	 if ($imagetype == 'big')
	   {
           $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",13);
	   $Test->writeValues($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie1"); 
	   // Finish the graph
           $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",13);
	   $Test->drawLegend(1021,60,$DataSet->GetDataDescription(),255,255,255);
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",17);
     	   $Test->drawTitle(85,37,$title,50,50,50,994);
	   }
	 else
	   {
           $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",8); 
           $Test->writeValues($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie1");
	   // Finish the graph
           $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",8);
	   $Test->drawLegend(596,35,$DataSet->GetDataDescription(),255,255,255); 
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",10);	   
	   $Test->drawTitle(50,22,$title,50,50,50,580);
	   }

         // находим время загрузки страницы и кол-во SQL запросов
	 $loadinfo = loadinfo($time_start); 
	 if ($imagetype == 'big')
	   {
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",12);
	   $Test->drawTitle(994,39,WinToHTMLChars($loadinfo['image']),50,50,50,1157);
	   $Test->drawTitle(1345,497,$language['monitorglobaltext1'] . $config['verison'],50,50,50,1165,"",90);
	   }
	 else
	   {
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",7);
	   $Test->drawTitle(580,23,WinToHTMLChars($loadinfo['image']),50,50,50,675);
	   $Test->drawTitle(785,290,$language['monitorglobaltext1'] . $config['verison'],50,50,50,680,"",90);
	   
	   }	 

	 // генерируем картинку и выдаем в stream
	 $Test->Stroke();

	 // если в конфиге стоит true для записи логов longbuild - пишем лог
	 if($config['longbuildtxtlog'])
    	   {
    	   $logger = new txtLogger("longbuild");
    	   unset($logger);  	
    	   }
    	}   
    }
  else                         
    {
    // определяем имя сервера
    $servername = get_servername($serverid);
    $title = WinToHTMLChars($servername) . 
    	     WinToHTMLChars($language['stats_for_month']) . $monthdata['monthandyear']; 
    
    if ($libtype == 'flash')
      {

      if ($givedata == false)
        {
        // подключаем библиотеку OFC
	require_once(ROOT_DIR . '/include/ofc/open-flash-chart-object.php');
        $requesturl = $_SERVER["PHP_SELF"];
        $requesturl.= '?' . $_SERVER["QUERY_STRING"]. '&givedata=true';
        if ($imagetype=='big')
          {echo open_flash_chart_object_str($flashWBig,$flashHBig,$requesturl,false,'');}
	else
          {echo open_flash_chart_object_str($flashWSmall,$flashHSmall,$requesturl,false,'');} 
          
	exit();
	}
   
     
   if ($priority == 'all')
     {
     $MonthArrayRel = returnMonthArrayAbs($period,'d'); 
     }
   else
     {
     $MonthArrayRel = returnMonthArrayRel($period,'d.m');

     }      
      $arrayX_text        =  return_arrayX_to_text($arrayX);
      $loadinfo           =  loadinfo($time_start);
      $loadtime_title     =  WinToHTMLChars($loadinfo['image']);
      unset($loadinfo);                                   
      $online_title       =  WinToHTMLChars(   $language['onlinetext1'] . "#val#" . $language['mentext1'] );
      $version_title      =  $language['monitorglobaltext1'] . $config['verison'];
      $MonthArrayRel      =  return_arrayX_to_text2($MonthArrayRel);
      $bar_colour         =  '#D54C78';
      $x_axis_colour      =  '#909090'; 
      $bg_colour          =  '#EFEFEF';//'#F8F8F8';//'#F8F8D8'; 
      $y_axis_colour      =  '#5555f'; 
      $y_axis_grid_colour =  '#DDDDDD';//'#eeeeee';
      $x_axis_grid_colour =  '#DDDDDD';//'#eeeeee';
      
$output = '{ 
"title": { "text": "' . $title . '" }, 
"elements": 
[
 	    {
	     "type": "bar_3d", 
	     "tip": "' . $online_title . '", 
	     "colour": "' . $bar_colour . '", 
	     "line-style": { "style": "dash", "on": 4, "off": 4 }, 
	     "dot-style": { "type": "hollow-dot", "width": 1, "size": 2 }, 
	     "values": [ ' . $arrayX_text . ' ], 
	     "fill-alpha": 0.5, 
	     "width": 5, 
	     ' . $on_show . ' 
	    },
	    
            	 {
			"type":      "line",
			"colour":    "#736AFF",
			"font-size": 1,
			"width":     1,
			"dot-style": {
				"type":"solid-dot", "colour":"' . $bg_colour . '", "dot-size": 3,
				"tip":"' . $loadtime_title . '" },
			"values" : [{"x":5, "y": "' . intval($max*1.1) . '" } ]
		},
		{
			"type":      "line",
			"colour":    "#736AFF",
			"font-size": 1,
			"width":     1,
			"dot-style": {
				"type":"solid-dot", "colour":"' . $bg_colour . '", "dot-size": 3,
				"tip":"' . $version_title . '" },
			"values" : [{"x":6, "y": "' . intval($max*1.1) . '" } ]
		}

],

"bg_colour": "' . $bg_colour . '",
"x_axis": { "3d": 5, 
	    "colour": "' . $x_axis_colour . '", 
	    "grid-colour": "' . $x_axis_grid_colour . '", 
	    "offset": true,
	        "labels": {

      "rotate": -45,

      "labels": [ ' . $MonthArrayRel . ' ]
    } 
	  }, 

"y_axis": { "min": 0, 
	    "max": ' . intval($max*1.1) . ', 
	    "steps": 1, 
	    "grid-colour": "' . $y_axis_grid_colour . '", 
	    "colour": "' . $y_axis_colour . '" 
	  }, 

  "tooltip":{
    "mouse": 2,
    "stroke":1
  } 	  
}';    
       
    // выводим data для swf файла
    echo $output;
    
    // если в конфиге стоит true для записи логов longbuild - пишем лог  
    if ($config['longbuildtxtlog'])
      {
      $logger = new txtLogger("longbuild");
      unset($logger);  	
      }
    }
    else
    { 
	 
	 // подключаем библиотеки pChart
	 include_once(ROOT_DIR . "/include/pChart/pChart/pData.class");   
	 include_once(ROOT_DIR . "/include/pChart/pChart/pChart.class");  
	 // Dataset definition 
	 $DataSet = new pData;
	 $DataSet->AddPoint($arrayX, "Serie1");
	 
	 if ($priority == 'all')
      	   {
      	   $MonthArrayRel = returnMonthArrayAbs($period); 
      	   }
    	 else 
     	   {
     	   $MonthArrayRel = returnMonthArrayRel($period);
     	   }
	 $DataSet->AddPoint($MonthArrayRel,"Serie3");
	 $DataSet->AddSerie("Serie1");
	 $DataSet->SetAbsciseLabelSerie("Serie3");
	 $DataSet->SetSerieName(WinToHTMLChars('Онлайн'),"Serie1");

	 
	 // Initialise the graph
	 if ($imagetype == 'big')
	   {
	   $Test = new pChart(1200,394);
	   $Test->setGraphArea(85,51,1165,342);
	   $Test->drawFilledRoundedRectangle(7,7,1192,382,5,240,240,240);
	   $Test->drawRoundedRectangle(5,5,1191,385,5,230,230,230);
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",13);
	   }
	 else
	   {
	   $Test = new pChart(700,230);
	   $Test->setGraphArea(50,30,680,200);
	   $Test->drawFilledRoundedRectangle(7,7,693,223,5,240,240,240);
	   $Test->drawRoundedRectangle(5,5,695,225,5,230,230,230);
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",8);
	   }  	 
	 
	 $Test->drawGraphArea(255,255,255,TRUE);
	 $Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2,TRUE);
	 $Test->drawGrid(4,TRUE,230,230,230,50);
	

 	 if ($imagetype == 'big')
	   {
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",10);
	   }
	 else
	   {
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",6);
	   }  	 
         // Draw the 0 line
	 $Test->drawTreshold(0,143,55,72,TRUE,TRUE);
	 // Draw the bar graph
	 $Test->drawBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),TRUE);	
 	 
 	 if ($imagetype == 'big')
	   {
           $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",13);
	   $Test->writeValues($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie1"); 
           $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",13);
	   $Test->drawLegend(1021,60,$DataSet->GetDataDescription(),255,255,255);
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",17);
     	   $Test->drawTitle(85,37,$title,50,50,50,994);
	   }
	 else
	   {
           $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",8); 
           $Test->writeValues($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie1");
           $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",8);
	   $Test->drawLegend(596,35,$DataSet->GetDataDescription(),255,255,255); 
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",10);	   
	   $Test->drawTitle(50,22,$title,50,50,50,580);
	   }
	 
	 // находим время загрузки страницы и кол-во SQL запросов
	 $loadinfo = loadinfo($time_start); 
	 if ($imagetype == 'big')
	   {
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",12);
	   $Test->drawTitle(994,39,WinToHTMLChars($loadinfo['image']),50,50,50,1157);
	   $Test->drawTitle(1345,497,$language['monitorglobaltext1'] . $config['verison'],50,50,50,1165,"",90);
	   }
	 else
	   {
	   $Test->setFontProperties("./include/pChart/Fonts/tahoma.ttf",7);
	   $Test->drawTitle(580,23,WinToHTMLChars($loadinfo['image']),50,50,50,675);
	   $Test->drawTitle(785,290,$language['monitorglobaltext1'] . $config['verison'],50,50,50,680,"",90);
	   
	   }	 
	 
	 // генерируем картинку и выдаем в stream
	 $Test->Stroke();

	 // если в конфиге стоит true для записи логов longbuild - пишем лог
  	 if($config['longbuildtxtlog'])
    	   {
    	   $logger = new txtLogger("longbuild");
    	   unset($logger);  	
    	   }
      }	   
    }  
    
  exit;
  }  

?>