<?php

// get the HTTP method, path and body of the request
$method = $_SERVER;
$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
$input = json_decode(file_get_contents('php://input'),true);

/*echo print_r($method) . '<br>';
echo print_r($request). '<br>';
echo print_r($input). '<br>';*/

include('/usr/lib/cgi-bin/dev/robert/includes/config_PDO.php');

try {
  $dbh = new PDO(ARIA_DB, ARIA_USERNAME, ARIA_PASSWORD);
} catch (PDOException $e){
  print "Error! Could not connect to ARIA: " . $e->getMessage();
  die();
}

//echo "DB connected!";

include 'php/patientModel.php';
$pID = $request[1];
$patient = new Patient($dbh,$pID);

//check which function is required switch/case?
switch ($request[0]) {
	case 'getAllPatients':
		echo json_encode($patient->getAllPatients());
		break;
	case 'getPatientPhoto':
		echo $patient->getPatientPhoto();
		break;
	case 'getPlanningTimes':
		echo json_encode($patient->getPlanningTimes());
		break;
	case 'getPatientDocumentList':
		echo json_encode($patient->getPatientDocumentList());
		break;
	case 'getDocument':
		echo $patient->getDocument($input['FileName']);
		break;
	case 'getNewStart':
		echo json_encode($patient->getNewStart());
		break;
	case 'getSGAS':
		echo json_encode($patient->getSGAS());
		break;
	case 'getTreatmentInfo':
		echo json_encode($patient->getTreatmentInfo());
		break;
	case 'getHistologyInfo':
		echo json_encode($patient->getHistologyInfo());
		break;
	default:
		# code...
		break;
}

$dbh = null;

?>