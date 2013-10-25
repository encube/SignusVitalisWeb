<?php

include 'backend.php';
$db = new Database();
$hospital = new Hospital();

echo $hospital->get_hospital_name().", ".$hospital->get_hospital_location();


?>