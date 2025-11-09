<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
require_once("../../config.php");

$conn = getOracleConnection();
if (!$conn) {
  echo json_encode(["success" => false, "message" => "Koneksi database gagal"]);
  exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || empty($data["matkul_id"])) {
  echo json_encode(["success" => false, "message" => "Data tidak valid"]);
  exit;
}

$sql = "DELETE FROM MATA_KULIAH WHERE MATKUL_ID = :id";
$parse = oci_parse($conn, $sql);
oci_bind_by_name($parse, ":id", $data["matkul_id"]);

$exec = oci_execute($parse);
if ($exec) {
  oci_commit($conn);
  echo json_encode(["success" => true, "message" => "Data mata kuliah berhasil dihapus"]);
} else {
  $err = oci_error($parse);
  echo json_encode(["success" => false, "message" => "Gagal menghapus data: " . $err["message"]]);
}

oci_free_statement($parse);
oci_close($conn);
?>
