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
nsa.DueDateTime as Date 

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
AND ac.ActivityCode LIKE '%L RECEIVED%' 

UNION 

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
AND ac.ActivityCode NOT LIKE '%CT%' 
AND ac.ActivityCode NOT LIKE '%CONSULT%' 
AND ac.ActivityCode NOT LIKE '%L RECEIVED%' 

UNION 

SELECT DISTINCT 
pt.PatientId, 
ac.ActivityCode, 
sa.ScheduledStartTime as CreationDate 

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
AND ac.ActivityCode NOT LIKE '%CT%' 
AND ac.ActivityCode NOT LIKE '%Nursing%' 

UNION 

SELECT DISTINCT 
pt.PatientId, 
ac.ActivityCode, 
samh.HstryDateTime 

FROM 
Patient pt, 
ScheduledActivity sa, 
ScheduledActivityMH samh, 
ActivityInstance aci, 
Activity ac 

WHERE 
pt.PatientSer = sa.PatientSer 
AND sa.ActivityInstanceSer = aci.ActivityInstanceSer 
AND samh.ActivityInstanceSer = aci.ActivityInstanceSer 
AND aci.ActivitySer = ac.ActivitySer 
--AND sa.CreationDate >= DATEADD(day,-45,CONVERT(date,GETDATE())) 
--AND pt.PatientId = '1138005' 
AND pt.PatientId = '$patientID' 
AND ac.ActivityCode LIKE '%CT%' 
AND sa.ObjectStatus NOT LIKE '%Deleted%' 
AND samh.ObjectStatus NOT LIKE '%Deleted%' 
AND samh.ScheduledActivityCode LIKE '%Manually Completed%' 

ORDER BY Date DESC
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
	$tokens = array("new start",
                  "ready for treatment",
                  //"ready to show",
                  "ready for dose calculation",
                  "ready for md contour", 
                  "ct sim",
                  "consult",
                  "l received");

  // set tokens in planTime
  $planTime->setTokens($tokens);

  // set filetr for doseclac
  $events = array("READY FOR ELECTRON PLANNING", "READY FOR IMRT PLANNING",
   "READY FOR STEREOTACTIC PLANNING", "READY FOR TBI CALCULATION");

  //add all query results to the planTime class
	while($row = mssql_fetch_array($query)){
    // Map 
    if (in_array($row[1], $events)){
      $row[1] = "READY FOR DOSE CALCULATION";
    }
    if (strpos($row[1], 'OFFSITE') !== false){
      $row[1]  = str_replace("CON", "CONSULT", $row[1]);
    }
    if (strpos($row[1], 'One Rx') !== false){
      $row[1]  = "New Start";
    }
/*    if (strpos($row[1], 'RECEIVED') !== false){
      $row[2] = substr($row[2], 0,11) . " 01:00AM";
      //echo $row[2];
    }*/

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