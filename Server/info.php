<?php

/*
* Information Script File (record.php)
* Author: kiethmark bandiola
* Description: Sends basic information to the client-side machine
*    This will NOT include:
* 		- Patient Vitals
*		- User Authentication Data
*/

include 'backend.php';
$db = new Database();

if (isset($_GET['wardlist'])){
	$sql = "SELECT * FROM ward"; $query = mysql_query($sql);

	$json = '['; $first = true;
	while($result = mysql_fetch_array($query)){
		if ($first){ $first = false; } else { $json .= ", "; }

		$info = '{"wardID":'.$result['idward'].', "wardName":"'.$result['ward_name'].'"}';

		$json .= $info;
	} $json .= "]";

	echo $json;
} elseif (isset($_GET['p'])){
	if ($_GET['p'] == "ward"){ //For Ward Information...
		$wardID = $_GET['wardid'];

		$ward = new Ward($wardID);
		echo $ward->show_ward_information_json();
	}
}



?>