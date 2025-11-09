<?php
header("Content-Type: application/json");
require_once("../../config.php");

$conn = getOracleConnection();
$input = json_decode(file_get_contents("php://input"), true);

$sql = "UPDATE MAHASISWA SET 
          NIM = :nim,
          NAMA_LENGKAP = :nama,
          EMAIL = :email,
          NO_TELEPON = :telp,
          ALAMAT = :alamat,
          SEMESTER = :semester,
          JENIS_KELAMIN = :jk,
          UPDATED_AT = SYSDATE
        WHERE MAHASISWA_ID = :id";

$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":id", $input["mahasiswa_id"]);
oci_bind_by_name($stid, ":nim", $input["nim"]);
oci_bind_by_name($stid, ":nama", $input["nama_lengkap"]);
oci_bind_by_name($stid, ":email", $input["email"]);
oci_bind_by_name($stid, ":telp", $input["no_telepon"]);
oci_bind_by_name($stid, ":alamat", $input["alamat"]);
oci_bind_by_name($stid, ":semester", $input["semester"]);
oci_bind_by_name($stid, ":jk", $input["jenis_kelamin"]);

if (oci_execute($stid))
  echo json_encode(["success" => true, "message" => "Mahasiswa berhasil diupdate"]);
else
  echo json_encode(["success" => false, "message" => "Gagal update mahasiswa"]);

oci_free_statement($stid);
oci_close($conn);
?>
