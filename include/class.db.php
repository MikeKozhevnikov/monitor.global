<?php

/*

===================================================================
 Monitor Global
-------------------------------------------------------------------
 2007-2015, Mike Kozhevnikov
===================================================================
 File: /include/class.db.php
===================================================================
 Назначение: Класс доступа и произведения операций с базой данных
===================================================================

*/

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

class db_sql
{

public $database  =  "";
public $server    =  "";
public $user      =  "";
public $password  =  "";
public $link_id   =  0;
public $query_id  =  0;
public $q_cache   =  array();

   
function db_sql($dbName,$hostname,$dbUname,$dbPasswort) 
{
$this->database  =  $dbName;
$this->server    =  $hostname;
$this->user      =  $dbUname;
$this->password  =  $dbPasswort;
$this->myconnect();      
}

/**
 * db_sql::myconnect()
 * 
 * Конструктор:
 * $db_sql = new db_sql;
 * $db_sql->database=$db;
 * $db_sql->server=$sql_host;
 * $db_sql->user=$sql_user;
 * $db_sql->password=$sql_pass;
 * $db_sql->myconnect();		  
 * @return link_id
 */
function myconnect()
{


$this->link_id = mysqli_connect($this->server, $this->user, $this->password);

if (!$this->link_id) {
    echo "Ошибка: Невозможно установить соединение с MySQL." . PHP_EOL;
    echo "Код ошибки errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Текст ошибки error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}

//echo "Соединение с MySQL установлено!" . PHP_EOL;
//echo "Информация о сервере: " . mysqli_get_host_info($this->link_id) . PHP_EOL;


/* изменение набора символов на utf8 */
if (!mysqli_set_charset($this->link_id, "utf8")) {
    //printf("Ошибка при загрузке набора символов utf8: %s\n", mysqli_error($this->link_id));
    exit();
} /*else {
    printf("Текущий набор символов: %s\n", mysqli_character_set_name($this->link_id));
}*/

/*if (!$this->link_id)
  {die("Нет подключения к базе данных" );}*/
  
$db_select = mysqli_select_db($this->link_id,$this->database);
if (!$db_select)
  {
  die("Базы данных не существует: ".$this->server.",".
  		  	     		  	      	        $this->user.",".
								$this->password.",".
								$this->database." ".mysqli_error() );
  }
return $this->link_id;
}

		
/**
 * db_sql::sql_query()
 * Обычный SQL-запрос, может использоваться для Insert, Update, Delete и т.д
 * Применение:
 * $db_sql->sql_query("
 *                    SELECT * 
 *  		              FROM statslog 
 *		                WHERE serverid = 1
 		                  LIMIT 20;");
 * или 
 * $db_sql->sql_query("INSERT INTO statslog 
 *  		               VALUES('','2','2008-08-09','','','','')");
 * 
 * @param $query_statement
 * @return query_id
 */
function sql_query($query_statement) 
{
global $query_count;

$this->query_id = mysqli_query($this->link_id,$query_statement);
if(!$this->query_id)
  {
  trigger_error("Query fatal error:<br><b>Query:</b>" . $query_statement . 
  		"<br><b>Ошибка:</b>" . mysqli_error($this->link_id),E_USER_ERROR);
  }
$query_count++;	
$this->test['q_cache'][] = $query_statement;	

return $this->query_id;		
}
		

/**
 * db_sql::query_array()
 * Возвращает массив данных, возвращенных SQL запросом. Далее нет необходимости
 * делать fetch_array
 * Применение: 
 * $config = $db_sql->query_array("
 *                                SELECT * from servers 
 *  	     		  	                WHERE enable=1
 *			                          LIMIT 20;");
 * 
 * @param $query_statement
 * @return Array
 */
function query_array($query_statement) 
{
$query_id = $this->sql_query($query_statement);
$return_array = $this->fetch_array($query_id);

$this->free_result($query_id);

return $return_array;	
}
		

/**
* db_sql::fetch_array()
* Версия fetch_Array для элемента класса 
* Применение:
* $result = $db_sql->sql_query("
*                                SELECT * from review
*  	    			                   LIMIT 20;");
* while($review = $db_sql->fetch_array($result)) {
* echo "<br>".$review['post']."<br>";
* }
* 
* @param $query_id
* @return Array
*/
function fetch_array($query_id=-1) 
{
//if ($query_id!=-1) 
 // {
  	$this->query_id = $query_id;
 // }
$this->result = mysqli_fetch_array($this->query_id);

return $this->result;	
}
	

/**
 * db_sql::insert_id()
 * 
 * 
 * @return 
 */
function insert_id() 
{
return mysql_insert_id($this->link_id);
}
		
		
/**
 * db_sql::sql_fetch_row()
 * SQL запрос с последующим fetch_row для всех элементов
 * Применение:
 * list ($id) = $db_sql->sql_fetch_row("
 *                                    SELECT id 
 *               	      			        FROM servers 
 *	                     			        WHERE enable=1
 *                          					LIMIT 10;");
 * 
 * @param $query_statement
 * @return 
 */
function sql_fetch_row($query_statement) 
{
$this->result = mysqli_fetch_row($this->sql_query($query_statement));

return $this->result;		 
}
        
        
/**
 * db_sql::fetch_row()
 * fetch_row для элемента
 * 
 * @param $result_set
 * @return 
 */        
function fetch_row($result_set) 
{
$this->result = mysqli_fetch_row($result_set);

return $this->result;		 
}
     
/**
* db_sql::num_rows()
* Количество строк результата запроса
* Применение:
* $query = $db_sql->sql_query("
*                            SELECT * from stats 
*             	   		       WHERE date >='2008-08-09'
*               			       AND   date <='2008-09-02'
*               			       LIMIT 20;");
* $rows = $db_sql->num_rows($query);
* 
* @param $query_id
* @return 
*/
function num_rows($query_id=-1) 
{
if ($query_id!=-1) 
  {$this->query_id = $query_id;}
  
return 2;// mysql_num_rows($this->query_id);
}


/**
* db_sql::free_result()
* Освобождаем память
* 
* @param $query_id
* @return 
*/
function free_result($query_id=-1) 
{
if ($query_id!=-1) 
  {$this->query_id=$query_id;}
  
return @mysqli_free_result($this->query_id);
}


/**
* db_sql::closeSQL()
* Закрываем подключение
* 
* @return 
*/
function closeSQL() 
  {@mysqli_close($this->link_id);}

} 
		 
?>