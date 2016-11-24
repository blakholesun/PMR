<?php

include('/usr/lib/cgi-bin/dev/robert/includes/config_PDO.php');

try {
  $dbh = new PDO(ARIA_DB, ARIA_USERNAME, ARIA_PASSWORD);
} catch (PDOException $e){
  print "Error!: " . $e->getMessage();
  die();
}

echo "success!"
    //echo "Got a dbh<br>";

?>