<?php
require_once __DIR__ . '/../includes/db.php'; // The mysql database connection script
if(isset($_GET['lid']))
	$leagueID = $mysqli->real_escape_string($_GET['lid']);
$arrStyles = ['team1','team2','team3','team4','team5','team6','team7','team8'];
$arrUserStyles = [];
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
			$arrUserStyles[$row['user_name']] = array_pop($arrStyles);
			$index++;
		}
    }
}

$query="SELECT team.tid,uni_name,region_rank,seed,mascot,abbrev,year.year,league.league_name,users.user_name,league_members.pick_number,users.uid FROM team 
		LEFT JOIN region ON team.rid = region.rid 
		LEFT JOIN year ON team.yid = year.yid 
		LEFT JOIN ownership	ON team.tid = ownership.tid 
		LEFT JOIN league ON ownership.lid = league.lid
		LEFT JOIN users ON ownership.uid = users.uid
		LEFT JOIN league_members ON league_members.lid=league.lid AND league_members.uid = users.uid
		WHERE ownership.lid='$leagueID' 
		UNION
		SELECT team.tid,uni_name,region_rank,seed,mascot,abbrev,year.year,NULL AS league_name,NULL as user_name,NULL as pick_number,NULL as uid FROM team
	  	LEFT JOIN region ON team.rid = region.rid 
		LEFT JOIN year ON team.yid = year.yid 
	    WHERE tid NOT IN (SELECT tid FROM ownership WHERE lid='$leagueID') AND team.yid = (SELECT yid FROM league WHERE lid='$leagueID')
	    ORDER BY pick_number,uid";
$result = $mysqli->query($query) or die($mysqli->error.__LINE__);


$arrUnsortedBracket = array();
if($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
		$arrUnsortedBracket[] = $row;
	}
}

foreach($arrUnsortedBracket as $row)
{
	if(!isset($arrUserStyles[$row['user_name']]))
		$arrUserStyles[$row['user_name']] = array_pop($arrStyles);
	//Create a formated array where first index is the region rank and the second is the seed, then create an array within those indices containing the team info.
	//Check to see if the seed already exists if so it's a playin game and combine the information.
	if(isset($arrBracket['bracket'][$row['region_rank']][$row['seed']]['uni_name']))
	{
		$arrBracket['bracket'][$row['region_rank']][$row['seed']]['uni_name'] = $arrBracket['bracket'][$row['region_rank']][$row['seed']]['uni_name'] . '/' . $row['uni_name'];
		$arrBracket['bracket'][$row['region_rank']][$row['seed']]['formatted_name'] = $row['seed'] . ' ' . $arrBracket['bracket'][$row['region_rank']][$row['seed']]['uni_name'];
		$arrBracket['bracket'][$row['region_rank']][$row['seed']]['mascot'] = $arrBracket['bracket'][$row['region_rank']][$row['seed']]['mascot'] . '/' . $row['mascot'];
		$arrBracket['bracket'][$row['region_rank']][$row['seed']]['abbrev'] = $arrBracket['bracket'][$row['region_rank']][$row['seed']]['abbrev'] . '/' . $row['abbrev'];
		$arrBracket['bracket'][$row['region_rank']][$row['seed']]['tid'] = $arrBracket['bracket'][$row['region_rank']][$row['seed']]['tid'] . '/' . $row['tid'];
	}
	else
	{
		$arrBracket['bracket'][$row['region_rank']][$row['seed']]['uni_name'] = $row['uni_name'];
		$arrBracket['bracket'][$row['region_rank']][$row['seed']]['formatted_name'] = $row['seed'] . ' ' . $row['uni_name'];
		$arrBracket['bracket'][$row['region_rank']][$row['seed']]['mascot'] = $row['mascot'];
		$arrBracket['bracket'][$row['region_rank']][$row['seed']]['abbrev'] = $row['abbrev'];
		$arrBracket['bracket'][$row['region_rank']][$row['seed']]['tid'] = $row['tid'];
		$arrBracket['bracket'][$row['region_rank']][$row['seed']]['style'] = (isset($arrUserStyles[$row['user_name']]) ? $arrUserStyles[$row['user_name']] : '');
	}
}
	echo $json_response = json_encode($arrBracket);
	//style="background-color:lightgrey;"
?>