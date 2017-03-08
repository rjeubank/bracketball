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
	WHERE year.year=" . $year . " AND ownership.lid='$leagueID' ORDER BY league_members.pick_number, users.uid";
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

$query="SELECT game.winid,game.points,game.loseid,users.user_name, region.region_rank AS winregion, team.uni_name AS winteam, team.mascot AS winmascot, team.abbrev AS winabbrev, team.seed AS winseed, game.winscore,
	(SELECT seed FROM team WHERE team.tid = loseid) AS loseseed, game.losescore,
	(SELECT region_rank FROM region LEFT JOIN team ON team.rid = region.rid WHERE team.tid = game.loseid) AS loseregion FROM game
	LEFT JOIN team ON game.winid = team.tid
	LEFT JOIN region ON team.rid = region.rid
	LEFT JOIN ownership	ON game.winid = ownership.tid
	LEFT JOIN year ON team.yid = year.yid 
	LEFT JOIN users ON ownership.uid = users.uid
	WHERE year.year=" . $year . " AND ownership.lid='$leagueID' ORDER BY users.uid";
$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
$index = 0;
foreach($arrUserStyles as $username => $style)
{
	$arrBracket['standings'][$index]['style'] = $style;
	$arrBracket['standings'][$index]['score'] = 0;
	$arrBracket['standings'][$index]['user_name'] = $username;
	$arrUsernameIndex[$username] = $index;
	$index++;
}
for($i = 1; $i<5; $i++){
	for($j=1;$j<16;$j++){
		$arrBracket['games'][$i][$j] = [];
	}
}
$arrBracket['games']['finalfour'][1] = [];
$arrBracket['games']['finalfour'][2] = [];
$arrBracket['games']['finalfour'][3] = [];
if($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
		$arrBracket['standings'][$arrUsernameIndex[$row['user_name']]]['score'] += $row['points'];
		if($row['winregion'] == $row['loseregion'])
		{
			if($row['winseed'] == $row['loseseed'])
			{
				$arrBracket['bracket'][$row['winregion']][$row['winseed']]['uni_name'] = $row['winteam'];
				$arrBracket['bracket'][$row['winregion']][$row['winseed']]['formatted_name'] = $row['winseed'] . ' ' . $row['winteam'];
				$arrBracket['bracket'][$row['winregion']][$row['winseed']]['mascot'] = $row['winmascot'];
				$arrBracket['bracket'][$row['winregion']][$row['winseed']]['abbrev'] = $row['winabbrev'];
			}
			else
			{
				if(in_array($row['winseed'],array(1,16)) && in_array($row['loseseed'],array(1,16))){
					if($row['winseed'] < $row['loseseed'])
						$arrBracket['games'][$row['winregion']][1] = [$row['winscore'],$row['losescore']];
					else
						$arrBracket['games'][$row['winregion']][1] = [$row['losescore'],$row['winscore']];
				}
				else if(in_array($row['winseed'],array(8,9)) && in_array($row['loseseed'],array(8,9))){
					if($row['winseed'] < $row['loseseed'])
						$arrBracket['games'][$row['winregion']][2] = [$row['winscore'],$row['losescore']];
					else
						$arrBracket['games'][$row['winregion']][2] = [$row['losescore'],$row['winscore']];
				}
				else if(in_array($row['winseed'],array(5,12)) && in_array($row['loseseed'],array(5,12))){
					if($row['winseed'] < $row['loseseed'])
						$arrBracket['games'][$row['winregion']][3] = [$row['winscore'],$row['losescore']];
					else
						$arrBracket['games'][$row['winregion']][3] = [$row['losescore'],$row['winscore']];
				}
				else if(in_array($row['winseed'],array(4,13)) && in_array($row['loseseed'],array(4,13))){
					if($row['winseed'] < $row['loseseed'])
						$arrBracket['games'][$row['winregion']][4] = [$row['winscore'],$row['losescore']];
					else
						$arrBracket['games'][$row['winregion']][4] = [$row['losescore'],$row['winscore']];
				}
				else if(in_array($row['winseed'],array(6,11)) && in_array($row['loseseed'],array(6,11))){
					if($row['winseed'] < $row['loseseed'])
						$arrBracket['games'][$row['winregion']][5] = [$row['winscore'],$row['losescore']];
					else
						$arrBracket['games'][$row['winregion']][5] = [$row['losescore'],$row['winscore']];
				}
				else if(in_array($row['winseed'],array(3,14)) && in_array($row['loseseed'],array(3,14))){
					if($row['winseed'] < $row['loseseed'])
						$arrBracket['games'][$row['winregion']][6] = [$row['winscore'],$row['losescore']];
					else
						$arrBracket['games'][$row['winregion']][6] = [$row['losescore'],$row['winscore']];
				}
				else if(in_array($row['winseed'],array(7,10)) && in_array($row['loseseed'],array(7,10))){
					if($row['winseed'] < $row['loseseed'])
						$arrBracket['games'][$row['winregion']][7] = [$row['winscore'],$row['losescore']];
					else
						$arrBracket['games'][$row['winregion']][7] = [$row['losescore'],$row['winscore']];
				}
				else if(in_array($row['winseed'],array(2,15)) && in_array($row['loseseed'],array(2,15))){
					if($row['winseed'] < $row['loseseed'])
						$arrBracket['games'][$row['winregion']][8] = [$row['winscore'],$row['losescore']];
					else
						$arrBracket['games'][$row['winregion']][8] = [$row['losescore'],$row['winscore']];
				}
				else if(in_array($row['winseed'],array(1,16)) && in_array($row['loseseed'],array(8,9)) || in_array($row['winseed'],array(8,9)) && in_array($row['loseseed'],array(1,16))){
					if(in_array($row['winseed'],array(1,16)))
						$arrBracket['games'][$row['winregion']][9] = [$row['winscore'],$row['losescore']];
					else
						$arrBracket['games'][$row['winregion']][9] = [$row['losescore'],$row['winscore']];
				}
				else if(in_array($row['winseed'],array(5,12)) && in_array($row['loseseed'],array(4,13)) || in_array($row['winseed'],array(4,13)) && in_array($row['loseseed'],array(5,12))){
					if(in_array($row['winseed'],array(5,12)))
						$arrBracket['games'][$row['winregion']][10] = [$row['winscore'],$row['losescore']];
					else
						$arrBracket['games'][$row['winregion']][10] = [$row['losescore'],$row['winscore']];
				}
				else if(in_array($row['winseed'],array(6,11)) && in_array($row['loseseed'],array(3,14)) || in_array($row['winseed'],array(3,14)) && in_array($row['loseseed'],array(6,11))){
					if(in_array($row['winseed'],array(6,11)))
						$arrBracket['games'][$row['winregion']][11] = [$row['winscore'],$row['losescore']];
					else
						$arrBracket['games'][$row['winregion']][11] = [$row['losescore'],$row['winscore']];
				}
				else if(in_array($row['winseed'],array(7,10)) && in_array($row['loseseed'],array(2,15)) || in_array($row['winseed'],array(2,15)) && in_array($row['loseseed'],array(7,10))){
					if(in_array($row['winseed'],array(7,10)))
						$arrBracket['games'][$row['winregion']][12] = [$row['winscore'],$row['losescore']];
					else
						$arrBracket['games'][$row['winregion']][12] = [$row['losescore'],$row['winscore']];
				}
				else if(in_array($row['winseed'],array(1,16,8,9)) && in_array($row['loseseed'],array(5,12,4,13)) || in_array($row['winseed'],array(5,12,4,13)) && in_array($row['loseseed'],array(1,16,8,9))){
					if(in_array($row['winseed'],array(1,16,8,9)))
						$arrBracket['games'][$row['winregion']][13] = [$row['winscore'],$row['losescore']];
					else
						$arrBracket['games'][$row['winregion']][13] = [$row['losescore'],$row['winscore']];
				}
				else if(in_array($row['winseed'],array(6,11,3,14)) && in_array($row['loseseed'],array(7,10,2,15)) || in_array($row['winseed'],array(7,10,2,15)) && in_array($row['loseseed'],array(6,11,3,14))){
					if(in_array($row['winseed'],array(6,11,3,14)))
						$arrBracket['games'][$row['winregion']][14] = [$row['winscore'],$row['losescore']];
					else
						$arrBracket['games'][$row['winregion']][14] = [$row['losescore'],$row['winscore']];
				}
				else if(in_array($row['winseed'],array(1,16,8,9,5,12,4,13)) && in_array($row['loseseed'],array(6,11,3,14,7,10,2,15)) || in_array($row['winseed'],array(6,11,3,14,7,10,2,15)) && in_array($row['loseseed'],array(1,16,8,9,5,12,4,13))){
					if(in_array($row['winseed'],array(1,16,8,9,5,12,4,13)))
						$arrBracket['games'][$row['winregion']][15] = [$row['winscore'],$row['losescore']];
					else
						$arrBracket['games'][$row['winregion']][15] = [$row['losescore'],$row['winscore']];
				}
			}
		}
		else
		{
			if(in_array($row['winregion'],array(1,4)) && in_array($row['loseregion'],array(1,4))){
				if($row['winregion'] < $row['loseregion'])
					$arrBracket['games']['finalfour'][1] = [$row['winscore'],$row['losescore']];
				else
					$arrBracket['games']['finalfour'][1] = [$row['losescore'],$row['winscore']];
			}
			else if(in_array($row['winregion'],array(2,3)) && in_array($row['loseregion'],array(2,3))){
				if($row['winregion'] < $row['loseregion'])
					$arrBracket['games']['finalfour'][2] = [$row['losescore'],$row['winscore']];
				else
					$arrBracket['games']['finalfour'][2] = [$row['winscore'],$row['losescore']];
			}
			else{
				if(in_array($row['winregion'],array(1,4)))
					$arrBracket['games']['finalfour'][3] = [$row['winscore'],$row['losescore']];
				else
					$arrBracket['games']['finalfour'][3] = [$row['losescore'],$row['winscore']];
			}
		}
		
	}
}
//print_r($arrBracket);
//echo $arrBracket['games'][3][4][12]['winid'];
	echo $json_response = json_encode($arrBracket);
	//style="background-color:lightgrey;"
?>