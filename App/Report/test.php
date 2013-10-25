<?php

// Graph require library :)
require_once('graph/src/jpgraph.php');
require_once('graph/src/jpgraph_line.php');
require_once('graph/src/jpgraph_bar.php');

$data = ""; $width = 600; $height = 200;
$graph = new Graph($width, $height);
		
$graph->SetScale('intint'); $graph->title->Set('Sunspot example');
$graph->xaxis->title->Set('(year from 1701)'); $graph->yaxis->title->Set('(# sunspots)');
		
$year = array(1, 2, 3, 4, 5);
$ydata = array(1, 2, 3, 4, 5);

// Create the linear plot
$lineplot = new LinePlot($ydata);
 
// Add the plot to the graph
$graph->Add($lineplot);

$graph->Stroke();
		
?>