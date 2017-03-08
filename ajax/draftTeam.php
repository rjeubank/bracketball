<?php
require_once '/../includes/db.php'; // The mysql database connection script
if(isset($_GET['user']) && isset($_GET['team']) && isset($_GET['league'])){
	$user = $mysqli->real_escape_string($_GET['user']);
	$team = $mysqli->real_escape_string($_GET['team']);
	$league = $mysqli->real_escape_string($_GET['league']);
	$query="SELECT * FROM ownership WHERE lid='$league' AND tid='$team'";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
	if($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$arrSelected[] = $row;
		}
	}
	if(isset($arrSelected[0]))
	{
		echo $json_response = 'selected';
	}
	else{
		$query="SELECT * FROM draft_status WHERE lid='$league'";
		$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
		if($result->num_rows > 0) {
			while($row = $result->fetch_assoc()) {
				$arrStatus[] = $row;
			}
		}
		if($arrStatus[0]['pick'] == 64){
			$query = "UPDATE draft_status SET status='completed', pick=0 WHERE lid='$league'";
			$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
		}
		else{
			$arrStatus[0]['pick'] = $arrStatus[0]['pick']+1;
			$query = "UPDATE draft_status SET pick=".$arrStatus[0]['pick']." WHERE lid=".$league;
			$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
		}
		$query="INSERT INTO ownership(uid,tid,lid)  VALUES ('$user', '$team', '$league')";
		$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
		$result = $mysqli->affected_rows;
		echo $json_response = 'success';
	}
}

?>