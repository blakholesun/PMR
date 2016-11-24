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
im.ImageId,
im.ImageType,
im.Status,
s.FileName,
im.CreationDate

FROM
Patient pt,
Image im,
ImageSlice ims,
Slice s


WHERE
pt.PatientSer = im.PatientSer
AND im.ImageSer = ims.ImageSer
AND s.SliceSer = ims.SliceSer
AND im.Status NOT LIKE '%Disposed%'
AND im.ImageType LIKE '%DRR%'
AND im.CreationDate >= DATEADD(day,-21,CONVERT(date,GETDATE()))
AND pt.PatientId = '2217482'

ORDER BY im.CreationDate DESC
";

$query = mssql_query($sql);


if(!mssql_num_rows($query)) {
	echo 'No records found.';
}else{
	while($row = mssql_fetch_array($query)){
		if ($row[5] == ''){
			echo 'No records found.';
		} else{
			$file = fopen("webdictionary.txt", "r") or die("Unable to open file!");
			$img = base64_encode($row[5]);
			echo $img;
		}
	}
}



mssql_free_result($query);

?>