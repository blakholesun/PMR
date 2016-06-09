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

    $sql = "
    SELECT DISTINCT
    --Patient.LastName,
    --Patient.FirstName,
    --Patient.PatientId,
    visit_note.note_tstamp AS CreationDate,
    --CONVERT(datetime, visit_note.note_tstamp) AS CreationDate,
    --CAST(visit_note.note_tstamp AS datetime) AS CreationDate,
    --convert(date, visit_note.note_tstamp, 120) AS CreationDate,
    note_typ.note_typ_desc,
    visit_note.appr_flag,
    --visit_note.author_stkh_id,
    --author.user_last_name,
    --visit_note.appr_stkh_id,
    --approved.user_last_name,
    visit_note.doc_file_loc,
    --visit_note.external_document_link,
    --visit_note.trans_log_userid,
    --creator.user_last_name,
    visit_note.signed_stkh_id
    --signed.user_last_name

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
    --AND Patient.PatientId = '$patientID'
    AND (Patient.PatientId = '$patientID' OR Patient.PatientId2 = '$patientID' )
    --AND Patient.PatientId = '5218456'
    AND note_typ.note_typ = visit_note.note_typ
    ORDER BY CreationDate DESC
    ";

    // $query holds results of SQL query in unreadable format
    $query = mssql_query($sql);

    // Create array to hold reable results of the query
    $json = array();

    // Loop through rows in the result of the query
    // Each row is different document
    while($row = mssql_fetch_array($query)){
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
        $rowArray = array(
            //'FirstName' => $row[1],
            //'LastName' => $row[0],
            //'DocName' => $row[4],
            //'Author' => $row[6],
            // 'Author' => $rowone[0],
            //'CreatedBy' => $row[10],
            // 'CreatedBy' => $rowtwo[0],
            //'ApprovedBy' => $row[7],
            // 'ApprovedBy' => $rowthree[0],
            //'SignedBy' => $row[11],
            // 'SignedBy' => $rowfour[0],
            'Date'              => $mysqldate,
            'DocType'           => $row[1],
            'ApprovalStatus'    => $row[2],
            'FileName'          => $row[3],
            'Signed'            => $row[4]
            
        );
        array_push($json,$rowArray);
    }

    echo json_encode($json);

    /* Free statement and connection resources. */
    if (!$query) {
        die('Query failed.');
    }

    // Free the query result
    mssql_free_result($query);
?>