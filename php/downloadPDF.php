<?php
    header("Access-Control-Allow-Origin: *");
    //header("Access-Control-Allow-Origin: http://172.26.66.41:8006");

    // Store the POSTed filename in a variable, set path to those documents manually

    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    //$inputname = $request->filename;
    $inputname = "719133.doc";
    //$inputname = $_POST['filename'];
    // Get extension of input file (e.g doc or pdf), does not include period
    $inputext = pathinfo($inputname, PATHINFO_EXTENSION);
    //echo $inputext;
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
        
        // Location where generated PDF will be stored temporarily until after served to user
        $ext_pdfpath = "http://172.26.66.41/devDocuments/robert/pdftemp/";
        $loc_pdfpath = "/var/www/devDocuments/robert/pdftemp/";

        // convert the file
        echo  $loc_pdfpath . ' ' . $loc_docpath . $inputname;
        exec('/opt/libreoffice4.3/program/soffice.bin --writer --headless --convert-to pdf --nologo --outdir ' . $loc_pdfpath . ' ' . $loc_docpath . $inputname);
        //send the filename back
        //echo $json = $ext_pdfpath.$outputname;
        
        //echo str_replace('\\/', '/', json_encode($json));

        // download the file
        // header("Content-Disposition: attachment; filename=\"".basename($ext_pdfpath.$outputname)."\"");
        // readfile($ext_pdfpath.$outputname);

        // OR open the file in a new tab
        //header("Content-type: application/pdf");
        //header("Content-Disposition: inline; filename=\"".basename($ext_pdfpath.$outputname)."\"");
        //readfile($ext_pdfpath.$outputname);

        // remove the temporary PDF file after serving
        //exec('rm ' . $loc_pdfpath . $outputname, $output, $return);

        // if returning via json (ajax post), uncomment the following 3 lines:
        // $json = array();
        // $json = array('a'=>$output, 'b'=>$return);
        // echo json_encode($json);
    } 
    // if the file is a PDF file, just give it to the user (do not call LibreOffice):
    else {
        // download the file
        // header("Content-Disposition: attachment; filename=\"".basename($ext_docpath.$inputname)."\"");
        // readfile($ext_docpath.$inputname);

        // OR open the file in a new tab
        /*header("Content-type: application/pdf");
        header("Content-Disposition: inline; filename=\"".basename($ext_docpath.$inputname)."\"");
        readfile($ext_docpath.$inputname);*/

        $json = $ext_docpath.$inputname;
        echo str_replace('\\/', '/', json_encode($json));
    }


    // Simplest way to return pdf version of file (hardcoded paths & files)
    // header("Access-Control-Allow-Origin: http://172.26.66.41:8006");

    // $filepath = "http://172.26.66.41/devDocuments/logan/temp/test.pdf";

    // exec('/opt/libreoffice4.3/program/soffice.bin --writer --headless --convert-to pdf --nologo --outdir /var/www/devDocuments/logan/temp/ /var/www/devDocuments/logan/mytest/test.docx', $output, $return);

    // header("Content-Disposition: attachment; filename=\"".basename($filepath)."\"");

    // readfile($filepath);

    // // exec('rm /var/www/devDocuments/logan/mytest/test.pdf', $output, $return);
?>