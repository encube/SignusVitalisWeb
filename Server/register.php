<?php

/*
* User Registration Script - register.php
* Author: kiethmark bandiola
* Description: Handles the registration form done on the phone.
* Input: JSON data. 
*/

include 'backend.php';
$db = new Database();

// {
//  "last_name":"<last_name>", "first_name":"<first_name>", 
//  "middle_name":"<middle_name>", "rank":<rank>, "ward":null|<wardID>
// }

$data = $_POST['data']; 
//$data = '{
//			"last_name":"Bandiola", "first_name":"Scalaberch", "middle_name":"Sevilla",
//			"rank":"NURSE", "ward":3, "password":"kmbandiola"
//		}';

$json = json_decode($data);

//print_r($json);

$user = new User(null);
$status = $user->register_new_user($json);

if ($status){ 
	echo '{"regStatus":"true"}';
} else { echo '{"regStatus":"false"}'; }



?>