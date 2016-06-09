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
    use variansystem;
    SELECT DISTINCT
    pt.LastName,
    pt.FirstName,
    ci.ConfigValue,
    co.CourseId,
    ps.PlanSetupId,
    convert(date, ps.CreationDate) AS CreationDate,
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
    --pt.PatientId = '$patientID'
    (pt.PatientId = '$patientID' OR pt.PatientId2 = '$patientID') 
    --pt.PatientId = '5197129'
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

    // $query holds results of SQL query in unreadable format
    $query = mssql_query($sql);

    // $mytype = mssql_field_type($query, 3);
    // print_r ($mytype);
    // echo "<br><br>";

    // Create array to hold reable results of the query
    $json = array();

    // Loop through rows in the result of the query
    $index = 0;
    while($row = mssql_fetch_array($query)){
        $json[$index] = $row;
        $index = $index + 1;
    }

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

        $coursedict[$c_count][$p_count]["date"] = $json[$index]["CreationDate"];

        $coursedict[$c_count][$p_count][$f_count]["name"] = $f_id;

        $index += 1;

        //-------------------------------------------------------------------------
        // This works for field names as dictionary keys
        // $c_id = $json[$index]["CourseId"];
        // $p_id = $json[$index]["PlanSetupId"];
        // $f_id = $json[$index]["RadiationId"];

        // if ($coursedict[$c_id] == NULL) {
        //     $p_count = 0;
        // }
        // if ($coursedict[$c_id][$p_id] == NULL) {
        //     $f_count = 0;
        // }

        // $coursedict[$c_id][$p_id]["name"] = $json[$index]["PlanSetupId"];
        // $coursedict[$c_id][$p_id]["intent"] = $json[$index]["Intent"];
        // $coursedict[$c_id][$p_id]["dose"] = $json[$index]["PrescribedDose"];
        // $coursedict[$c_id][$p_id]["nofractions"] = $json[$index]["NoFractions"];
        // $coursedict[$c_id][$p_id]["date"] = $json[$index]["CreationDate"];
        // $coursedict[$c_id][$p_id][$f_count] = $f_id;

        // $p_count += 1;
        // $f_count += 1;
        // $index += 1;
    }

    // Returns the json-ified dictionary to the calling jquery function
    // print_r ($coursedict);
    echo json_encode($coursedict);

    /* Free statement and connection resources. */
    if (!$query) {
        die('Query failed.');
    }

    // Free the query result
    mssql_free_result($query);
?>