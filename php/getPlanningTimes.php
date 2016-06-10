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
SELECT
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
AND pt.PatientId = '5152463'
AND nsa.ObjectStatus NOT LIKE '%Deleted%'

UNION

SELECT
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
AND pt.PatientId = '5152463'
AND sa.ObjectStatus NOT LIKE '%Deleted%'

ORDER BY nsa.CreationDate DESC
";

$query = mssql_query($sql);


if(!mssql_num_rows($query)) {
  echo 'No records found.';
}else{
	$sequence = array();
	$tokens = array("ready for treatment"			=> false,
					"ready to show"					=> false,
					"ready for dose calculation" 	=> false,
					"ready for md contour" 			=> false, 
					"ct sim + call md"				=> false, 
					"consult"						=> false);
	$stack = array();
	while($row = mssql_fetch_array($query)){
		foreach ($tokens as $key => $value) {
			//echo strpos(strtolower($row[1]), $value);
			$lower = strtolower($row[1]);
			$pos = strpos($lower,$key);
			//echo "$lower ==== $key ----------------> $pos <br>";
			if (strpos(strtolower($row[1]), $key) !== false && !$tokens[$key] ) {
            	$tokens[$key] = true;
            	array_push($stack,$key);
            	$phpdate = strtotime($row[2]);
        		$mysqldate = date( 'M d Y H:i', $phpdate );
        		$rowArray = array(
            		'PatientId'         => $row[0],
            		'ActivityCode'		=> $row[1],
            		'CreationDate'    	=> $row[2] //$mysqldate           
        		);
        		array_push($sequence,$rowArray);
        	}
		}
    }

    $somekeys = array_keys($tokens);

    if ($stack !== $somekeys){
    	echo "<br>Sequence does not exit. Cannot build chart.";
    } else{
    	$json = array();
    	//TODO GET CONSECUTIVE TIMES
    	foreach($sequence as $key => $value) {
    		$sequence[$key]['CreationDate'];
    	}
    	echo json_encode(array('planTimes'=>$json));
    }

	
}

mssql_free_result($query);
?>