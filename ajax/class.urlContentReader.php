<?php
require_once __DIR__ . '/includes/db.php'; // The mysql database connection script
class urlContentReader{
	private $url='';
	private $headline='';
	private $arrArticle = array();
	private $imagePath = '';
	private $rawContent = '';
	private $arrGameResults = array();


	function __construct($url) {
		$this->url = $url;
		$this->loadHtmlContent();
		$this->parseCbsContent();
	}

	function setUrl($inputUrl) {
		$this->url = $inputUrl;
	}

	function loadHtmlContent(){
		$this->rawContent = file_get_contents($this->url);

	}
	function getHtmlContent(){
   		return $this->rawContent;
	}

	function getHeadline(){
   		return $this->headline;
	}

	function getArticle(){
   		return $this->arrArticle;
	}

	function displayContent(){
		echo '<img src="' . $this->imagePath . '" /><br>';
		echo "Headline: " .  $this->headline . "<br>";
		echo "Article:<br>";
		foreach($this->arrArticle as $paragraph)
			echo $paragraph . "<br>";

	}

	function parseCnnContent(){
		
		//Grab Headline from raw content located in the pg-headline class
		$headline = strstr($this->rawContent,'<h1 class="pg-headline">');
		$this->headline = strip_tags(strstr($headline,'</h1>',true));
		//Grab image path using the image tag in the html
		$rawImagePath = strstr($this->rawContent,'data-src-large=');
		$arrImagePath = explode('"',$rawImagePath,5);
		//$arrImagePath = explode("=",$arrImagePath[0]);
		$this->imagePath = $arrImagePath[1];
		//Parse the raw content searching for body paragraphs putting them in an array for the article for individual paragraphs to be stored seperately.
		$parseContent = $this->rawContent;
		while(preg_match ('/<p class="zn-body__paragraph">/i',$parseContent))
		{
			$parseContent = strstr($parseContent,'<p class="zn-body__paragraph">');
			$this->arrArticle[] = strip_tags(strstr($parseContent,'</p>',true));
			$parseContent = substr($parseContent,27);
		}
		//Check end of article content to see if it contains link text to other articles, if so pop that text off the array.
		while(preg_match ('/Read:/i',end($this->arrArticle)))
			array_pop($this->arrArticle);
	}

	function parseCbsContent(){
		global $mysqli;
		$parseContent = $this->rawContent;
		//Grab Headline from raw content located in the pg-headline class
		while(preg_match('/<table class="lineScore ncaabBoxScore postEvent">/i',$parseContent))
		{
			$parseContent = strstr($parseContent,'<table class="lineScore ncaabBoxScore postEvent">');
			$parseContent = substr($parseContent,48);
			$arrParseContent = explode("</table>",$parseContent);
			$currentGame = $arrParseContent[0];
			unset($arrParseContent[0]);
			$parseContent = implode("</table>",$arrParseContent);
			if(strpos($currentGame,'class="finalStatus"') && strpos($currentGame,'Final'))
			{
				$arrCurrentGame = explode("teamLocation",$currentGame);
				$strTeamOne = $arrCurrentGame[1];
				$arrTeamOne = explode("</",$strTeamOne);
				foreach($arrTeamOne as $index=>$value)
				{
					if($index == 0)
					{
						$arrValue = explode('>',strip_tags($value));
						$teamOneName = $arrValue[1];
					}
					if(strpos($value,'teamRank'))
					{
						$arrValue = explode('>',strip_tags($value));
						$teamOneRank = $arrValue[1];
					}
					if(strpos($value,'finalScore'))
					{
						$arrValue = explode('>',strip_tags($value));
						$teamOneScore = $arrValue[1];
					}
				}
				//print($teamOneName . ' rank ' . $teamOneRank . ' score ' . $teamOneScore . '<br>');
				$strTeamTwo = $arrCurrentGame[2];
				$arrTeamTwo = explode("</",$strTeamTwo);
				foreach($arrTeamTwo as $index=>$value)
				{
					if($index == 0)
					{
						$arrValue = explode('>',strip_tags($value));
						$teamTwoName = $arrValue[1];
					}
					if(strpos($value,'teamRank'))
					{
						$arrValue = explode('>',strip_tags($value));
						$teamTwoRank = $arrValue[1];
					}
					if(strpos($value,'finalScore'))
					{
						$arrValue = explode('>',strip_tags($value));
						$teamTwoScore = $arrValue[1];
					}
				}
				//print($teamTwoName . ' rank ' . $teamTwoRank . ' score ' . $teamTwoScore);
				/*
				if($teamOneScore > $teamTwoScore)
				{
					$this->arrGameResults[$teamOneName][$teamTwoName]['winscore'] = $teamOneScore;
					if($teamOneRank != $teamTwoRank)
						$this->arrGameResults[$teamOneName][$teamTwoName]['points'] = 17 - substr($teamTwoRank,1);
					else
						$this->arrGameResults[$teamOneName][$teamTwoName]['points'] = 0;
					$this->arrGameResults[$teamOneName][$teamTwoName]['losescore'] = $teamTwoScore;
				}
				else
				{
					$this->arrGameResults[$teamTwoName][$teamOneName]['winscore'] = $teamTwoScore;
					if($teamOneRank != $teamTwoRank)
						$this->arrGameResults[$teamTwoName][$teamOneName]['points'] = 17 - substr($teamOneRank,1);
					else
						$this->arrGameResults[$teamTwoName][$teamOneName]['points'] = 0;
					$this->arrGameResults[$teamTwoName][$teamOneName]['losescore'] = $teamOneScore;
				}
				*/
				//Original format
				if($teamOneScore > $teamTwoScore)
				{
					$arrResult['winteam'] = $teamOneName;
					$arrResult['winscore'] = $teamOneScore;
					if($teamOneRank != $teamTwoRank)
						$arrResult['points'] = 17 - substr($teamTwoRank,1);
					else
						$arrResult['points'] = 0;
					$arrResult['loseteam'] = $teamTwoName;
					$arrResult['losescore'] = $teamTwoScore;
				}
				else
				{
					$arrResult['winteam'] = $teamTwoName;
					$arrResult['winscore'] = $teamTwoScore;
					if($teamOneRank != $teamTwoRank)
						$arrResult['points'] = 17 - substr($teamOneRank,1);
					else
						$arrResult['points'] = 0;
					$arrResult['loseteam'] = $teamOneName;
					$arrResult['losescore'] = $teamOneScore;
				}
				
				array_push($this->arrGameResults,$arrResult);
			}
		}
print_r($this->arrGameResults);
echo '<br><br><br>';
		$query="SELECT tid, uni_name FROM team LEFT JOIN year on team.yid=year.yid WHERE year=2016";
		$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
		if($result->num_rows > 0) {
    		while($row = $result->fetch_assoc()) {
    			$teams[$row['uni_name']] = $row['tid'];
    		}
    	}
		$query="SELECT game.winid,game.points,game.loseid, team.uni_name AS winteam, game.winscore, game.losescore,(SELECT uni_name FROM team WHERE team.tid=game.loseid) as loseteam FROM game
			LEFT JOIN team ON game.winid = team.tid
			LEFT JOIN year ON team.yid = year.yid 
			WHERE year.year=2016";
		$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

		if($result->num_rows > 0) {
    		while($row = $result->fetch_assoc()) {
    			$arrDbGameResults[$row['winteam']][$row['loseteam']]['points'] = $row['points'];
			}
		}
		$query = '';
		print_r($arrDbGameResults);
		foreach($this->arrGameResults as $game)
		{
			if(!isset($arrDbGameResults[$game['winteam']][$game['loseteam']]['points']))
			{
				if($query == '')
					$query .= sprintf("INSERT INTO game (winid,loseid,winscore,losescore,points,yid) VALUES  ('%s','%s','%s','%s','%s',2)", $teams[str_replace("&#039;","'",$game['winteam'])], $teams[str_replace("&#039;","'",$game['loseteam'])], $game['winscore'], $game['losescore'], $game['points']);
				else
					$query .= sprintf(",('%s','%s','%s','%s','%s',2)", $teams[str_replace("&#039;","'",$game['winteam'])], $teams[str_replace("&#039;","'",$game['loseteam'])], $game['winscore'], $game['losescore'], $game['points']);
			}
		}
		if($query != '')
			$mysqli->query($query) or die($mysqli->error.__LINE__);
		//print_r($this->arrGameResults);
	}
}
?>