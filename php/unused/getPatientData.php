<?php

include('/usr/lib/cgi-bin/dev/robert/includes/config.php');

$link = mssql_connect(ARIA_DB, ARIA_USERNAME, ARIA_PASSWORD);

    //echo "Got a link<br>";

if (!$link) {
  die('Something went wrong while connecting to MSSQL');
}

$sql = "
USE variansystem;
SELECT DISTINCT
pt.PatientId,
pt.LastName,
pt.FirstName,
--pt.PatientSer,
ac.ActivityCode,
nsa.NonScheduledActivityCode,
--nsa.ObjectStatus,
nsa.DueDateTime,
doc.FirstName,
doc.LastName,
pdiag.TumorSize, 
pdiag.SummaryStage, 
pdiag.StageCriteria, 
diag.Description,
diag.DateStamp as DiagDate,
pt.DateOfBirth,
pt.Sex

FROM
Patient pt

INNER JOIN PatientDoctor pd ON pt.PatientSer = pd.PatientSer
INNER JOIN NonScheduledActivity nsa ON pt.PatientSer = nsa.PatientSer
INNER JOIN ActivityInstance aci ON nsa.ActivityInstanceSer = aci.ActivityInstanceSer
INNER JOIN Activity ac ON aci.ActivitySer = ac.ActivitySer
INNER JOIN Doctor doc ON doc.ResourceSer = pd.ResourceSer
INNER JOIN Diagnosis diag ON pt.PatientSer = diag.PatientSer 
INNER JOIN PrmryDiagnosis pdiag ON diag.DiagnosisSer = pdiag.DiagnosisSer 

WHERE
--DATENAME(weekday, nsa.DueDateTime) = 'Tuesday'
nsa.DueDateTime >= DATEADD(day, 0 ,CONVERT(date,GETDATE()))

--AND nsa.NonScheduledActivityCode LIKE '%Open%'
AND nsa.NonScheduledActivityCode LIKE '%Completed%'
AND ac.ActivityCode LIKE '%PMR%'
AND pd.PrimaryFlag = '1'
AND pd.OncologistFlag = '1'
AND diag.Description NOT LIKE '%ERROR%' 

ORDER BY doc.FirstName ASC, pt.LastName, DiagDate DESC";

$query = mssql_query($sql); //or die('Query Failed.');
//echo $query;
$arr = array();

if(!mssql_num_rows($query)) {
  echo 'No records found.';
}else{

  while($row = mssql_fetch_array($query)){
    $phpdate = strtotime($row[12]);
    $mysqldate = date( 'M d Y', $phpdate );
    $row[3] = str_replace("PMR", "", $row[3]);
    if ($row[10] == ""){
      $row[10] = "Stage Not Available";
    } 
    if ($row[11] == ""){
      $row[11] = "Not Available";
    }
    //str_replace("Malignant neoplasm", "CA - ", $row[10]);
    $rowArr = array(
      'PatientID'     => $row[0],
      'LastName'      => $row[1],
      'FirstName'     => $row[2],
      'PMR'           => $row[3],
      'NSAC'          => $row[4],
      'CreationDate'  => $row[5],
      'DocFirstName'  => $row[6],
      'DocLastName'   => $row[7],
      'TumorSize'     => $row[8],
      'SummaryStage'  => $row[9],
      'StageCriteria' => $row[10],
      'Diagnosis'     => $row[11],
      'DiagDate'      => $mysqldate,
      'DateOfBirth'   => $row[13],
      'Sex'           => $row[14]
    );
    array_push($arr,$rowArr);
  }

}
  # JSON-encode the response
  //header('Content-Type: application/json');
//$encoded = str_replace("\"StageCriteria\":null", 
//  "\"StageCriteria\":\"Stage Not Available\"", json_encode(array('list'=>$arr)));
//$encoded = str_replace("Malignant neoplasm of", "CA - ", $encoded);
//echo str_replace("null","\"Not Available\"",$encoded);
echo str_replace("Malignant neoplasm", "CA ",json_encode(array('list'=>$arr)));


mssql_free_result($query);
?>
