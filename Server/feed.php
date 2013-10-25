<?php

include 'backend.php';
$db = new Database();

//Check user id...
//$session = new Session($_GET['s']);

// Then, get the news feed based on the user using...


//$eed = new Feed(1, 2, "2013-12-02 09:00:21", 4);
//echo $feed->print_feed();

if (isset($_GET['addrandom'])){
	$sql = "INSERT INTO feed VALUES(null,3, NOW(), 17, 2)";
	mysql_query($sql);
}

if (isset($_GET['m'])){
	// Charchar: Get one session from active sessions :)
	//$session = new Session(null);
	//$active_sessions = $session->get_active_sessions();
	//$_GET['s'] = $active_sessions[sizeof($active_sessions) - 30];

	$session = new Session($_GET['s']);

	// Initiate... list of json :)
	$json = '[';

	//Get first le role...
	$user = $session->get_current_user();
	if ($user->get_position() == "NURSE"){
		$chuchu = "WHERE ward_id=".$user->get_assigned_ward();
	} else { $chuchu = ""; }

	$sql = "SELECT * FROM feed ".$chuchu." ORDER BY timestamp DESC LIMIT 0, 15";

	//echo $sql."<br>";

	$query = mysql_query($sql); $first = true;
	while($r = mysql_fetch_array($query)){
		//echo $r['idfeed']. ") ";

		$feed = new Feed($r['idfeed'], $r['feed_type'], $r['timestamp'], $r['link_id'] );
		//echo $feed->convert_feed_to_text()."<br>";

		$data = '{"feedid":"'.$r['idfeed'].'",
				  "text":"'.$feed->convert_feed_to_text().'",
				  "timestamp":"'.$r['timestamp'].'", 
				  "wardid":"'.$r['ward_id'].'" }';

		if ($first){
			$json .= $data; $first = false;
		} else { $json .= ", ".$data; }
	} $json .= ']'; echo $json;
} 
else if (isset($_GET['w'])){

	if (isset($_GET['init'])){
		$my_ip_address = $_SERVER['REMOTE_ADDR'];

		$machine = new Machine($my_ip_address);
		$wardID = $machine->get_ward_id();

		$sql = "SELECT * FROM feed WHERE ward_id=".$wardID." ORDER BY timestamp DESC LIMIT 0, 6";
		$query = mysql_query($sql); $data = "";
		while($r = mysql_fetch_array($query)){
			$feed = new Feed($r['idfeed'], $r['feed_type'], $r['timestamp'], $r['link_id'] );
			$data .= "
				<div class='feed-container-item'>
					<div class='feed-container-item-date'>
						".$r['timestamp']."
					</div>".$feed->convert_feed_to_html()."
				</div>
			";
		} echo $data;
	} else if (isset($_GET['count'])){
		$machine = new Machine($_SERVER['REMOTE_ADDR']);
		$ward_id = $machine->get_ward_id();
		
		if (isset($_GET['cases'])){
			$ward = new Ward($ward_id);
			$total_cases = $ward->get_total_cases(); $current = $ward->get_current_cases();
			
			echo "
				<span style='font-size:200%'>
					".$current."/".$total_cases."
				</span><br>Cases Registered";
			
		} else if (isset($_GET['vitals'])){
			$ward = new Ward($ward_id);
			$total_vitals = $ward->get_total_vitals();
			
			echo "
				<span style='font-size:200%'>
					".$total_vitals."
				</span><br>Vitals Submitted";
			
		} else {
			$feed = new DashboardFeed($ward_id);
			echo $feed->get_total_feed_from_db();
		}
		
	} else if (isset($_GET['feed'])){
		$machine = new Machine($_SERVER['REMOTE_ADDR']);
		$ward_id = $machine->get_ward_id();
		
		$prev = $_GET['p']; $next = $_GET['c']; $limit = $next - $prev; $data = "";
		$sql = "SELECT * FROM `feed` WHERE ward_id=$ward_id ORDER BY timestamp DESC LIMIT 0,".$limit;
		$query = mysql_query($sql); while($r = mysql_fetch_array($query)){
			$feed = new Feed($r['idfeed'], $r['feed_type'], $r['timestamp'], $r['link_id'] );
			$data .= "
				<div class='feed-container-item new-item-feed' style='border-color:#45aeea'>
					<div class='feed-container-item-date'>
						".$r['timestamp']."
					</div>".$feed->convert_feed_to_html()."
				</div>
				";
		} echo $data;
	}

}
function convert_idfeed($linkID, $type){
	switch($type){
		case 2:
		case 4:
			return "SELECT * FROM cases WHERE idcases=".$linkID;
			break;
		case 3:
			return "SELECT * FROM vitals WHERE idvitals=".$linkID;
	}
}

?>