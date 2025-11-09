<?php
header("Content-Type: application/json");
require_once("../../config.php");

$conn = getOracleConnection();
$input = json_decode(file_get_contents("php://input"), true);

$sql = "INSERT INTO MATA_KULIAH 
        (KODE_MATKUL, NAMA_MATKUL, SKS, SEMESTER, JENIS_MATKUL, CREATED_AT)
        VALUES (:kode, :nama, :sks, :semester, :jenis, SYSDATE)";

$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":kode", $input["kode_matkul"]);
oci_bind_by_name($stid, ":nama", $input["nama_matkul"]);
oci_bind_by_name($stid, ":sks", $input["sks"]);
oci_bind_by_name($stid, ":semester", $input["semester"]);
oci_bind_by_name($stid, ":jenis", $input["jenis_matkul"]);

if (oci_execute($stid))
  echo json_encode(["success" => true, "message" => "Mata kuliah berhasil ditambahkan"]);
else
  echo json_encode(["success" => false, "message" => "Gagal menambah mata kuliah"]);

oci_free_statement($stid);
oci_close($conn);
?>
