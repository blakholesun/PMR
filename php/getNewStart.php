<?php

include('/usr/lib/cgi-bin/dev/robert/includes/config.php');

$link = mssql_connect(ARIA_DB, ARIA_USERNAME, ARIA_PASSWORD);

    //echo "Got a link<br>";

if (!$link) {
	die('Something went wrong while connecting to MSSQL');
}

$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$patientID = $request->patientID;

$sql = "
SELECT DISTINCT TOP 1
Patient.PatientId,
ScheduledActivity.ScheduledStartTime AS CreationDate,
Activity.ActivityCode

FROM  
variansystem.dbo.Patient Patient,
variansystem.dbo.ScheduledActivity ScheduledActivity,
variansystem.dbo.ActivityInstance ActivityInstance,
variansystem.dbo.Activity Activity

WHERE     
ScheduledActivity.ActivityInstanceSer = ActivityInstance.ActivityInstanceSer
AND ActivityInstance.ActivitySer = Activity.ActivitySer
AND Patient.PatientSer 				= ScheduledActivity.PatientSer         
AND Patient.PatientId				= '$patientID'
AND ScheduledActivity.ObjectStatus 		!= 'Deleted' 
AND Activity.ActivityCode LIKE '%New Start%'

ORDER BY CreationDate DESC
";

$query = mssql_query($sql);


if(!mssql_num_rows($query)) {
  $mysqldate = 'Unavailable';;
}else{
	$row = mssql_fetch_array($query);
	$phpdate = strtotime($row[1]);
	$mysqldate = date( 'M d Y H:i', $phpdate );
}

echo json_encode(array('startDate'=>$mysqldate));

mssql_free_result($query);

?>