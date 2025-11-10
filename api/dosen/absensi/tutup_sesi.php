<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=utf-8");
require_once("../../config.php");

$conn = getOracleConnection();
if (!$conn) {
  echo json_encode(["success" => false, "message" => "Koneksi database gagal"]);
  exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$sesi_id = $data["sesi_id"] ?? null;

if (!$sesi_id) {
  echo json_encode(["success" => false, "message" => "ID sesi tidak ditemukan"]);
  exit;
}

$sql = "UPDATE SESI_ABSENSI 
        SET STATUS = 'CLOSED', DITUTUP_PADA = SYSDATE 
        WHERE SESI_ID = :id";
$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":id", $sesi_id);

if (oci_execute($stid)) {
  oci_commit($conn);
  echo json_encode(["success" => true, "message" => "Sesi absensi berhasil ditutup"]);
} else {
  echo json_encode(["success" => false, "message" => "Gagal menutup sesi absensi"]);
}

oci_free_statement($stid);
oci_close($conn);
?>
