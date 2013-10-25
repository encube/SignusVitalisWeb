<?php

include 'backend.php';
$db = new Database();


//$_GET['v']; $_GET['i']
$user = new User($_GET['i']);
$user->update_shift($_GET['v']);
echo $user->elaborate_shift_information($user->get_shift());


?>