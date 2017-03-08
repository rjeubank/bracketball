<?php
require_once '/../includes/db.php'; // The mysql database connection script
$year = '2016';
if(isset($_GET['lid']))
	$leagueID = $mysqli->real_escape_string($_GET['lid']);
$query="SELECT team.tid,uni_name,region_rank,seed,mascot,abbrev,year.year,league.league_name,users.user_name FROM team 
	LEFT JOIN region ON team.rid = region.rid 
	LEFT JOIN year ON team.yid = year.yid 
	LEFT JOIN ownership	ON team.tid = ownership.tid 
	LEFT JOIN league ON ownership.lid = league.lid
	LEFT JOIN users ON ownership.uid = users.uid
	LEFT JOIN league_members ON league_members.lid=league.lid AND league_members.uid = users.uid
	WHERE year.year=" . $year . " AND ownership.lid='$leagueID' ORDER BY league_members.pick_number";
$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

$arrStyles = ['team1','team2','team3','team4','team5','team6','team7','team8'];
$arrUserStyles = [];
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