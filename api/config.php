<?php
function getOracleConnection() {
    $username = "uas";
    $password = "uas";
    $connectionString = "localhost/orcl";

    $conn = oci_connect($username, $password, $connectionString);
    if (!$conn) {
        $e = oci_error();
        throw new Exception("Gagal konek ke Oracle: " . $e['message']);
    }
    return $conn;
}
?>