<?php
require_once __DIR__ . '/../includes/db.php'; // The mysql database connection script
if(isset($_GET['user']) && isset($_GET['league'])){
	$user = $mysqli->real_escape_string($_GET['user']);
	$league = $mysqli->real_escape_string($_GET['league']);
    #Get all game related information including team information with the points scored.
    $query = "SELECT * FROM game LEFT JOIN ownership ON winid = tid LEFT JOIN team ON winid = tid WHERE ownership.uid = " . $user . " AND ownership.lid = " .  $league;
    $result = $mysqli->query($query) or die($mysqli->error.__LINE__);

    $arr = array();
    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $arr[] = $row;
        }
    }

    # JSON-encode the response
    echo $json_response = json_encode($arr);
}

?>