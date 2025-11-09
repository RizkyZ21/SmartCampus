<?php
header("Content-Type: application/json");
require_once("../../config.php");

$conn = getOracleConnection();
$input = json_decode(file_get_contents("php://input"), true);

$sql = "UPDATE MATA_KULIAH SET 
          KODE_MATKUL = :kode,
          NAMA_MATKUL = :nama,
          SKS = :sks,
          SEMESTER = :semester,
          JENIS_MATKUL = :jenis,
          UPDATED_AT = SYSDATE
        WHERE MATKUL_ID = :id";

$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":id", $input["matkul_id"]);
oci_bind_by_name($stid, ":kode", $input["kode_matkul"]);
oci_bind_by_name($stid, ":nama", $input["nama_matkul"]);
oci_bind_by_name($stid, ":sks", $input["sks"]);
oci_bind_by_name($stid, ":semester", $input["semester"]);
oci_bind_by_name($stid, ":jenis", $input["jenis_matkul"]);

if (oci_execute($stid))
  echo json_encode(["success" => true, "message" => "Mata kuliah berhasil diupdate"]);
else
  echo json_encode(["success" => false, "message" => "Gagal update mata kuliah"]);

oci_free_statement($stid);
oci_close($conn);
?>
