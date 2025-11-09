<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
require_once("../../config.php");

$conn = getOracleConnection();
if (!$conn) {
  echo json_encode(["success" => false, "message" => "Koneksi database gagal"]);
  exit;
}

$sql = "SELECT DOSEN_ID, NAMA_LENGKAP FROM DOSEN ORDER BY NAMA_LENGKAP ASC";
$parse = oci_parse($conn, $sql);
oci_execute($parse);

$data = [];
while ($row = oci_fetch_assoc($parse)) {
  $data[] = $row;
}

echo json_encode(["success" => true, "data" => $data]);

oci_free_statement($parse);
oci_close($conn);
?>
