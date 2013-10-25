<?php

include 'backend.php';
$db = new Database();

$notifications = new NotificationMachine(new Machine($_SERVER['REMOTE_ADDR']));
//$unmonitored = $notifications->get_unmonitored_cases();

if (isset($_GET['count'])){
	echo $notifications->get_unmonitored_count();
}



?>