/*

==============================================================
 Monitor Global
--------------------------------------------------------------
 2007-2009, Mike Kozhevnikov
==============================================================
 File: /ajax/ajax_sandbox.js
==============================================================
 Назначение: JS скрипт c функциями для работы sandbox с AJAX
==============================================================

*/

function sandbox_categoryload() 
{
http.open('get', 'http://monitor.webcodes.club/ajax/categoryload.php');
http.onreadystatechange =  sandbox_categoryload_makeoutput;
http.send(null);
}

function sandbox_categoryload_makeoutput() 
{
  if(http.readyState == 4)
  {
  var response = http.responseText;
  document.getElementById('category1').innerHTML = response;
  document.getElementById('category2').innerHTML = response;  
  sandbox_serversload1(document.getElementById('category1').value);
  sandbox_serversload2(document.getElementById('category2').value);
  }
}

function sandbox_serversload1(catid)
{
http2.open('get', 'http://monitor.webcodes.club/ajax/serversload.php?catid='+catid);
http2.onreadystatechange =  sandbox_serversload1_makeoutput;
http2.send(null);
}

function sandbox_serversload1_makeoutput()
{
  if(http2.readyState == 4)
  {
  var response = http2.responseText;  
  document.getElementById('serverslist1').innerHTML = response;
  }
}

function sandbox_serversload2(catid)
{
http3.open('get', 'http://monitor.webcodes.club/ajax/serversload.php?catid='+catid);
http3.onreadystatechange =  sandbox_serversload2_makeoutput;
http3.send(null);
}

function sandbox_serversload2_makeoutput()
{
  if(http3.readyState == 4)
  {
  var response = http3.responseText;  
  document.getElementById('serverslist2').innerHTML = response;
  }
}

function changedisable(id)
{
if (document.getElementById(id).disabled)
  {document.getElementById(id).disabled=false;}
else
  {document.getElementById(id).disabled=true;}  
}

function buildgraph(windowid) 
{

date2     = document.getElementById('f_date_d').value;
server2   = document.getElementById('serverslist2').value;
relative  = document.getElementById('relative').value;
server1   = document.getElementById('serverslist1').value;
valuetype = document.getElementById('valuetype').value;
date1     = document.getElementById('f_date_c').value;
period    = document.getElementById('period').value;
graphtype = document.getElementById('graphtype').value;
libtype   =  document.getElementById('graphtypelib').value;

link = 'http://monitor.webcodes.club/statsdaybuilddata.php?';
link += 'serverid=' + server1;
link += '&periodtype=' + period;
link += '&period=' + date1;
link += '&priority=all';
if (graphtype=='online')
  {link += '&charttype=online';}
if (graphtype=='onoff')
  {link += '&charttype=onoff';} 
  
if (libtype=='flash')
  {
  link += '&libtype=flash';
  }
if (relative=='relyes')
  {
  link += '&precision=relatively';
  link += '&serveridrel=' + server2;
  link += '&periodrel=' + date2;
  }
if (valuetype=='average')
  {link += '&valueofdata=mean';}
if (valuetype=='max')
  {link += '&valueofdata=peak';}  
  				        

if (libtype=='flash')
  {
  if (windowid=='new')
    {
    sandbox_flashload_new_window(link);
    }
  else
    {
    sandbox_flashload(link);
    }  

  }
else
  {
  if (windowid=='new')
    {
    newWindow('<center><a href="' + link + '&imagetype=big"><img src="' + link + '" alt="" title="Нажмите, чтобы увеличить" /></a></center><center><font size="1" color="gray">&uArr; нажмите на картинку, чтобы увеличить &uArr;</font><center>');
    }
  else
    {
    document.getElementById('graph').innerHTML = '<center><a href="' + link + '&imagetype=big"  onclick="return hs.expand(this)"><img src="' + link + '" alt="Статистика за день" title="Нажмите, чтобы увеличить" /></a></center><center><font size="1" color="gray">&uArr; нажмите на картинку, чтобы увеличить &uArr;</font><center>';
    }  

  }

}

function sandbox_flashload(linktograph) 
{
document.getElementById('graph').innerHTML = 'Loading  <img src="http://monitor.webcodes.club/template/images/loading.gif">';
http.open('get', linktograph);
http.onreadystatechange =  sandbox_flashload_makeoutput;
http.send(null);
}

function sandbox_flashload_makeoutput() 
{
  if(http.readyState == 4)
  {
  var response = http.responseText;
  document.getElementById('graph').innerHTML = '<center>'+response+'</center>';

  }
}

function sandbox_flashload_new_window(linktograph) 
{
http.open('get', linktograph);
http.onreadystatechange =  sandbox_flashload_new_window_makeoutput;
http.send(null);
}

function sandbox_flashload_new_window_makeoutput() 
{
  if(http.readyState == 4)
  {
  var response = http.responseText;
  var myWin;
  var nocache = Math.random();  
  myWin= open("", "newWindow"+nocache);
  myWin.document.open();
  myWin.document.write('<center>'+response+'</center>');
  myWin.document.close();
  }
}

function newWindow(string)
{
var myWin;
var nocache = Math.random();  
  myWin= open("", "newWindow"+nocache,'z-lock=yes,alwaysLowered=yes');
  myWin.document.open();
  myWin.document.write(string);
  myWin.document.close();

}