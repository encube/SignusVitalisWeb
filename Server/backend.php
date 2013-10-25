<?php

date_default_timezone_set("Asia/Manila");

/**
* Here goes the main object definitions.
**/

class User {

	private $user_id;

	private $user_name;
	private $assigned_ward;
	private $shift;
	private $position; 

	public function User($userid){
		if ($userid == null){ //Either will be used for LOGIN or REGISTER

		} else {
			$sql = "SELECT * FROM user
					LEFT JOIN registered_user ON user.iduser=registered_user.user_iduser
					WHERE user.iduser=".$userid;

			$query = mysql_query($sql); if (mysql_num_rows($query) == 0){
				$this->user_id = false;
			} else {
				$result = mysql_fetch_array($query);

				$this->user_id = $userid;
				$this->user_name = new Person($result['user_last_name'], $result['user_first_name'], $result['user_middle_name']);
				$this->assigned_ward = $result['ward_idward'];
				$this->shift =  $result['shift'];
				$this->position = $result['user_rank'];
			}
		}
	}

	public function get_user_id(){ return $this->user_id; }
	public function get_user_name(){ return $this->user_name; }
	public function get_assigned_ward(){ return $this->assigned_ward; }
	public function get_shift(){ return $this->shift; }
	public function get_position(){ return $this->position; }

	public function authenticate($username, $password){
		$sql = "SELECT iduser FROM user WHERE user_name='$username' AND user_password='$password' ";
		$query = mysql_query($sql); if (mysql_num_rows($query) == 0){
			return false;
		} else { $result = mysql_fetch_array($query); return $result['iduser']; }
	}

	public function is_username_already_taken($username_md5){
		$sql = "SELECT iduser FROM user WHERE user_name='$username_md5' ";
		$query = mysql_query($sql); if (mysql_num_rows($query) == 0){
			return false;
		} else { return true; }
		//$result = mysql_fetch_array($query);
	}

	public function register_new_user($data){
		//$data is an object that was decoded from a json string...
		$password  = md5($data->password);
		$username  = md5($this->generate_username($data->first_name, $data->last_name));

		if ($this->is_username_already_taken($username)){
			echo '{"status":false, "errcode":"UAT"}';
		} else { 
			$sql = "INSERT INTO user VALUES(null, '".$data->last_name."', '".$data->first_name."', 
					'".$data->middle_name."', '$username', '$password', '".$data->rank."')";

			if (!mysql_query($sql)){ return false; } else { //elseif ($data->ward != null){
				if ($data->ward != null){
					$user_id = $this->get_user_id_by_username($username);
					$sql = "INSERT INTO registered_user VALUES(null, null, $user_id, ".$data->ward.")";

					if (!mysql_query($sql)){ return false; } else { return true; }
				} else { return true; }
			}
		}
	}

	public function get_user_id_by_username($md5_username){
		$sql = "SELECT iduser FROM user WHERE user_name='$md5_username' ";
		$query = mysql_query($sql); if (mysql_num_rows($query) == 0){ return false; } 
		else { 
			$result = mysql_fetch_array($query); return $result['iduser'];
		}
	}

	public function generate_username($firstname, $lastname){
		$first = explode(" ", $firstname); $last = explode(" ", $lastname); $f = $l = "";
		foreach($first as $firstname_char){ $f .= $firstname_char; }
		foreach($last as $lastname_char){ $l .= $lastname_char; }

		return strtolower($firstname).".".strtolower($lastname);
	}

	//Shift Converter... :)
	public function elaborate_shift_information($shift_tag){
		switch($shift_tag){
			case "7-15" : return "7am - 3pm"; break;
			case "15-23" : return "3pm - 11pm"; break;
			case "23-7" : return "11pm - 7am"; break; 
		}
	}

	public function is_on_duty(){
		if ($this->get_position() == "NURSE"){
			$isOnline = $this->is_online(); $isMyDuty = $this->is_my_duty_now();
			return $isOnline and $isMyDuty;
		} else { return $this->is_online(); }
	}

	// Duty Calls...
	public function is_my_duty_now(){
		if ($this->get_shift() == null){
			return false;
		} else {
			$shift = explode("-", $this->get_shift());  
			$hour = date("G"); $end = $shift[1] - 1;

			if ($this->get_shift() == "23-7"){
				if ($hour != 23){
					$hour += 24;
				} $end += 24;
			}

			if ($hour >=  $shift[0]){
				if ($end >= $hour) { return true; } else { return false; }
			} else { return false; }
		}
	}

	public function is_online(){
		$sql = "SELECT idsession FROM session WHERE timestamp_logout IS NULL AND user_iduser=".$this->get_user_id();
		$query = mysql_query($sql); if (mysql_num_rows($query) == 0){
			return false;
		} else { return true; }
	}


	public function update_shift($new_shift_tag){
		$this->shift = $new_shift_tag;
		$sql = "UPDATE registered_user SET shift='$new_shift_tag' WHERE user_iduser=".$this->get_user_id();
		return mysql_query($sql);
	}
	
	public function is_this_my_password($password){
		$pass = md5($password);
		
		$sql = "SELECT COUNT(*) FROM user WHERE user_password='$pass' AND iduser=".$this->get_user_id();
		$query = mysql_query($sql); $result = mysql_fetch_array($query);
		if ($result['COUNT(*)'] == 0){
			return false;
		} else { return true; }
	}
	
	public function update_password($password){
		$newpassword = md5($password);
		$sql = "UPDATE user SET user_password='$newpassword' WHERE iduser=".$this->get_user_id();
		return mysql_query($sql);
	}
}

class Ward {

	private $ward_id;
	private $ward_name;
	private $ward_room_num;
	private $ward_floor;
	private $ward_location;

	public function Ward($wardID){
		if ($wardID != null){
			$sql = "SELECT * FROM ward WHERE idward=".$wardID;
			$query = mysql_query($sql); $result = mysql_fetch_array($query);

			$this->ward_id = $wardID;
			$this->ward_name = $result['ward_name'];
			$this->ward_location = $result['ward_location'];
			$splitted = explode(' ', $result['ward_location']);
			$this->ward_room_num = $splitted[1];
			$this->ward_floor = $splitted[0];
		} else { $ward_name = $ward_room_num = $ward_floor = null; }
	}

	public function show_ward_information_json(){
		return '{"name":"'.$this->ward_name.'", 
				 "rm_num":"'.$this->ward_room_num.'", 
				 "flr":"'.$this->ward_floor.'" }';
	}

	public function get_ward_name(){ return $this->ward_name; }
	public function get_ward_location(){ return $this->ward_location; }
	
	public function get_total_cases(){
		$sql0 = "SELECT COUNT(*) FROM cases WHERE ward_idward=".$this->ward_id;
		$query = mysql_query($sql0); $result0 = mysql_fetch_array($query);
		
		return $result0['COUNT(*)'];
	}
	
	public function get_current_cases(){
		$sql0 = "SELECT COUNT(*) FROM cases WHERE status='ADMITTED' AND ward_idward=".$this->ward_id;
		$query = mysql_query($sql0); $result0 = mysql_fetch_array($query);
		
		return $result0['COUNT(*)'];
	}
	
	public function get_discharged_cases(){
		return $this->get_total_cases() - $this->get_current_cases();
	}
	
	public function get_total_vitals(){
		$sql = "SELECT COUNT(*) FROM vitals LEFT JOIN cases ON cases.idcases=vitals.case_idcases
					WHERE cases.ward_idward=".$this->ward_id;
					
		$query = mysql_query($sql); $result0 = mysql_fetch_array($query);
		return $result0['COUNT(*)'];
	}
	
	public function get_unmonitored_cases(){
		$sql = "SELECT idcases FROM cases WHERE status='ADMITTED' AND ward_idward=".$this->ward_id;
		$query = mysql_query($sql); $cases = Array();
		while($result = mysql_fetch_array($query)){
			$case = new Cases($result['idcases']);
			
			if (!$case->is_patient_case_new() and !$case->is_inside_wait_time()){
				$cases[] = $case;
			}
		
		} return $cases;
	}
	
}

class Cases {

	private $case_number;
	private $case_date;
	private $case_patient;
	private $diagnosis;
	private $bed_number;
	private $ward_assigned;
	private $status;
	private $timer;

	private $vitals_list;

	public function Cases($case_id){
		if ($case_id != null){
			$sql = "SELECT * FROM cases WHERE idcases=".$case_id;
			$query = mysql_query($sql); if (mysql_num_rows($query) != 0){
				$result = mysql_fetch_array($query);

				$this->case_number   = $case_id;
				$this->case_date     = $result['date_admitted'];
				$this->case_patient  = new Patient($result['patient_idpatient']);
				$this->diagnosis     = $result['diagnosis'];
				$this->bed_number    = $result['bed_number'];
				$this->ward_assigned = $result['ward_idward'];
				$this->status        = $result['status'];
				$this->timer 		 = $result['timer_hours'];


				$this->vitals_list 	 = Array();
				$sql = "SELECT idvitals FROM vitals WHERE case_idcases=".$case_id;
				$query0 = mysql_query($sql); if (mysql_num_rows($query0) != 0){

					while($result0 = mysql_fetch_array($query0)){
						$vitals = $this->vitals_list; $new_vitals = new Vitals($result0['idvitals']);
						$vitals[] = $new_vitals; $this->vitals_list = $vitals;
					}

				}
			}
		}
	}

	//Getter/Setter methods
	public function get_case_number(){ return $this->case_number; }
	public function get_case_timestamp(){ return $this->case_date; }
	public function get_case_patient(){ return $this->case_patient; }
	public function get_diagnosis(){ return $this->diagnosis; }
	public function get_bed_number(){ return $this->bed_number; }
	public function get_ward_assigned(){ return $this->ward_assigned; }
	public function get_status(){ return $this->status; }
	public function get_monitor_timeframe(){ return $this->timer; }

	public function get_vitals_list(){ return $this->vitals_list; }

	public function get_admit_date(){ 
		$result = explode(" ", $this->get_case_timestamp()); return $result[0];
	}

	public function get_admit_time(){
		$result = explode(" ", $this->get_case_timestamp()); return $result[1];
	}

	public function is_patient_case_new(){
		if ($this->get_number_of_vitals_submitted() == 0){
			return true;
		} else { return false; }
	}
	
	public function is_case_discharged(){
		 if ($this->status == "DISCHARGED"){
			return true;
		 } else { return false; }
	}
	
	public function update_vitals_record_time($value){
		$sql = "UPDATE cases SET timer_hours=$value WHERE idcases=".$this->get_case_number();
		return mysql_query($sql);
	}

	public function get_number_of_vitals_submitted(){
		return sizeof($this->vitals_list);
	}

	public function has_monitor_timeframe(){
		if ($this->timer == 0){
			return false;
		} else { return true; }
	}
	
	public function is_inside_wait_time(){
		
		$today = new Time("NOW"); $todate = new Date("NOW");
		$last_vital_recieved = $this->get_recent_vital(); $minutes_on_waiting = $this->get_monitor_timeframe();
		
		if ($last_vital_recieved != false){ //Ibig sabihin, NOT a new case na siya :)
			$lvr = explode(" ", $last_vital_recieved->get_timestamp()); $clone = new Time($lvr[1]);
			$last_vital_timestamp = new Time($lvr[1]); $last_vital_date = new Date($lvr[0]);
			
			//Check if they are on the same date...	
			if ($todate->get_day() != $last_vital_date->get_day()){
				if ($minutes_on_waiting < (60*24)){ //Lapas ba ug one day?
					return false;
				}
				
				$difference = ($todate->get_day() - $last_vital_date->get_day())*24;
				$newHour = $difference + $today->get_hour(); $today->set_hour($newHour);
			}
			
			$last_vital_timestamp->add_time($minutes_on_waiting);
			if ($last_vital_timestamp->is_this_time_larger_than_this($today)){
				return true;
			} else { return false; }
			
		} else { return true; } //Ibig sabihin, new case na siya :)
			
/* 	
		$minutes_on_waiting = $this->get_monitor_timeframe(); $today = new Time("NOW");
		$last_vital_recieved = $this->get_recent_vital(); 
		if ($last_vital_recieved != false){ //Ibig sabihin, new case na siya :)
			$lvr = explode(" ", $last_vital_recieved->get_timestamp());
			$last_vital_timestamp = new Time($lvr[1]); $clone = new Time($lvr[1]);
			
			$last_vital_timestamp->add_time($minutes_on_waiting);
			if ($last_vital_timestamp->is_this_time_larger_than_this($today)){
				return true;
			} else { return false; } 
		} else { return true; } //Ibig sabihin, new case na siya :) */
		
	}

	public function is_already_monitored(){
		//Check if already have given the vitals...
		$previous_time = $expected_time = null; $isOkay = false;
		$time_frame = $this->get_monitor_timeframe(); //in minutes ni... :)

		if ($this->is_patient_case_new()){
			$isOkay = true;
		} else {
			//Get recent vitals submission...
			//print_r($case->get_vitals_list());
			$vitals_list = $this->get_vitals_list(); 
			$size = $this->get_number_of_vitals_submitted();
			$recent_vital = $vitals_list[$size-1];

			$timestamp = explode(" ", $recent_vital->get_timestamp());
			$previous_time = new Time($timestamp[1]); $expected_time = new Time($timestamp[1]);
			$expected_time->add_time($time_frame);

			$today = new Time("NOW");
			//print_r($today);
			if (!$expected_time->is_past_this_time($today)){ //$expected > $today, outside na
				//echo "it is passed!";
				//Kailangan na ni record
				$isOkay = true;
			} else {  $isOkay = false; }
		}

		return $isOkay;
	}

	public function get_recent_vital(){
		$sql = "SELECT idvitals FROM vitals WHERE case_idcases=".$this->get_case_number()." ORDER BY time_stamp DESC";
		$query = mysql_query($sql); if (mysql_num_rows($query) == 0){
			return false;
		} else { 
			$result = mysql_fetch_array($query); return new Vitals($result['idvitals']);
		}
	}

	//Load Vitals List...
	public function load_vitals_list(){
		$vitals = Array();

		$sql = "SELECT idvitals FROM vitals WHERE case_idcases=".$this->get_case_number()." ORDER BY time_stamp DESC";
		$query = mysql_query($sql);
		while($result = mysql_fetch_array($query)){
			$vital = new Vitals($result['idvitals']);
			$vitals[] = $vital;
		} return $vitals;
	}

	//Discharge Patient
	public function discharge_patient(){

		$sql = "UPDATE cases SET status='DISCHARGED' WHERE idcases=".$this->get_case_number();
		if (!mysql_query($sql)){ return false; } else { return true; }
	}

	public function get_next_case_number(){
		$sql = "SELECT COUNT(*) FROM cases";
		$query = mysql_query($sql); $result = mysql_fetch_array($query);

		return $result['COUNT(*)'] + 1;
	}

}

class Vitals {

	private $vitals_id;
	private $case_id;

	private $patient_recorded;
	private $timestamp_recorded;
	private $attending_nurse;

	private $temperature;
	private $respiratory_rate;
	private $blood_pressure;
	private $pulse_rate;

	private $locked;

	public function Vitals($vitals_source){
		if ($vitals_source == "NEW"){ //Means it is NEW

		} else {
			$sql = "SELECT * FROM vitals LEFT JOIN cases 
					ON cases.idcases=vitals.case_idcases WHERE idvitals=".$vitals_source;
			
			$query = mysql_query($sql); if (mysql_num_rows($query) != 0){
				$result = mysql_fetch_array($query);

				$this->vitals_id = $vitals_source;
				$this->case_id = $result['case_idcases'];

				$this->patient_recorded = new Patient($result['patient_idpatient']);
				$this->timestamp_recorded = $result['time_stamp'];
				$this->attending_nurse = new User($result['user_iduser']);
				
				$this->temperature = $result['temperature'];
				$this->respiratory_rate = $result['resp_rate'];
				$this->blood_pressure = $result['blood_press'];
				$this->pulse_rate = $result['pulse_rate'];

				$this->locked = $result['lock'];
			}
		}
	}

	//Getter/Setters for this class...
	public function get_vitals_id(){ return $this->vitals_id; }
	public function get_case_assigned_id(){ return $this->case_id; }
	public function get_patient(){ return $this->patient_recorded; }
	public function get_timestamp(){ return $this->timestamp_recorded; }
	public function get_attending_nurse(){ return $this->attending_nurse; }
	public function get_temperature(){ return $this->temperature; }
	public function get_respiratory_rate(){ return $this->respiratory_rate; }
	public function get_blood_pressure(){ return $this->blood_pressure; }
	public function get_pulse_rate(){ return $this->pulse_rate; }

	public function set_vitals_id($vitals_id){   $this->vitals_id = $vitals_id; }
	public function set_patient($patient){   $this->patient_recorded = $patient; }
	public function set_timestamp($timestamp){   $this->timestamp_recorded = $timestamp; }
	public function set_attending_nurse($nurse){   $this->attending_nurse = $nurse; }
	public function set_temperature($hr){   $this->temperature = $hr; }
	public function set_respiratory_rate($rr){   $this->respiratory_rate = $rr; }
	public function set_blood_pressure($br){   $this->blood_pressure = $br; }
	public function set_pulse_rate($pp){   $this->pulse_rate = $pp; }

	//Register vitals to the database...
	public function register_vitals_to_db($data){
		// Format for JSON submission:
		// session_id, caseid, hr, rr, bp, pr

		//Get CURRENT_USER for the SESSION
		$user_session = new Session($data->session_id);
		$user_id = $user_session->get_current_user()->get_user_id();
		
		$bp = (string)$data->bp;

		//For now, I'll use CURRENT_TIMESTAMP of the server... :)
		$sql = "INSERT INTO vitals VALUES(null, CURRENT_TIMESTAMP,
					".$data->temp.", '".$bp."', ".$data->rr.", ".$data->pr.",
					".$data->case_id.", $user_id, 0)";
		

		if (!mysql_query($sql)){ return false; } else { return true; }
	}

	public function update_vitals_db($data){

		$sql = "UPDATE vitals SET
					temperature=".$data->temp.",
					blood_press=".$data->bp.",
					resp_rate=".$data->rr.",
					pulse_rate=".$data->pr."
				WHERE idvitals=".$this->vitals_id;

		if (!mysql_query($sql)){ return false; } else { return true; }
	}

	public function is_vitals_normal(){
		return $this->is_rr_normal() and $this->is_pulse_normal() and $this->is_bp_normal();
	}

	public function is_vitals_locked(){
		if ($this->locked == 0){
			return false;
		} else { return true; }
	}

	public function lock_vitals(){
		//Assumption: vitals are not yet locked...
		$sql = "UPDATE vitals SET vitals.lock=1 WHERE idvitals=".$this->vitals_id;
		if (!mysql_query($sql)){ return false; } else { return true; }
	}

	// Pang himantay sa mga vital signs... :)
	public function is_rr_normal(){

		$patient = $this->get_patient(); $rr = $this->get_respiratory_rate();
		//$age = $patient->get_age();
		$bracket = $patient->get_age_bracket();
		
		//echo $age;

		
		if ($bracket == "NEWBORN" and ($rr >= 30 and $rr <= 80)){
			return true;
		} elseif ($bracket == "INFANT" and ($rr >= 30 and $rr <= 50)){
			return true;
		} elseif ($bracket == "TODDLER" and ($rr >= 23 and $rr <= 35)){
			return true;
		} elseif ($bracket == "PRESCHOOL" and ($rr >= 20 and $rr <= 30)){
			return true;
		} elseif ($bracket == "SCHOOLAGE" and ($rr >= 18 and $rr <= 26)){
			return true;
		} elseif ($bracket == "ADOLESCENT" and ($rr >= 12 and $rr <= 20)){
			return true;
		} elseif ($bracket == "ADULT" and ($rr >= 12 and $rr <= 20)){
			return true;
		} elseif ($bracket == "ELDERLY" and ($rr >= 12 and $rr <= 20)){
			return true;
		} else { return false; }
	
	}

	public function is_pulse_normal(){

		$patient = $this->get_patient(); $bp = $this->get_pulse_rate();
		$age = $patient->get_age(); $bracket = $patient->get_age_bracket();

		if ($bracket == "NEWBORN" and ($bp >= 80 and $bp <= 180)){
			return true;
		} elseif ($bracket == "INFANT" and ($bp >= 120 and $bp <= 160)){
			return true;
		} elseif ($bracket == "TODDLER" and ($bp >= 80 and $bp <= 140)){
			return true;
		} elseif ($bracket == "PRESCHOOL" and ($bp >= 80 and $bp <= 110)){
			return true;
		} elseif ($bracket == "SCHOOLAGE" and ($bp >= 75 and $bp <= 105)){
			return true;
		} elseif ($bracket == "ADOLESCENT" and ($bp >= 60 and $bp <= 100)){
			return true;
		} elseif ($bracket == "ADULT" and ($bp >= 60 and $bp <= 100)){
			return true;
		} elseif ($bracket == "ELDERLY" and ($bp >= 60 and $bp <= 100)){
			return true;
		} else { return false; }
	
	}

	public function is_bp_normal(){
		//Assumption: BP is MMM/NNN (systolic/diastolic)
		
		$value = explode("/", $this->get_blood_pressure());
		$systolic = $value[0]; //upper value
		$diastolic = $value[1]; //lower value

		if ($systolic < 120 and $diastolic > 80){
			return true;
		} else { return false; }
		
		//if ($value < 120 and $value > 80){
		//	return true;
		//} else { return false; }
	}

}

class Patient {

	private $id;
	private $name;
	private $birthdate;
	private $list_of_cases;

	public function Patient($patient_id){
		if ($patient_id != null){
			$this->id = $patient_id;

			$sql = "SELECT * FROM patient WHERE idpatient=".$patient_id;
			$query = mysql_query($sql); if (mysql_num_rows($query) != 0){
				$p = mysql_fetch_array($query);

				$this->name = new Person($p['patient_last_name'], $p['patient_first_name'], $p['patient_middle_name']);
				$this->birthdate = new Date($p['patient_bdate']);
			}
		}
	}

	public function get_patient_id(){ return $this->id; }
	public function get_patient_name(){ return $this->name; }
	public function get_birthdate(){ return $this->birthdate; }
	public function get_age(){
		return $this->birthdate->get_difference_from_date(new Date("NOW"));
	}

	public function set_patient_id($id){ $this->id = $id; }
	public function set_name($l, $f, $m){ $this->name = new Person($l, $f, $m); }

	//stub:
	public function set_age($age){ $this->birthdate = $age; }

	public function get_age_bracket(){
		$age = $this->get_age();
		$date = $this->get_birthdate();

		if ($age == 0){
			if ($date->convert_to_days() > 28){
				return "INFANT";
			} else { return "NEWBORN"; }
		} elseif($age >= 1 and $age <= 3){
			return "TODDLER";
		} elseif($age >= 4 and $age <= 6){
			return "PRESCHOOL";
		} elseif($age >= 7 and $age <= 12){
			return "SCHOOLAGE";
		} elseif($age >= 13 and $age <= 17){
			return  "ADOLESCENT";
		} elseif($age >= 18 and $age <= 60){
			return  "ADULT";
		} elseif($age > 60){ return  "ELDERLY"; }
	}

	public function get_current_case(){

	}

	public function register_patient($l, $f, $m, $b){
		//Input: $info is already an object from JSON
		$new_patient_id = $this->generate_new_patient_id();
		$sql = "INSERT INTO patient VALUES($new_patient_id, '$l', '$f', '$m', '$b')";
		if (!mysql_query($sql)){
			return false;
		} else { return true; }
	}

	public function is_patient_already_in_db(){
		$person = $this->get_patient_name();
		$sql = "SELECT idpatient FROM patient WHERE 
					patient_last_name='".$person->get_last_name()."' AND
					patient_first_name='".$person->get_first_name()."' AND
					patient_middle_name='".$person->get_middle_name()."'
		"; $query = mysql_query($sql);

		if (mysql_num_rows($query) == 0){
			return false;
		} else { return true; }
	}

	public function add_patient_case($i, $d, $a, $b, $w, $t){
		$patient_id = $this->get_patient_id();
		$sql = "INSERT INTO cases VALUES($i, '$d', '$a', $b, 'ADMITTED', $patient_id, $w, $t)";

		//echo $sql;

		if (!mysql_query($sql)){
			return false;
		} else { return true; }
	}

	public function get_id_by_name(){
		$person = $this->get_patient_name();
		$sql = "SELECT idpatient FROM patient WHERE 
					patient_last_name='".$person->get_last_name()."' AND
					patient_first_name='".$person->get_first_name()."' AND
					patient_middle_name='".$person->get_middle_name()."'
		"; $query = mysql_query($sql); $result = mysql_fetch_array($query);

		return $result['idpatient'];
	}

	public function get_all_my_cases_html($current_case){
		//Returns a specific set of data... :)
		//Returns an array of ['caseID', 'date_admitted'];

		$sql = "SELECT idcases, date_admitted FROM cases WHERE 
					patient_idpatient=".$this->get_patient_id()." ORDER BY date_admitted DESC";

		$query = mysql_query($sql); $result = Array();
		while($result = mysql_fetch_array($query)){
			//$result[] = Array($result['idcases'], $result['date_admitted']);
			//array_push($result, array("orange", "banana"));
			//print_r($result);

			$data = "<li>";
			if ($current_case == $result['idcases']){
				$data .= "<a href='#' data-case-id='".$result['idcases']."' class='active-case'><i class='icon icon-ok'></i> &nbsp";
			} else { $data .= "<a href='#' data-case-id='".$result['idcases']."'>&nbsp &nbsp &nbsp&nbsp"; }
			
			$data .= "Case # ".$result['idcases']." 
					(".$result['date_admitted'].")</a></li>";

			echo $data;
		}

		//return $result; 
	}

	// Temporary Generation for patient number... :)
	public function generate_new_patient_id(){
		$sql = "SELECT COUNT(*) FROM patient";
		$query = mysql_query($sql); $result = mysql_fetch_array($query);

		return $result['COUNT(*)'] + 1;
	}

}

class Person {

	private $last; private $first; private $middle = NULL;

	//ucfirst(strtolower($l))

	public function Person($lastname, $firstname, $middlename){

		$this->last = $this->format_name($lastname); 
		$this->first = $this->format_name($firstname); 
		$this->middle = $this->format_name($middlename);
	}

	public function get_last_name(){ return $this->last; }
	public function get_first_name(){ return $this->first; }
	public function get_middle_name(){ return $this->middle; }

	public function get_full_name(){
		//Format := LastName, FirstName M.
		if ($this->get_middle_name() != null){ $hasDot = "."; } else { $hasDot = ""; }
		return $this->last.", ".$this->first." ".$this->middle[0].$hasDot;
	}

	public function get_full_name_middle(){
		//Format := LastName, FirstName Middle
		return $this->last.", ".$this->first." ".$this->middle;
	}
	
	public function get_full_name_no_middle(){
		return $this->last.", ".$this->first;
	}
	
	public function get_full_name_first_name(){
		if ($this->get_middle_name() != NULL or $this->get_middle_name() != ""){ $hasDot = "."; } 
		else { $hasDot = ""; }
		
		return $this->first." ".$this->middle[0].$hasDot." ".$this->last;
	}

	public function format_name($name){
		$name_data = explode(" ", $name); $result = "";
		foreach($name_data as $data){
			$result .= ucfirst(strtolower($data)) . " ";
		} return $result;
	}

}

/**
* Here goes the system object definitions.
**/

class Database {

	private $db = false;

	private $db_name = "nurse";
	private $db_host = "localhost";
	private $db_user = "root";
	private $db_pass = "";

	// Cloud Credentials...
	private $db_host_cloud = "http://scalaberch-database.ap01.aws.af.cm";

	public function Database(){
		//Database Connection Opener
		$this->db = mysql_connect($this->db_host, $this->db_user, $this->db_pass);
		mysql_select_db($this->db_name, $this->db);
	}

	public function is_connected(){
		return $this->db;
	}

	public function close(){
		mysql_close($this->db); $this->db = false;
	}

}

class Report{

	//Attribs
	private $pdf;
	private $pcase;
	
	//Some TCPDF Constants
	
	private $COLOR_BLUE  = array(0, 0, 255);
	private $COLOR_RED   = array(255, 0, 0);
	private $COLOR_GREEN = array(0, 255, 0);
	
	private $GRID_LINE_STYLE = array('width'=>0, 'color'=>array(0,0,0));
	
	//Initiator... :)
	public function Report($case_id){
		$this->pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		
		// set default header data
		$this->pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

		// set header and footer fonts
		$this->pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$this->pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$this->pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		//set margins
		$this->pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$this->pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		//set auto page breaks
		$this->pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		
		//Set the case... :)
		$this->pcase = new Cases($case_id);
		
		//Print the pages... :)
		$this->get_vitals_page(); 
		//$this->get_graph_page(); // mao ni ang karaan... :) 
		$this->get_graph_page_new();
	}
	
	public function plot_graph_data(){
		//Assumes that the graph has already been drawn... :)
		$pointList = array($this->get_axis("TEMP"),$this->get_axis("PULSE"),$this->get_axis("RESP"));
		
		//$pointList = array(array(43, 44), array(120, 230), array(20, 0));
		for($i=0;$i<sizeof($pointList);$i++){
			$this->plot_data_list($pointList[$i], $i);
		}
		
		//Draw something pantabon sa limits :)
		$this->pdf->Rect(15, 42, 180, 38, 'F', null, array(255, 255, 255));
		$this->pdf->Rect(15, 270, 180, 28, 'F', null, array(255, 255, 255));
		
	}
	
	public function plot_data_list($dataset, $key){
		$tempList = $this->get_axis("TEMP");
		
		switch($key){
			case 0:
				$upper_limit_graph = 43; $lower_limit_graph = 24; 
				$mult = 10; $color = $this->COLOR_BLUE;
				break;
			case 1:
				$upper_limit_graph = 220; $lower_limit_graph = 30; 
				$mult = 1; $color = $this->COLOR_RED;
				break;
			case 2:
				$upper_limit_graph = 210; $lower_limit_graph = 20; 
				$mult = 1; $color = $this->COLOR_GREEN;
				break;
			
		}
		
		$origin = new Point(40, 80); 
		//$this->pdf->Circle($origin->x(), $origin->y(), 1, 0, 360, 'F', null, $color);
		$currentPoint = new Point($origin->x() - 5, $origin->y()); $previousPoint = null;

		foreach($dataset as $data){
			$new_x = $currentPoint->x()+10; $new_y = (($upper_limit_graph-$data)*$mult)+$origin->y();
			
			$currentPoint = new Point($new_x, $new_y);
			if ($data <= $upper_limit_graph and $data >= $lower_limit_graph){
				$this->pdf->Circle($currentPoint->x(), $currentPoint->y(), 1, 0, 360, 'F', null, $color);
			}
			
			
			if (sizeof($tempList) > 1){
				if ($previousPoint != null){
					$this->pdf->Line($previousPoint->x(), $previousPoint->y(), 
									 $currentPoint->x(), $currentPoint->y(), array('width'=>0.8, 'color'=>$color));
				} $previousPoint = new Point($currentPoint->x(), $currentPoint->y());
			}
	
		}
	}
	
	public function draw_graph_table(){
		$this->pdf->SetLineStyle($this->GRID_LINE_STYLE);
	
		//I'll draw the first part of the graph...
		$this->pdf->SetFont('helvetica', 'N', 11);
		$table_y_coords = 42; $date_time = "YYYY-MM-DD HH:MM:SS";
		
		$this->pdf->SetXY(15, 79);
		$this->pdf->Cell(10, 38, "R", 1, $ln=0, 'C', 0, '', 0, false, 'L', 'B');
		$this->pdf->Cell(10, 38, "P", 1, $ln=0, 'C', 0, '', 0, false, 'L', 'B');
		$this->pdf->Cell(10, 38, "T", 1, $ln=0, 'C', 0, '', 0, false, 'L', 'B');
	
		// Do the second part of the graph... :)
		//$this->pdf->Rotate(-90); 
		$this->pdf->SetXY(15, 80); // Draw the base line... :) 
		$this->pdf->Cell(10, 190, "", 1, $ln=0, 'C', 0, '', 0, false, 'T', 'C');
		$this->pdf->Cell(10, 190, "", 1, $ln=0, 'C', 0, '', 0, false, 'T', 'C');
		$this->pdf->Cell(10, 190, "", 1, $ln=0, 'C', 0, '', 0, false, 'T', 'C');
		
		//Iterate for the table cells :)
		$this->pdf->SetXY(45, 80); $first = true; $limit = 16; $border = 1;
		for ($i=1; $i<20; $i++){
			for ($j=1; $j<$limit; $j++){
				if (!$first){
					switch($j){
						case 1: case 2: case 3:
							$border = 0; break;
						default:
							$border = 1;
					}
				}
			
				$this->pdf->Cell(10, 10, "", $border, $ln=0, 'C', 0, '', 0, false, 'T', 'C');
			} $this->pdf->Ln(10);
			
			if ($first){ $first = false; $limit = 19; }
		}
		
		//Write the scales :)
		$currentHeight = 88; $this->pdf->SetXY(37, $currentHeight);
		for ($t=42; $t>34; $t--){
			$this->pdf->Write(0, $t, '', 0, '', true, 0, false, false, 0);
			$currentHeight += 10; $this->pdf->SetXY(37, $currentHeight);
		}
		
		$currentHeight = 110; $currentTab = 25; $this->pdf->SetXY($currentTab, $currentHeight);
		for ($t=180; $t>40; $t-=20){
			if ($t == 100){ $currentTab += 1; } $currentHeight += 20;
		
			$this->pdf->Write(0, $t, '', 0, '', true, 0, false, false, 0);
			$this->pdf->SetXY($currentTab, $currentHeight);
		}
		
		$currentHeight = 228; $this->pdf->SetXY(17, $currentHeight); 
		for ($t=50; $t>10; $t-=10){
			//if ($t == 100){ $currentTab += 1; } 
			$currentHeight += 10;
		
			$this->pdf->Write(0, $t, '', 0, '', true, 0, false, false, 0);
			$this->pdf->SetXY(17, $currentHeight);
		}
		
		//Write the date stamps :)
		$dateStamps = $this->get_axis("DATE"); $dateStampSize = sizeof($dateStamps);
		$date_x = 35; $date_y = 60; $this->pdf->SetXY($date_x, $date_y); 
		$this->pdf->SetFont('helvetica', 'N', 8.5); $this->pdf->Rotate(90); 
		
		for ($r = 0; $r <16; $r++){
			if ($r != 0){
				$index = $r -1; if ($r < sizeof($dateStamps)+1){
					$timestamp = $dateStamps[$index];
				} else { $timestamp = ""; }
				
				$this->pdf->Cell(38, 10, $timestamp, 1, $ln=0, 'C', 0, '', 0, false, 'T', 'C');	
			} $this->pdf->Ln(10);
		} $this->pdf->Rotate(-90);
		
		
	}
	
	public function get_graph_page_new(){
		$this->pdf->AddPage('P', '', false, false); //$this->print_patient_profile("H");
		$this->pdf->SetFont('helvetica', 'N', 10); $case = $this->pcase;
		
		//Write the Case Information...
		$this->pdf->writeHTMLCell(0, 0, 15, 36, '<b>Case No: '.$case->get_case_number().'</b>', 0, 0, 0, true, 'L', true);
		$patient = $case->get_case_patient();
		$this->pdf->writeHTMLCell(0, 0, 45, 36, '<b>Patient Name:</b> '.$patient->get_patient_name()->get_full_name(), 0, 0, 0, true, 'L', true);
		
		
		$this->plot_graph_data();
		$this->draw_graph_table(); 
	}
	
	public function get_vitals_page(){
		// Prints the first page of the document... :)
		// Gets the chuchu of the patient... :)
		
		$this->pdf->AddPage(); $this->print_patient_profile("L");
		
		$this->pdf->SetFont('helvetica', 'N', 12);
		$this->pdf->writeHTMLCell(0, 0, 15, 57, 'VITAL SIGNS RECORD SHEET', 0, 0, 0, true, 'C', true);
		
		$this->pdf->SetFont('helvetica', 'N', 11);
		if ($this->pcase->get_number_of_vitals_submitted() == 0){
			$html = "<i><br>The patient doesn't have any recorded instance of a vital sign.</i>";
			$this->pdf->writeHTMLCell(0, 20, 15, 65, $html, 1, 0, 0, true, 'C', true);
		} else { $this->get_vitals_table(); }
		
	}
	
	public function get_vitals_table(){
		//X-attribs of the fields... :)
		$date_x = 15; $time_x = 37; $nurse_x = 57; $bp_x = 117; 
		$rr_x = 136; $hr_x = 155; $t_x = 174; //$s_x = 169;
		$current_height = 67; //name says all
		
		//print out the header :)
		$this->pdf->writeHTMLCell(22, 0, $date_x, $current_height, "Date", 1, 0, 0, true, 'C', true);
		$this->pdf->writeHTMLCell(20, 0, $time_x, $current_height, "Time", 1, 0, 0, true, 'C', true);
		$this->pdf->writeHTMLCell(60, 0, $nurse_x, $current_height, "Attending Nurse", 1, 0, 0, true, 'C', true);
		$this->pdf->writeHTMLCell(19, 0, $bp_x, $current_height, "BP", 1, 0, 0, true, 'C', true);
		$this->pdf->writeHTMLCell(19, 0, $rr_x, $current_height, "RR", 1, 0, 0, true, 'C', true);
		$this->pdf->writeHTMLCell(19, 0, $hr_x, $current_height, "HR", 1, 0, 0, true, 'C', true);
		$this->pdf->writeHTMLCell(20, 0, $t_x, $current_height, "Temp", 1, 0, 0, true, 'C', true);
		//$this->pdf->writeHTMLCell(0, 0, $s_x, $current_height, "Status", 1, 0, 0, true, 'C', true);
		
		//then, do the loop :)

		$vitals_list = $this->pcase->load_vitals_list();
		foreach($vitals_list as $vitals){
			$current_height += 5;
			$timestamp = explode(" ", $vitals->get_timestamp());
			$nurse_name = $vitals->get_attending_nurse()->get_user_name()->get_full_name();
			
			$this->pdf->writeHTMLCell(22, 0, $date_x, $current_height, $timestamp[0], 1, 0, 0, true, 'C', true);
			$this->pdf->writeHTMLCell(20, 0, $time_x, $current_height, $timestamp[1], 1, 0, 0, true, 'C', true);
			$this->pdf->writeHTMLCell(60, 0, $nurse_x, $current_height, $nurse_name, 1, 0, 0, true, 'C', true);
			$this->pdf->writeHTMLCell(19, 0, $bp_x, $current_height, $vitals->get_blood_pressure(), 1, 0, 0, true, 'C', true);
			$this->pdf->writeHTMLCell(19, 0, $rr_x, $current_height, $vitals->get_respiratory_rate()." cpm", 1, 0, 0, true, 'C', true);
			$this->pdf->writeHTMLCell(19, 0, $hr_x, $current_height, $vitals->get_pulse_rate()." bpm", 1, 0, 0, true, 'C', true);
			$this->pdf->writeHTMLCell(20, 0, $t_x, $current_height, $vitals->get_temperature()." C", 1, 0, 0, true, 'C', true);
			
			//if ($vitals->is_vitals_normal()){ 
			//	$status = "NORMAL"; } 
			//else { $status = "ABNORMAL"; }
			//$this->pdf->writeHTMLCell(0, 0, $s_x, $current_height, $status, 1, 0, 0, true, 'C', true);
		} $current_height += 6;
		
		$html = "Total Vitals Recorded: ".$this->pcase->get_number_of_vitals_submitted();
		$this->pdf->writeHTMLCell(0, 0, 15, $current_height+10, $html, 0, 0, 0, true, 'L', true);
		
		
	}
	
	public function get_graph_page(){
		// OLD VERSION NI SIYA :P
		// Prints the first page of the document... :)
		// Gets the chuchu of the patient... :)
		
		$this->pdf->AddPage('L', '', false, false); $this->print_patient_profile("V");
		$this->pdf->SetFont('helvetica', 'N', 12);
		$this->pdf->writeHTMLCell(0, 0, 15, 56, 'VITAL SIGNS GRAPH SHEET', 0, 0, 0, true, 'C', true);
		
		$data = ""; $width = 500; $height = 200;
		
		if ($this->pcase->get_number_of_vitals_submitted() == 0){
			$html = "<i><br>The patient doesn't have any recorded instance of a vital sign.</i>";
			$this->pdf->writeHTMLCell(0, 20, 15, 65, $html, 1, 0, 0, true, 'C', true);
		} else {
			/* Create your dataset object */ 
			$myData = new pData(); 
	 
			/* Add data in your dataset */ 
			$myData->addPoints($this->get_axis("DATE"),"Labels"); 
			$myData->addPoints($this->get_axis("TEMP"),"Temp"); 
			$myData->addPoints($this->get_axis("PULSE"),"Pulse");
			$myData->addPoints($this->get_axis("RESP"),"Resp");
			$myData->setAbscissa("Labels"); $myData->setAbsicssaPosition(AXIS_POSITION_TOP); 
			
			/* Set the axis and set the max/min values */
			$myData->setSerieOnAxis("Temp", 0); $myData->setAxisUnit(0,"°C");
			$myData->setSerieOnAxis("Pulse", 1); $myData->setAxisPosition(1,AXIS_POSITION_LEFT); $myData->setAxisUnit(1,"b/m");
			$myData->setSerieOnAxis("Resp", 2); $myData->setAxisPosition(2,AXIS_POSITION_LEFT); $myData->setAxisUnit(2,"c/m");
			$bounds = array(0=>array("Min"=>26,"Max"=>43), 1=>array("Min"=>0,"Max"=>280), 2=>array("Min"=>10,"Max"=>180));
			

			/* Create a pChart object and associate your dataset */ 
			$myPicture = new pImage(760,330,$myData);
			$myPicture->setFontProperties(array("FontName"=>"pgraph/fonts/calibri.ttf", "FontSize"=>8));
			$myPicture->setGraphArea(140,40,750,300); $myPicture->drawLegend(520,319, array("Mode"=>LEGEND_HORIZONTAL));
			
			$format = array("DrawSubTicks"=>false, "LabelRotation"=>0, "Mode"=>SCALE_MODE_MANUAL,
							"GridR"=>0, "GridG"=>0, "GridB"=>0, "GridAlpha"=>20,
							"ManualScale"=>$bounds, "DrawXLines" => true, "DrawYLines"=>array(0));
								
			$myPicture->drawScale($format); $myPicture->drawLineChart(); 
			$myPicture->drawPlotChart(array("PlotBorder"=>TRUE,"PlotSize"=>1,"BorderSize"=>1,"Surrounding"=>-60,"BorderAlpha"=>80, "DisplayValues"=>true, "DisplayColor"=>DISPLAY_AUTO));
			
			$myPicture->Render("basic.png");
			$this->pdf->Image('basic.png', 15, 63, 0, 0, '', '', '', true, 300, '', false, false, 0, true, false, false);
			
		}
		
		
	}
	
	public function get_axis($type){
		$case = $this->pcase; $result = Array();
		$vitals_list = $case->load_vitals_list();
		foreach($vitals_list as $vitals){
			if ($type == "TEMP"){
				$result[] = $vitals->get_temperature();
			} elseif ($type == "DATE"){
				//Format it daan... LOL...
				$format = explode(' ', $vitals->get_timestamp());
				$date = new Date($format[0]); 
				$result[] = $date->get_short_date()."\n".$format[1];
			} elseif ($type == "PULSE"){
				$result[] = $vitals->get_pulse_rate();
			} elseif ($type == "RESP"){
				$result[] = $vitals->get_respiratory_rate();
			}
			
		} return $result;
	}
	
	public function print_patient_profile($type){
		$case = $this->pcase; $h_factor = 0;
		if ($type == "H"){ $h_factor = 80; }
	
		//Case Number and Date
		$this->pdf->SetFont('helvetica', 'N', 10);
		$this->pdf->writeHTMLCell(0, 0, 15, 35, '<hr>', 0, 0, 0, true, 'L', true);
		$this->pdf->writeHTMLCell(0, 0, 15, 36, '<b>Case No: '.$case->get_case_number().'</b>', 0, 0, 0, true, 'L', true);
		$this->pdf->writeHTMLCell(0, 0, 134+$h_factor, 36, '<b>Date Admitted:</b> '.$case->get_case_timestamp(), 0, 0, 0, true, 'L', true);
		
		//Case Number and Date
		$patient = $case->get_case_patient();
		$this->pdf->writeHTMLCell(95, 20, 15, 40, '<b>Patient Name:</b> '.$patient->get_patient_name()->get_full_name(), 0, 0, 0, true, 'L', true);
		
		if ($patient->get_age() == 0){
			if ($patient->get_birthdate()->convert_to_days() > 28){
				$age = $patient->get_age()." days old";
			} else { $age = $patient->get_age()." days old"; }
		} else { $age = $patient->get_age()." y/o"; }
		
		$this->pdf->writeHTMLCell(0, 0, 95+$h_factor, 40, '<b>Age:</b>'.$age, 0, 0, 0, true, 'L', true);
		$this->pdf->writeHTMLCell(0, 0, 134+$h_factor, 40, '<b>Birth Date:</b> '.$patient->get_birthdate()->get_date(), 0, 0, 0, true, 'L', true);
		
		//Case Number and Date
		$this->pdf->writeHTMLCell(0, 0, 15, 44, '<b>Diagnosis:</b> '.$case->get_diagnosis(), 0, 0, 0, true, 'L', true);
		$this->pdf->writeHTMLCell(0, 0, 134+$h_factor, 44, '<b>Bed Number:</b> '.$case->get_bed_number(), 0, 0, 0, true, 'L', true);
		
		$this->pdf->writeHTMLCell(0, 0, 15, 48, '<b>Status:</b> '.$case->get_status(), 0, 0, 0, true, 'L', true);
		$ward = new Ward($case->get_ward_assigned());
		$this->pdf->writeHTMLCell(0, 0, 134+$h_factor, 48, '<b>Ward Assigned:</b> '.$ward->get_ward_name(), 0, 0, 0, true, 'L', true);
		$this->pdf->writeHTMLCell(0, 0, 15, 54, '<hr>', 0, 0, 0, true, 'L', true);
	}
	
	public function print_pdf(){
		$this->pdf->Output('report.pdf', 'I');
	}

} 

// Some subclass :)
class Point {
	private $x_coord; private $y_coord;
	
	public function Point($x, $y){
		$this->x_coord = $x;
		$this->y_coord = $y;
	}
	
	public function clonePoint(){
		return new Point($this->x(), $this->y());
	}
	
	public function x(){ return $this->x_coord; }
	public function y(){ return $this->y_coord; }
}

class Hospital {
	
	private $hospital_name; private $hospital_location;

	public function Hospital(){
		$sql = "SELECT * FROM hospital"; $query = mysql_query($sql);
		$result = mysql_fetch_array($query);
		
		$this->hospital_name     = $result['hospital_name'];
		$this->hospital_location = $result['hospital_location'];
	}
	
	public function get_hospital_name(){
		return $this->hospital_name;
	}
	
	public function get_hospital_location(){
		return $this->hospital_location;
	}

}

class Session {

	private $session_id;
	private $current_user;
	private $ip_address;
	private $time_in;
	private $time_out;

	public function Session($session_id){
		if ($session_id == "NEW" or $session_id == null){
			$this->current_user = $this->ip_address = $this->time_in = $this->time_out = null;
			$this->session_id = "NEW";
		} else {
			$sql = "SELECT * FROM session WHERE idsession='$session_id'";
			$query = mysql_query($sql); if (mysql_num_rows($query) == 0){
				$this->session_id = $this->current_user = $this->ip_address = $this->time_in = $this->time_out = null;
			} else {
				$result = mysql_fetch_array($query);
				$this->session_id = $result['idsession'];
				$this->current_user = new User($result['user_iduser']);
				$this->ip_address = null; // FOr now
				$this->time_in = $result['timestamp_login'];
				$this->time_out = $result['timestamp_logout'];
			}
		}
	}

	public function get_session_id(){ return $this->session_id; }
	public function get_current_user(){ return $this->current_user; }
	public function get_time_login(){ return $this->time_in; }
	public function get_time_logout(){ return $this->time_out; }

	public function is_session_exists(){
		//Check if a session exists
		if ($this->get_session_id() == null){
			return false;
		} else { return true; }
	}

	public function is_session_expired(){
		// This means that user has already logged out... :)
		// Also there is a session existing... 
		if ($this->is_session_exists() and $this->get_time_logout() != null){
			return true;
		} else { return false; }
	}

	public function register_new_session_entry($user_id, $new_session_id){
		//Assumption: $user is the user_id in the database...
		$sql = "INSERT INTO session VALUES('$new_session_id', $user_id, CURRENT_TIMESTAMP, null)";
		return mysql_query($sql);
	}

	public function expire_session_now(){
		$session_id = $this->get_session_id();
		$sql = "UPDATE session SET timestamp_logout=CURRENT_TIMESTAMP WHERE idsession='$session_id'";
		return mysql_query($sql);
	}

	public function generate_new_session_id($string){
		return md5($string."-".date("Y-m-d-h:i:s"));
	}

	public function get_session_size(){
		$sql = "SELECT COUNT(*) FROM session"; 
		$query = mysql_query($sql); $result = mysql_fetch_array($query);
		return $result['COUNT(*)'];
	}

	public function get_active_sessions(){
		// Algorithm: Getting all the sessions without logout timestamp... :)

		$sql = "SELECT idsession FROM session WHERE timestamp_logout IS NULL";
		$sessions = Array(); $query = mysql_query($sql);

		while($result = mysql_fetch_array($query)){
			$sessions[] = $result['idsession'];
		}

		return $sessions;
	}
	
	public function is_this_user_has_already_active_session($userid){
		$sql = "SELECT idsession FROM session WHERE timestamp_logout IS NULL AND user_iduser=".$userid;
		$query = mysql_query($sql); if (mysql_num_rows($query) == 0){
			return false;
		} else { return true; }
	}
	
	public function get_previous_sessions_that_is_not_logout($userid){
		$list = array(); 
		$sql = "SELECT idsession FROM session WHERE timestamp_logout IS NULL AND user_iduser=".$userid;
		$query = mysql_query($sql); if (mysql_num_rows($query) != 0){
			while($result = mysql_fetch_array($query)){
				$list[] = new Session($result['idsession']);
			}
		} return $list;
	}
}

class Machine {

	private $ward_id; //The ward assigned to the machine
	private $ip_address;

	public function Machine($ip_address){
		$this->ip_address = $ip_address;
		//Check if IP is in list... :)
		$sql = "SELECT idward FROM ward WHERE ward_ip_address='$ip_address'";
		$query = mysql_query($sql); if (mysql_num_rows($query) != 0){
			$result = mysql_fetch_array($query);
			$this->ward_id = $result['idward'];
		} else { $this->ward_id = false; }
	}

	public function get_ward_id(){ return $this->ward_id; }
	public function get_ip_address(){ return $this->ip_address; }

}

class NotificationMachine {

	private $machine;
	private $ward;

	public function NotificationMachine($machine){
		//Get the concerning ward
		$this->machine = $machine; $this->ward = new Ward($this->machine->get_ward_id());
	}
	
	public function get_unmonitored_count(){
		$unmonitored_cases = $this->ward->get_unmonitored_cases();
		return sizeof($unmonitored_cases);
	}
	
	public function get_unmonitored_cases(){
		$unmonitored_cases = $this->ward->get_unmonitored_cases();
		foreach($unmonitored_cases as $case){
			echo $case->get_case_number();
		}
	}


}

class DashboardFeed {
	// Gets the latest feed inside the database.
	// This is for web service only.
	
	private $feed_count_limit = 15;
	private $feed_list;
	private $ward_id;

	public function DashboardFeed($ward_id){
		$this->feed_list = array();
		
		$this->ward_id = $ward_id;
		$sql = "SELECT * FROM feed";
		if ($ward_id != null){
			$sql .= " WHERE ward_id=".$ward_id;
		} $sql .= " ORDER BY timestamp DESC LIMIT 0, ".$this->feed_count_limit;
		
		$query = mysql_query($sql); if (mysql_num_rows($query) != 0){
			while($r = mysql_fetch_array($query)){
				$feed = new Feed($r['idfeed'], $r['feed_type'], $r['timestamp'], $r['link_id'] );
				
				$feed_list = $this->feed_list;
				$feed_list[] = $feed; $this->feed_list = $feed_list;
			}
		}
	}
	
	public function get_total_feed_from_db(){
		$ward_id = $this->ward_id; $sql = "SELECT COUNT(*) FROM feed";
		if ($ward_id != null){
			$sql .= " WHERE ward_id=".$ward_id;
		} $query = mysql_query($sql);
		
		if (mysql_num_rows($query) == 0){
			return 0;
		} else { $result = mysql_fetch_array($query); return $result['COUNT(*)']; }	
	}
	
	

}

class Feed {

	private $feed_id;
	private $feed_type;
	private $feed_timestamp;
	private $feed_text;
	private $feed_link_id;

	// Some constants for the Feed_type
	private $FEED_ADMITTED   = 2;
	private $FEED_RECORDED   = 3;
	private $FEED_DISCHARGED = 4;

	public function Feed($i, $t, $m, $l){
		//Assign values to the attributes...
		$this->feed_id        = $i;
		$this->feed_type      = $t;
		$this->feed_timestamp = $m;
		$this->feed_link_id	  = $l;

		$this->feed_text = $this->convert_feed_to_text();
	}

	public function get_feed_id(){ return $this->feed_id; }
	public function get_feed_type(){ return $this->feed_type; }
	public function get_feed_timestamp(){ return $this->feed_timestamp; }
	public function get_feed_link(){ return $this->feed_link_id; }
	
	public function register_this_to_database($ward_id){
		$sql = "INSERT INTO feed VALUES(null, ".$this->feed_type.", NOW(), 
					".$this->feed_link_id.", $ward_id)";
		return mysql_query($sql);
		
	}

	public function get_feed_query(){
		switch($this->feed_type){
			case $this->FEED_ADMITTED:
			case $this->FEED_DISCHARGED:
				return "SELECT * FROM cases WHERE idcases=".$this->feed_link_id; break;
			case $this->FEED_RECORDED:
				return "SELECT * FROM vitals LEFT JOIN cases 
						ON cases.idcases=vitals.case_idcases 
						WHERE idvitals=".$this->feed_link_id; break;
		}
	} 

	public function convert_feed_to_text(){
		$subject = null; $predicate = null;

		$query  = mysql_query($this->get_feed_query());
		$result = mysql_fetch_array($query);

		if ($this->feed_type == $this->FEED_RECORDED){
			$attending_nurse = new User($result['user_iduser']);
			$patient = new Patient($result['patient_idpatient']); 

			$subject  = $attending_nurse->get_user_name()->get_full_name_first_name() . " has recorded patient ";
			$subject .= $patient->get_patient_name()->get_full_name_first_name()." 's vitals ";
		} else {
			$action = null; $room_number = $result['bed_number'];
			$patient = new Patient($result['patient_idpatient']); 

			if ($this->feed_type == $this->FEED_ADMITTED){ $action = "admitted at";
			} else { $action = "discharged from"; }

			$subject = $patient->get_patient_name()->get_full_name_first_name()." has been ".$action." room ".$room_number;
		}

		$predicate = ""; //"on ".$this->feed_timestamp;

		return $subject." ".$predicate;
	}
	
	public function convert_feed_to_html(){
		$subject = null; $predicate = null;

		$query  = mysql_query($this->get_feed_query());
		$result = mysql_fetch_array($query);

		if ($this->feed_type == $this->FEED_RECORDED){
			$attending_nurse = new User($result['user_iduser']);
			$patient = new Patient($result['patient_idpatient']); 

			$subject  = "<b>".$attending_nurse->get_user_name()->get_full_name_first_name() . "</b> has recorded patient ";
			$subject .= "<b>".$patient->get_patient_name()->get_full_name_first_name()." 's</b> vitals ";
		} else {
			$action = null; $room_number = $result['bed_number'];
			$patient = new Patient($result['patient_idpatient']); 

			if ($this->feed_type == $this->FEED_ADMITTED){ $action = "admitted at";
			} else { $action = "discharged from"; }

			$subject = "<b>". $patient->get_patient_name()->get_full_name_first_name()."</b> has been ".$action." room ".$room_number;
		}

		$predicate = ""; //"on ".$this->feed_timestamp;

		return $subject." ".$predicate;
	}

	public function print_feed(){ return $this->feed_text; }
	
}

class Date {

	private $month; private $day; private $year;

	public function Date($iso_date){
		if ($iso_date == "NOW"){ $iso_date = date("Y-m-d"); }
		$date_info = explode("-", $iso_date);

		$this->month = $date_info[1];
 		$this->day   = $date_info[2];
 		$this->year  = $date_info[0];
	}

	public function get_month(){ return $this->month; }
	public function get_day(){ return $this->day; }
	public function get_year(){ return $this->year; }

	public function get_date(){ 
		return $this->get_year()."-".$this->get_month()."-".$this->get_day();
	}
	
	public function get_short_date(){
		$year = $this->get_year(); $y = $year[2].$year[3];
		return $this->get_month()."/".$this->get_day()."/".$y;
	}

	public function check_how_many_days($month_num){
		switch($month_num){
			case 1: case 3: case 5: case 7: case 8: case 10: case 12:
				return 31; break;
			case 4: case 6: case 9: case 11:
				return 30; break;
			case 2: return 28; break;
		}
	}

	public function convert_to_days(){
		// Sample: January 4, 2012 would extract 4 days...
		// 		   March 3, 2012 would extract 31+28+3 days...

		$days = 0;
		for ($i=1; $i<$this->get_month(); $i++){
			$days += $this->check_how_many_days($i);
		} $days += $this->get_day();

		return $days;
	}

	public function get_difference_from_date($date){
		//$date is a Date object defined above... :)
		//Assumption: $this < $date

		$result = $date->get_year() - $this->get_year();
		if ($date->get_month() < $this->get_month()){
			$result -= 1;
		} elseif ($date->get_day() < $this->get_day()) {
			$result -= 1;
		}

		return $result;

	}

}

class Time {

	private $hour; private $minute; private $second;

	public function Time($time_format){
		// Format is: HH:MM:SS
		// Base format is 24-hour format...
		if ($time_format == "NOW"){ 
			$time_format = date("H:i:s");  
		}
		
		$time = explode(":", $time_format);
		$this->hour 	= $time[0];
		$this->minute 	= $time[1];
		$this->second 	= $time[2];
	}
	
	public function print_time(){
		return $this->get_hour().":".$this->get_minute().":".$this->get_second();
	}

	public function get_hour(){ return $this->hour; }
	public function get_minute(){ return $this->minute; }
	public function get_second(){ return $this->second; }
	
	public function set_hour($hour){ $this->hour = $hour; }
	public function set_minute($minute){ $this->minute = $minute; }
	public function set_second($second){ $this->second = $second; }

	public function add_time($minutes){
		$to_add_minute = $minutes % 60; $to_add_hours  = ($minutes - $to_add_minute)/60;

		$this->minute += $to_add_minute; $this->hour += $to_add_hours;
		if ($this->hour > 23){ $this->hour -= 24; }
	}

	public function is_past_this_time($time){ //is this larger than this?
		$result = false; 
		if ($this->get_hour() > $time->get_hour()){ $result = true;
		} elseif($this->get_minute() > $time->get_minute()){ 
			$result = true;
		} elseif($this->get_second() > $time->get_second()){ 
			$result = true;
		} return $result;
	}
	
	public function is_this_time_larger_than_this($time){
		// Just compares the minutes and the hours...
		// At 24-hour time allotment :)
		
		// Compare the hours between the two times :)		
		if ($this->get_hour() > $time->get_hour()){
			return true;
		} elseif ($this->get_hour() == $time->get_hour()) {	
			if ($this->get_minute() > $time->get_minute()){
				return true;
			} else { return false; }
		} else { return false; }
	}

}



?>