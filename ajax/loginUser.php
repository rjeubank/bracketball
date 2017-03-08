<?php
require_once '/../includes/db.php'; // The mysql database connection script
require_once '/../includes/auth.php'; 
if(isset($_POST['email']) && isset($_POST['password'])){
    $response = $auth->login($_POST['email'],$_POST['password'],1);
    if($response['message'] == 'Email address / password are incorrect.')
        $return['status']='fail';
    else{
        $return['status']='success';
        setcookie("authID",$response['hash'],$response['expire'],'/');
    }
    $return['message'] = $response['message'];
}
echo $json_response = json_encode($return);
?>