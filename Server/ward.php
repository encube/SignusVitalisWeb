<?php
	include 'backend.php';
	$db = new Database();
	$sql = "UPDATE registered_user SET ward_idward =".$_POST['wardid']." WHERE user_iduser=".$_POST['userid'];
	mysql_query($sql);
	
	$db->close();

?>