<?php

include 'backend.php';
$db = new Database();


if (isset($_GET['insert'])){

	//$json = '{ 
	//		"session_id":"558ed5ea31ce8fb60b85899bd0bcd120",
	//		"case_id":"4",
	//		"temp":"120",
	//		"rr":"33",
	//		"bp":"80/122",
	//		"pr":"23",
	//		"timestamp":""
	//	 }';

	$json = $_POST['data'];

	$data = json_decode($json); //$isOkay = false;
	$vitals = new Vitals("NEW"); $case = new Cases($data->case_id);
	
	/*
	//Check if on monitor...
	if ($case->has_monitor_timeframe()){ //If bantayonon... :)

		//Check if already have given the vitals...
		$previous_time = $expected_time = null; 
		$time_frame = $case->get_monitor_timeframe(); //in minutes ni... :)

		if ($case->is_patient_case_new()){
			//Get admitted time...
			//$previous_time = new Time($case->get_admit_time()); 
			//$expected_time = new Time($case->get_admit_time());
			//$expected_time->add_time($time_frame); 

			$isOkay = true;

		} else {
			//Get recent vitals submission...
			//print_r($case->get_vitals_list());
			$vitals_list = $case->get_vitals_list(); 
			$size = $case->get_number_of_vitals_submitted();
			$recent_vital = $vitals_list[$size-1];

			$timestamp = explode(" ", $recent_vital->get_timestamp());
			$previous_time = new Time($timestamp[1]); $expected_time = new Time($timestamp[1]);
			$expected_time->add_time($time_frame);

			//print_r($previous_time);
			//print_r($expected_time);

			$today = new Time("NOW");
			//print_r($today);
			if (!$expected_time->is_past_this_time($today)){ //$expected > $today, outside na
				//echo "it is passed!";
				//Kailangan na ni record
				$isOkay = true;
			} else { 
				//echo "not yet passed!"; 
				echo '{"submit":"false", "observe":"true"}'; 
			}
		}

	} else { $isOkay = true; }
	*/
	//Check for abnormalities for the specific vitals...

	//$isOkay = false;
	//if ($case->is_patient_case_new()){
		// Diritso ni siya ug add
	//	$isOkay = true;
	//} else {
	//	if ($case->is_inside_wait_time()){
			// if true... dili i.add...
	//		echo '{"submit":"false", "observe":"true"}'; 
	//	} else {
			// If false and value, i.add dayun...
	//		$isOkay = true;
	//	}
	//}
	
	$isOkay = true;
	if ($isOkay){
		$new_vitals = new Vitals("NEW");
		if ($new_vitals->register_vitals_to_db($data)){
			$recent_vital = $case->get_recent_vital(); $type = 3; //FEED_RECORDED
			$link_id = $recent_vital->get_vitals_id();
		//
			$vital = new Feed(null, $type, "NOW", $link_id);
			$vital->register_this_to_database($case->get_ward_assigned());
			echo '{"submit":"true"}';
		} else { echo '{"submit":"false"}'; }
	} 
} 
elseif (isset($_GET['edit']) ){
	//Get userid from session id
	//$json = '{ 
	//		"session_id":"f7d37f257ad239d3d3ad9e09d1cbd25a",
	//		"vitals_id":"5",
	//		"temp":"120",
	//		"rr":"33",
	//		"bp":"12",
	//		"pr":"3",
	//	//	"timestamp":""
	//	 }';

	/*
		input: {session_id:value, vitals_id:value, temp:val, rr:val, bp, val, pr:val}
		input: POST
		output: {edit:BOOLEAN}
	*/

	$json = $_POST['data']; 
	$data = json_decode($json); $session = new Session($data->session_id);

	//Check if userid matches 
	$userid = $session->get_current_user()->get_user_id();
	$vitals = new Vitals($data->vitals_id);
	if ($vitals->get_attending_nurse()->get_user_id() == $userid){
		//Update the chu chu in the database... :)

		if (isset($_GET['lock'])){

			if ($vitals->lock_vitals()){
				//Lock success!
				echo '{"lock":"true"}';
			} else { echo '{"lock":"false"}'; }

		} else {

			if ($vitals->update_vitals_db($data)){
				//Update success!
				echo '{"edit":"true"}';
				//Check for abornmalities...
			} else { echo '{"edit":"false"}'; }
		}


	} else {
		echo '{"canAccess":"false"}';
	}
} elseif (isset($_GET['list'])){
	//$_GET['c'] = '6';

	//Get first the user who wants to access this... :)
	$session = new Session($_GET['s']);
	$user    = $session->get_current_user();

	$case = new Cases($_GET['c']);
	$vitals_list = $case->load_vitals_list();

	$json = '['; $first = true;
	foreach($vitals_list as $vital){
		
		// Checking if locked...
		if ($vital->is_vitals_locked()){
			$lock = "true";
		} else { $lock = "false"; }

		//Checking if can edit...
		$attending_nurse = $vital->get_attending_nurse()->get_user_id();
		$attending_nurse_name = $vital->get_attending_nurse()->get_user_name()->get_full_name();
		if ($attending_nurse == $user->get_user_id()){
			$edit = "true";
		} else { $edit = "false"; }

		$data = '{
			"vitalsID":"'.$vital->get_vitals_id().'",
			"temp":"'.$vital->get_temperature().'",
			"rr":"'.$vital->get_respiratory_rate().'",
			"bp":"'.$vital->get_blood_pressure().'",
			"pr":"'.$vital->get_pulse_rate().'",
			"isLock":"'.$lock.'",
			"isEdit":"'.$edit.'",
			"timestamp":"'.$vital->get_timestamp().'",
			"nurse_name":"'.$attending_nurse_name.'"
		}';

		if ($first){
			$json .= $data; $first = false;
		} else { $json .= ", ".$data; }
		
	} $json .= ']'; echo $json;
	//print_r($vitals_list);


}


?>