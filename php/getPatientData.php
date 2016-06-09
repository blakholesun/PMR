<?php

include('/usr/lib/cgi-bin/dev/robert/includes/config.php');

$link = mssql_connect(ARIA_DB, ARIA_USERNAME, ARIA_PASSWORD);

    //echo "Got a link<br>";

if (!$link) {
  die('Something went wrong while connecting to MSSQL');
}

$sql = "
USE variansystem;
SELECT
pt.PatientId,
pt.LastName,
pt.FirstName,
--pt.PatientSer,
--ac.ActivityCode,
nsa.NonScheduledActivityCode,
--nsa.ObjectStatus,
nsa.CreationDate,
doc.FirstName,
doc.LastName,
pdiag.TumorSize, 
pdiag.SummaryStage, 
pdiag.StageCriteria, 
diag.Description,
diag.DateStamp as DiagDate

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
nsa.CreationDate >= DATEADD(day,-14,CONVERT(date,GETDATE()))
AND
--nsa.NonScheduledActivityCode LIKE '%Open%'
nsa.NonScheduledActivityCode LIKE '%Completed%'
AND
ac.ActivityCode LIKE '%PMR%'
AND
pd.PrimaryFlag = '1'
AND
pd.OncologistFlag = '1'
AND
diag.Description NOT LIKE '%ERROR%' 

ORDER BY doc.FirstName ASC, pt.LastName, DiagDate DESC";

$query = mssql_query($sql); //or die('Query Failed.');
//echo $query;
$arr = array();

if(!mssql_num_rows($query)) {
  echo 'No records found.';
}else{

  while($row = mssql_fetch_array($query)){
    $phpdate = strtotime($row[11]);
    $mysqldate = date( 'M d Y', $phpdate );

    if ($row[9] == ""){
      $row[9] = "Stage Not Available";
    } elseif ($row[10] == ""){
      $row[10] = "Not Available";
    }
    //str_replace("Malignant neoplasm", "CA - ", $row[10]);
    $rowArr = array(
      'PatientID'     => $row[0],
      'LastName'      => $row[1],
      'FirstName'     => $row[2],
      'NSAC'          => $row[3],
      'CreationDate'  => $row[4],
      'DocFirstName'  => $row[5],
      'DocLastName'   => $row[6],
      'TumorSize'     => $row[7],
      'SummaryStage'  => $row[8],
      'StageCriteria' => $row[9],
      'Diagnosis'     => $row[10],
      'DiagDate'      => $mysqldate
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
