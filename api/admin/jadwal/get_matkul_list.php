<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
require_once("../../config.php");

$conn = getOracleConnection();
if (!$conn) { echo json_encode(["success"=>false,"message"=>"Koneksi database gagal"]); exit; }

$sql = "SELECT MATKUL_ID, KODE_MATKUL, NAMA_MATKUL FROM MATA_KULIAH ORDER BY NAMA_MATKUL";
$stid = oci_parse($conn, $sql);
oci_execute($stid);

$data = [];
while ($r = oci_fetch_assoc($stid)) {
  $data[] = ["ID"=>$r["MATKUL_ID"], "NAMA"=>($r["KODE_MATKUL"] ? $r["KODE_MATKUL"] . " - " . $r["NAMA_MATKUL"] : $r["NAMA_MATKUL"])];
}

echo json_encode(["success"=>true,"data"=>$data]);

oci_free_statement($stid);
oci_close($conn);
?>
