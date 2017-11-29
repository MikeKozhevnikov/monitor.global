<?php


error_reporting(55);
@error_reporting ( E_ALL ^ E_NOTICE );

$root = str_replace("\include\ofc", "", dirname ( __FILE__ ));
$root = str_replace("/include/ofc", "", $root);
define ( 'ROOT_DIR', $root );
unset($root);

include_once(ROOT_DIR . "/include/config.inc.php");
include_once(ROOT_DIR . "/include/class.db.php");
include_once(ROOT_DIR . "/include/functions.inc.php");
include_once(ROOT_DIR . "/include/class.log.php");

$time_start = getmicrotime();



$data = array();

$db_sql = New db_sql($config['dbName'],$config['hostname'],$config['dbUname'],$config['dbPasswort']); 


/*$query1 = $db_sql->sql_query("SELECT * 
  	    		FROM " . $config['statslog_table'] . " 
  			WHERE date='" . $period . "' 
			AND serverid = '" . $serverid . "' 
  			LIMIT 1;");  */
  			
$query1 = $db_sql->sql_query("SELECT * 
  	    		FROM " . $config['statslog_table'] . " 
  			WHERE date='2009-10-05' 
			AND serverid = '2' 
  			LIMIT 1;");  			
  				
$query = $db_sql->fetch_array($query1);

$data = array();

$max = 0;
for( $i=0; $i<=23; $i++ )
  {
  $value = intval($query['time'. $i]);
  $data[] = $value; 
  if ($value>$max)
    {$max = $value;}
     
  }
  
require_once(ROOT_DIR . '/include/ofc/OFC/OFC_Chart.php');

$title = new OFC_Elements_Title(WinToHTMLChars('Статистика за день:'));

$bar = new OFC_Charts_Bar_3d();
$bar->set_values( $data );
$bar->colour = '#5555ff';//'#D54C78';
$bar->set_tooltip (WinToHTMLChars('Онлайн:<br>#val# чел.'));


$x_axis = new OFC_Elements_Axis_X();
$x_axis->set_3d( 5 );
$x_axis->colour = '#909090';
$x_axis->set_labels( array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23) );

$y_axis = new OFC_Elements_Axis_Y();
$y_axis->set_range(0,intval($max*1.1));


$chart = new OFC_Chart();
$chart->set_title( $title );
$chart->add_element( $bar );
$chart->set_x_axis( $x_axis );
$chart->set_y_axis( $y_axis );

echo $chart->toPrettyString();

