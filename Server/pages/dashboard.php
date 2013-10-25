<?php
	include "../backend.php";
	$db = new Database();

	$my_ip_address = $_SERVER['REMOTE_ADDR'];
	$machine = new Machine($my_ip_address);

	$ward = new Ward($machine->get_ward_id());
?>
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

	$('#feed-container, #ward-info-pane-cont').hide(0);

	$('#ward-info-pane-cont').show('fade', 1800);

	// Do the animation of initial state for the feed...
	var initialFeed = $.ajax({ url:_appHandler.changeLocation('feed.php?w&init') });
	initialFeed.done(function(data){
		$('#feed-container').html(data);
		setTimeout(function(){
			$('#feed-container .feed-container-item').each(function(){ $(this).hide(0); });
			$('#feed-container').show(0); var timer = 500;
			$('#feed-container .feed-container-item').each(function(){ 
				$(this).show('fade', timer); timer += 500; 
			});
		}, 200);
	});
	
	// Initiate feed object...
	_feedHandler.__init__(); 
	_feedHandler.setFeedContainer($('#feed-container'));
	_feedHandler.vitals_container = $('#vitals-registered-container');
	_feedHandler.cases_container = $('#cases-registered-container');
	
	
	// Do the thread nao!
	setTimeout(function(){
		_feedHandler.startLiveFeed();
	}, 500);

	$('#notification-btn').click(function(){
		$('#notification-form').modal('show'); 
	});
	
	$('#online-btn').click(function(){
		//Get current date and time...
		
		//Open chuchu sesame... :)
		$('#online-personnel-form .modal-body').html("Loading personnel list... Please wait... ");
		$('#online-personnel-form').modal('show'); 
		
		var online = $.ajax({ url:_appHandler.changeLocation('session.php?w&active') });
		online.done(function(data){
			$('#online-personnel-form .modal-body').html(data);
		});
	});
	
	$('#online-personnel-form-cancel').click(function(){
		$('#online-personnel-form').modal('hide');
	});
	
	//Set the button for notification
	_notificationHandler.button = $('#notification-counter-htm');

});
</script>
<h3 class='titlea'> Information Dashboard <small id='test-feed'></small></h3>
<div style='margin-left:0%;margin-top:5%'>
	<div class='span3' style='margin-left:-1%;'>
		<table id='ward-info-pane-cont' class='table'>
			<tr><td>
				<div>
					<span style='font-size:200%'>
						<?php echo $ward->get_ward_name(); ?>
					</span><br>Ward Name
				</div>
			</td></tr>
			<tr><td>
				<div>
					<span style='font-size:200%'>
						<?php echo $ward->get_ward_location(); ?>
					</span><br>Location
				</div>
			</td></tr>
			<tr><td>
				<div id='cases-registered-container'>
					<?php
					$total_cases = $ward->get_total_cases(); $current = $ward->get_current_cases();
			
			echo "
				<span style='font-size:200%'>
					".$current."/".$total_cases."
				</span><br>Cases Registered";
					?>
				</div>
			</td></tr>
			<tr><td>
				<div id='vitals-registered-container'>
					<?php
			$total_vitals = $ward->get_total_vitals();
			
			echo "
				<span style='font-size:200%'>
					".$total_vitals."
				</span><br>Vitals Submitted";
					?>
				</div>
			</td></tr>
		</table>
	</div>
	<div class='span8' style='margin-left:3%;margin-top:-3%'>
		<style>
		#feed-container .feed-container-item {
			border: 1px #ddd solid; padding: 10px;
			margin:10px 0px 10px 0px;
		}

		#feed-container .feed-container-item:hover {
			background-color: #45aeea; color:white;
		}

		#feed-container .feed-container-item .feed-container-item-date {
			float:right; font-size:70%; margin-top:-1%;
		}

		#feed-container {
			height:300px; overflow-y:auto; overflow-x:hidden;
		}
		</style>
		<!--<div class='pull-right' style='margin-top:-5%'>
			 <button id='online-btn' class='alternate'>
				<i class='icon icon-user'></i> Online Personnel </button>
	<!--		 <button id='notification-btn'> 
				<span id='notification-counter-htm'>(10)</span> Notifications 
			</button> ->
		</div>-->
		<div id='feed-container' style='margin-top:5%'>
			<div class='feed-container-item'>
				<div class='feed-container-item-date'>
					2012-12-21 02:21:22
				</div>
				patient chuchu has recorded chuchu...
			</div>
		</div>
	</div>
	<div id='online-personnel-form' class='modal hide fade' data-keyboard='false' data-backdrop='static'>
		<div class='modal-header'>
			<h3 id='alert-modal-box-header' style='color:#555'> Online Personnel
				<small id='current-date' style='color:#555'>as of </small>
			</h3>
		</div>
		<div class='modal-body'>
			Loading personnel list... Please wait... 
		</div>
		<div class='modal-footer'>
			<button id='online-personnel-form-cancel' class='alternate'>Close</button>
		</div>
	</div>
	
</div>