<script>
$(function(){

	// Constant for loading page...
	var patient_list_container = $('#patient-list-container'),
		search_query_field     = $('#search-patient-fld'),
		filter_patient_field   = $('#search-type-dropdown');

	// Patient List Refresh... :)
	$('#search-patient-fld').keyup(function(){
		update_patient_list(patient_list_container, search_query_field, filter_patient_field);
	});

	//Executing and Emulating Dropdown Actions :)
	$('#search-type-dropdown li a').click(function(){
		var id = $(this).attr('id'); 
		var text = id.toLowerCase(); text = text.replace(text.charAt(0), id.charAt(0));

		$('#search-type-txt').html(text); $('#search-type-dropdown').attr('data-type', id);
		update_patient_list(patient_list_container, search_query_field, filter_patient_field);
	});

	// Initial State... :)
	update_patient_list(patient_list_container, search_query_field, filter_patient_field);
});

function update_patient_list(cont, txtFilter, typeFilter){
	// Constant for loading page...
	cont.html("<div align='center'><br><br><br><img src='img/load.gif' /></div>");

	var request = $.ajax({
		url:_address.full_address('patients.php?w'), 
		data:{ q:txtFilter.val(), t:typeFilter.attr('data-type') }
	});

	request.done(function(data){ setTimeout(function(){ cont.html(data); }, 100); });
	request.fail(function(){
		//TODO
	});
}

</script>
<div id=''>
	<h3>Patient List <small>for the current ward/station</small></h3>
	<div style='margin-top:10px;margin-bottom:10px'>
		<div class="form-search">
		  <input type="text" id='search-patient-fld' class="input-medium search-query span5" placeholder='Search for patient...'>
		</div>
		<div class='pull-right' style='margin-top:-4%'>
			<div class="btn-group">
			  <button class="btn">Filter Patient List (<span id='search-type-txt'>All</span>)</button>
			  <button class="btn dropdown-toggle" data-toggle="dropdown">
			    <span class="caret"></span>
			  </button>
			  <ul id='search-type-dropdown' class="dropdown-menu pull-right" data-type='ALL'>
			    <li><a id='ALL'>All Patients</a></li>
			    <li><a id='ADMITTED'>Admitted</a></li>
			    <li><a id='DISCHARGED'>Discharged</a></li>
			  </ul>
			</div>
		</div>
	</div>
	<div id='patient-list-container'>
		<table class='table table-striped table-hover'>
			<tr>
				<th>Patient Name</th><th>Room No.</th><th>Status</th>
			</tr>
			<tr>
				<td>Bandiola, Kieth Mark</td>
				<td>#21</td><td>ADMITTED</td>
			</tr>
		</table>
	</div>
</div>