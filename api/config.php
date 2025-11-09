<?php
function getOracleConnection() {
  $username = "uas";
  $password = "uas";
  $connectionString = "localhost/orcl"; // ganti sesuai service_name kamu
  $conn = oci_connect($username, $password, $connectionString, 'AL32UTF8');
  return $conn;
}
?>
