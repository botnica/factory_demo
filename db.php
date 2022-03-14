<?php


/* connect to DB */
function dbConnect(){
  $conn = new mysqli("homestead.test", "homestead", "secret", "demo") or die("Connect failed: %s\n". $conn -> error);
  return $conn;
}

/* sisconnect */
function dbDisconnect($conn){
  $conn -> close();
}
?>
