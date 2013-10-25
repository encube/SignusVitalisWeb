<?php

include 'backend.php';
$db = new Database();

//Get Machine IP Address... :)
$machine = new Machine($_SERVER['REMOTE_ADDR']);
$wardID = $machine->get_ward_id();

if ($_GET['r'] == "PHYSICIAN"){
	$sql = "SELECT iduser FROM user WHERE user_rank='PHYSICIAN' 
			AND user_last_name LIKE '%".$_GET['t']."%' ORDER BY user_last_name ASC";
} else if($_GET['r'] == "NURSE"){
	if ($_GET['s'] == "ALL"){
		$shift = "";
	} else if ($_GET['s'] == "NO"){
		$shift = " AND registered_user.shift IS NULL ";
	} else { $shift = " AND registered_user.shift='".$_GET['s']."'"; }

	$sql = "SELECT iduser FROM registered_user LEFT JOIN user ON user.iduser=registered_user.user_iduser
			WHERE user.user_last_name LIKE '%".$_GET['t']."%' AND
			registered_user.ward_idward=".$wardID." $shift AND user.user_rank='NURSE' ORDER BY user.user_last_name ASC ";
} else {
	$sql = "SELECT iduser FROM user WHERE user_last_name LIKE '%".$_GET['t']."%' ORDER BY user_last_name ASC";
}

$query = mysql_query($sql);
//$sql = "SELECT * FROM "
?>
<script>
var _isEditing = false;

$(function(){

	$('.shift-text-update-btn, .shift-update-form').hide(0);

	$('.shift-text').mouseover(function(){
		if (!_isEditing){
			var parent = $(this).parent();
			$(this).hide('fade', 200, function(){
				var siya = parent.children('.shift-text-update-btn');
				siya.show('fade', 200);
			});
		}
		
		//text.html("<div style='background-color:blue;padding:10px'>Update Shift</div>");
	});

	$('.shift-text-update-btn').mouseout(function(){
		if (!_isEditing){
			var parent = $(this).parent();
			$(this).hide('fade', 200, function(){
				var siya = parent.children('.shift-text');
				siya.show('fade', 200);
			});
		}
	}).click(function(){
		var parent = $(this).parent(); _isEditing = true;
		var siya = parent.children('.shift-text'); siya.hide(0);
		$(this).hide('fade', 500, function(){
			var kana = parent.children('.shift-update-form'); 
			kana.show('fade', 500);
		});
	});

	$('.shift-update-cancel-btn').click(function(){
		_isEditing = false;
		var mainParent = $(this).parent().parent().parent();

		var formContainer = mainParent.children('.shift-update-form');
		formContainer.hide('fade', 500, function(){
			var text = mainParent.children('.shift-text');

			text.show('fade', 500);
			mainParent.children('.shift-update-btn-0').html('Save Changes');
		});
	});

	$('.shift-update-btn-0').click(function(){
		$(this).html("Processing... "); var ako = $(this);

		var mainParent = $(this).parent().parent().parent();
		var form	   = mainParent.children('.shift-update-form'),
			value	   = form.children('select').val(),
			id 		   = mainParent.attr('data-user-id');

		//alert(mainParent.html());
		var req = $.ajax({ url:_appHandler.changeLocation('shift.php'), data:{ v:value, i:id }});
		req.done(function(data){
			var kato = mainParent.children('.shift-text');
			kato.html(data);

			ako.html('Save Changes');

			//Override ni tanan!
			update_personnel_list();
			$('#shift-updated-status').show('fade', 500, function(){
				setTimeout(function(){ $('#shift-updated-status').hide('fade', 500); }, 5000);
			});
		});		
	});
	
	$('.send_to').click(function(){
		var nid  = $(this).parent().parent().attr('id');
		$('#sendtoform').attr('data-nurse-id', nid);
		$('#sendtoform').modal('show');
	});
	
	$('#sendformclose').click(function(){
		$('#sendtoform').modal('hide');
	});
	
	$('#sendformsubmit').click(function(){
		var wardid = $('#wardselect').val();
		var nurseid = $('#sendtoform').attr('data-nurse-id');
		var req = $.ajax({type:"POST", url:_appHandler.changeLocation('ward.php'), data:{ wardid:wardid, userid:nurseid} });
		req.done(function(){
			update_personnel_list();
			$('#sendtoform').modal('hide');
		});
	});

});
</script>
<div id='patient-name-table' >
	<table class='table table-striped table-hover'>
	<th>Personnel Name</th>
	<th>Role</th>
	<th>Shift</th>
	<th>On-duty</th>
	<th></th>
	<?php
	if (mysql_num_rows($query) == 0 or !$query){
		echo "No personnel found for this query.";
	} else {
	
		while($result = mysql_fetch_array($query)){

			$user = new User($result['iduser']); $continue = true;
			if ($_GET['r'] == "ALL"){
				if ($user->get_position() == "NURSE" and $user->get_assigned_ward() != $wardID){
					$continue = false;
				}
			}

			if ($continue){
				echo "<tr id='".$result['iduser']."'>";
				echo "<td>".$user->get_user_name()->get_full_name()."</td>";
				echo "<td>".$user->get_position()."</td>";
				if ($user->get_position() == "PHYSICIAN"){
					$shift = " - ";
				} else {
					if ($user->get_shift() == null){
						$has_shift = "true"; $shift_data = 'No Shift Assigned';
					} else { $has_shift = "false"; $shift_data = $user->elaborate_shift_information($user->get_shift()); }

					$shift = "
						<div class='shift-container' data-user-id='".$user->get_user_id()."' data-has-shift='$has_shift' data-shift='$shift_data'>
							<div class='shift-text'>$shift_data</div>
							<div class='shift-text-update-btn' style='background-color:#45aeea;color:white;padding:5px 10px 5px 10px'>
								<i class='icon icon-edit icon-white'></i> Update Shift
							</div>
							<div class='shift-update-form'>
								<select>
									<option value='7-15'>7 AM - 3 PM </option>
									<option value='15-23'>3 PM - 11 PM </option>
									<option value='23-7'>11 PM - 7 AM </option>
								</select>
								<div>
									<button class='shift-update-btn-0'> Save Changes </button>
									<button class='alternate shift-update-cancel-btn'> Cancel </button>
								</div>
							</div>
						</div>
					";

					//if ($user->get_shift() == null){
					//	$shift = "<button>test</button>";
					//} else { $shift = $user->elaborate_shift_information($user->get_shift()); }
				}
				echo "<td>$shift</td>";
				
				if ($user->get_position() == "NURSE"){
					if ($user->is_online()){
						if ($user->is_my_duty_now()){
							$duty = "<span class='badge badge-success' title='Personnel is online and on-duty.'>On-duty</span>";
						} else { $duty = "<span class='badge badge-success' title='Personnel is online.'>Off-duty</span>"; }
					} else { $duty = "<span class='badge' >Off-duty</span>";  }
				} else {
					if ($user->is_online()){ $duty = "<span class='badge badge-success' title='Personnel is online and on-duty.'>On-duty</span>"; } 
					else { $duty = "<span class='badge'>Off-duty</span>"; }
				}

				echo "<td>$duty</td>";
				if ($user->get_position() == "NURSE"){
					echo "<td><button class='send_to btn-small'> send to </button></td>";
				}else{
					echo "<td> </td>";
				}
				echo "</tr>";
			}
		}
	}
	?>
	</table>
</div>