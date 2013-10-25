<?php

require_once('tcpdf/config/lang/eng.php');
require_once('tcpdf/tcpdf.php');

// Graph require library :)
//require_once('graph/src/jpgraph.php');
//require_once('graph/src/jpgraph_line.php');
//require_once('graph/src/jpgraph_bar.php');

require_once('pgraph/class/pData.class.php');
require_once('pgraph/class/pDraw.class.php');
require_once('pgraph/class/pImage.class.php');



//Extend first the TCPDF class...
class MYPDF extends TCPDF {
	
	public function Header() {
		$this->SetY(10); $hospital = new Hospital();

		$this->SetFont('helvetica', 'N', 12);
		$html = $hospital->get_hospital_name();
		$this->writeHTML($html, true, false, true, false, 'C');
		$this->SetFont('helvetica', 'N', 8);
		$html = $hospital->get_hospital_location()."<br>";
		$this->writeHTML($html, true, false, true, false, 'C');	
		
		
		$this->SetFont('helvetica', 'N', 20);
		$html = "Vital Signs Visualization Report";
		$this->writeHTML($html, true, false, true, false, 'C');
	}
	
	public function Footer(){
		$this->SetY(-15); $this->SetFont('helvetica', 'N', 8);
		$html = "Page ".$this->getAliasNumPage().'/'.$this->getAliasNbPages()." | Report Generated: ".date("m-d-Y h:i:s")." | Signus Vitalis";
		$this->Cell(0, 10, $html, 0, false, 'R', 0, '', 0, false, 'T', 'M');
	}
	
}

//Get Object Class... :)
$link = "C:/wamp/www/VSRS/Server/backend.php";
include $link;


//Connect to Database first...
$db = new Database();

//Initiate the case with case ID
$caseID = $_GET['id'];

//Get the report :)
$report = new Report($caseID);
$report->print_pdf();

?>