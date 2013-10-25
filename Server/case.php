<?php

include 'backend.php';
$db = new Database();


if (isset($_GET['add'])){

	//Get this ward ID...
	$machine = new Machine($_SERVER['REMOTE_ADDR']);
	$wardID = $machine->get_ward_id();

	//$data = Array('bandiola', 'kieth mark', 'sevilla', '', '1993-09-19');
	$patient = new Patient(null); $data   = $_GET['d']; 
	$patient->set_name($data[0], $data[1], $data[2]);

	if (!$patient->is_patient_already_in_db()){
		//Register the guy on the chuchu...
		$person = $patient->get_patient_name();
		$patient->register_patient($person->get_last_name(), $person->get_first_name(), 
										$person->get_middle_name(), $data[4]);
	}

	$patientID = $patient->get_id_by_name(); $case = new Cases(null);
	$caseID = $case->get_next_case_number(); $patient->set_patient_id($patientID);
	if ($data[6] == "-1"){
		$counter = $data[7];
	} else { $counter = $data[6]; }
	
	$patient->add_patient_case($caseID, date('Y-m-d h:i:s'), $data[3], $data[5], $wardID, $counter);
	
	//Then register to Dashboard....
	
	$register = new Feed(null, 2, "NOW", $caseID);
	$register->register_this_to_database($wardID);
} else if (isset($_GET['discharge'])){
	$case = new Cases($_GET['c']);
	$case->discharge_patient();
	
	$register = new Feed(null, 4, "NOW", $case->get_case_number());
	$register->register_this_to_database($case->get_ward_assigned());
}  else if (isset($_GET['time'])){
	$case = new Cases($_GET['c']);
	$case->update_vitals_record_time($_GET['v']);

}




?>