<?php
    header("Access-Control-Allow-Origin: *");
    //header("Access-Control-Allow-Origin: http://172.26.66.41:8006");

    // Store the POSTed filename in a variable, set path to those documents manually

    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $inputname = $request->FileName;
    
    // Get extension of input file (e.g doc or pdf), does not include period
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
        
        // Location where generated PDF will be stored temporarily until after served to user
        $ext_pdfpath = "http://172.26.66.41/devDocuments/robert/pdftemp/";
        $loc_pdfpath = "/var/www/devDocuments/robert/pdftemp/";

        // convert the file
        //echo  $loc_pdfpath . ' ' . $loc_docpath . $inputname;
        exec('/opt/libreoffice4.3/program/soffice.bin --writer --headless --convert-to pdf --nologo --outdir ' . $loc_pdfpath . ' ' . $loc_docpath . $inputname, $one, $two);
        //send the filename back
        echo $json = $ext_pdfpath.$outputname;
    } 
    // if the file is a PDF file, just give it to the user (do not call LibreOffice):
    else {
        //Send filename back
        echo $json = $ext_docpath.$inputname;
    }

?>