<?php

/*

=================================================================
 Monitor Global
-----------------------------------------------------------------
 2007-2009, Mike Kozhevnikov
=================================================================
 File: /include/class.log.php
=================================================================
 Назначение: Класс логирования работы системы в txt и на jabber
=================================================================

*/
 
class txtLogger 
{

var $availableOptions = array("size","time");
var $rotationBasedOn = "size";
var $logFileName = NULL;//"logs/test.log";
var $maxLogSize = 1024;
var $logFileNameArray = array(
    		         "statsupdate" => "logs/statsupdate.log",
    		         "longbuild" => "logs/longbuild.log",
			 "access" => "logs/access.log",
			 "errors" => "logs/errors.log",
			);

function txtLogger($logName)
{
$this->logFileName = $this->logFileNameArray[$logName];
switch ($logName)
  {
  case 'access':
    $this->accesstxt();
    break;
  case 'longbuild':
    $this->longbuildtxt();
    break;
  default:
    break;    
  }

}

function accesstxt()
{
/*
$request = 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"];
if ($_SERVER["QUERY_STRING"])
  {$request.= '?' . $_SERVER["QUERY_STRING"];}
$this->txtlog("\nREQUEST: " . $request . 
	      "\nHTTP_USER_AGENT: " . $_SERVER['HTTP_USER_AGENT'] . 
	      "\nREMOTE_ADDR: " . $_SERVER['REMOTE_ADDR'] . 
	      "\nREMOTE_PORT: " . $_SERVER['REMOTE_PORT'] . 
	      "\nHTTP_REFERER: " .$_SERVER['HTTP_REFERER']);
*/

}

function longbuildtxt()
{
global $loadinfo, $config;
$isLong = (float)((float)($loadinfo['loadtime']) - (float)($config['maxbuildtime']));
if ($isLong > 0)
  {
  $request = 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"];
  if ($_SERVER["QUERY_STRING"])
    {$request.= '?' . $_SERVER["QUERY_STRING"];}
  $this->txtlog("\nloadtime: ". $loadinfo['loadtime'] . 
      		"\nsql: " . $loadinfo['sql'] . 
		"\n" . $request);
  }
}

function txtlog($log) 
{
$fh = fopen($this->logFileName,"a");
fwrite($fh,date('Y-m-d H:i:s')." ".$log."\n\n");
fclose($fh);
}
    
}

class jabberLogger
{
var $logTypeArray = array(
    		         "statsupdate" => "logs/statsupdate.log",
    		         "longbuild" => "logs/longbuild.log",
			 "access" => "logs/access.log",
			 "errors" => "logs/errors.log",
			 );
function jabberLogger($logtype=NULL)
{
include_once(ROOT_DIR . "/include/xmpphp/XMPPHP/XMPP.php");
switch ($logName)
  {
  case 'access':
    $this->accessjabber();
    break;
  case 'longbuild':
    $this->longbuildtxt();
    break;
  case 'statsupdate':
    break;
  case 'errors':
    break;    
  default:
    break;    
  }

} 

function accessjabber()
{
$request = 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"];
if ($_SERVER["QUERY_STRING"])
  {$request.= '?' . $_SERVER["QUERY_STRING"];}
$this->jabberlog("\nREQUEST: " . $request . 
	      "\nHTTP_USER_AGENT: " . $_SERVER['HTTP_USER_AGENT'] . 
	      "\nREMOTE_ADDR: " . $_SERVER['REMOTE_ADDR'] . 
	      "\nREMOTE_PORT: " . $_SERVER['REMOTE_PORT'] . 
	      "\nHTTP_REFERER: " .$_SERVER['HTTP_REFERER']);
}

function longbuildjabber()
{
global $loadinfo, $config;
$isLong = (float)((float)($loadinfo['loadtime']) - (float)($config['maxbuildtime']));
if ($isLong > 0)
  {
  $request = 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"];
  if ($_SERVER["QUERY_STRING"])
    {$request.= '?' . $_SERVER["QUERY_STRING"];}
  $this->jabberlog("\nloadtime: ". $loadinfo['loadtime'] . 
      		   "\nsql: " . $loadinfo['sql'] . 
  		   "\n" . $request);
  }
}

function jabberlog($log)
{

echo 'conn START';
$conn = new XMPPHP_XMPP($config['jabberserver'], 5222, $config['jabberusername'], $config['jabberpassword'], 'xmpphp', 'gmail.com', $printlog=false, $loglevel=XMPPHP_Log::LEVEL_INFO);

echo 'conn END';

try 
  {
    $conn->connect();
    $conn->processUntil('session_start');
    $conn->presence();
    $conn->message($config['jabberuserto'], $log);
    $conn->disconnect();
  }
catch(XMPPHP_Exception $e) 
  {
  die($e->getMessage());
  }
}

}
  
?>