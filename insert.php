<html>
<body>
<form action="insert.php" method="GET">
 
Enter Article URL:
<input type="text" name="urlText">
 
<input type="submit" name="submit" value="Submit">
</form>
</body>
</html>
<?php
//Just a simple check to see if there is a post for url and also with error handling should it not be a cnn.com article
require_once('/ajax/class.urlContentReader.php');

if(isset($_GET["urlText"]))
{
	if(strpos($_GET["urlText"],'cbssports.com')){
		$cnnArticle = new urlContentReader($_GET["urlText"]);
		$cnnArticle->displayContent();
	}
	else
		echo "Please enter a CNN news article";
}


?>
