<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
require_once("../../config.php");

$conn = getOracleConnection();
if (!$conn) { echo json_encode(["success"=>false,"message"=>"Koneksi database gagal"]); exit; }

$sql = "SELECT RUANG_ID, KODE_RUANG, NAMA_RUANG FROM RUANG_KELAS ORDER BY NAMA_RUANG";
$stid = oci_parse($conn, $sql);
oci_execute($stid);

$data = [];
while ($r = oci_fetch_assoc($stid)) {
  $label = $r["KODE_RUANG"] ? $r["KODE_RUANG"] . " - " . $r["NAMA_RUANG"] : $r["NAMA_RUANG"];
  $data[] = ["ID"=>$r["RUANG_ID"], "NAMA"=>$label];
}

echo json_encode(["success"=>true,"data"=>$data]);

oci_free_statement($stid);
oci_close($conn);
?>
