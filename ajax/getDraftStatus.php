<?php
require_once __DIR__ . '/../includes/db.php'; // The mysql database connection script
if(isset($_GET['lid']))
	$leagueID = $mysqli->real_escape_string($_GET['lid']);
$query="SELECT * FROM draft_status WHERE lid='$leagueID'";
$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
if($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
		$arrStatus[] = $row;
	}
}
echo $json_response = json_encode($arrStatus[0]);

?>