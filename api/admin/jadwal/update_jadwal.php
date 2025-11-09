<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
require_once("../../config.php");

$conn = getOracleConnection();
if (!$conn) { echo json_encode(["success"=>false,"message"=>"Koneksi database gagal"]); exit; }

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || empty($data["jadwal_id"])) { echo json_encode(["success"=>false,"message"=>"Data tidak valid"]); exit; }

$sql = "
  UPDATE JADWAL_KULIAH
  SET MATKUL_ID = :MATKUL_ID,
      DOSEN_ID = :DOSEN_ID,
      RUANG_ID = :RUANG_ID,
      HARI = :HARI,
      JAM_MULAI = :JAM_MULAI,
      JAM_SELESAI = :JAM_SELESAI,
      TAHUN_AJARAN = :TAHUN_AJARAN,
      UPDATED_AT = SYSDATE
  WHERE JADWAL_ID = :JADWAL_ID
";

$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":MATKUL_ID", $data["matkul_id"]);
oci_bind_by_name($stid, ":DOSEN_ID", $data["dosen_id"]);
oci_bind_by_name($stid, ":RUANG_ID", $data["ruang_id"]);
oci_bind_by_name($stid, ":HARI", $data["hari"]);
oci_bind_by_name($stid, ":JAM_MULAI", $data["jam_mulai"]);
oci_bind_by_name($stid, ":JAM_SELESAI", $data["jam_selesai"]);
oci_bind_by_name($stid, ":TAHUN_AJARAN", $data["tahun_ajaran"]);
oci_bind_by_name($stid, ":JADWAL_ID", $data["jadwal_id"]);

$ok = oci_execute($stid);
if ($ok) {
  oci_commit($conn);
  echo json_encode(["success"=>true,"message"=>"Jadwal berhasil diperbarui"]);
} else {
  $err = oci_error($stid);
  echo json_encode(["success"=>false,"message"=>"Gagal memperbarui jadwal: " . ($err["message"] ?? "")]);
}

oci_free_statement($stid);
oci_close($conn);
?>
