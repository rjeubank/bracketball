<?php
require_once '/../includes/db.php'; // The mysql database connection script
require_once '/../includes/auth.php'; 
if(isset($_POST['hash'])){
	$return = $auth->logout($_POST['hash']);
}
echo $json_response = json_encode($return);
?>