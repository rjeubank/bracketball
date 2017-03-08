<?php 
require_once '../includes/db.php'; // The mysql database connection script
if(isset($_GET['user']) && isset($_GET['email']) && isset($_GET['password'])){
	$user = $mysqli->real_escape_string($_GET['user']);
	$email = $mysqli->real_escape_string($_GET['email']);
	$password = $mysqli->real_escape_string($_GET['password']);

#Creating a random salt to crate a hash from the password
    $cost = 5;
	$salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
	$salt = sprintf("$2a$%02d$", $cost) . $salt;
	$hash = crypt($password, $salt);


	$query="INSERT INTO users(user_name,email,password)  VALUES ('$user', '$email', '$hash')";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

	$result = $mysqli->affected_rows;

	echo $json_response = json_encode($result);
	}
?>