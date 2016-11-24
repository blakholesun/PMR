<?php

include('/usr/lib/cgi-bin/dev/robert/includes/config.php');

$link = mssql_connect(ARIA_DB, ARIA_USERNAME, ARIA_PASSWORD);

if (!$link) {
	die('Something went wrong while connecting to MSSQL');
}

//$patientID = $_POST["patientID"];

$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$patientID = $request->patientID;
//$pass = $request->password;

//echo $patientID;

$sql = "
USE variansystem;
SELECT DISTINCT
pt.PatientId,
ph.Thumbnail

FROM
Patient pt

INNER JOIN Photo ph ON pt.PatientSer = ph.PatientSer

WHERE
pt.PatientId = '$patientID'
--pt.PatientId = 'AAAA1'
";

$query = mssql_query($sql);


if(!mssql_num_rows($query)) {
  //echo 'No records found.';
}else{
	$row = mssql_fetch_array($query);
}

if ($row[1] == ''){
	echo 'No records found.';
} else{
	$img = base64_encode($row[1]);
	echo $img;
}

mssql_free_result($query);

?>