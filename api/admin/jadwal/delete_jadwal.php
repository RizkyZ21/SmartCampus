<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
require_once("../../config.php");

$conn = getOracleConnection();
if (!$conn) { echo json_encode(["success"=>false,"message"=>"Koneksi database gagal"]); exit; }

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || empty($data["jadwal_id"])) { echo json_encode(["success"=>false,"message"=>"Data tidak valid"]); exit; }

$sql = "DELETE FROM JADWAL_KULIAH WHERE JADWAL_ID = :JADWAL_ID";
$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":JADWAL_ID", $data["jadwal_id"]);

$ok = oci_execute($stid);
if ($ok) {
  oci_commit($conn);
  echo json_encode(["success"=>true,"message"=>"Jadwal berhasil dihapus"]);
} else {
  $err = oci_error($stid);
  echo json_encode(["success"=>false,"message"=>"Gagal menghapus jadwal: " . ($err["message"] ?? "")]);
}

oci_free_statement($stid);
oci_close($conn);
?>
