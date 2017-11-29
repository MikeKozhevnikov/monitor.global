<?php 

/*

==================================================
 Monitor Global
--------------------------------------------------
 2007-2009, Mike Kozhevnikov
==================================================
 File: /include/functions.inc.php
==================================================
 Назначение: Файл с различными функциями системы
==================================================

*/

// функция, возвращающая название сервера, входные данные: ид сервера
function get_servername($serverid)
{
global $config,$db_sql;
$queryName = $db_sql->sql_query("
                              SELECT name  
  	    			                FROM " . $config['servers_table'] . " 
  				                    WHERE id='" . $serverid . "' 
  				                    LIMIT 1;");
  				     
$queryName  = $db_sql->fetch_array($queryName);          
$servername = $queryName['name'];
    
return $servername;    
}

// функция проверки языковых cookie. при нахождении на компьютере cookie - берет его, при отсутствии - передает cookie 
// с дефолтным значением: "ru" 
function language_cookie()
{
if (isset ( $_COOKIE['language'] ) and $_COOKIE['language'] != '')
  {
  if ($_COOKIE['language']!='en' && $_COOKIE['language']!='ru' && $_COOKIE['language']!='de' && $_COOKIE['language']!='blr')
    {
    $lang = 'ru';
    set_cookie ( "language", 'ru', 365 );
    }
  else
    {$lang = $_COOKIE['language'];}
  }
else
  {
  set_cookie( "language", 'ru', 365 );
  $lang = 'ru';
  }
  
return $lang; 
}

// функция установки cookie
function set_cookie($name, $value, $expires) {
	
if( $expires ) 
  $expires = time() + ($expires * 86400);
else 
  $expires = FALSE;

setcookie($name, $value, $expires);

}

// функция преобразования даты вида '2009-08-21' в вид '21.08.2009'
function dateToNormal ($time)
{
$time = mktime (0,0,0,$time{5}.$time{6},$time{8}.$time{9},$time{0}.$time{1}.$time{2}.$time{3});
$time = date("d.m.Y",$time);

return $time;
}

// функция, выводящая отформатированную информацю о загрузке страницы: 
// время исполнения скрипта, количество sql запросов
function loadinfo($time_start)
{
global $query_count,$language;
$time_end = getmicrotime();
$loadtime = $time_end - $time_start;
if (strstr($loadtime, "E-"))
  $loadtime = 0.0001;
else  
  {
  preg_match("([\d]*\.\d\d\d)",$loadtime,$m);
  $loadtime = floatval($m[0]);
  if ($loadtime == 0)
    {$loadtime = 0.0001;} 
  }
if (!$query_count)
  $query_count = 0;  

$loadinfo['full']     =  $language['pagebuildtime'] . ': ' . $loadtime . $language['pagebuildtime2'] . $query_count . '.';
$loadinfo['image']    =  'Build in: '. $loadtime . ' sec ' . 'SQL: ' . $query_count;
$loadinfo['loadtime'] =  $loadtime;
$loadinfo['sql']      =  $query_count;

return $loadinfo; 
}

// функция переадресации при ошибке, при необходимости вывести сообщение об ошибке
function rideSite($ride_url, $info) 
{
global $config,$language,$lang;

$time_start = getmicrotime();

$title = $language['siteridetitle'];
$header = file_get_contents ('./template/' . $lang . 'header.html');
$footer = file_get_contents ('./template/' . $lang . 'footer.html');
$menu = file_get_contents ('./template/' . $lang . 'menu.html');
$main = '<h1>' . $language['siteridetitle'] . '</h1><br>';	

$javascript = '';//$header_script1 . $header_script2;

$main.= '<br>' . $info . '<br><br><br><br><br><br><br><br><br><br><br><br><br><br>';
$main.= '<div class="divider"></div>';


$loadinfo = loadinfo($time_start);

$meta = "<!--meta-->\n" . '<meta http-equiv="refresh" content="3;URL=' . $ride_url . '" />';
$footer = str_replace("{LOADINFO}",$loadinfo['full'],$footer);
$header = str_replace("{JAVASCRIPT}",$javascript,$header);
$header = str_replace("<!--meta-->",$meta,$header);
$header = str_replace("{TITLE}",$title,$header);
$output = $header . $menu . $main . $footer;
echo $output;

}

// вычисление даты предыдущего дня
function getPastDayDate($time)
{
$time = mktime (0,0,0,$time{5}.$time{6},$time{8}.$time{9},$time{0}.$time{1}.$time{2}.$time{3});
$time = date("Y-m-d", $time - 86400*1);

return $time;
}

// вычисление даты предыдущей недели для недели $time
// выходная дата относительная, т.е день - произвольный день из диапазопа дней недели
function getPastWeekDate($time)
{
$time = mktime (0,0,0,$time{5}.$time{6},$time{8}.$time{9},$time{0}.$time{1}.$time{2}.$time{3});
$time = date("Y-m-d", $time - 86400*7);

return $time;
}
// вычисление даты предыдущего месяца для месяца $time
// с контролем "проблемных" месяцев и "проблемных" дней
// выходная дата относительная, т.е день - произвольное число из диапазопа чисел месяца
function getPastMonthDate($time)
{
$day = $time{8}.$time{9};
$time = mktime (0,0,0,$time{5}.$time{6},$time{8}.$time{9},$time{0}.$time{1}.$time{2}.$time{3});
if ($day >= 1 && $day <= 15)
  {$time = date("Y-m-d", $time - 86400*17);}
if ($day >=16 && $day <= 31) 
  {$time = date("Y-m-d", $time - 86400*35);}

return $time;
}


// функция вычисления размера базы данных и количества строк в ней
function getMySQLSize()
{
global $db_sql,$config;                                                 

$mysql_size     =  0;
$mysql_rownums  =  0;

$query = $db_sql->sql_query("SHOW TABLE STATUS FROM `" . $config['dbName'] . "`");

while ($query2 = $db_sql->fetch_array ($query))
  {
  $mysql_size += $query2['Data_length'] + $query2['Index_length'];
  $mysql_rownums += $query2['Rows'];
  }
$mysql_size= $mysql_size/1024;
preg_match("([\d]*\.\d)",$mysql_size,$mysql_size); 
$mysql_info[0] = $mysql_size[0];
$mysql_info[1] = $mysql_rownums;

return $mysql_info;
}

// возвращает максимум онлайна по данным за день
// при вызове необходимо следить, чтобы переменная $query не перекрывалась другим запросом к Ѕƒ
function getMax()
{
global $query;
$max=0;
for ($i=0;$i<=23;$i++)
  {
  if($query[time . $i] > $max)
  $max = $query[time . $i];
  }
return $max;
}

// возвращает средний онлайн по данным за день
// при вызове необходимо следить, чтобы переменная $query не перекрывалась другим запросом к Ѕƒ
function getAverage()
{
global $query;
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
if (!$j)
  $j=24;
$meanvalue = (int)($sum/$j);
return $meanvalue;
}

// функция, которая возвращает массив дат недели, в который потом будут заносится значения онлайна
function getWeekDataArray($weekbegin)
{
$time = mktime (0,0,0,$weekbegin{5}.$weekbegin{6},$weekbegin{8}.$weekbegin{9},$weekbegin{0}.$weekbegin{1}.$weekbegin{2}.$weekbegin{3});
for($i=1;$i<=7;$i++)
  {
  $temp = date("Y-m-d", $time + 86400*($i-1));
  $WeekDataArray[$temp] = 0;
  }

return $WeekDataArray;
}

// функция, которая возвращает массив дат месяца, в который потом будут заносится значения онлайна
function getMonthDataArray($monthbegin,$daysonmonth)
{
$time = mktime (0,0,0,$monthbegin{5}.$monthbegin{6},$monthbegin{8}.$monthbegin{9},$monthbegin{0}.$monthbegin{1}.$monthbegin{2}.$monthbegin{3});
if ($daysonmonth)
  {
  for($i=1;$i<=$daysonmonth;$i++)
    {
    $temp = date("Y-m-d", $time + 86400*($i-1));
    $MonthDataArray[$temp] = 0;
    }
  }
else
  {
  for($i=1;$i<=30;$i++)
    {
    $temp = date("Y-m-d", $time + 86400*($i-1));
    $MonthDataArray[$temp] = 0;
    }
  }  

return $MonthDataArray;
}

// функция преобразования кириллицы в коды HTML, необходима для рисования кириллицы на графиках pChart и jpGraph
// работает с любыми символами: латиница, кириллица, спец знаки, преобразует только необходимое,
// остальное - оставляет в неизменном виде
function WinToHTMLChars($string)
{
$codtable = array(
	"ј" => "&#1040;",  "Ѕ" => "&#1041;",   "¬" => "&#1042;", 	"√" => "&#1043;",   "ƒ" => "&#1044;",
	"≈" => "&#1045;",  "∆" => "&#1046;",   "«" => "&#1047;", 	"»" => "&#1048;",   "…" => "&#1049;",
	" " => "&#1050;",  "Ћ" => "&#1051;",   "ћ" => "&#1052;",	"Ќ" => "&#1053;",   "ќ" => "&#1054;",
	"ѕ" => "&#1055;",  "–" => "&#1056;",   "—" => "&#1057;",	"“" => "&#1058;",   "”" => "&#1059;",
	"‘" => "&#1060;",  "’" => "&#1061;",   "÷" => "&#1062;",	"„" => "&#1063;",	"Ў" => "&#1064;",
	"ў" => "&#1065;",  "Џ" => "&#1066;",   "џ" => "&#1067;",	"№" => "&#1068;",	"Ё" => "&#1069;",
	"ё" => "&#1070;",  "я" => "&#1071;",   "а" => "&#1072;",	"б" => "&#1073;",	"в" => "&#1074;",
	"г" => "&#1075;",  "д" => "&#1076;",   "е" => "&#1077;",	"ж" => "&#1078;",	"з" => "&#1079;",
	"и" => "&#1080;",  "й" => "&#1081;",   "к" => "&#1082;",	"л" => "&#1083;",	"м" => "&#1084;",
	"н" => "&#1085;",  "о" => "&#1086;",   "п" => "&#1087;",	"р" => "&#1088;",	"с" => "&#1089;",
	"т" => "&#1090;",  "у" => "&#1091;",   "ф" => "&#1092;",	"х" => "&#1093;",	"ц" => "&#1094;",
	"ч" => "&#1095;",  "ш" => "&#1096;",   "щ" => "&#1097;",	"ъ" => "&#1098;",	"ы" => "&#1099;",
	"ь" => "&#1100;",  "э" => "&#1101;",   "ю" => "&#1102;",	"я" => "&#1103;",	"0" => "0",
	"1" => "1",        "2" => "2",         "3" => "3",		    "4" => "4",		    "5" => "5",	
	"6" => "6",        "7" => "7",  		   "8" => "8",		    "9" => "9",		    "A" => "A",
	"B" => "B",        "C" => "C",		     "D" => "D",		    "E" => "E",		    "F" => "F",
	"G" => "G",        "H" => "H",		     "I" => "I", 		    "J" => "J",		    "K" => "K",
	"L" => "L",        "M" => "M",  		   "N" => "N", 		    "O" => "O",  		  "P" => "P",
	"Q" => "Q",        "R" => "R",         "S" => "S",   		  "T" => "T",    		"U" => "U",
	"V" => "V",        "W" => "W",         "X" => "X", 		    "Y" => "Y",  		  "Z" => "Z",
	"a" => "a",        "b" => "b", 		     "c" => "c",    		"d" => "d",     	"e" => "e",
	"f" => "f",        "g" => "g",  		   "h" => "h",      	"i" => "i", 		  "j" => "j",
	"k" => "k",        "l" => "l", 		     "m" => "m", 		    "n" => "n", 		  "o" => "o",
	"p" => "p",        "q" => "q",		     "r" => "r",  		  "s" => "s", 		  "t" => "t",
	"u" => "u",        "v" => "v",  		   "w" => "w",  		  "x" => "x",  		  "y" => "y",
	"z" => "z",        "А" =>"&#1026;",	   "Б" =>"&#1027;",  	"В" =>"&#8218;",  "Г" =>"&#1107;",
	"Д" =>"&#8222;",   "Е" =>"&#8230;",  	 "Ж" =>"&#8224;", 	"З" =>"&#8225;", 	"ђ" =>"&#172;",
	"Й" =>"&#8240;",   "К" =>"&#1033;", 	 "Л" =>"&#8249;", 	"М" =>"&#1034;", 	"Н" =>"&#1036;",
	"О" =>"&#1035;",   "П" =>"&#1039;",  	 "Р" =>"&#1106;",  	"С" =>"&#8216;",  "Т" =>"&#8217;",
	"У" =>"&#8220;",   "Ф" =>"&#8221;",    "Х" =>"&#8226;",   "Ц" =>"&#8211;",  "Ч" =>"&#8212;",
	"Щ" =>"&#8482;",   "Ъ" =>"&#1113;",    "Ы" =>"&#8250;",   "Ь" =>"&#1114;",  "Э" =>"&#1116;",
	"Ю" =>"&#1115;",   "Я" =>"&#1119;",  	 "°" =>"&#1038;",   "¢" =>"&#1118;",  "£" =>"&#1032;",
	"§" =>"&#164;",    "•" =>"&#1168;",  	 "¶" =>"&#166;",   	"І" =>"&#167;",   "®" =>"&#1025;",
	"©" =>"&#169;",    "™" =>"&#1028;",    "Ђ" =>"&#171;",    "ђ" =>"&#172;",   "Ѓ" =>"&#174;",
	"ѓ" =>"&#1031;",   "∞" =>"&#176;",   	 "±" =>"&#177;",  	"≤" =>"&#1030;",  "≥" =>"&#1110;",
	"і" =>"&#1169;",   "µ" =>"&#181;",     "∂" =>"&#182;",   	"Ј" =>"&#183;",   "Є" =>"&#1105;",
	"є" =>"&#8470;",   "Ї" =>"&#1108;",    "ї" =>"&#187;",    "њ" =>"&#1111;",  "[" =>"[",
	"\\" =>"\\",       "]" =>"]",       	 "^" =>"^",      	  "_" =>"_",    		"`" =>"`",
	":" =>":",         ";" =>";",       	 "<" =>"<",       	"=" =>"=",       	">" =>">",
	"@" =>"@",         "-" =>"-",    		   "." =>".",         "/" =>"/",      	"!" =>"!",
	"#" =>"#",         "$" =>"$",      	   "%" =>"%",   		  "&" =>"&",       	"'" =>"'",
	"(" =>"(",         ")" =>")",   		   "*" =>"*",    		  "+" =>"+",  		  "," =>",",
	"-" =>"-",         "." =>".",  		     "/" =>"/",     		"?" =>"?",   		  " " => " ",
	"," => ",",        "{" =>"{",   		   "|" =>"|", 		    "}" =>"}",     		"~" =>"~",
	"?" =>"?",
 	);
 
$len = strlen($string);
for ($i=0;$i<$len;$i++)
  {$newstring = $newstring . $codtable[$string[$i]];}

return $newstring; 
}

// функция, которая возвращает значение даты, соответствующей дате начала записи данных для указанного сервера
function getLogStartTime($serverid)
{
global $db_sql, $config;
$query = $db_sql->sql_query("SELECT date 
       	                     FROM " . $config['statslog_table'] . " 
			     WHERE serverid=" . $serverid ." 
			     ORDER BY date ASC 
			     LIMIT 1;");
$query1 = $db_sql->fetch_array ($query);

return $query1['date'];
}

// функция для выдачи времени в миллисекундах. используется для получения времени генерирования страницы
function getmicrotime()
{ 
list($usec, $sec) = explode(" ",microtime()); 

return ((float)$usec + (float)$sec); 
} 

// функция вывода подписей к графику для относительной недели
// необязательный параметр $type = "HTML" or "JAVA", используется в зависимости от типов графиков - пхп или флэш
function returnWeekArrayRel($time1,$type="HTML")
{
global $language;
$time1 = mktime (0,0,0,$time1{5}.$time1{6},$time1{8}.$time1{9},$time1{0}.$time1{1}.$time1{2}.$time1{3});
if ($type=="HTML")
{
for ($i=0;$i<=6;$i++)
  {
  $timetemp = $time1 + 86400*$i;
  $temp = date("l",$timetemp);
  switch ($temp)                              
    {
    case "Monday":
      $WeekArrayRel[$i] = $language['MondayHTMLchars'];
      break;
    case "Tuesday":
      $WeekArrayRel[$i] = $language['TuesdayHTMLchars'];
      break;
    case "Wednesday":
      $WeekArrayRel[$i] = $language['WednesdayHTMLchars'];
      break;
    case "Thursday":
      $WeekArrayRel[$i] = $language['ThursdayHTMLchars'];
      break;
    case "Friday":
      $WeekArrayRel[$i] = $language['FridayHTMLchars'];
      break;
    case "Saturday":
      $WeekArrayRel[$i] = $language['SaturdayHTMLchars'];
      break;
    case "Sunday":
      $WeekArrayRel[$i] = $language['SundayHTMLchars'];
      break;  
    }
  }
}

if ($type=="JAVA")
{
for ($i=0;$i<=6;$i++)
  {
  $timetemp = $time1 + 86400*$i;
  $temp = date("l",$timetemp);
  switch ($temp)                              
    {
    case "Monday":
      $WeekArrayRel[$i] = $language['MondayJAVAchars'];
      break;
    case "Tuesday":
      $WeekArrayRel[$i] = $language['TuesdayJAVAchars'];
      break;
    case "Wednesday":
      $WeekArrayRel[$i] = $language['WednesdayJAVAchars'];
      break;
    case "Thursday":
      $WeekArrayRel[$i] = $language['ThursdayJAVAchars'];
      break;
    case "Friday":
      $WeekArrayRel[$i] = $language['FridayJAVAchars'];
      break;
    case "Saturday":
      $WeekArrayRel[$i] = $language['SaturdayJAVAchars'];
      break;
    case "Sunday":
      $WeekArrayRel[$i] = $language['SundayJAVAchars'];
      break;  
    } 
  }
}


return $WeekArrayRel;
}

// функция вывода подписей к графику для абсолютной недели
// необязательный параметр $type = "HTML" or "JAVA", используется в зависимости от типов графиков - пхп или флэш
function returnWeekArray($type='HTML')
{
global $language;
if ($type=='HTML')
{
$WeekArray[0] = $language['MondayHTMLchars'];
$WeekArray[1] = $language['TuesdayHTMLchars'];
$WeekArray[2] = $language['WednesdayHTMLchars'];
$WeekArray[3] = $language['ThursdayHTMLchars'];
$WeekArray[4] = $language['FridayHTMLchars'];
$WeekArray[5] = $language['SaturdayHTMLchars'];
$WeekArray[6] = $language['SundayHTMLchars'];
}
if ($type=='JAVA')
{
$WeekArray[0] = $language['MondayJAVAchars'];
$WeekArray[1] = $language['TuesdayJAVAchars'];
$WeekArray[2] = $language['WednesdayJAVAchars'];
$WeekArray[3] = $language['ThursdayJAVAchars'];
$WeekArray[4] = $language['FridayJAVAchars'];
$WeekArray[5] = $language['SaturdayJAVAchars'];
$WeekArray[6] = $language['SundayJAVAchars'];
}
return $WeekArray;
}

// функция вывода подписей к графику для дня
// входные данные: необязательные данные: тип выходных данных: $type = 'HTML' или 'JAVA' в зависимости от типа 
// графика: пхп или флэш; формат вывода: $text = false или true в зависимости от того хотите ли ¬ы получить 
// массив или строку с данными 
function returnDayArray($type='HTML',$text=false)
{
global $language;
if ($type=='HTML')
{
  if ($text)
  {
  for ($i=0;$i<=23;$i++)
    {
    $DayArray.= '"' . $i . $language['hourword']. '", ';          
    }
  $DayArray = substr($DayArray, 0, strlen($DayArray)-2);     
  }
  else
  {
  for ($i=0;$i<=23;$i++)
    {
    $DayArray[$i] = $i . $language['hourword'];
    }
  }
}

if ($type=='JAVA')
{
  if ($text)
  {
  for ($i=0;$i<=23;$i++)
    {
    $DayArray.= '"' . $i . $language['hourwordJAVAchars']. '", ';        
    }
  $DayArray = substr($DayArray, 0, strlen($DayArray)-2);    
  }
  else
  {
  for ($i=0;$i<=23;$i++)
    {
    $DayArray[$i] = $i . $language['hourwordJAVAchars'];
    }
  }
}

return $DayArray;
}

// функция перевода данных массива в строку
// последняя запятая опускается
// like: 10,20,180
function return_arrayX_to_text($arrayX)
{
foreach($arrayX as $val)
{$arrayX_text.=$val . ', ';}
$arrayX_text = substr($arrayX_text, 0, strlen($arrayX_text)-2); 

return $arrayX_text;
}

// функция перевода данных массива в строку с кавычками
// последняя запятая опускается
// like: "20","30","180"
function return_arrayX_to_text2($arrayX)
{
foreach($arrayX as $val)
{$arrayX_text.='"' . $val . '", ';}
$arrayX_text = substr($arrayX_text, 0, strlen($arrayX_text)-2); 

return $arrayX_text;
}


// функция вывода подписей к графику для относительного месяца
function returnMonthArrayRel($time,$format='d')
{
$time = mktime (0,0,0,$time{5}.$time{6},$time{8}.$time{9},$time{0}.$time{1}.$time{2}.$time{3});

for ($i=1;$i<=30;$i++)
  {$MonthArrayRel[$i] = date($format, ($time - 86400*(30-$i)));}

return $MonthArrayRel;
}
  
// функция вывода подписей к графику для абсолютного месяца
function returnMonthArrayAbs($time,$format='d')
{
$timebackup = $time;
$time = mktime (0,0,0,$time{5}.$time{6},$time{8}.$time{9},$time{0}.$time{1}.$time{2}.$time{3});
$temp = date("t",$time);
$timestart = mktime (0,0,0,$timebackup{5}.$timebackup{6},01,$timebackup{0}.$timebackup{1}.$timebackup{2}.$timebackup{3}); 
for ($i=1;$i<=$temp;$i++)
  {$MonthArrayAbs[$i] = date($format, ($timestart + 86400*($i-1)));}
  
return $MonthArrayAbs;
}  

// функция вывода данных о месяце (начало месяца, конец месяца, кол-во дней в месяце) для относительного месяца
function returnMonthData($time)
{
$timebackup = $time;
$time 	    = mktime (0,0,0,$time{5}.$time{6},$time{8}.$time{9},$time{0}.$time{1}.$time{2}.$time{3});
$temp 	    = date("t",$time);

$monthdata['monthbeginrel']  =  date("Y-m-d", $time - 86400*29);
$monthdata['monthendrel']    =  date("Y-m-d", $time);
$monthdata['monthbeginabs']  =  date("Y-m-d", mktime (0,0,0,$timebackup{5}.$timebackup{6},01,$timebackup{0}.
			     	     	      	      $timebackup{1}.$timebackup{2}.$timebackup{3}));
$monthdata['monthendabs']    =  date("Y-m-d", mktime (0,0,0,$timebackup{5}.$timebackup{6},(int)$temp,
			     	     	      	      $timebackup{0}.$timebackup{1}.$timebackup{2}.$timebackup{3}));
$monthdata['daysonmonth']    =  $temp;
$monthdata['monthandyear']   =  date("m.Y", mktime (0,0,0,$timebackup{5}.$timebackup{6},(int)$temp,$timebackup{0}.
			     	     	    	    $timebackup{1}.$timebackup{2}.$timebackup{3}));

return $monthdata;
}


// функция вывода данных о неделе (начало недели, конец недели) для относительной и для абсолютной недели
function returnWeekData($time)
{
$time = mktime (0,0,0,$time{5}.$time{6},$time{8}.$time{9},$time{0}.$time{1}.$time{2}.$time{3});
$temp = date("l",$time);
$weekdata['weekbeginrel'] = $time - 86400*6;
$weekdata['weekendrel'] = $time;
switch ($temp)                              
  {
  case "Monday":
    $weekdata['weekbeginabs']  = $time;
    $weekdata['weekendabs']    = $time + 86400*6;
    break;
  case "Tuesday":
    $weekdata['weekbeginabs']  = $time - 86400;
    $weekdata['weekendabs']    = $time + 86400*5;
    break;
  case "Wednesday":
    $weekdata['weekbeginabs']  = $time - 86400*2;
    $weekdata['weekendabs']    = $time + 86400*4;
    break;
  case "Thursday":
    $weekdata['weekbeginabs']  = $time - 86400*3;
    $weekdata['weekendabs']    = $time + 86400*3;
    break;
  case "Friday":
    $weekdata['weekbeginabs']  = $time - 86400*4;
    $weekdata['weekendabs']    = $time + 86400*2;
    break;
  case "Saturday":
    $weekdata['weekbeginabs']  = $time - 86400*5;
    $weekdata['weekendabs']    = $time + 86400;
    break;
  case "Sunday":
    $weekdata['weekbeginabs']  = $time - 86400*6;
    $weekdata['weekendabs']    = $time;
    break;  
  }
  
$temp11 = $weekdata['weekbeginrel'];
$temp22 = $weekdata['weekendrel'];
$temp33 = $weekdata['weekbeginabs'];
$temp44 = $weekdata['weekendabs'];
  
$weekdata['weekbeginrel']  =  date("Y-m-d", $weekdata['weekbeginrel']);
$weekdata['weekendrel']    =  date("Y-m-d", $weekdata['weekendrel']);
$weekdata['weekbeginabs']  =  date("Y-m-d", $weekdata['weekbeginabs']);
$weekdata['weekendabs']    =  date("Y-m-d", $weekdata['weekendabs']);
  
// день начало-конец относительной недели
$weekdata['weekbeginendrelday'] = date("d", $temp11) . "-" . date("d", $temp22);
// день начало-конец абсолютной недели
$weekdata['weekbeginendabsday'] = date("d", $temp33) . "-" . date("d", $temp44);

// обрабатываем исключительный случай недели на стыке месяцев
// для относительной недели
if (date("m", $temp11) != date("m", $temp22))
  $weekdata['weekbeginendrelmonth'] = date("m", $temp11) . "-" . date("m", $temp22);  
else
  $weekdata['weekbeginendrelmonth'] = date("m", $temp11);
// лдя абсолютной недели
if (date("m", $temp33) != date("m", $temp44))
  $weekdata['weekbeginendabsmonth'] = date("m", $temp33) . "-" . date("m", $temp44);  
else
  $weekdata['weekbeginendabsmonth'] = date("m", $temp33); 
  
// обрабатываем исключительный случай недели на стыке годов
// для относительной недели
if (date("Y", $temp11) != date("Y", $temp22))
  $weekdata['weekbeginendrelyear'] = date("Y", $temp11) . "-" . date("Y", $temp22);  
else
  $weekdata['weekbeginendrelyear'] = date("Y", $temp11);
// для абсолютной недели
if (date("Y", $temp33) != date("Y", $temp44))
  $weekdata['weekbeginendabsyear'] = date("Y", $temp33) . "-" . date("Y", $temp44);  
else
  $weekdata['weekbeginendabsyear'] = date("Y", $temp33); 
        
return $weekdata;
}

// функция определения имени нужной ячейки массива при заносе информации в базу данных под определенным часом.
function returnTimeVal()
{
return 'time' . date("G");
}

// конвертация utf8 кодировки в win кодировку (необходима при обновлении данных через statsupdate.php)	
function utf8_win($s)
{ 
  $out=""; 
  $c1=""; 
  $byte2=false; 
  for ($c=0;$c<strlen($s);$c++)
    { 
    $i=ord($s[$c]); 
    if ($i<=127) 
      $out.=$s[$c]; 
    if ($byte2)
      { 
      $new_c2=($c1&3)*64+($i&63); 
      $new_c1=($c1>>2)&5; 
      $new_i=$new_c1*256+$new_c2; 
      if ($new_i==1025)
        { 
        $out_i=168; 
      }else{ 
      if ($new_i==1105){ 
      $out_i=184; 
      }else { 
      $out_i=$new_i-848; 
      } 
      } 
      $out.=chr($out_i); 
      $byte2=false; 
      } 
      if (($i>>5)==6) 
        { 
        $c1=$i; 
        $byte2=true; 
	} 
	} 
	
return $out; 
}

?>