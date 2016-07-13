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
USE variansystem; 
SELECT DISTINCT 
pt.PatientId, 
ac.ActivityCode, 
nsa.DueDateTime 

FROM 
Patient pt, 
NonScheduledActivity nsa, 
ActivityInstance aci, 
Activity ac 

WHERE 
pt.PatientSer = nsa.PatientSer 
AND nsa.ActivityInstanceSer = aci.ActivityInstanceSer 
AND aci.ActivitySer = ac.ActivitySer 
AND ac.ActivityCode LIKE '%SGAS%'
--AND nsa.CreationDate >= DATEADD(day,-45,CONVERT(date,GETDATE())) 
--AND pt.PatientId = '5174277' 
AND pt.PatientId = '$patientID' 
AND nsa.ObjectStatus NOT LIKE '%Deleted%'

ORDER BY nsa.DueDateTime DESC
";

$query = mssql_query($sql);


if(!mssql_num_rows($query)) {
  $mysqldate = 'Unavailable';
}else{
	$row = mssql_fetch_array($query);
	$phpdate = strtotime($row[2]);
	$mysqldate = date( 'M d Y', $phpdate );
}

echo json_encode(array('DueDate'=>$mysqldate));

mssql_free_result($query);

?>