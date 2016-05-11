<?php
  include('/var/www/PMR/config/config.php');

  $sql = 'SELECT name, owner FROM pet';
  $result = $mysqli->query($sql) or die($mysqli->error.__LINE__);

  $arr = array();
  if($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
      $arr[] = $row; 
    }
  }
  # JSON-encode the response
  //header('Content-Type: application/json');
  $json_response = json_encode(array('things'=>$arr));
 
  // # Return the response
  echo $json_response;
?>