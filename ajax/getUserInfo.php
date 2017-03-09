<?php
require_once __DIR__ . '/../includes/db.php'; // The mysql database connection script
require_once __DIR__ . '/../includes/auth.php';

$user = $auth->getSessionUID($_COOKIE['authID']);

$query="SELECT users.uid,user_name,email,league.lid,league_name,draft_time FROM users LEFT JOIN league_members ON users.uid = league_members.uid LEFT JOIN league ON league.lid = league_members.lid WHERE users.uid='$user'";
$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

$arrUser = array();
if($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $arrUser[] = $row;
    }
}

	echo $json_response = json_encode($arrUser);
?>