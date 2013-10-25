<?php


include "backend.php";
$db = new Database();

/* 
$now = new Date("NOW");
$dates = Array(new Date("2013-07-01"), new Date("2012-12-24"));
foreach($dates as $date){
	echo "Birhdate is: ".$date->get_date()." and age is ";
	$age = $date->get_difference_from_date($now);
	if ($age == 0){
		$days = $date->convert_to_days(); $thisMonth = $date->get_month();
		if ($days > $date->check_how_many_days($thisMonth)){
			//echo $date->check_how_many_days($thisMonth);
			echo " months old";
		} else { echo $days." days old"; }
	} else { echo $age." years old."; }

	echo "<br>";
} */


$machine = new Machine($_SERVER['REMOTE_ADDR']);
$wardID = $machine->get_ward_id(); $clone = null;

$sql = "SELECT idcases FROM cases WHERE ward_idward=".$wardID;
$query = mysql_query($sql); while($result = mysql_fetch_array($query)){
	$case = new Cases($result['idcases']); $clone = $case;
	
	$hasMonitor = null;
	if ($case->is_patient_case_new()){
		// Diritso ni siya ug add
		$hasMonitor = "NEW CASE. CAN ADD";
	} else {
		if ($case->is_inside_wait_time()){
			// if true... dili i.add...
			$hasMonitor = "INSIDE WAIT TIME. CAN'T ADD";
		} else {
			// If false and value, i.add dayun...
			$hasMonitor = "CAN ADD";
		}
	}
	
	echo "Case No: ".$case->get_case_number();
	echo "<br>Patient Name: ".$case->get_case_patient()->get_patient_name()->get_full_name();
	echo "<br>Monitor Time: ".$case->get_monitor_timeframe()." minutes";
	echo "<br>Is Monitored: ".$hasMonitor;
	
	$latest_vital = $case->get_recent_vital();
	if ($latest_vital != false){
		echo "<br>Last Vital Sent: ".$latest_vital->get_timestamp();
	} else { echo "<br>Last Vital Sent: None"; }
	
	
	echo "<br>-------------------------------------------<br>";
}

echo "<br><br>";
$kiss = new Cases(2); $today = new Time("NOW");

$kini = new Time("22:10:00"); $time = new Time("NOW");
echo "kini ". $kini->print_time()." | time ".$time->print_time()."<br>";
if ($kini->is_this_time_larger_than_this($time)){
	echo $kini->print_time()." is larger than ".$time->print_time();
} else { echo $kini->print_time()." is NOT larger than ".$time->print_time(); }
//echo $today->print_time();


/*
echo "Is Current Time inside? ";
if ($last_vital_timestamp->is_past_this_time($today)){
	echo "yes!";
} else { echo "no!"; }  */

?>