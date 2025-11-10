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
$dosen_id = $data["dosen_id"] ?? null;

if (!$dosen_id) {
  echo json_encode(["success" => false, "message" => "ID dosen tidak ditemukan"]);
  exit;
}

$sql = "
SELECT s.SESI_ID, s.TANGGAL, s.STATUS, s.DIBUKA_PADA, s.DITUTUP_PADA,
       m.NAMA_MATKUL, j.HARI, j.JAM_MULAI, j.JAM_SELESAI
FROM SESI_ABSENSI s
JOIN JADWAL_KULIAH j ON s.JADWAL_ID = j.JADWAL_ID
JOIN MATA_KULIAH m ON j.MATKUL_ID = m.MATKUL_ID
WHERE s.DOSEN_ID = :id
ORDER BY s.TANGGAL DESC
";

$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":id", $dosen_id);
oci_execute($stid);

$result = [];
while ($row = oci_fetch_assoc($stid)) $result[] = $row;

echo json_encode(["success" => true, "data" => $result]);
oci_free_statement($stid);
oci_close($conn);
?>
