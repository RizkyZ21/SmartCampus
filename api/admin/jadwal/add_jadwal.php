<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
require_once("../../config.php");

$conn = getOracleConnection();
if (!$conn) { echo json_encode(["success"=>false,"message"=>"Koneksi database gagal"]); exit; }

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) { echo json_encode(["success"=>false,"message"=>"Data tidak valid"]); exit; }

$sql = "
  INSERT INTO JADWAL_KULIAH (
    JADWAL_ID, MATKUL_ID, DOSEN_ID, RUANG_ID, HARI, JAM_MULAI, JAM_SELESAI, TAHUN_AJARAN, CREATED_AT
  ) VALUES (
    SEQ_JADWAL.NEXTVAL, :MATKUL_ID, :DOSEN_ID, :RUANG_ID, :HARI, :JAM_MULAI, :JAM_SELESAI, :TAHUN_AJARAN, SYSDATE
  )
";

$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":MATKUL_ID", $data["matkul_id"]);
oci_bind_by_name($stid, ":DOSEN_ID", $data["dosen_id"]);
oci_bind_by_name($stid, ":RUANG_ID", $data["ruang_id"]);
oci_bind_by_name($stid, ":HARI", $data["hari"]);
oci_bind_by_name($stid, ":JAM_MULAI", $data["jam_mulai"]);
oci_bind_by_name($stid, ":JAM_SELESAI", $data["jam_selesai"]);
oci_bind_by_name($stid, ":TAHUN_AJARAN", $data["tahun_ajaran"]);

$ok = oci_execute($stid);
if ($ok) {
  oci_commit($conn);
  echo json_encode(["success"=>true,"message"=>"Jadwal berhasil ditambahkan"]);
} else {
  $err = oci_error($stid);
  echo json_encode(["success"=>false,"message"=>"Gagal menambah jadwal: " . ($err["message"] ?? "")]);
}

oci_free_statement($stid);
oci_close($conn);
?>
