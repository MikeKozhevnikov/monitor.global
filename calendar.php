<?php

/*

============================================================
 Monitor Global
------------------------------------------------------------
 2007-2009, Mike Kozhevnikov
============================================================
 File: calendar.php
============================================================
 Назначение: Календарь для выбора дат на страницах sandbox
============================================================

*/

error_reporting(55);

include_once("./include/config.inc.php");
include_once("./include/class.db.php");
include_once("./include/functions.inc.php");

echo '<link rel="stylesheet" type="text/css" media="all" href="template/calendar-blue.css" />
<script type="text/javascript" src="template/js/calendar.js"></script>
<script type="text/javascript" src="template/js/calendar-en.js"></script>
<script type="text/javascript" src="template/js/calendar-setup.js"></script>'; 

echo '<form action="x3" method="get">
  Дата: <input type="text" name="newdate" id="f_date_c" size="10"  class=edit><img src="template/images/img.gif"  align="absmiddle" id="f_trigger_c" style="cursor: pointer; border: 0" title="Выбор даты с помощью календаря"/>
  <br>
  Период: <select name="name1">
  <option value="day">День</option>
  <option value="week">Неделя</option>
  <option value="month">Месяц</option>
  </select>
  <br>
  График: <select name="name1">
  <option value="sda">Онлайн (кол-во человек)</option>
  <option value="sda">Онлайн\оффлайн сервера</option>
  </select> 
  <br>  
  Значения : <select name="name1">
    <option value="sda">Средние</option>
    <option value="sda">Максимальные</option>
  </select> 
   <br> 
  Категория: <select name="name1">
   <option value="sda">abcbca</option>
   <option value="sda">absscbca</option>
  </select>  
  <br>
    Сервер: <select name="name1">
    <option value="sda">abcbca</option>
    <option value="sda">absscbca</option>
  </select> 
    
  <br>  
  Относительно другого сервера?: <select name="name1">
  <option value="sda">Нет</option>
  <option value="sda">Да</option>
  </select>
  <br>
  Дата: <input type="text" name="newdate" id="f_date_d" size="10"  class=edit><img src="template/images/img.gif"  align="absmiddle" id="f_trigger_d" style="cursor: pointer; border: 0" title="Выбор даты с помощью календаря"/>
  <br>
  Категория: <select name="name1">
  <option value="sda">abcbca</option>
  <option value="sda">absscbca</option>
  </select>  
  <br>
    Сервер: <select name="name1">
    <option value="sda">abcbca</option>
    <option value="sda">absscbca</option>
  </select> ';

echo '   <script type="text/javascript">
    Calendar.setup({
        inputField     :    "f_date_c",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "f_trigger_c",  // trigger for the calendar (button ID)
        align          :    "Br",           // alignment
		timeFormat     :    "24",
		showsTime      :    true,
        singleClick    :    true
    });
</script> ';

echo '   <script type="text/javascript">
    Calendar.setup({
        inputField     :    "f_date_d",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "f_trigger_d",  // trigger for the calendar (button ID)
        align          :    "Br",           // alignment
		timeFormat     :    "24",
		showsTime      :    true,
        singleClick    :    true
    });
</script> ';

?>