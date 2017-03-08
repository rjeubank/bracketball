<?php

?>
<html>
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
    	<div class="main">
			<form id="loginform" class="form" method="post">
			<h2>Register</h2>
			<label>Email :</label>
			<input type="text" name="demail" id="email">
            <label>User Name :</label>
            <input type="text" name="username" id="username">
			<label>Password :</label>
			<input type="password" name="password" id="password">
            <label>Confirm Password :</label>
            <input type="password" name="confirmPassword" id="confirmPassword">
			<input type="submit" value="Login">
			</form>
		</div>
    </body>
    <script type="text/javascript">

    $("#loginform").submit(function( event ) {
    	event.preventDefault();
		var email = $("#email").val();
        var username = $("#username").val();
		var password = $("#password").val();
        var confirmPassword = $("#confirmPassword").val();
        var params = { email: email, password:password, confirmPassword: confirmPassword, username: username};
		$.post("ajax/registerUser.php",params).done(function(data){
			data = JSON.parse(data);
			if(data['status']=='success'){
				alert(data['message']);
				window.location = "/bracketball";
			}
			else{
				alert(data['message']);
			}
		});
	});
    </script>
</html>