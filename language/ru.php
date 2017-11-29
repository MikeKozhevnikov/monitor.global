<?php

/*

=============================================
 Monitor Global
---------------------------------------------
 2007-2009, Mike Kozhevnikov
=============================================
 File: /language/ru.php
=============================================
 Назначение: Языковой файл, язык - русский
=============================================

*/

$language = array(
	  "titleindex" 						=> "monitor.global - система мониторинга интернет-сервисов",
	  "titleabout" 						=> "monitor.global - О системе",
	  "titlecategoryh1" 				=> "monitor.global -> Список категорий",
	  "categorydescriptionserver" 		=> "Описание",
	  "categoryvalueofservers" 			=> "Кол-во серверов",
	  "statsh1" 						=> "monitor.global -> статистика системы мониторинга",
	  "a1" 								=> 'Всего серверов в системе: <b><font color="green">%s</font></b>. Из них для <b><font color="green">%s</font></b> ведется мониторинг, для <b><font color="red">%s</font></b> не ведется<br><br>',

	  "a2" 								=> 'Максимальный онлайн за все время мониторинга был зарегистрирован на сервере <a class="decor" href="serverdetail.php?serverid=%s">%s</a> и составил <b><font color="green">%s</font></b><br><br>',

	  "serverPOinfo" 					=> "Информация о ПО сервера",
	  "titleabout" 						=> "monitor.global - О системе",
	  "mysqlveigth" 					=> "Объем базы данных",
	  "kb" 								=> "Кб",
	  "kolvostrings" 					=> "Количество строк",
	  "OS" 								=> "OC",
	  "servername" 						=> "Имя сервера",
	  "apacheversion" 					=> "Версия apache",
	  "phpversion" 						=> "Версия php",
	  "mysqlversion" 					=> "Версия mysql",
	  "serverlist_serverlistofcategory" => "monitor.global -> cписок серверов категории -> ",
	  "listofallservers" 				=> "cписок всех серверов системы мониторинга",
	  "listofallservers2" 				=> "monitor.global -> cписок всех серверов системы мониторинга",
	  "sortby" 							=> "Сортировать сервера по",
	  "abc" 							=> "алфавиту",
	  "startmonitoring" 				=> "началу записи данных о сервере",
	  "averageonline" 					=> "среднему онлайну",
	  "category" 						=> "Категория",
	  "description" 					=> "Описание",
	  "averageonlinelastday" 			=> "Средний онлайн за последний день",
	  "startmonitorforserver" 			=> "Система записывает данные о сервере с",
	  "stats_this_day" 					=> "Данные за текущий день",
	  "stats_of_day" 					=> "Статистика за день",
	  "clicktoenlarge" 					=> "Нажмите, чтобы увеличить",
	  "clicktoenlarge2" 				=> "нажмите на картинку, чтобы увеличить",
	  "stats_of_week" 					=> "Данные за неделю",
	  "stats_of_month" 					=> "Данные за месяц",
	  "stats_of_day_rel_last_day" 		=> "Данные за текущий день относительно предыдущего дня",
	  "stats_of_day_rel_last_day2" 		=> "Статистика за текущий день относительно предыдущего дня",
	  "stats_of_week_rel_last_week" 	=> "Данные за текущую неделю относительно данных по предыдущей неделе",
	  "stats_of_week_rel_last_week2" 	=> "Статистика за текущую неделю относительно предыдущей недели",
	  "stats_of_month_rel_last_month" 	=> "Данные за месяц относительно прошлого месяца",
	  "stats_of_month_rel_last_month2" 	=> "Статистика за месяц относительно прошлого месяца",
	  "onoffserverday" 					=> "Данные об онлайне\оффлайне сервера за день",
	  "onoffserverweek" 				=> "Данные об онлайне\оффлайне сервера за неделю",
	  "onoffservermonth" 				=> "Данные об онлайне\оффлайне сервера за месяц",
	  "onoffserverday2" 				=> "Статистика онлайна\оффлайна за день",
	  "onoffserverweek2" 				=> "Статистика онлайна\оффлайна за неделю",
	  "onoffservermonth2" 				=> "Статистика онлайна\оффлайна за месяц",
	  "serverstatstitle" 				=> "monitor.global -> cтатистика сервера -> ",
	  "serverstatsh1" 					=> "monitor.global -> cтатистика сервера",
	  "averageonlinelastday" 			=> 'Средний онлайн за последний день - <font color="green"><b>%s</b></font>; неделю - <font color="green"><b>%s</b></font>; месяц - <font color="green"><b>%s</b></font><br>',

	  "maxonlinelastday" 				=> 'Максимальный онлайн за последний день - <font color="green"><b>%s</b></font>; неделю - <font color="green"><b>%s</b></font>; месяц - <font color="green"><b>%s</b></font><br>',

	  "maxofmaxserver" 					=> 'Максимальный онлайн за весь период был зарегистрирован  <b>%s</b> и составил - <font color="green"><b>%s</b></font><br>',

	  "startmonitorforserver" 			=> "Система записывает данные о сервере с",
	  "dataofthisdayweekmonth" 			=> "Данные за текущий день, неделю и месяц",
	  "dataofthisrellastdayweekmonth" 	=> "Данные относительно прошедших дня, недели и месяца",
	  "dataonoffoflastdayweekmonth" 	=> "Данные об онлайне\оффлайне сервера за текущий день, неделю и месяц",
	  "sandboxlinktext" 				=> "Sandbox (самостоательный выбор всех параметров) (откроется в новом окне)",
	  "statistic_maxofmaxofservers" 	=> 'Максимальный <b>средний</b> онлайн за все время мониторинга был зарегистрирован на сервере <a class="decor" href="serverdetail.php?serverid=%s">%s</a> и составил <b><font color="green">%s</font></b><br><br>',
	  "error400title" 					=> "Ошибка 400. Неправильный запрос (Bad Request)",
	  "error400info" 					=> "При отправке данных на сервер (формирование запроса) произошла ошибка, попытайтесь повторить операцию снова через некоторое время.",

	  "error401title" 					=> "Ошибка 401. Необходима авторизация (Authorization Required)",
	  "error401info" 					=> "Для доступа к запрашиваемой странице необходима авторизация.",
	  "error403title" 					=> "Ошибка 403. Доступ запрещен (Forbidden)",
	  "error403info" 					=> "Вам запрещен доступ к этой странице.",
	  "error404title" 					=> "Ошибка 404. Страница не найдена (Not Found)",
	  "error404info" 					=> "К сожалению, запрашиваемая Вами страница не найдена.",
	  "error500title" 					=> "Ошибка 500. Внутренная ошибка сервера (Internal Server Error)",
	  "error500info" 					=> "На сервере произошла ошибка, попытайтесь повторить операцию немного позже.",
	  "reviewtitle" 					=> "monitor.global -> записи, посты, новости -> ",
	  "reviewtitle2" 					=> "monitor.global -> записи, посты, новости -> ",
	  "analytic" 						=> "аналитика",
	  "nomaterialsforthiscategory" 		=> "Для данной категории материалы отсутствуют",
	  "reviewlisth1" 					=> "новости, записи, посты -> категории",
	  "aboutsandbox" 					=> 'Sandbox - система, которая позволяет Вам самим полностью настроить вывод необходимых данных. Система предупреждает ошибки и не позволяет выбрать неверные данные. Выберете необходимые параметры ниже и нажмите на кнопку - "Показать"',

	  "sandbox_date" 					=> "Дата",
	  "sandbox_period" 					=> "Период",
	  "sandbox_day" 					=> "День",
	  "sandbox_week" 					=> "Неделя",
	  "sandbox_month" 					=> "Месяц",
	  "sandbox_graph" 					=> "График",
	  "sandbox_online" 					=> "Онлайн (кол-во пользователей)",
	  "sandbox_onoff" 					=> "Онлайн\оффлайн сервера",
	  "sandbox_values" 					=> "Значения",
	  "sandbox_average" 				=> "Средние",
	  "sandbox_max" 					=> "Максимальные",
	  "sandbox_server" 					=> "Сервер",
	  "sandbox_server2" 				=> "Сервер 2",
	  "sandbox_relativery" 				=> "Сравнить?",
	  "sandbox_yes" 					=> "Нет",
	  "sandbox_no" 						=> "Да",
	  "sandbox_date2" 					=> "Дата 2",
	  "sandbox_category2" 				=> "Категория 2",
	  "sandbox_category" 				=> "Категория",
	  "sandbox_showbutton" 				=> "Показать",
	  "pagebuildtime" 					=> "Страница сгенерирована за",
	  "pagebuildtime2" 					=> " сек. Количество запросов к БД: ",
	  "siteridetitle" 					=> "monitor.global -> переадресация",
	  "MondayHTMLchars" 				=> "&#1055;&#1086;&#1085;&#1077;&#1076;&#1077;&#1083;&#1100;&#1085;&#1080;&#1082;", // Понедельник
	  "TuesdayHTMLchars" 				=> "&#1042;&#1090;&#1086;&#1088;&#1085;&#1080;&#1082;",                             // Вторник
	  "WednesdayHTMLchars" 				=> "&#1057;&#1088;&#1077;&#1076;&#1072;",                                           // Среда
	  "ThursdayHTMLchars" 				=> "&#1063;&#1077;&#1088;&#1074;&#1077;&#1088;&#1075;",                             // Четверг
	  "FridayHTMLchars" 				=> "&#1055;&#1103;&#1090;&#1085;&#1080;&#1094;&#1072;",                             // Пятница
	  "SaturdayHTMLchars" 				=> "&#1057;&#1091;&#1073;&#1073;&#1086;&#1090;&#1072;",                             // Суббота
	  "SundayHTMLchars" 				=> "&#1042;&#1086;&#1089;&#1082;&#1088;&#1077;&#1089;&#1077;&#1085;&#1100;&#1077;", // Воскресенье
	  "hourword" 						=> "&#1095;",           															// ч
	  "averageonlinelastday2" 			=> "Средний онлайн за последний день ",
	  "MondayJAVAchars" 				=> "\u041F\u043E\u043D\u0435\u0434\u0435\u043B\u044C\u043D\u0438\u043A",
	  "TuesdayJAVAchars" 				=> "\u0412\u0442\u043E\u0440\u043D\u0438\u043A",
	  "WednesdayJAVAchars" 				=> "\u0421\u0440\u0435\u0434\u0430",
	  "ThursdayJAVAchars" 				=> "\u0427\u0435\u0440\u0432\u0435\u0440\u0433",
	  "FridayJAVAchars" 				=> "\u041F\u044F\u0442\u043D\u0438\u0446\u0430",
	  "SaturdayJAVAchars" 				=> "\u0421\u0443\u0431\u0431\u043E\u0442\u0430",
	  "SundayJAVAchars" 				=> "\u0412\u043E\u0441\u043A\u0440\u0435\u0441\u0435\u043D\u044C\u0435",
	  
	  "hourwordJAVAchars" 				=> "\u0447",
	  "openwenwindow" 					=> "Открыть в новом окне",
	  "for_a_week" 						=> "за неделю: ",
	  "for_a_day" 						=> "за день: ",
	  "for_a_month" 					=> "за месяц: ",
	  "online_offline1" 				=> ": онлайн\\оффлайн ",
	  "no_data_to_graph" 				=> "Нет данных для отображения",
	  "monitorglobaltext1" 				=> "Monitor Global ",
	  "stats_for_day" 					=> " : cтатистика за день ",
	  "onlinetext1" 					=> "Онлайн: ",
	  "mentext1" 						=> " чел.",
	  "stats_for_day2" 					=> "Cтатистика за день: ",
	  "stats_for_week" 					=> " : cтатистика за неделю ",
	  "stats_for_week2" 				=> "Cтатистика за неделю: ",
	  "stats_for_month" 				=> " : cтатистика за месяц ",
	  "stats_for_month2" 				=> "Cтатистика за месяц: ",
	  "" => "",
	  "" => "",
	  "" => "",
	  "" => "",
	  "" => "",
	  "" => "",
	  "" => "",
	  "" => "",
	  "" => "",
	  "" => "",
	  "" => "",
	  "" => "",
	  "" => "",
	  "" => "",               
   	  );

?>