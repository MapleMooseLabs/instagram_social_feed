<?php

$_SERVER['REMOTE_ADDR'] = '0.0.0.0';

function bootstrap_d6() {
        require('_bootstrap.php');

        date_default_timezone_set('America/New_York');

        $base_url = 'https://'.$_SERVER['SERVER_NAME'];

        define('BASE_URL', $base_url);

        require_once './includes/bootstrap.inc';
        require_once './includes/common.inc';
        require_once './includes/module.inc';
        drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
        drupal_load('module', 'node');
}

function bootstrap_d7() {

        chdir("../../../../.."); //the Drupal root, relative to the directory of the path
        define('DRUPAL_ROOT', getcwd());

        date_default_timezone_set('America/New_York');

        $base_url = 'https://'.$_SERVER['SERVER_NAME'];

        define('BASE_URL', $base_url);

        require_once './includes/bootstrap.inc';
        require_once './includes/common.inc';
        require_once './includes/module.inc';
        drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
        drupal_load('module', 'node');

}

function db_connect_d7() {

	include(dirname(__FILE__) . '/../sites/default/settings.php');

	$db = $conf['databases']['default']['default'];
		$conn = mysql_connect($db['host'], $db['username'], $db['password']);
	if($conn)
		mysql_select_db($db['database'], $conn);
	else
		die('Could not connect: ' . mysql_error());

	echo "\n\nConnected to Drupal 7\n";

	return $conn;

}

function fix_chars($str) {

	echo "\n\nBEFORE: $str";
	$before = array('’', '•');
	$after = array("'", "-");

	$str = str_replace($before,$after,$str);
	echo "\n\nAFTER: $str";
	return $str;
}
