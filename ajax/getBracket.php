<?php
require_once __DIR__ . '/../includes/db.php'; // The mysql database connection script

$query="SELECT * FROM team LEFT JOIN region ON team.rid = region.rid LEFT JOIN year ON team.yid = year.yid WHERE year.year=2016";
$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

$arrUnsortedBracket = array();
if($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $arrUnsortedBracket[] = $row;
    }
}
foreach($arrUnsortedBracket as $row)
{
	//Create a formated array where first index is the region rank and the second is the seed, then create an array within those indices containing the team info.
	//Check to see if the seed already exists if so it's a playin game and combine the information.
	if(isset($arrBracket[$row['region_rank']][$row['seed']]['uni_name']))
	{
		$arrBracket[$row['region_rank']][$row['seed']]['uni_name'] = $arrBracket[$row['region_rank']][$row['seed']]['uni_name'] . '/' . $row['uni_name'];
		$arrBracket[$row['region_rank']][$row['seed']]['formatted_name'] = $row['seed'] . ' ' . $arrBracket[$row['region_rank']][$row['seed']]['uni_name'];
		$arrBracket[$row['region_rank']][$row['seed']]['mascot'] = $arrBracket[$row['region_rank']][$row['seed']]['mascot'] . '/' . $row['mascot'];
		$arrBracket[$row['region_rank']][$row['seed']]['abbrev'] = $arrBracket[$row['region_rank']][$row['seed']]['abbrev'] . '/' . $row['abbrev'];
	}
	else
	{
		$arrBracket[$row['region_rank']][$row['seed']]['uni_name'] = $row['uni_name'];
		$arrBracket[$row['region_rank']][$row['seed']]['formatted_name'] = $row['seed'] . ' ' . $row['uni_name'];
		$arrBracket[$row['region_rank']][$row['seed']]['mascot'] = $row['mascot'];
		$arrBracket[$row['region_rank']][$row['seed']]['abbrev'] = $row['abbrev'];
	}
}


	echo $json_response = json_encode($arrBracket);
?>