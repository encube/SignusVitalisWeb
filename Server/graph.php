<?php
	include "backend.php";
	$db = new Database();
	$case = new Cases($_GET['c']);
	$vitals_list = $case->load_vitals_list();
	//echo $_GET['c'];

	$date_data = $resp_data = $pulse_data = $temp_data = "["; $first = true; 
	foreach($vitals_list as $vitals){
		//echo $vitals;
		$date  = $vitals->get_timestamp();
		$resp  = $vitals->get_respiratory_rate();
		$pulse = $vitals->get_pulse_rate();
		$temp  = $vitals->get_temperature();

		if ($first){
			$first = false; 

			$date_data .= "'$date'"; $resp_data .= "$resp";
			$pulse_data .= "$pulse"; $temp_data .= "$temp";
		} else {
			$date_data .= ",'$date'"; $resp_data .= ",$resp";
			$pulse_data .= ",$pulse"; $temp_data .= ",$temp";
		}

		
	} $date_data .= "]"; $resp_data .= "]"; $pulse_data .= "]"; $temp_data .= "]";

?>
<script>
$(function(){

	//$('#pulse-rate-graph').highcharts({
	var chart = $('#graph-container').highcharts({
		chart:{ type:'line', zoomType:'x'}, title:{ text:"Vital Signs Visualization" }, 
		tooltip:{ valuesuffix:'bpm' },
		//renderTo:'#pulse-rate-graph',
		xAxis:[{
			categories: <?php echo $date_data; ?>,
			labels :{
				align: 'right', rotation:-75, formatter: function(){
					var obj = this.value; var string = obj.toString();
					var value = string.split(" ");

					return value[0]+"<br>"+value[1];
				}
			}
		}],
		yAxis: [
			{ // Primary yAxis
                labels: {
                    formatter: function() {
                        return this.value +'°C';
                    },
                    style: {
                        color: '#89A54E'
                    }
                },
                title: {
                    text: 'Temperature',
                    style: {
                        color: '#89A54E'
                    }
                }, min:26, max:43
    
            }, { // Secondary yAxis
                gridLineWidth: 0,
                title: {
                    text: 'Pulse Rate',
                    style: {
                        color: '#4572A7'
                    }
                },
                labels: {
                    formatter: function() {
                        return this.value +' bpm';
                    },
                    style: {
                        color: '#4572A7'
                    }
                }, min:0, max:280
    
            },{ // Tertiary yAxis
                gridLineWidth: 0,
                title: {
                    text: 'Respiratory Rate',
                    style: {
                        color: '#AA4643'
                    }
                },
                labels: {
                    formatter: function() {
                        return this.value +' bpm';
                    },
                    style: {
                        color: '#AA4643'
                    }
                }, min:10, max:180
            }

		],
		series: [
			{ name:"Resp Rate",  yAxis:2, color: '#AA4643',
				data: <?php echo $resp_data; ?> },
			{ name:"Pulse Rate",  yAxis:1, color: '#4572A7',
				data: <?php echo $pulse_data; ?> },
			{ name:"Temperature",  yAxis:0, color:'#89A54E',
				data: <?php echo $temp_data; ?> }

			//{ name:"Resp Rate",  xAxis:0,
			//	data:[31, 32, 51, 43, 42, 23, 31, 33, 33, 32, 31, 36] }
		]
	});


});
</script>
<div style='margin-top:3%;margin-left:-3%'>
<!--	<div class='span3'>
		<ul id='visual-tab' class="nav nav-pills nav-stacked">
		  <li class='active'><a href='#pulse-rate-graph-cont'>Pulse Rate</li></a>
		  <li><a href='#resp-rate-graph-cont'>Respiratory Rate</li></a>
		  <li><a href='#temp-graph-cont'>Temperature</li></a>
		</ul>
	</div>
	<div class='tab-content'>
		<div class='tab-pane active' id='pulse-rate-graph-cont' style='margin-left:-2%'>
			<div id="pulse-rate-graph" style="height:320px;" class='span9'></div>
		</div>
		<div class='tab-pane' id='resp-rate-graph-cont' style='padding-left:2%'>
			<div id="resp-rate-graph" style="height:400px;width:300px; "></div>
		</div>
		<div class='tab-pane' id='temp-graph-cont' style='padding-left:2%'>
			<div id="temp-graph" style="height:400px;width:300px; "></div>
		</div>
	</div>  -->
	<div id='graph-container' style='width:90%;height:420px;'>

	</div>
</div>