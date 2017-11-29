// full day names
Calendar._DN = new Array
("Воскресенье",
 "Понедельник",
 "Вторник",
 "Среда",
 "Четверг",
 "Пятница",
 "Суббота",
 "Воскресенье");


// short day names
Calendar._SDN = new Array
("Вс",
 "Пн",
 "Вт",
 "Ср",
 "Чт",
 "Пт",
 "Сб",
 "Вс");

// First day of the week. "0" means display Sunday first, "1" means display
// Monday first, etc.
Calendar._FD = 1;

// full month names
Calendar._MN = new Array
("Январь",
 "Февраль",
 "Март",
 "Апрель",
 "Май",
 "Июнь",
 "Июль",
 "Август",
 "Сентябрь",
 "Октябрь",
 "Ноябрь",
 "Декабрь");

// short month names
Calendar._SMN = new Array
("Янв",
 "Фев",
 "Мар",
 "Апр",
 "Май",
 "Июн",
 "Июл",
 "Авг",
 "Сен",
 "Окт",
 "Ноя",
 "Дек");

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "Помощь + О календаре";

Calendar._TT["ABOUT"] =
"О выборе времени:\n" +
"- Кликните на любой дате (числе) чтобы выбрать его\n" +
"- Кликните и удерживайте на \">\" или \"<\" для быстрого выбора месяца\n" +
"- Кликните и удерживайте на \">>\" или \"<<\" для быстрого выбора года\n" +
"- По календарю можно перемещаться с помощью клавиатуры\n";
Calendar._TT["ABOUT_TIME"] =                                                              
"\nНаписан: by Mihai Bazon (c) dynarch.com 2002-2005\n" + 
"Доработан: by atybrc (c) game.global.by 2007-2009\n";

Calendar._TT["PREV_YEAR"] = "Предыдущий год";
Calendar._TT["PREV_MONTH"] = "Предыдущий месяц";
Calendar._TT["GO_TODAY"] = "Текущая дата";
Calendar._TT["NEXT_MONTH"] = "Следующий месяц";
Calendar._TT["NEXT_YEAR"] = "Следующий год";
Calendar._TT["SEL_DATE"] = "Выберете дату";
Calendar._TT["DRAG_TO_MOVE"] = "";
Calendar._TT["PART_TODAY"] = " (сейчас)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "Сначала %s";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "0,6";

Calendar._TT["CLOSE"] = "Закрыть";
Calendar._TT["TODAY"] = "Сейчас";
Calendar._TT["TIME_PART"] = "Нажмите для смены значения";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%Y-%m-%d";
Calendar._TT["TT_DATE_FORMAT"] = "%a, %b %e";

Calendar._TT["WK"] = "Неделя";
Calendar._TT["TIME"] = "Время:";
