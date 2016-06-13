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
--AND pt.PatientId = '1138005'
AND pt.PatientId = '$patientID'
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
--AND pt.PatientId = '1138005'
AND pt.PatientId = '$patientID'
AND sa.ObjectStatus NOT LIKE '%Deleted%'

ORDER BY nsa.CreationDate DESC
";

$query = mssql_query($sql);


if(!mssql_num_rows($query)) {
  echo 'No records found.';
}else{
	$sequence = array();

    //list of tokens to look for
	$tokens = array("ready for treatment"			=> false,
       //"ready to show"					=> false,
       "ready for dose calculation" 	=> false,
       "ready for md contour" 			=> false, 
       "ct sim"				=> false );
      // "consult"						=> false);

    //create a stack that we will push in order sequence to
	$stack = array();
	while($row = mssql_fetch_array($query)){
		foreach ($tokens as $key => $value) {
			//echo strpos(strtolower($row[1]), $value);
			/*$lower = strtolower($row[1]);
			$pos = strpos($lower,$key);
			echo "$lower ==== $key ----------------> $pos <br>";*/

            // If the token exists in the field and it is not already present push to 
            // stack and push to sequence array
			if (strpos(strtolower($row[1]), $key) !== false && !$tokens[$key] ) {
               $tokens[$key] = true;
               array_push($stack,$key);
                //echo $row[2] ."<br>";
               $timestamp = strtotime($row[2]);
               $dt = new DateTime();
               $dt->setTimestamp($timestamp);
               //echo $dt->getTimestamp() ."<br>";
               $rowArray = array(
                  'PatientId'         => $row[0],
                  'ActivityCode'		=> $row[1],
                  'CreationDate'    	=> $dt
                  );
               array_push($sequence,$rowArray);
           }
       }
   }

    // Compare stack to token sequence, if they are the same then we can generate a chart
   $somekeys = array_keys($tokens);
    //print_r($sequence);
   if ($stack !== $somekeys){
       echo "Sequence does not exit. Cannot build chart.";
       print_r($sequence);
   } else{
       $time = array();
    	//TODO GET CONSECUTIVE TIMES
       $keys = array_keys($sequence);
       for($i = 1; $i < count($keys); $i++) {
        $datetime1 = $sequence[$keys[$i]]['CreationDate'];
        $datetime0 = $sequence[$keys[$i-1]]['CreationDate'];
        //$hours = abs($datetime1->getTimestamp()-$datetime0->getTimestamp())/3600;
        $interval = $datetime1->diff($datetime0);
        $days = $interval->days;
        //$hours = ($interval->days)*24;
        //echo $hours;
        //Remove weekends
        $period = new DatePeriod($datetime1, new DateInterval('P1D'), $datetime0);

        // best stored as array, so you can add more than one
        $holidays = array('2012-09-07');

        foreach($period as $dt) {
            $curr = $dt->format('D');

            // for the updated question
            if (in_array($dt->format('Y-m-d'), $holidays)) {
                $days--;
            }

            // substract if Saturday or Sunday
            if ($curr == 'Sat' || $curr == 'Sun') {
                $days--;
            }
        }
            array_push($time, $days);
        }  
    echo json_encode(array('planTimes'=>$time));

    }


}

mssql_free_result($query);
?>