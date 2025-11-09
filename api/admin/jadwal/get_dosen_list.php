<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
require_once("../../config.php");

$conn = getOracleConnection();
if (!$conn) { echo json_encode(["success"=>false,"message"=>"Koneksi database gagal"]); exit; }

$sql = "SELECT DOSEN_ID, NAMA_LENGKAP FROM DOSEN ORDER BY NAMA_LENGKAP";
$stid = oci_parse($conn, $sql);
oci_execute($stid);

$data = [];
while ($r = oci_fetch_assoc($stid)) {
  $data[] = ["ID"=>$r["DOSEN_ID"], "NAMA"=>$r["NAMA_LENGKAP"]];
}

echo json_encode(["success"=>true,"data"=>$data]);

oci_free_statement($stid);
oci_close($conn);
?>
