/*

===============================================================
 Monitor Global
---------------------------------------------------------------
 2007-2009, Mike Kozhevnikov
===============================================================
 File: /ajax/ajax_serverdetail.js
===============================================================
 Назначение: JS скрипт для AJAX подгрузки стандартных графиков
===============================================================

*/

function server_detail_current(id) 
  {
    document.getElementById('msg').innerHTML = "";
    nocache = Math.random();
    http.open('get', 'http://monitor.webcodes.club/serverdetail.php?ajax=current&serverid=' +id+ '&nocache='+nocache);
    http.onreadystatechange =  server_detail_current_makeoutput;
    http.send(null);
  }

function server_detail_current_makeoutput() 
  {
  if(http.readyState == 4)
    {
    var response = http.responseText;
    document.getElementById('current').innerHTML = response;
    }
  }

function server_detail_currellast(id) 
  {
  document.getElementById('msg2').innerHTML = '';
  nocache = Math.random();
  http.open('get', 'http://monitor.webcodes.club/serverdetail.php?ajax=currellast&serverid=' +id+ '&nocache='+nocache);
  http.onreadystatechange =  server_detail_currellast_makeoutput;
  http.send(null);
  }

function server_detail_currellast_makeoutput() 
{
  if(http.readyState == 4)
    {
    var response = http.responseText;
    document.getElementById('currellast').innerHTML = response;
    document.getElementById('msg2').innerHTML = '';
    }
}

function server_detail_currentonoff(id) 
  {
  document.getElementById('msg3').innerHTML = '';
  nocache = Math.random();
  http.open('get', 'http://monitor.webcodes.club/serverdetail.php?ajax=currentonoff&serverid=' +id+ '&nocache='+nocache);
  http.onreadystatechange =  server_detail_currentonoff_makeoutput;
  http.send(null);
  }

function server_detail_currentonoff_makeoutput() 
{
  if(http.readyState == 4)
    {
    var response = http.responseText;
    document.getElementById('currentonoff').innerHTML = response;
    document.getElementById('msg3').innerHTML = '';
    }
}

function server_detail_sandboxload(id) 
  {
  document.getElementById('msg4').innerHTML = '';
  nocache = Math.random();
  http.open('get', 'http://monitor.webcodes.club/serverdetail.php?ajax=sandboxload&nocache='+nocache);
  http.onreadystatechange =  server_detail_sandboxload_makeoutput;
  http.send(null);
  }

function server_detail_sandboxload_makeoutput() 
{
  if(http.readyState == 4)
    {
    var response = http.responseText;
    document.getElementById('sandbox').innerHTML = response;
    document.getElementById('msg4').innerHTML = '';
    }
}
