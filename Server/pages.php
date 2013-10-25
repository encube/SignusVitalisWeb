<?php



if (isset($_GET['p'])){
	$page = $_GET['p'];

	if ($page == "home"){
		include "pages/home.php";
	}



}




?>