<html>
<body>
<form action="index.php" method="POST">
 
Enter Article URL:
<input type="text" name="urlText">
 
<input type="submit" name="submit" value="Submit">
</form>
</body>
</html>
<?php
//Just a simple check to see if there is a post for url and also with error handling should it not be a cnn.com article
require_once('../insert/class.urlContentReader.php');

if(isset($_POST["urlText"]))
{
	if(strpos($_POST["urlText"],'cbssports.com')){
		$cnnArticle = new urlContentReader($_POST["urlText"]);
		$cnnArticle->displayContent();
	}
	else
		echo "Please enter a CNN news article";
}


?>
