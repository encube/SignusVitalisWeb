<?php

/*
* Session Handling Script - session.php
* Author: kiethmark bandiola
* Description: Handles the session parameters for the whole system.
* 	Accepts both mobile and web (station) units.
*   Authorizes login/logout and session checkers. :)
* Input: JSON data. Output: JSON data.
*/

include 'backend.php';
$db = new Database();

if (isset($_GET['w'])){
	// This actions are for the station/ward only.
	// This includes check of IP address of the unit.

	if (isset($_GET['active'])){
		$session = new Session(null);
		$active_sessions = $session->get_active_sessions();

		//print_r($active_sessions);
		if (isset($_GET['debug'])){
			echo "<table>";
			echo "<tr><th>Session ID</td><th>User ID</th><th>Name</th><th>Ward ID</th><th>Role</th></tr>";
			foreach($active_sessions as $sessions){
				echo "<tr>";
				echo "<td>".$sessions."</td>";
				$session_now = new Session($sessions);
				echo "<td>".$session_now->get_current_user()->get_user_id()."</td>";
				echo "<td>".$session_now->get_current_user()->get_user_name()->get_full_name()."</td>";
				echo "<td>".$session_now->get_current_user()->get_assigned_ward()."</td>";
				echo "<td>".$session_now->get_current_user()->get_position()."</td>";


				echo "</tr>";
			}

			echo "</table>";
		} else {
			$machine = new Machine($_SERVER['REMOTE_ADDR']);

			echo "<table class='table'>";
			echo "<tr><th></th><th>Name</th><th>Position</th><th>Last Sign In</th></tr>";
			foreach($active_sessions as $sessions){
				$session_now = new Session($sessions);
				$this_ward = $session_now->get_current_user()->get_assigned_ward();
				if ($this_ward == $machine->get_ward_id()){
					echo "<tr>";
					
					if ($session_now->get_current_user()->is_online()){
						echo "<th><span class='badge badge-success'>&nbsp</span></th>";
					}
					
					echo "<td>".$session_now->get_current_user()->get_user_name()->get_full_name()."</td>";
					echo "<td>".$session_now->get_current_user()->get_position()."</td>"; 
					echo "<td>".$session_now->get_time_login()."</td>";
					echo "</tr>";
				}
			}
		}
		

	} else {
		$my_ip_address = $_SERVER['REMOTE_ADDR'];

		$machine = new Machine($my_ip_address);
		$wardID = $machine->get_ward_id();

		// Generate JSON String... :)
		echo '{"wardID":"'.$wardID.'"}';
	}

} else if (isset($_GET['m'])){
	if (isset($_GET['check'])){
		// This checks the mobile device if it has an existing sesion or not...
		// 

		
	} elseif (isset($_GET['login'])){
		// Assumes: New login user...
		// Date/Time Format is: YYYY-MM-DD HH:MM:SS
		// Input: Username, Password, Date and Time...

		$user = $_POST['u']; //"kiethmark.bandiola";
		$pass = $_POST['p']; //"kmbandiola";

		$userSession = new User(null); $json = '{';
		$username = md5($user); $password = md5($pass);

		$userID = $userSession->authenticate($username, $password);
		if (!$userID){
			$json .= '"isAuth":false';
		} else { 
			$json .= '"isAuth":true';
			//Check date/time is synch in server... :)
			//Assume muna na correct lahat... :)
			//$clientTime = new DateTime($_POST['t']);
			$json .= ', "timeSynch":true';

			// Generate SESSION_ID and store it now... :)
			$session = new Session("NEW");
			
			// Check if this user has already existing sessions?
			// If already has, then delete that session... :)
			if ($session->is_this_user_has_already_active_session($userID)){
				$prev_sessions = $session->get_previous_sessions_that_is_not_logout($userID);
				foreach($prev_sessions as $prev){
					$prev->expire_session_now();
				}
			}
			
			// Then generate new session ID
			$session_id = $session->generate_new_session_id($user);

			// Store it to DB;
			$session->register_new_session_entry($userID, $session_id);

			$json .= ', "sID":"'.$session_id.'" ';

		} $json .= '}'; echo $json;

	} elseif (isset($_GET['logout'])){
		$session_id = $_POST['s'];
		$session = new Session($session_id);

		if (!$session->expire_session_now()){
			echo '{"logout":"false"}';
		} else { echo '{"logout":"true"}'; }

	} elseif (isset($_GET['info'])){
		//URI: session.php?m&info&s=SOME_SESSION_ID_HERE
		//Assumption: SESSION is not yet LOGGED OUT...
		//Databag: Shows the user information assigned on the session...

		$session = new Session($_GET['s']); $user = $session->get_current_user();
		$ward = new Ward($user->get_assigned_ward());
		$shift_tag = $user->get_shift();

		$data = '{
			"name":"'.$user->get_user_name()->get_full_name().'",
			"position":"'.$user->get_position().'",';

		if ($user->get_position() == "PHYSICIAN"){
			$data .= '"ward":"null",  "shift":"null", "on-duty":"true" '; 
		} else { 
			if ($user->is_on_duty()){ $duty = "true"; } else { $duty = "false"; }
			
			$data .= '"ward":"'.$ward->get_ward_name().'", 
			          "shift":"'.$user->elaborate_shift_information($shift_tag).'",
			          "on-duty":"'.$duty.'"'; 
		}

		
		$data .= ' }';

		//Output:
		// shift, on-duty?, position, name, ward
		echo $data;
	}
}

$db->close();

?>