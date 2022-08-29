<?php
namespace Eogsoft\Playground;
//session_start();   // I am trying use as less resources as possible

use \PDO;
use Eogsoft\Playground\mysql_crud_class;

$settings = [
  'servername'=>'localhost',
  'port'=> '3306',
  'username'=>'root',
  'password'=>'',
  'dbname'=>'test',
  'tablename'=>'persons',
  // columns to be shown on HTML TABLE element
  'viewable'=> ['id', 'name', 'email', 'phone'],
  // columns to be filled by HTML FORM element
  'fillable'=> ['name', 'phone', 'email', 'address']
];

$url = "http".(!empty($_SERVER['HTTPS'])?"s":"")."://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
//$url.=basename(__FILE__);

/*
CREATE TABLE `persons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `persons_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
*/


// creating data source name DSN
$dsn = "mysql:host={$settings['servername']};port={$settings['port']};dbname={$settings['dbname']};charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // enable PDO errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   // fetch associative arrays by default
    PDO::ATTR_EMULATE_PREPARES => false,                // Use native prepared statements
];

// try to connect to database server
try {
	// $dbh = new PDO($dsn, username, password,[
		// PDO::ATTR_DEFAULT_FETCH_MODE =>PDO::FETCH_ASSOC,
		// PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', //after php5.3.6
	// ]);
	$dbh = new PDO($dsn, $settings['username'], $settings['password'], $options);
	//$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	//$dbh->exec('SET NAMES utf8'); // old version
} catch (PDOException $e) {
	print "Error!: " . $e->getMessage() . "<br/>";
	die();
}

// connection is ok, let us go further
require('mysql_crud_class.php');
$crud = new mysql_crud_class($dbh, $settings['tablename'], $settings['fillable'], $settings['viewable']);

// we are using action variable for all action
// $action = filter_input(INPUT_POST, 'q');
$action='index';	// default action is index
if( isset($_REQUEST['q']) )
	$action = $_REQUEST['q'];

// Only POST method is accepted at the moment
if( filter_input(INPUT_POST, '_method') == 'post' || $action == 'index')
{
	switch ($action)
	{
		case 'create':
			break;
			
		case 'store':
			$result = $crud->store();
			break;
			
		case 'show':
			$result = $crud->show(filter_input(INPUT_POST, 'id'));
			break;
			
		case 'previous':
			$result = $crud->previousRow(filter_input(INPUT_POST, 'id'));
			break;
			
		case 'next':
			$result = $crud->nextRow(filter_input(INPUT_POST, 'id'));
			break;
			
		case 'edit':
			$result = $crud->edit(filter_input(INPUT_POST, 'id'));
			break;
			
		case 'update':
			$result = $crud->update(filter_input(INPUT_POST, 'rowid'));
			break;
			
		case 'delete':
			$result = $crud->delete(filter_input(INPUT_POST, 'rowid'));
			break;
			
		case 'index':
			$result = $crud->index();
			break;
	}
}
//die( $action . ' - ' . filter_input(INPUT_POST, '_method'));
if( $crud->is_ajax_request() == true)
	echo json_encode($result);
else
	include('theme.php');
