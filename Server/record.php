<?php

/*
*  Data Record Input Script - record.php
*  Author: kiethmark bandiola
*  Uses a mobile phone on inputting data...
*  Input: JSON string. No NULL inputs
*/

include "backend.php";
$database = new Database();

if (isset($_GET['insert'])){
	// This acts for a new data inserted to the database
	// This leaves a NOT LOCKED option

	// Get user by checking SESSION_ID stored in APP
	$sessionid = "4918jfij89wja89f9q82jrfafa9sa";

	// Then initiate data transfer
	//$sql = "INSERT INTO vitals VALUES(NULL, $time, $t, $b, $r, $p, $c, $n, 0)";

	// Send data to database..

	// Check for abnormal values...

	// Send back to phone a response...
	echo '{"normal":"true"}';


} else {
	return 0;
}




?>