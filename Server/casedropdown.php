<?php
include 'backend.php';
$db = new Database();
$caseid = $_GET['c'];
?>

<script>
$(function(){
	$('#view-prev-cases-cases-list-container li a').click(function(){
		var classs = $(this).attr('class'),
			id     = $(this).attr('data-case-id');

		if (classs != "active-case"){
			$('#case-info-pane').attr('data-case', id);
			load_profile_content();

			//$('#profile-content-vitals, #profile-content-graph').hide(0);

			$('#profile-menu ul li').each(function(){
				var c = $(this).attr('class'); 
				if (c == "active"){  $(this).removeClass('active'); }
			}); $('#information').addClass('active');
		} 
	});
});
</script>

<?php
	$case = new Cases($caseid); $patient = $case->get_case_patient();
	$patient->get_all_my_cases_html($caseid);

	//print_r($myCases);

	//foreach($myCases as $leCase){
	//	echo "<li><a href='#'>Case # ".$leCase[0]." (".$leCase[1].")</a></li>";
	//}
?>