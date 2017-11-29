<?php

/*

====================================================================
 Monitor Global
--------------------------------------------------------------------
 2007-2009, Mike Kozhevnikov
====================================================================
 File: sandbox.php
====================================================================
 Назначение: Страница с выбором параметров для построения графиков
====================================================================

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

$title = 'monitor.global -> Sandbox';

$lang = language_cookie(); 

$header = file_get_contents (ROOT_DIR . '/template/' . $lang . '/header.html');
$footer = file_get_contents (ROOT_DIR . '/template/' . $lang . '/footer.html');
$menu = file_get_contents (ROOT_DIR . '/template/' . $lang . '/menu.html');
include_once(ROOT_DIR . "/language/" . $lang . ".php");

$output.= '<h1>monitor<span>.global</span> -> Sandbox</h1><br>';
	
$header_script2 = '<script type="text/javascript" src="template/js/calendar.js"></script>
<script type="text/javascript" src="template/js/calendar-en.js"></script>
<script type="text/javascript" src="template/js/calendar-setup.js"></script>';

$header_script3 = '
<script type="text/javascript" src="../template/highslide/highslide.js"></script>
<link rel="stylesheet" type="text/css" href="../template/highslide/highslide.css" />
<!--[if lt IE 7]>
<link rel="stylesheet" type="text/css" href="../template/highslide/highslide-ie6.css" />
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
$javascript.= $header_script2 . $header_script3 . $header_script4;

$db_sql = New db_sql($config['dbName'],$config['hostname'],$config['dbUname'],$config['dbPasswort']);

$output.=  "\n<script type=\"text/javascript\">
function showTooltip(id)
{
  var myDiv = document.getElementById(id);
  if(myDiv.style.display == 'none')
    {myDiv.style.display = 'block';} 
  else
    {myDiv.style.display = 'none';}

return false;
}
function closeTooltip(id)
{
  var myDiv = document.getElementById(id);
  myDiv.style.display = 'none';

return false;
}

function changevalue(id,value)
{
  document.getElementById(id).value = value;

return false;
}

</script>";

$output.= "\n" . '<link rel="stylesheet" type="text/css" media="all" href="template/calendar-blue.css" />'; 

$output.= "\n<script src=\"ajax/ajax_init.js\" type=\"text/javascript\"></script>\n<script src=\"ajax/ajax_sandbox.js\" type=\"text/javascript\"></script>\n<script type=\"text/javascript\">sandbox_categoryload();</script>";

$output.='<body>
' . $language['aboutsandbox'] . ':<br><br>
<form action="form1" method="get">
  <table width="50%">
  <tr><td width="30%">' . $language['sandbox_date'] . ':</td> 
  <td width="80%"><input type="text" name="newdate" id="f_date_c" size="10"  class=edit value="' . date("Y-m-d") . '"><img src="template/images/img.gif"  align="absmiddle" id="f_trigger_c" style="cursor: pointer; border: 0" title="Выбор даты с помощью календаря"/></td></tr>
  <tr><td width="30%">' . $language['sandbox_period'] . ': 
  </td> 
  <td width="80%"><select id=period name="name1" style="width:175px">
    <option value="day">' . $language['sandbox_day'] . '</option>
    <option value="week">' . $language['sandbox_week'] . '</option>
    <option value="month">' . $language['sandbox_month'] . '</option>           
  </select></td></tr>
  <tr><td width="30%">
  ' . $language['sandbox_graph'] . ':</td> 
  <td width="80%"> 
  <select id=graphtype name="name1" style="width:175px" onchange="' . "javascript:changedisable('valuetype');javascript:changedisable('relative');javascript:closeTooltip('rel');javascript:changevalue('graphtypelib','php');javascript:changevalue('relative','relno');javascript:changedisable('graphtypelib');" . '">
    <option value="online">' . $language['sandbox_online'] . '</option>
    <option value="onoff">' . $language['sandbox_onoff'] . '</option>
  </select></td></tr>
  <tr><td width="30%">
  Тип Графика:</td> 
  <td width="80%"> 
  <select id=graphtypelib name="name1" style="width:175px" onchange="' . "" . '">
    <option value="php">php</option>
    <option value="flash">flash</option>
  </select></td></tr>  
  <tr><td width="30%">
  ' . $language['sandbox_values'] . ':</td> 
  <td width="80%"> 
  <select id=valuetype style="width:175px">
    <option value="average">' . $language['sandbox_average'] . '</option>
    <option value="max">' . $language['sandbox_max'] . '</option>
  </select></td></tr> 

  <tr><td width="30%">' . $language['sandbox_category'] . ':</td> 
  <td width="80%"> 
  <select id="category1" style="width:175px" onChange="' . "javascript:sandbox_serversload1(document.getElementById('category1').value);" . '" >
  </select>            
  </td></tr>
  <tr><td width="30%">' . $language['sandbox_server'] . ':</td> 
  <td width="80%"> 
  <select id=serverslist1 style="width:175px">
  </select>  
  </td></tr>
  <tr><td width="30%">' . $language['sandbox_relativery'] . '</td> 
  <td width="80%"> 
  <select id=relative style="width:175px" onchange="javascript:showTooltip(\'rel\');" name="name1">
    <option value="relno">' . $language['sandbox_yes'] . '</option>
    <option value="relyes">' . $language['sandbox_no'] . '</option>
  </select>
  </table>
  <div id=rel style="display: none">
  <table width="50%">
  <tr><td width="30%">' . $language['sandbox_date2'] . ':</td> 
  <td width="80%"> 
  <input type="text" name="newdate" id="f_date_d" size="10"  class=edit value="' . date("Y-m-d") . '"><img src="template/images/img.gif"  align="absmiddle" id="f_trigger_d" style="cursor: pointer; border: 0" title="Выбор даты с помощью календаря"/>
  </td></tr>
  <tr><td width="30%">' . $language['sandbox_category2'] . ':</td> 
  <td width="80%"> 
  <select style="width:175px" id=category2 onChange="' . "javascript:sandbox_serversload2(document.getElementById('category2').value);" . '">
  </select>  
  </td></tr>
  <tr><td width="30%">' . $language['sandbox_server2'] . ':</td> 
  <td width="80%"> 
  <select style="width:175px" id=serverslist2>
  </select></td></tr> 
  </table>
  </div>
  <br>
  <a class="decor" onClick="' . "javascript:buildgraph('this');" . '">' . $language['sandbox_showbutton'] . '</a> 
  &nbsp;&nbsp;&nbsp;
  <a class="decor" onClick="' . "javascript:buildgraph('new');" . '">' . $language['openwenwindow'] . '</a>
  </form><br>';

$output.= '<div id=graph></div>';
$output.= '<div id=graphinfo></div>';
  
$output.= "\n" . '<script type="text/javascript">
Calendar.setup
  ({
  inputField  : "f_date_c",     
  ifFormat    : "%Y-%m-%d",      
  button      : "f_trigger_c",  
  align       : "Br",           
  timeFormat  : "24",
  showsTime   : true,
  singleClick : true
  });
</script>';

$output.= "\n" . '<script type="text/javascript">
Calendar.setup
  ({
  inputField  : "f_date_d",     
  ifFormat    : "%Y-%m-%d",      
  button      : "f_trigger_d",  
  align       : "Br",           
  timeFormat  : "24",
  showsTime   : true,
  singleClick : true
  });
</script>';

$output.= '<div class="divider"></div>';

$output.="\n";

$main = $output;

$loadinfo = loadinfo($time_start); 
$footer   = str_replace("{LOADINFO}",$loadinfo['full'],$footer);
$header   = str_replace("{JAVASCRIPT}",$javascript,$header);
$header   = str_replace("{TITLE}",$title,$header);
$output   = $header . $menu . $main . $footer;

echo $output;

?>         
