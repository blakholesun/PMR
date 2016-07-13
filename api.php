<?php

// get the HTTP method, path and body of the request
$method = $_SERVER;
$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
$input = json_decode(file_get_contents('php://input'),true);

echo print_r($method) . '<br>';
echo print_r($request). '<br>';
echo print_r($input). '<br>';

include('/usr/lib/cgi-bin/dev/robert/includes/config_PDO.php');

try {
  $dbh = new PDO(ARIA_DB, ARIA_USERNAME, ARIA_PASSWORD);
} catch (PDOException $e){
  print "Error! Could not connect to ARIA: " . $e->getMessage();
  die();
}

echo "DB connected!";

include 'php/patientModel.php';

$patient = new Patient($dbh,$input['patientID']);

//check which function is required switch/case?

//echo json_encode($patient->getAllPatients());

//echo $patient->getPatientPhoto();

//echo json_encode($patient->getPlanningTimes());

//echo json_encode($patient->getPatientDocumentList());

//echo json_encode($patient->getNewStart());

//echo json_encode($patient->getSGAS());

//echo json_encode($patient->getTreatmentInfo());
$dbh = null;

?>