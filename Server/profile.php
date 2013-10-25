<?php
/*
* Patient Profile Script - profile.php
* Author: kiethmark bandiola
* Description: Shows the basic information of a case of
*		a patient. 
*
* Requires: Database Connection.
* Input: CaseID. SESSION_ID for an additional Input for Mobile.
* Output: Page Render for web. JSON Data for Mobile.
* Assumes: Client Time is the same with the server time.
*/

include "backend.php";
$db = new Database();
$caseid = $_GET['i'];

if (isset($_GET['m'])):
	$session_id = $_POST['s'];
	
	//Check if SESSION exists...
	$session = new Session($session_id);
	if ($session->is_session_exists()){
		//Get Ward Information...
		$case = new Cases($caseid);

		$patient = $case->get_case_patient()->get_patient_name();

		$info  = '{"id":'.$case->get_case_number().', "name":"'.$patient->get_full_name().'", "diagnosis":"'.$case->get_diagnosis().'", ';
		$info .= '"date":"'.$case->get_case_date().'", "bed_number":'.$case->get_bed_number().', "status":"'.$case->get_status().'"}';

		$current_time = date("H:i:s");
		//echo $current_time;

		$json = '{"result":'.$info.'}';;
		//$object = json_decode($json);
		//print_r($object);
	} else { $json = '{"result":null}'; }

	echo $json;

elseif (isset($_GET['w'])):
?>
<script>
$(function(){
	$('#close-profile-btn').click(function(){
		$('.titlea').hide('fade', 500);
		$('#profile-container').hide('slide', {"direction":"left"}, 500, function(){
			$('.titlea').html('Ward / Patient List').show('fade', 500);
			$('#patients-table-cont-container').show('slide', {"direction":"left"}, 500);
		});
	});

	// Profile Menu Traversal...
	$('#profile-menu #main-profile-menu0 li').click(function(){
		var id = $(this).attr('id'), prevID = null;

		if ($(this).attr('class') != "active"){
			$('#profile-menu ul li').each(function(){
				var c = $(this).attr('class'); 
				if (c == "active"){ 
					$(this).removeClass('active'); prevID = $(this).attr('id');
				}
			});

			$(this).addClass('active');

			//Switch to Pane... :)
			animate_switch_profile(prevID, id);
		}
	});

	load_profile_content();
	//$('#vitals-list-table').jScrollPane({ mouseWheelSpeed:30 });
});

function animate_switch_profile(close, open){
	var objectClose = mapping_on_objects(close);
	objectClose.hide('slide', {"direction":"left"}, 500, function(){
		var objectOpen = mapping_on_objects(open);
		objectOpen.show('slide', {"direction":"left"}, 500);
	});
}

function mapping_on_objects(id){
	var objectOpen = null;
	if (id == "information"){
		objectOpen = $('#profile-contents-information');
	} else if (id == "vitals-list"){
		objectOpen = $('#profile-content-vitals');
	} else if (id == "graph-view"){
		objectOpen = $('#profile-content-graph');
	}

	return objectOpen;
}

function load_profile_content(){
	var caseid = $('#case-info-pane').attr('data-case');

	$('#profile-content').html("<br><br><br><br><br>Loading...");
	var loadRequest = $.ajax({ url:_appHandler.changeLocation('caseprofile.php'), data:{ c:caseid }});
	loadRequest.done(function(data){
		$('#profile-content').html(data);
	}); loadRequest.fail(function(){ load_profile_content(); });

	var loadRequest0 = $.ajax({ url:_appHandler.changeLocation('casedropdown.php'), data:{ c:caseid }});
	loadRequest0.done(function(data){
		$('#view-prev-cases-cases-list-container').html(data);
	});
	
	
}

</script>
<style> 
#profile-menu { 
	margin-top: 1%; font-family:"Roboto-Regular";
	margin-left:-3%;  width:95%;
}
#profile-menu ul{ list-style: none;  padding-left:0px; } /* background-color:#eee; */
#profile-menu ul li{ 
	float:left; width:15%; padding:10px 15px 10px 15px;
	border-left: 1px #ccc solid; margin-right:1%;
}
#profile-menu ul li:hover { background-color:#ddd; color:#555;  }
#profile-menu .active { background-color:#45aeea; color:white; } 
</style>
<br><br>
<div id='profile-menu' class='row span12'>
	<ul id='main-profile-menu0'>
		<li id='information' class='active'> 
			Information
		</li>
		<li id='vitals-list'> Vitals List </li>
		<li id='graph-view'> Visualization </li>
	</ul>
	<div id='case-info-pane' data-case='<?php echo $caseid; ?>' style='float:right;margin-top:-1%;'>
		<style>
		#view-prev-cases ul li{
			float:left; width:100%;
		}

		#view-prev-cases ul li:hover{ background-color:transparent; }
		#view-prev-cases ul li a:hover { text-decoration: underline; color:#45aeea; }

		.dropdown-menu>li>a:hover { background-color:transparent; background-image: none; }
		</style>
		<div id='view-prev-cases' class='dropdown' style='float:left;margin-right:8px'>
			<button id='view-prev-btn' class="dropdown-toggle" data-toggle="dropdown">
				View Previous Cases 
				<!-- <i class='icon-chevron-down icon-white icon'></i> -->
				<b class="caret"></b>
			</button>
			<ul class='dropdown-menu' role='menu' aria-labelledby="dLabel" style='width:150%'>
				<div id='view-prev-cases-cases-list-container' >
				
				</div>
			</ul>
		</div>
		<button id='close-profile-btn' class='alternate'>
			<i class='icon icon-remove'></i> Close Profile</button>
	</div>
</div>
<div id='profile-content'>
	
</div>

<!--
<div class='row' style='margin-left:0px;margin-top:5px;'>
	<div>
		<table class='table'>
			<tr><th>Last Name</th><td>Bandiola</td>
				<th>First Name</th><td>Kieth Mark </td>
				<th>Middle Name</th><td>Sevilla</td></tr>
		</table>
		<ul class="nav nav-tabs nav-stacked">
		  <li><a href='#'>Case # 222</a></li>
		</ul>
	</div>
</div> -->
<?php endif; ?>