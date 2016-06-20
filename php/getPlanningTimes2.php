<?php

include('/usr/lib/cgi-bin/dev/robert/includes/config.php');

$link = mssql_connect(ARIA_DB, ARIA_USERNAME, ARIA_PASSWORD);

    //echo "Got a link<br>";

$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$patientID = $request->patientID;

if (!$link) {
  die('Something went wrong while connecting to MSSQL');
}

$sql = "

USE variansystem;
SELECT DISTINCT
pt.PatientId,
ac.ActivityCode,
nsa.CreationDate

FROM
Patient pt,
NonScheduledActivity nsa,
ActivityInstance aci,
Activity ac

WHERE
pt.PatientSer = nsa.PatientSer
AND nsa.ActivityInstanceSer = aci.ActivityInstanceSer
AND aci.ActivitySer = ac.ActivitySer
--AND nsa.CreationDate >= DATEADD(day,-45,CONVERT(date,GETDATE()))
--AND pt.PatientId = '1138005'
AND pt.PatientId = '$patientID'
AND nsa.ObjectStatus NOT LIKE '%Deleted%'

UNION

SELECT DISTINCT
pt.PatientId,
ac.ActivityCode,
sa.CreationDate

FROM
Patient pt,
ScheduledActivity sa,
ActivityInstance aci,
Activity ac

WHERE
pt.PatientSer = sa.PatientSer
AND sa.ActivityInstanceSer = aci.ActivityInstanceSer
AND aci.ActivitySer = ac.ActivitySer
--AND sa.CreationDate >= DATEADD(day,-45,CONVERT(date,GETDATE()))
--AND pt.PatientId = '1138005'
AND pt.PatientId = '$patientID'
AND sa.ObjectStatus NOT LIKE '%Deleted%'

ORDER BY nsa.CreationDate DESC
";

$query = mssql_query($sql);


if(!mssql_num_rows($query)) {
  echo 'No records found.';
}else{

  // Create PlanningTime class
  spl_autoload_register(function ($PlanningTimes){
    include $PlanningTimes . '.php';
  });
  $planTime = new PlanningTimes();

  //list of tokens to look for
	$tokens = array("ready for treatment",
                  //"ready to show",
                  "ready for dose calculation",
                  "ready for md contour", 
                  "ct sim",
                  "consult");

  // set tokens in planTime
  $planTime->setTokens($tokens);

  // set filetr for daseclac
  $events = array("READY FOR ELECTRON PLANNING", "READY FOR IMRT PLANNING",
   "READY FOR STEREOTACTIC PLANNING", "READY FOR TBI CALCULATION");

  //add all query results to the planTime class
	while($row = mssql_fetch_array($query)){
    // Map 
    if (in_array($row[1], $events)){
      $row[1] = "READY FOR DOSE CALCULATION";
    }

    $rowArray = array(
    'PatientId'         => $row[0],
    'ActivityCode'		  => $row[1],
    'CreationDate'    	=> $row[2]);
    $planTime->pushToOriginalSequence($rowArray);
  }

  //use planTime built in method to get the right sequence
  //throws false on failure
  $isSuccess = $planTime->generateSequence();
  $isValidTimes = $planTime->generatePlanTime();

  if ( $isSuccess && $isValidTimes){ 
    echo json_encode(array( 'planTimes'=>$planTime->getPlanTimes(),
                            'sequence'=>$planTime->getSequence()));
  } else {
    echo json_encode(array( 'sequence'=>$planTime->getSequence()));
  }
}

mssql_free_result($query);
?>