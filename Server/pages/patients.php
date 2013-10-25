<!--  Here starts the UI -->
<!--  Patient List UI -->

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

	$('#add-patient-container, #profile-container').hide(0);
	$('#record-time-others').hide(0);

	$("#search-patient-fld").keyup(function(){
		get_patient_table();
	});

	$('#birthdate').datepicker({
		changeMonth:true, changeYear:true, dateFormat:"yy-mm-dd"
	});

	$('#add-new-patient-btn').click(function(){
		$('.titlea').hide('fade', 500);
		$('#patients-table-cont-container').hide('slide', {"direction":"left"}, 500, function(){
			$('.titlea').html('Add New Patient Case').show('fade', 500);
			$('#add-patient-container').show('slide', {"direction":"left"}, 500);
		});
	});

	$('#cancel-add-patient').click(function(){
		destroy_fieldset(); $('#record-time-others').hide('fade', 500);

		$('.titlea').hide('fade', 500);
		$('#add-patient-container').hide('slide', {"direction":"left"}, 500, function(){
			$('.titlea').html('Ward / Patient List').show('fade', 500);
			$('#patients-table-cont-container').show('slide', {"direction":"left"}, 500);
		});
	});

	$('#submit-new-patient-btn').click(function(){
		$(this).html("Adding case... Please wait...").attr('disabled', 'disabled');
		$('#add-patient-container input, #add-patient-container select').attr('disabled', 'disabled');

		//Tilokon ang data daan :)
		var data = Array(), count = 0;
		$('#add-patient-container table :input').each(function(){
			data[count] = $(this).val(); count++;
		});

		//Send data to server na :)
		var addRequest = $.ajax({ url:_appHandler.changeLocation('case.php?add') , data:{d:data} });
		addRequest.done(function(data){
			$('#add-status').html("Case is successfully registered");
			//$('#add-status').html(data);
			$('#submit-new-patient-btn').html("Register Case").removeAttr('disabled'); 
			$('#add-patient-container input, #add-patient-container select').removeAttr('disabled');

			setTimeout(function(){ $('#add-status').html(""); }, 5000); destroy_fieldset();
		});


	});

	$('#vitals-record-time').change(function(){
		var value = $(this).val();
		if (value == "-1"){
			$('#record-time-others').show('fade', 500).val("");
		} else {
			$('#record-time-others').hide('fade', 500);
		}
	});

	$('#refresh-btn').click(function(){ get_patient_table(); });

	//Show initial state...
	get_patient_table();
});

function destroy_fieldset(){
	$('#add-patient-container input').val(""); $('#vitals-record-time').val('0');
}

function get_patient_table(){
	$('#patients-table-container').html('loading...');
	var request = $.ajax({ url:_appHandler.changeLocation('patients.php?w'),
		data: { q: $('#search-patient-fld').val() }
	});

	request.done(function(data){
		$('#patients-table-container').html(data);
	}); request.fail(function(){ });
}
</script>
<h3 class='titlea'>Ward / Patient List</h1>
<div style='margin-left:-2%;'>
 	<div id='patients-table-cont-container'>
		<div style='margin-top:5%'>
			
			<button id='add-new-patient-btn'>
				<i class='icon icon-white icon-plus'></i> Add New Patient</button>
			<button id='refresh-btn' class='alternate' title='Refresh Patient List'>
				<i class='icon icon-refresh'></i></button>
			<div style='float:right;margin-right:6%'>
				<input type='text' id='search-patient-fld' placeholder='Search Patient' />
			</div>

		</div>

		<table class='table table-striped' style='width:93%;margin-top:2%'>
			<tr class='table-row-custom'>
				<tr>
					<th style='width:28%'>Patient Name</th>
					<th style='width:14%'>Room Number</th>
					<th style='width:44%' align='center'>Diagnosis</th>
					<th>Status</th>
				</tr>
			</tr>
		</table>
		<div id='patients-table-container'>


		</div>
	</div>
	<div id='profile-container'>

	</div>
	<div id='add-patient-container'>
		<br><br>
		<table class='table' style='width:95%'>
			<tr>
				<td>Last Name</td>
				<td><input type='text' placeholder='Last Name' /></td>
				<td>First Name</td>
				<td><input type='text' placeholder='First Name' /></td>
				<td>Middle Name</td>
				<td><input type='text' placeholder='Middle Name' /></td>
			</tr>
			<tr>
				<td>Diagnosis</td>
				<td colspan=3>
					<input type='text' style='width:98%' id='diagnosis' placeholder='Patient Diagnosis' />
				</td>
				<td>Birth Date</td>
				<td><input type='text' id='birthdate' placeholder='YYYY-MM-DD' /></td>
			</tr>
			<tr>
				<td>Bed Number</td>
				<td><input type='text' placeholder='Last Name' /></td>
				<td>Vitals Record Time</td>
				<td colspan=3>
					<select id='vitals-record-time'>
						<option value='0'> Anytime </option>
						<option value='120'> 2 hours </option>
						<option value='240'> 4 hours </option>
						<option value='-1'> Others </option>
					</select>
					<input id='record-time-others' style='width:51%;margin-left:15px' type='text' placeholder='Specify Record Time (in minutes)' />
				</td>
			</tr>
		</table>
		<div style='float:right;margin-right:6%'>
			<span id='add-status'></span>
			<button id='submit-new-patient-btn'><i class='icon icon-white icon-edit'></i>  Register Case</button>
			<button id='cancel-add-patient' class='alternate'><i class='icon icon-remove'></i>  Cancel</button>
		</div>
	</div>
</div>
