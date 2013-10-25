
<?php

	// Usage:
	// URL: http://<BASE_URL>/server/password.php?update
	// Input: POST['i'] -> JSON -> {sessionid:<session_id>, oldpassword:<old_password>, newpassword:<new_password>}
	// Output: JSON -> {isOldCorrect:bool, success:bool}

	include "backend.php";
	$db = new Database();
	
	//$_POST['i'] = '{
	//					"sessionid":"e5e9c4143964811b02efbce8a7b1c5b0",
	//					"oldpassword":"aaa", "newpassword":"encube"
	//			   }'; 
	
	$data = json_decode($_POST['i']);
	$session = new Session($data->sessionid);
	$user = $session->get_current_user();
	
	if (isset($_GET['update'])){
		//Check first the password if correct...
		if ($user->is_this_my_password($data->oldpassword)){
			//Then update password...
			$query = $user->update_password($data->newpassword);
			if (!$query){
				echo '{"isOldCorrect":"true", "success":"false"}';
			} else {
				echo '{"isOldCorrect":"true", "success":"true"}';
			}
		} else {
			echo '{"isOldCorrect":"false", "success":"false"}';
		}
		
	}


?>