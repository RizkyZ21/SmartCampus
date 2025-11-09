<?php
header("Content-Type: application/json");
require_once("../../config.php");

$conn = getOracleConnection();

$sql = "SELECT * FROM MAHASISWA ORDER BY MAHASISWA_ID";
$stid = oci_parse($conn, $sql);
oci_execute($stid);

$data = [];
while ($row = oci_fetch_assoc($stid)) $data[] = $row;

echo json_encode(["success" => true, "data" => $data]);
oci_free_statement($stid);
oci_close($conn);
?>
