<?php
  include('/usr/lib/cgi-bin/dev/robert/includes/config.php');

  $conn = mssql_connect(DB, UNAME, PWD);

  if (!$conn){
    die('Connection is broke yo.')
  }

  $sql = 'SELECT name, owner FROM pet';
  $result = mssql_query($sql) or die('Query Failed.');

  $arr = array();
  if($result->num_rows > 0) {
    while($row = mssql_fetch_array($result)) {
      $arr[] = $row; 
    }
  }
  # JSON-encode the response
  //header('Content-Type: application/json');
  $json_response = json_encode(array('things'=>$arr));
 
  // # Return the response
  echo $json_response;

  mssql_free_result($result)
?>