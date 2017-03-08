<?php
require_once '/../includes/db.php'; // The mysql database connection script
if(isset($_GET['lid']))
	$leagueID = $mysqli->real_escape_string($_GET['lid']);
if(isset($_GET['uid']))
	$userID = $mysqli->real_escape_string($_GET['uid']);
$year=2016;
$query="SELECT league.league_name,users.user_name,users.uid,league_members.pick_number FROM league_members 
	LEFT JOIN league ON league_members.lid = league.lid
	LEFT JOIN users ON league_members.uid = users.uid
	WHERE league_members.lid='$leagueID' ORDER BY league_members.pick_number, users.uid";
$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

$arrStyles = ['team1','team2','team3','team4','team5','team6','team7','team8'];
$arrUserStyles = [];
$index = 1;
if($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
    	if(!isset($userID) || $userID=='')
    		$index = $row['pick_number'];
        if(!isset($arrUserStyles[$row['user_name']]))
        {
			$arrUserStyles[$index]['style'] = array_pop($arrStyles);
			$arrUserStyles[$index]['uid'] = $row['uid'];
			$arrUserStyles[$index]['user_name'] = $row['user_name'];
			$index++;
		}
    }
}
$arrDraftOrder = [];
if(isset($userID) && $userID!=''){
	foreach($arrUserStyles AS $pick => $user){
		for($i=0;$i<8;$i++){
			if($i%2 == 0)
				$arrDraftOrder[$i*8+$pick] = $user;
			else
				$arrDraftOrder[(($i+1)*8)-$pick+1] = $user;
		}
	}
}
$arrTeamsOwned = [];
if(isset($userID))
{
	$query="SELECT team.tid,uni_name,year.year,league.league_name,users.user_name FROM team 
		LEFT JOIN year ON team.yid = year.yid 
		LEFT JOIN ownership	ON team.tid = ownership.tid 
		LEFT JOIN league ON ownership.lid = league.lid
		LEFT JOIN users ON ownership.uid = users.uid
		WHERE year.year='$year' AND ownership.lid='$leagueID' AND ownership.uid='$userID'";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
	if($result->num_rows > 0) {
	    while($row = $result->fetch_assoc()) {
	    	$arrTeamsOwned[] = $row['uni_name'];
	    }
	}
}
$arrResults['users'] = $arrUserStyles;
$arrResults['teams'] = $arrTeamsOwned;
$arrResults['order'] = $arrDraftOrder;


//print_r($arrResults);
	echo $json_response = json_encode($arrResults);
	//style="background-color:lightgrey;"
?>