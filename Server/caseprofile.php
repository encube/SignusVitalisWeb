<?php

include "backend.php";
$db = new Database();

$case = new Cases($_GET['c']);
?>

<script>
var _isEditingVitals = false;

	$(function(){
		$('#profile-content-vitals, #profile-content-graph').hide(0);
		$('#graph-view').click(function(){
			$('#graph-container-main').html("<div align='center'><br><br>Loading Visualization...</div>");

			var graphReq = $.ajax({ url:_appHandler.changeLocation('graph.php'), data:{ c:<?php echo $_GET['c']; ?> }});
			graphReq.done(function(data){
				$('#graph-container-main').html(data);
			}); graphReq.fail(function(){ alert(); })
		});
		
		$('#discharge-patient-option').click(function(){
			var c = confirm("Are you sure do you want to discharge the patient? Press OK to continue.");
			if (c){
				var dischargeRequest = $.ajax({ url:_appHandler.changeLocation('case.php?discharge'),
					data:{ c:<?php echo $_GET['c']; ?> }
				});
				dischargeRequest.done(function(data){
					load_profile_content();
				});
			}
		});
		
		$('#vitals-info-update-btn').click(function(){
			_isEditingVitals = true;
			
			$(this).hide('fade', 500, function(){
				//$('#vitals-info-update-form').show('fade', 500);
				$('#vitals-info-update-form').modal('show');
				$('#vitals-info-text').show('fade', 200);
				
			});
		}).mouseout(function(){
			if (!_isEditingVitals){
				$(this).hide('fade', 200, function(){
					$('#vitals-info-text').show('fade', 200);
				});
			}
		}).hide(0);
		
		$('#vitals-info-text').mouseover(function(){
			if (!_isEditingVitals){
				$(this).hide('fade', 200, function(){
					$('#vitals-info-update-btn').show('fade', 200);
				});
			}
		});
		
		$('#vitals-info-update-form-cancel').click(function(){
			_isEditingVitals = false;
			$('#vitals-info-update-form').modal('hide');
			
			// Clean and destroy form
			$('#record-time-others').hide(0).val(""); $('#vitals-record-time').val("0");
		});
		
		$('#vitals-record-time').change(function(){
			var value = $(this).val();
			if (value == "-1"){
				$('#record-time-others').show('fade', 500);
			} else { $('#record-time-others').hide('fade', 500).val(""); }			
		});
		
		$('#vitals-info-update-form-submit').click(function(){
			var value = $('#vitals-record-time').val();
			if (value == "-1"){ value = $('#record-time-others').val(); }
			
			var updateTime = $.ajax({ url:_appHandler.changeLocation('case.php?time'),
				data: {v:value, c:<?php echo $case->get_case_number(); ?>}
			});
			
			updateTime.done(function(data){
				$('#vitals-info-update-form').modal('hide');
				
				// Clean and destroy form
				$('#record-time-others').hide(0).val(""); $('#vitals-record-time').val("0");
				
				_isEditingVitals = false; load_profile_content(); 
			});
		});
		
		$('#record-time-others').hide(0);
		
		$('#view-report-btn').click(function(){
			$('#report-viewer').modal('show');
			
			var h = "<iframe src='report/?id=<?php echo $_GET['c']; ?>' width=900 height=370 seamless></iframe>";
			$('#report-viewer .modal-body').html(h);
		});
		
	});
	</script>
	<br><br>
	<div id='profile-contents-information'>
		<br><br>
		<table class='table' style='padding-top:3%;width:93%'>
			<tr>
				<th>Case Number</th><td><?php echo $case->get_case_number(); ?></td>
				<th>Date Admitted</th><td><?php echo $case->get_case_timestamp(); ?></td>
				<th>STATUS</th><td><?php echo $case->get_status(); ?></td>
			</tr>
			<tr>
				<th>Patient Name</th>
				<td> <?php 
					echo $case->get_case_patient()->get_patient_name()->get_full_name_middle(); 
				?></td>
				<th>Diagnosis</th><td colspan=3><?php echo $case->get_diagnosis(); ?></td>
			</tr>
			<tr>
				<th>Bed Number</th><td><?php echo $case->get_bed_number(); ?></td>
			<?php
				$patient = $case->get_case_patient();
				if ($patient->get_age() == 0){
					if ($patient->get_birthdate()->convert_to_days() > 28){
						$age = $patient->get_age()." days old";
					} else { $age = $patient->get_age()." days old"; }
				} else { $age = $patient->get_age()." y/o"; }
			
			?>
				<th>Age Admitted</th><td><?php echo $age; ?></td>
				<th>Vitals Record Time</th>
				<td>
				
			<?php
				if (!$case->is_case_discharged()){
					echo "<div id='vitals-info-text'>";
				} else { echo "<div>"; }
			
						if ($case->get_monitor_timeframe() == 120){
							echo "2 hours";
						} else if ($case->get_monitor_timeframe() == 240){
							echo "4 hours";
						} else if ($case->get_monitor_timeframe() == 0){
							echo "Anytime specified.";
						} else { echo $case->get_monitor_timeframe()." minutes";  }
						?>
					</div>
			<?php
				if (!$case->is_case_discharged()):
			?>
					<div id='vitals-info-update-btn' style='color:#45aeea'>
						<button>Update Record Time</button>
					</div>
					<div id='vitals-info-update-form' class='modal hide fade' data-keyboard='false' data-backdrop='static'>
						<div class='modal-header'>
							<h3 id='alert-modal-box-header'>Update Record Time</h3>
						</div>
						<div class='modal-body'>
							<select id='vitals-record-time' class='span3'>
								<option value='0'> Anytime </option>
								<option value='120'> 2 hours </option>
								<option value='240'> 4 hours </option>
								<option value='-1'> Others </option>
							</select>
							<input id='record-time-others' style='margin-left:15px' class='span3' type='text' placeholder='Specify Record Time (in minutes)' />
						</div>
						<div class='modal-footer'>
							<button id='vitals-info-update-form-submit'>Save Changes</button>
							<button id='vitals-info-update-form-cancel' class='alternate'>Cancel</button>
						</div>
					</div>
			<?php endif; ?>
				</td>
			</tr>
		</table>
	</div>
	<div id='profile-content-vitals'>
		<br><br>
		<style>
		#vitals-list-table {
			overflow-y:auto; height:180px; margin-top:-20px; width:93%;
		}

		.jspDrag { background: #45aeea;}
		.jspTrack { background:#ddd;}
		</style>
		<table class='table table-striped table-hover' style='width:93%'>
			<tr>
				<td style='width:8%;'>No.</td>
				<td style='width:22%'>Date/Time Recorded</td>
				<td style='width:10%;'>Blood Pres.</td>
				<td style='width:10%'>Resp. Rate</td>
				<td style='width:10%;'>Pulse</td>
				<td style='width:14%'>Temp.</td>
				<td style='width:20%'>Attending Nurse</td>
			</tr>
		</table>
		<div id='vitals-list-table' >

			<table class='table table-striped table-hover' style='width:100%;' align='left'>
				<?php
					$vitals_list = $case->load_vitals_list(); $i=1;
					foreach($vitals_list as $vitals){
						echo "<tr>";
						echo "<td style='width:3%;'>".$i."<td>";
						echo "<td style='width:18%;'>".$vitals->get_timestamp()."<td>";
						echo "<td style='width:5%;'>".$vitals->get_blood_pressure()."<td>";
						echo "<td style='width:5%;'>".$vitals->get_respiratory_rate()."<td>";
						echo "<td style='width:5%;'>".$vitals->get_pulse_rate()."<td>";
						echo "<td style='width:5%;'>".$vitals->get_temperature()."<td>";
						echo "<td style='width:20%'>".$vitals->get_attending_nurse()->get_user_name()->get_full_name()."<td>";
						echo "</tr>";

						$i++;
					}
					
				?>
			</table>
		</div>
	</div>
	<div id='profile-content-graph'>
		<div id='graph-container-main' style='height:340px'>
			<div align='center'>
				<br><br>Loading Visualization...
			</div>
		</div>
	</div>
	<div id='profile-content-options' style='float:right;margin-top:-1%;margin-right:6%'>
	<?php
		if (!$case->is_case_discharged()){
			echo "<button id='discharge-patient-option'> <!-- <i class='icon icon-white icon-close'></i> -->
			Discharge Patient</button>";
		}
	
	?>
		<button id='view-report-btn'> <i class='icon icon-white icon-print'></i>
			Generate Report</button><br><br><br>
	</div>
	<div id='report-viewer' class='modal hide fade' data-keyboard='false' data-backdrop='static' style='width:920px;margin-left:-450px;'>
		<script>
		$(function(){
			$('#report-viewer-cancel').click(function(){
				$('#report-viewer').modal('hide'); $('#report-viewer .modal-body').html('Loading report...');
			});
		});
		</script>
		<div class='modal-header'>
			<h3 id='alert-modal-box-header' style='color:#555'> Generate Report Viewer</h3>
		</div>
		<div class='modal-body' style='height:370px;'>
			Loading report...
		</div>
		<div class='modal-footer'>
			<button id='report-viewer-cancel' class='alternate'>Close</button>
		</div>
	</div>