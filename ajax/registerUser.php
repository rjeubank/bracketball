<?php
require_once '/../includes/db.php'; // The mysql database connection script
require_once '/../includes/auth.php';
require_once '/../vendor/autoload.php';
if(isset($_POST['email']) && isset($_POST['password']) && isset($_POST['confirmPassword']) && isset($_POST['username'])){
    $response = $auth->register($_POST['email'],$_POST['password'],$_POST['confirmPassword'],array("user_name"=>$_POST['username']));
    if($response['message'] == 'Account created.')
        $return['status']='success';
    else{
        $return['status']='fail';
    }
    $return['message'] = $response['message'];
}
echo $json_response = json_encode($return);
?>