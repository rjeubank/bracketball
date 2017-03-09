<?php 
include(__DIR__ . "/../vendor/phpauth/phpauth/Config.php");
include(__DIR__ . "/../vendor/phpauth/phpauth/Auth.php");
$DB_HOST = 'localhost';
$DB_USER = 'id1011988_bracketball_admin';
$DB_PASS = 'Br@cketball1';
$DB_NAME = 'id1011988_bracketball';
$dbh = new PDO("mysql:host=".$DB_HOST.";dbname=".$DB_NAME, $DB_USER, $DB_PASS);
$config = new PHPAuth\Config($dbh);
$auth   = new PHPAuth\Auth($dbh, $config);
?>
