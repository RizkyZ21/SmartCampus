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
if (!$data) {
  echo json_encode(["success" => false, "message" => "Data tidak valid"]);
  exit;
}

$sql = "
  INSERT INTO MATA_KULIAH (
    KODE_MATKUL, NAMA_MATKUL, SKS, SEMESTER, DOSEN_ID, 
    JENIS_MATKUL, DESKRIPSI, CREATED_AT
  ) VALUES (
    :kode, :nama, :sks, :semester, :dosen_id, 
    :jenis, :deskripsi, SYSDATE
  )
";

$parse = oci_parse($conn, $sql);
oci_bind_by_name($parse, ":kode", $data["kode_matkul"]);
oci_bind_by_name($parse, ":nama", $data["nama_matkul"]);
oci_bind_by_name($parse, ":sks", $data["sks"]);
oci_bind_by_name($parse, ":semester", $data["semester"]);
oci_bind_by_name($parse, ":dosen_id", $data["dosen_id"]);
oci_bind_by_name($parse, ":jenis", $data["jenis_matkul"]);
oci_bind_by_name($parse, ":deskripsi", $data["deskripsi"]);

$exec = oci_execute($parse);
if ($exec) {
  oci_commit($conn);
  echo json_encode(["success" => true, "message" => "Mata kuliah berhasil ditambahkan"]);
} else {
  $err = oci_error($parse);
  echo json_encode(["success" => false, "message" => "Gagal menambah data: " . $err["message"]]);
}

oci_free_statement($parse);
oci_close($conn);
?>
