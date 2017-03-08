<html ng-app="sidebar">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
        <!-- Bootstrap Core CSS -->
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link href="css/bootstrap.css" rel="stylesheet">
        <link href="css/jquery.bracket.min.css" rel="stylesheet">
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
        <link rel="stylesheet" type="text/css" href="css/bracket.css"/>
        <link rel="stylesheet" type="text/css" href="css/sidebar.css"/>
        <link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
        <!-- Favicon -->
        <link rel="shortcut icon" href="basketball.ico" type="image/x-icon">
        <link rel="icon" href="basketball.ico" type="image/x-icon">
        <!--[if lt IE 9]><script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
        <title>Fantasy Bracketball</title>

    </head>

    <body>
    <div id="wrapper" ng-controller="sidebar">
    <?php require 'vendor/autoload.php';
    require_once 'includes/db.php';
    require_once 'includes/auth.php';
    if ($auth->isLogged()) {
        $user = $auth->getSessionUID($_COOKIE['authID']);
        $query="SELECT users.uid,user_name,email,league.lid,league_name,draft_time FROM users LEFT JOIN league_members ON users.uid = league_members.uid LEFT JOIN league ON league.lid = league_members.lid WHERE users.uid='$user'";
        $result = $mysqli->query($query) or die($mysqli->error.__LINE__);

        $arrUser = array();
        if($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $arrUser[] = $row;
            }
        }
    }

    // $response = $auth->login('rjeubank@gmail.com','Mitten5100',1);print_r($response);
    // setcookie("authID",$response['hash'],$response['expire']);
    ?>
             <!-- Sidebar -->
                <ul class="navigation">
                        <li class="nav-item">
                            <a href="#" id="mainlink">
                                Bracketball
                            </a>
                        </li>
                        <?php
                            if (!$auth->isLogged()) {
                                echo '<li class="nav-item"><a href="register.php">Register</a></li><li class="nav-item"><a href="login.php">Login</a></li>';
                            }
                        ?>
                        <li class="nav-item">
                            <a href="#" id="bracketlink">Bracket</a>
                        </li>
                        <li class="nav-item">
                            <a href="#">Scores</a>
                        </li>
                        <?php
                            foreach($arrUser as $index=>$league){
                                echo '<li class="nav-item"><div id="league'.($index+1).'"><a id="'.$league['lid'].'lid'.$league['uid'].'" href="#'.$league['lid'].'#'.$league['uid'].'">'.$league['league_name'].'</a></div></li>'; 
                            }
                        ?>
                    
                    <li class="nav-item"><div id="league2">
                    </div></li>
                    <li class="nav-item"><div id="league3">
                    </div></li>
                    <li class="nav-item"><div id="league4">
                    </div></li>
                    <li class="nav-item"><div id="league5">
                    </div></li>
                        <li class="nav-item">
                            <a href="#">My Account</a>
                        </li>
                        <?php
                            if ($auth->isLogged()) {
                                echo '<li class="nav-item" id="logout"><a href="#">Logout</a></li>';
                            }
                        ?>
                </ul>
            <input type="checkbox" id="nav-trigger" class="nav-trigger" />
            <label for="nav-trigger"></label>
            <div id="page-content-wrapper" class="page-content-wrapper">
                <div id=draftStatusWrapper>
                <div id=draftSliderWrapper></div>
                <label class="switchLabel">Bracket</label><label class="switch"><input id="viewSwitch" type="checkbox"><div class="slider round"></div></label><label class="switchLabel">Table</label><div id="selectedTeam">None Selected</div><button type="button" id="draftButton" >Select</button></div>
                <div id="maincontent-wrapper" class="container">
                </div>
                <div id="maincontent-hidden" class="hidden-container">
                </div>
                   <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.3.14/angular.min.js"></script>
                  <script src="js/jquery.js"></script>
                   <script type="text/javascript" src="app/app.js"></script>
                   <script type="text/javascript" src="js/jquery.bracket.min.js"></script>
            </div>
        </div>
    </body>
</html>
