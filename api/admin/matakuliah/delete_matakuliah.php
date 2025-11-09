<?php
header("Content-Type: application/json");
require_once("../../config.php");

$conn = getOracleConnection();
$input = json_decode(file_get_contents("php://input"), true);

$sql = "DELETE FROM MATA_KULIAH WHERE MATKUL_ID = :id";
$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":id", $input["matkul_id"]);

if (oci_execute($stid))
  echo json_encode(["success" => true, "message" => "Mata kuliah berhasil dihapus"]);
else
  echo json_encode(["success" => false, "message" => "Gagal menghapus mata kuliah"]);

oci_free_statement($stid);
oci_close($conn);
?>
