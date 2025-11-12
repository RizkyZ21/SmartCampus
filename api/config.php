<?php
function getOracleConnection() {
  $username = "uas";
  $password = "uas";
  $connectionString = "localhost/orcl";
  $conn = oci_connect($username, $password, $connectionString, 'AL32UTF8');
  return $conn;
}
?>
