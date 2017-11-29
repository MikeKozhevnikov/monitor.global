/*

=============================================
 Monitor Global
---------------------------------------------
 2007-2009, Mike Kozhevnikov
=============================================
 File: /ajax/ajax_init.js
=============================================
 Назначение: JS скрипт инициализации AJAX
=============================================

*/

// функция создания объекта XMLHTTPRequest  */
function createObject() {
var request_type;
var browser = navigator.appName;
if(browser == "Microsoft Internet Explorer"){
request_type = new ActiveXObject("Microsoft.XMLHTTP");
} else {
request_type = new XMLHttpRequest();
}
return request_type;
}

var http  = createObject();
var http2 = createObject();
var http3 = createObject();