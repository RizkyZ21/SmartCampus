<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=utf-8");
require_once("../../config.php");

$conn = getOracleConnection();
if (!$conn) {
  echo json_encode(["success" => false, "message" => "Koneksi database gagal"]);
  exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$dosen_id = $data["dosen_id"] ?? null;
$jadwal_id = $data["jadwal_id"] ?? null;

if (!$dosen_id || !$jadwal_id) {
  echo json_encode(["success" => false, "message" => "Data tidak lengkap"]);
  exit;
}

// Cek apakah ada sesi OPEN aktif
$cek = oci_parse($conn, "SELECT COUNT(*) AS CNT FROM SESI_ABSENSI WHERE DOSEN_ID = :dosen_id AND JADWAL_ID = :jadwal_id AND STATUS = 'OPEN'");
oci_bind_by_name($cek, ":dosen_id", $dosen_id);
oci_bind_by_name($cek, ":jadwal_id", $jadwal_id);
oci_execute($cek);
$row = oci_fetch_assoc($cek);
if ($row["CNT"] > 0) {
  echo json_encode(["success" => false, "message" => "Sesi absensi sudah dibuka untuk jadwal ini"]);
  exit;
}

// Insert sesi baru
$sql = "INSERT INTO SESI_ABSENSI (SESI_ID, JADWAL_ID, DOSEN_ID, STATUS) 
        VALUES (SEQ_SESI_ABSENSI.NEXTVAL, :jadwal_id, :dosen_id, 'OPEN')";
$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":jadwal_id", $jadwal_id);
oci_bind_by_name($stid, ":dosen_id", $dosen_id);

if (oci_execute($stid)) {
  oci_commit($conn);
  echo json_encode(["success" => true, "message" => "Sesi absensi berhasil dibuka"]);
} else {
  echo json_encode(["success" => false, "message" => "Gagal membuka sesi absensi"]);
}

oci_free_statement($stid);
oci_close($conn);
?>
