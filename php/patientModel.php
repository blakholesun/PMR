<?php

class Patient
{
    protected $ariadb;
    private $PatientID = '';

    public function __construct(PDO $ariadb, $PatientID)
    {
        $this->ariadb = $ariadb;
        $this->ariadb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->PatientID = $PatientID;
    }

    public function setPatientID($PatientID)
    {
    	$this->PatientID = $PatientID;
    }

    public function getAllPatients($request) 
    {
    	//echo print_r($request);
    	$sql = "SELECT DISTINCT
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
		nsa.DueDateTime >= :start
		AND nsa.DueDateTime <= :end
		--nsa.DueDateTime >= DATEADD(day, 0 ,CONVERT(date,GETDATE()))
		--nsa.DueDateTime <= DATEADD(day, +6 ,CONVERT(date,GETDATE()))

		AND nsa.NonScheduledActivityCode LIKE :status
		--AND nsa.NonScheduledActivityCode LIKE '%Completed%'
		AND ac.ActivityCode LIKE '%PMR%'
		AND nsa.ObjectStatus NOT LIKE '%Deleted%'
		AND pd.PrimaryFlag = '1'
		AND pd.OncologistFlag = '1'
		AND diag.Description NOT LIKE '%ERROR%' 

		ORDER BY doc.FirstName ASC, pt.LastName, DiagDate DESC";
		
		try {
			$sth = $this->ariadb->prepare($sql);
			$sth->bindParam(":start", date("Y-m-d H:i:s",strtotime($request['startDate'])), PDO::PARAM_STR);
			$sth->bindParam(":end", date("Y-m-d H:i:s",strtotime($request['endDate'])), PDO::PARAM_STR);
			$sth->bindParam(":status", $request['status'], PDO::PARAM_STR);
			$sth->execute();
		} catch (PDOException $e) {
			echo "Error! Could not query: " . $e->getMessage();
		}
		
		$arr = array();
		while($row = $sth->fetch(PDO::FETCH_NUM)){
		    $phpdate = strtotime($row[12]);
		    $mysqldate = date( 'M d Y', $phpdate );
		    $row[3] = str_replace("PMR", "", $row[3]);
		    if ($row[10] == ""){
		      	$row[10] = "Stage Not Available";
		    } 
		    if ($row[11] == ""){
		      	$row[11] = "Not Available";
		    }
		    $row[11] = str_replace("Malignant neoplasm", "CA - ", $row[11]);
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

		//$sth->execute();
		
        return array('list'=>$arr);
    }

    public function getPatientPhoto()
    {
    	$sql = " SELECT DISTINCT
		pt.PatientId,
		ph.Thumbnail

		FROM
		Patient pt

		INNER JOIN Photo ph ON pt.PatientSer = ph.PatientSer

		WHERE
		pt.PatientId = :pID
		--pt.PatientId = 'AAAA1'
		";
		try {
			$sth = $this->ariadb->prepare($sql);
			$sth->execute(array(':pID' => $this->PatientID));
		} catch (PDOException $e) {
			echo "Error! Could not query: " . $e->getMessage();
		}

		$row = $sth->fetch(PDO::FETCH_NUM);
		
		if ($row[1] == ''){
			return 'No records found.';
		} else{
			return base64_encode($row[1]);
		}
    }

    public function getPlanningTimes()
    {
    	$sql = "SELECT DISTINCT 
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
		AND pt.PatientId = :pID 
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
		AND pt.PatientId = :pID 
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
		AND pt.PatientId = :pID 
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
		AND pt.PatientId = :pID 
		AND ac.ActivityCode LIKE '%CT%' 
		AND sa.ObjectStatus NOT LIKE '%Deleted%' 
		AND samh.ObjectStatus NOT LIKE '%Deleted%' 
		AND samh.ScheduledActivityCode LIKE '%Manually Completed%' 

		ORDER BY Date DESC
		";

		try {
			$sth = $this->ariadb->prepare($sql);
			$sth->execute(array(':pID' => $this->PatientID));
		} catch (PDOException $e) {
			echo "Error! Could not query: " . $e->getMessage();
		}
		  
		// Create PlanningTime class
		spl_autoload_register(function ($PlanningTimes){
			include $PlanningTimes . '.php';
		});
		  
		$planTime = new PlanningTimes();

		//list of tokens to look for
		$tokens = array(	"new start",
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
		while($row = $sth->fetch(PDO::FETCH_NUM)){
		    // Map 
		    if (in_array($row[1], $events)){
		      $row[1] = "READY FOR DOSE CALCULATION";
		    }
		    if (strpos($row[1], "OFFSITE")){
		      $row[1]  = str_replace("CON", "CONSULT", $row[1]);
		    }
		    if (strpos($row[1], 'One Rx') !== false){
      			$row[1]  = "New Start";
    		}

		    $rowArray = array(
		    'PatientId'         => $row[0],
		    'ActivityCode'		=> $row[1],
			'CreationDate'    	=> $row[2]);
			$planTime->pushToOriginalSequence($rowArray);
		}

		//use planTime built in method to get the right sequence
		//throws false on failure
		$isSuccess = $planTime->generateSequence();
		$isValidTimes = $planTime->generatePlanTime();

		if ( $isSuccess && $isValidTimes){ 
			return array( 	'planTimes'=>$planTime->getPlanTimes(),
		    				'sequence'=>$planTime->getSequence());
		} else {
			return array( 'sequence'=>$planTime->getSequence());
		}
    }

    public function getPatientDocumentList()
    {
		$sql = "SELECT DISTINCT
	    visit_note.note_tstamp AS CreationDate,
	    note_typ.note_typ_desc,
	    visit_note.appr_flag,
	    visit_note.doc_file_loc,
	    visit_note.signed_stkh_id,
	    visit_note.appr_tstamp

	    FROM
	    variansystem.dbo.Patient Patient,
	    varianenm.dbo.pt pt,
	    varianenm.dbo.note_typ note_typ,
	    varianenm.dbo.visit_note visit_note 
	    INNER JOIN varianenm.dbo.userid author ON visit_note.author_stkh_id=author.stkh_id
	    LEFT JOIN varianenm.dbo.userid approved ON visit_note.appr_stkh_id=approved.stkh_id
	    INNER JOIN varianenm.dbo.userid creator ON visit_note.trans_log_userid=creator.stkh_id
	    LEFT JOIN varianenm.dbo.userid signed ON visit_note.signed_stkh_id=signed.stkh_id

	    WHERE 
	    pt.pt_id = visit_note.pt_id 
	    AND pt.patient_ser = Patient.PatientSer
	    AND (Patient.PatientId = :pID OR Patient.PatientId2 = :pID )
	    --AND Patient.PatientId = '5218456'
	    AND note_typ.note_typ = visit_note.note_typ
	    AND visit_note.valid_entry_ind = 'Y'
	    ORDER BY CreationDate DESC
	    ";

	    try {
			$sth = $this->ariadb->prepare($sql);
			$sth->execute(array(':pID' => $this->PatientID));
		} catch (PDOException $e) {
			echo "Error! Could not query: " . $e->getMessage();
		}

		$json = array();
		while($row = $sth->fetch(PDO::FETCH_NUM)){
	        if ($row[2] == "E") {
	            $row[2] = "Unapproved";
	        }
	        elseif ($row[2] == "A") {
	            $row[2] = "Approved";
	        }

	        if($row[4] == "") {
	            $row[4] = "Unsigned";
	        } else {
	            $row[4] = "Signed";
	        }
	        $phpdate = strtotime($row[0]);
	        $mysqldate = date( 'M d Y H:i', $phpdate );
	        $phpdate = strtotime($row[5]);
	        $apprdate = date( 'M d Y H:i', $phpdate );
	        $rowArray = array(
	            'Date'              => $mysqldate,
	            'DocType'           => $row[1],
	            'ApprovalStatus'    => $row[2],
	            'FileName'          => $row[3],
	            'Signed'            => $row[4],
	            'ApprovalTime'      => $apprdate
	        );
	        array_push($json,$rowArray);
	    }
    	return $json;
    }

    public function getDocument($inputname)
    {
    	$inputext = pathinfo($inputname, PATHINFO_EXTENSION);

	    // Manually identify the path to the files which will be converted/served
	    // Note that ext vs loc prefixes indicate use of URL or /var/www/
	    // When calling exec() command in php, use the 'loc' prefix variables
	    // When setting Content_Disposition header & calling readfile() use 'ext' prefix variables
	    $ext_docpath = "http://172.26.66.41/mount/VarianFILEDATA/";
	    $loc_docpath = "/var/www/mount/VarianFILEDATA/";

	    // if the file is not a PDF file, need to convert to pdf before giving to user:
	    if ($inputext != "pdf"){
	        // Output a file of same name, but with pdf extension
	        $outputname = basename($inputname, ".".$inputext).".pdf";
	        $loc_pdfpath = "/var/www/devDocuments/robert/pdftemp/";
	        $ext_pdfpath = "http://172.26.66.41/devDocuments/robert/pdftemp/";

	        if (!file_exists($loc_pdfpath.$outputname)){
	            // Convert doc to pdf
	            exec('/opt/libreoffice4.3/program/soffice.bin --writer --headless --convert-to pdf --nologo --outdir ' . $loc_pdfpath . ' ' . $loc_docpath . $inputname, $one, $two);
	        }
	        
	        //send the filename back
	        return $ext_pdfpath.$outputname;
	    } 
	    // if the file is a PDF file, just give it to the user (do not call LibreOffice):
	    else {
	        //Send filename back
	        return $ext_docpath.$inputname;
	    }
    }

    public function getNewStart()
    {
    	$sql = "SELECT DISTINCT TOP 1
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
		AND ScheduledActivity.ScheduledStartTime < DATEADD(day,0,CONVERT(date,GETDATE()))

		AND ActivityInstance.ActivitySer = Activity.ActivitySer
		AND Patient.PatientSer 				= ScheduledActivity.PatientSer         
		AND Patient.PatientId				= :pID
		AND ScheduledActivity.ObjectStatus 		!= 'Deleted' 
		AND (Activity.ActivityCode LIKE '%New Start%' OR Activity.ActivityCode LIKE '%One Rx%')

		ORDER BY CreationDate DESC
		";

		try {
			$sth = $this->ariadb->prepare($sql);
			$sth->execute(array(':pID' => $this->PatientID));
		} catch (PDOException $e) {
			echo "Error! Could not query: " . $e->getMessage();
		}

		if(!$row = $sth->fetch(PDO::FETCH_NUM)) {
  			$mysqldate = 'Unavailable';
		}else{
			$phpdate = strtotime($row[1]);
			$mysqldate = date( 'M d Y', $phpdate );
		}

		return array('startDate'=>$mysqldate);
    }

    public function getSGAS()
    {
    	$sql = "SELECT DISTINCT 
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
		AND pt.PatientId = :pID 
		AND nsa.ObjectStatus NOT LIKE '%Deleted%'

		ORDER BY nsa.DueDateTime DESC
		";

    	try {
			$sth = $this->ariadb->prepare($sql);
			$sth->execute(array(':pID' => $this->PatientID));
		} catch (PDOException $e) {
			echo "Error! Could not query: " . $e->getMessage();
		}

		if(!$row = $sth->fetch(PDO::FETCH_NUM)) {
  			$mysqldate = 'Unavailable';
		}else{
			$phpdate = strtotime($row[2]);
			$mysqldate = date( 'M d Y', $phpdate );
		}

		return array('DueDate'=>$mysqldate, 'Priority'=>$row[1]);
    }

    public function getTreatmentInfo()
    {

    	$sql = "SELECT DISTINCT
	    pt.LastName,
	    pt.FirstName,
	    ci.ConfigValue,
	    co.CourseId,
	    ps.PlanSetupId,
	    convert(date, ps.StatusDate) AS CreationDate,
	    ps.Intent,
	    co.ClinicalStatus,
	    ps.Status,
	    ps.StatusUserName,
	    rt.NoFractions,
	    rt.PrescribedDose,
	    rad.RadiationId,
	    radp.FieldDose

	    FROM
	    Patient pt,
	    Course co,
	    PlanSetup ps,
	    RTPlan rt,
	    Radiation rad,
	    ConfigurationItem ci,
	    RadiationRefPoint radp

	    WHERE
	    --pt.PatientId = :pID
	    (pt.PatientId = :pID OR pt.PatientId2 = :pID) 
	    --pt.PatientId = '5213748'
	    AND ps.Status IN ('TreatApproval', 'PlanApproval', 'Reviewed', 'Completed')
	    AND pt.PatientSer = co.PatientSer
	    AND radp.RTPlanSer = rt.RTPlanSer 
	    AND co.CourseSer = ps.CourseSer
	    AND ps.PlanSetupSer = rt.PlanSetupSer
	    AND ps.PlanSetupSer = rad.PlanSetupSer
	    AND ci.ConfigurationItemId = 'Dose'

	    ORDER BY
	    CreationDate DESC,
	    co.CourseId DESC,
	    ps.PlanSetupId
	    ";

    	try {
			$sth = $this->ariadb->prepare($sql);
			$sth->execute(array(':pID' => $this->PatientID));
		} catch (PDOException $e) {
			echo "Error! Could not query: " . $e->getMessage();
		}

		$json = array();
		$index = 0;

		$json = $sth->fetchAll(PDO::FETCH_ASSOC);
		//print_r($json);
	    $numrows = sizeof($json); // holds number of entries (i.e. number of fields)

	    // Loop variables:
	    $index = 0; // index of the loop, will count through # of rows from query
	    $f_count = 0; // count # of Fields in the current Treatment Plan
	    $p_count = 0; // count # of Treatment Plans in the current Course
	    $c_count = 0; // count # of Courses for the patient

	    $coursedict = array(); // array to hold the desired output
	    if ($numrows != 0) {
	        $coursedict["FirstName"] = $json[0]["FirstName"];
	        $coursedict["LastName"] = $json[0]["LastName"];
	        $coursedict["DoseUnits"] = $json[0]["ConfigValue"];
	    }
	    $initial = TRUE; // indicates if on initial loop (could just check if index is zero instead)
	    while($index < $numrows){
	        // Provides a dictionary with numeric keys (indexing similar to array)
	        // NOTE: THE SQL MUST HAVE ORDERED BY CLAUSE: ORDER BY COURSE THEN PLAN

	        // Names of current course/plan/field:
	        $c_id = $json[$index]["CourseId"];
	        $p_id = $json[$index]["PlanSetupId"];
	        $f_id = $json[$index]["RadiationId"];

	        // Determine which counters need to reset (i.e. if on new course or plan)
	        if (!$initial) {
	            if ($c_id == $json[$index-1]["CourseId"]) {
	                if ($p_id == $json[$index-1]["PlanSetupId"]) {
	                    $f_count+=1;
	                }
	                else {
	                    $p_count+=1;
	                    $f_count=0;
	                }
	            }
	            else {
	                $c_count+=1;
	                $p_count=0;
	                $f_count=0;
	            }
	        }
	        else {
	            $initial = FALSE;
	        }

	        // Assign desired values from DB into the dictionary. Dictionary may be
	        // indexed similarly to an array.
	        $coursedict[$c_count]["name"] = $json[$index]["CourseId"];
	        $coursedict[$c_count][$p_count]["name"] = $json[$index]["PlanSetupId"];
	        $coursedict[$c_count][$p_count]["intent"] = $json[$index]["Intent"];
	        $coursedict[$c_count][$p_count]["cstatus"] = $json[$index]["ClinicalStatus"];
	        $coursedict[$c_count][$p_count]["status"] = $json[$index]["Status"];
	        $coursedict[$c_count][$p_count]["status_user"] = $json[$index]["StatusUserName"];
	        if ($json[$index]["PrescribedDose"]==0){
	            $coursedict[$c_count][$p_count]["dose"] = $json[$index]["FieldDose"];
	        }else{
	            $coursedict[$c_count][$p_count]["dose"] = $json[$index]["PrescribedDose"];
	        }
	        $coursedict[$c_count][$p_count]["nofractions"] = $json[$index]["NoFractions"];

	        $coursedict[$c_count][$p_count]["date"] = date('M d Y', strtotime($json[$index]["CreationDate"]));

	        $coursedict[$c_count][$p_count][$f_count]["name"] = $f_id;

	        $index += 1;
	    }
		return $coursedict;
    }
}
