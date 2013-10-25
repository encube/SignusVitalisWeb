<?php

include 'backend.php';
$db = new Database();

$text = $_GET['q']; 
$type = null;

//Get WARD Information...

if (isset($_GET['m'])){
	//Get SESSION_ID first...
	$session_id = $_POST['s'];
	$session = new Session($session_id); $ward = "";

	$user = $session->get_current_user();
	$wardID = $user->get_assigned_ward();

	if ($user->get_position() != "PHYSICIAN"){
		$ward = "AND cases.ward_idward=".$wardID;
	}

	$type = "WHERE cases.status='ADMITTED' $ward AND"; 
} else {
	//Get Machine IP Address... :)
	$machine = new Machine($_SERVER['REMOTE_ADDR']);
	$wardID = $machine->get_ward_id();

	if ($_SERVER['REMOTE_ADDR'] != "::1"){
		$type = "WHERE cases.ward_idward=".$wardID;
	} else { $type = "WHERE cases.ward_idward=".$wardID; }

	//if ($_GET['t'] != 'ALL'){
	//	$type .= " AND cases.status='".$_GET['t']."' AND";
	//} else { $type .= " AND"; }
	$type .= " AND";
}

//Assumed Variables... :)

$sql = "SELECT cases.idcases, patient.patient_last_name, patient.patient_first_name, 
			cases.bed_number, cases.status, cases.diagnosis, cases.timer_hours, cases.date_admitted, cases.ward_idward
			FROM cases LEFT JOIN patient ON patient.idpatient=cases.patient_idpatient 
			$type patient.patient_last_name LIKE '%$text%' ORDER BY patient.patient_last_name ASC";

//echo "<br><br>".$sql;
$query = mysql_query($sql);

if (isset($_GET['m'])):

	$json = '['; $first = true;

	while($result = mysql_fetch_array($query)){
		if ($first){ $first = false; } else { $json .= ", "; }

		$name = $result['patient_last_name'].", ".$result['patient_first_name'];
		$case_id = $result['idcases']; $bed_number = $result['bed_number'];

		$kaso = new Cases($case_id);
		if ($kaso->is_patient_case_new()){
			// Diritso ni siya ug add
			$hasMonitor = "true";
		} else {
			if ($kaso->is_inside_wait_time()){
				// if true... dili i.add...
				$hasMonitor = "true"; //false
			} else {
				// If false and value, i.add dayun...
				$hasMonitor = "false";
			}
		}

		$recent_vital = $kaso->get_recent_vital();
		if (!$recent_vital){
			$abnormal = "false";
		} else {
			if ($recent_vital->is_vitals_normal()){
				$abnormal = "false";
			} else { $abnormal = "true"; }
		}
		

		//is_vitals_abnormal();
		$json .= '{"case_id":"'.$case_id.'", 
				   "patient_name":"'.$name.'", 
				   "bed_number":"'.$bed_number.'",
				   "diagnosis":"'.$result['diagnosis'].'",
				   "isAbnormal":"'.$abnormal.'",
				   "hasMonitored":"'.$hasMonitor.'",
				   "admitDate":"'.$result['date_admitted'].'",
				   "ward_id":"'.$result['ward_idward'].'"
				}';
	}

	$json .= "]";
	echo $json;


elseif (isset($_GET['w'])):
?>

<?php if (mysql_num_rows($query) == 0): ?>
<div class='alert' style='width:90%'>
	<b>Patient Not Found!</b> We could not find the searched patient in our current ward/station.
	Please check on the other ward/stations or contact your admissions office for more information.
</div>
<?php else: ?>
<script>
$(function(){
	$('#patient-name-table tr').click(function(){
		var id = $(this).attr('id'); //alert(id);

		//Animate Out the Patient List
		//$('#dashboard-container').hide('drop', {direction:"right"}, 500, function(){
		//	var patientProfileReq = $.ajax({ 
		//		url:_address.full_address('profile.php?w') , data:{ i:id }});

		//	patientProfileReq.done(function(data){
		//		$('#dashboard-container').html(data);
		//		$('#dashboard-container').show('drop', {direction:"right"}, 500);
		//	});
		//});

		$('.titlea').hide('fade', 500, function(){
			$('.titlea').html("Patient Case Profile").show('fade', 500);
		});

		var caseID = $(this).attr('id');
		$('#patients-table-cont-container').hide('slide', {"direction":"left"}, 500, function(){
			var profileRequest = $.ajax({ url:_appHandler.changeLocation('profile.php?w'),
				data:{ i:caseID }
			});

			profileRequest.done(function(data){
				$('#profile-container').html(data).show('slide', {"direction":"left"}, 500);
			});

		});


		//Get Information from the server... :)

		//Load the information into the DOM

		//Show the DOM


	});

	$('#patient-name-table').jScrollPane({ mouseWheelSpeed:30 });

});
</script>
<style>
#patient-name-table {
	overflow-y:auto; height:240px; margin-top:-20px;
}

.jspDrag { background: #45aeea;}
.jspTrack { background:#ddd;}
</style>
<div id='patient-name-table' >
	<table class='table table-striped table-hover'>
	<?php
		
		while($result = mysql_fetch_array($query)){
			$case_id = $result['idcases']; $kaso = new Cases($case_id);
			if ($result['status'] == "DISCHARGED"){
				$hasMonitor = "<span class='badge'>Discharged</span>";
			} else {
			
				if ($kaso->is_patient_case_new()){
					$hasMonitor = "<span class='badge badge-success' >Admitted</span>";
				} else {
					if (!$kaso->has_monitor_timeframe()){
						$hasMonitor = "<span class='badge badge-success' >Admitted</span>";
					} else {
						if ($kaso->is_inside_wait_time()){
							$hasMonitor = "<span class='badge badge-success' title='Patient is inside wait time'>Admitted</span>";
						} else {
							$hasMonitor = "<span class='badge badge-warning' title='Patient needs to be recorded on his/her vitals.'>Admitted</span>";
						}
					}
				}

			}

			echo "<tr id='".$result['idcases']."'>";
			echo "<td>".$result['patient_last_name'].", ".$result['patient_first_name']."</td>";
			echo "<td style='width:14%'>".$result['bed_number']."</td>";
			echo "<td>".$result['diagnosis']."</td>";
			
			echo "<td>".$hasMonitor."</td>";
			echo "</tr>";
		}
	?>
	</table>
</div>
<style>
.table-hover tbody tr:hover>td{ background-color:#45aeea;color:white;}
</style>
<?php endif; //End for the inner IF... :) ?>

<?php endif; ?>