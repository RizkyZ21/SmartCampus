<?php
header("Content-Type: application/json");
require_once("../../config.php");

$conn = getOracleConnection();
$input = json_decode(file_get_contents("php://input"), true);

$sql = "INSERT INTO MAHASISWA 
        (NIM, NAMA_LENGKAP, EMAIL, NO_TELEPON, ALAMAT, SEMESTER, JENIS_KELAMIN, CREATED_AT)
        VALUES (:nim, :nama, :email, :telp, :alamat, :semester, :jk, SYSDATE)";

$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":nim", $input["nim"]);
oci_bind_by_name($stid, ":nama", $input["nama_lengkap"]);
oci_bind_by_name($stid, ":email", $input["email"]);
oci_bind_by_name($stid, ":telp", $input["no_telepon"]);
oci_bind_by_name($stid, ":alamat", $input["alamat"]);
oci_bind_by_name($stid, ":semester", $input["semester"]);
oci_bind_by_name($stid, ":jk", $input["jenis_kelamin"]);

if (oci_execute($stid))
  echo json_encode(["success" => true, "message" => "Mahasiswa berhasil ditambahkan"]);
else
  echo json_encode(["success" => false, "message" => "Gagal menambah mahasiswa"]);

oci_free_statement($stid);
oci_close($conn);
?>
