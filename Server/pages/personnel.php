
<style>
	button {
		background-color:#45aeea; border:none; color:white; padding:10px 18px 10px 18px;
	} button:hover { background-color:white; color:#45aeea; border: 1px #45aeea solid; }

	.alternate { color:#45aeea; background-color:white; border: 1px #45aeea solid; }
	.alternate:hover { border:none; }

	#patient-name-table {
		overflow-y:auto; height:240px; margin-top:-20px; 
		width:95%;
	}

	h3{
 		font-family:"Roboto-Light"; font-weight: normal; color:#555;
 		margin-left:-2%; margin-bottom:-3%; padding:0; margin-top:4%
	}

</style>
<script>
$(function(){
	update_personnel_list();


	$('#shift-updated-status').hide(0);
	$('#search-person-fld').keyup(function(){
		update_personnel_list();
	});

	$('#shift-person-fld, #role-person-fld').change(function(){
		update_personnel_list();
	});
});

function update_personnel_list(){
	//Insert parameters...
	var text  = $('#search-person-fld').val(),
		shift = $('#shift-person-fld').val(),
		role  = $('#role-person-fld').val();

	var request = $.ajax({ url: _appHandler.changeLocation('personnel.php'),
		data: {t:text, s:shift, r:role}
	});

	request.done(function(data){
		$("#personnel-table-container").html(data);
	}); request.fail(function(){ update_personnel_list(); });

}
</script>
<h3 class='titlea'> Personnel List </h3>
<div style='margin-left:-2%;'>
	<div id='patients-table-cont-container'>
		<div style='margin-top:5%'>
<!--			
			<button id='add-new-nurse-btn'>Assign New Nurse</button>
			<button id='add-new-nurse-btn'>Assign New Nurse</button> 
			<div style='float:right;margin-right:6%'> -->

			<input type='text' id='search-person-fld' placeholder='Search for Personnel' />
			<select id='shift-person-fld'>
				<option value='ALL'> All Shifts </option>
				<option value='NO'> No Shift Specified </option>
				<option value='7-15'> 7 AM - 3 PM </option>
				<option value='15-23'> 3 PM - 11 PM </option>
				<option value='23-7'> 11 PM - 7 AM</option>
			</select>
			<select id='role-person-fld'>
				<option value='ALL'> All Roles </option>
				<option value='NURSE'> Show All Nurses </option>
				<option value='PHYSICIAN'> Show All Doctors </option>
			</select>
			<span id='shift-updated-status' style='color:green'>
				Shift successfully updated!
			</span>
		</div>

		<!--<table class='table table-striped' style='width:93%;margin-top:2%'>
			<tr class='table-row-custom'>
				<tr>
					<th style="width:28%">Personnel Name</th>
					<th>Role</th>
					<th>Shift</th>
					<th>On-duty</th>
					<th></th>
				</tr>
			</tr>
		</table> -->
		<div id='personnel-table-container' style='width:93%;margin-top:2%'>

		</div>
	</div>
	<div id='profile-container'>

	</div>

	<div id="sendtoform" class = "modal hide fade" data-nurse-id=''>
		<div class = "modal-header">
			<h3>Send this nurse to</h3>
		</div>
		<div class = "modal-body">
			<select id="wardselect" style="width:100%">
			<?php
				include '../backend.php';
				$db = new Database();
				$sql = 'SELECT * FROM ward';
				$query = mysql_query($sql);
				while($result = mysql_fetch_array($query)){
					echo "<option value='".$result['idward']."'>".$result['ward_name']." at ".$result['ward_location']."</option>";
				}
			?>
			</select>
		</div>
		<div class = "modal-footer">
			<button id = "sendformsubmit" >transfer</button>
			<button id = "sendformclose" class="alternate">close</button>
		</div>
	</div>

</div>