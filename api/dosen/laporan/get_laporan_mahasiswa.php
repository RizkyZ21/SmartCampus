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
$matkul_id = $data["matkul_id"] ?? "";

if (empty($matkul_id)) {
    echo json_encode(["success" => false, "message" => "MATKUL_ID wajib dikirim"]);
    exit;
}

$sql = "
SELECT 
  m.MAHASISWA_ID,
  m.NIM,
  m.NAMA_LENGKAP,
  SUM(CASE WHEN a.STATUS_KEHADIRAN = 'Hadir' THEN 1 ELSE 0 END) AS HADIR,
  SUM(CASE WHEN a.STATUS_KEHADIRAN = 'Izin' THEN 1 ELSE 0 END) AS IZIN,
  SUM(CASE WHEN a.STATUS_KEHADIRAN = 'Sakit' THEN 1 ELSE 0 END) AS SAKIT,
  SUM(CASE WHEN a.STATUS_KEHADIRAN = 'Alpa' THEN 1 ELSE 0 END) AS ALPA,
  NVL(n.NILAI_AKHIR, 0) AS NILAI_AKHIR,
  NVL(n.GRADE, '-') AS GRADE
FROM MAHASISWA m
LEFT JOIN NILAI n ON m.MAHASISWA_ID = n.MAHASISWA_ID AND n.MATKUL_ID = :matkul_id
LEFT JOIN JADWAL_KULIAH j ON j.MATKUL_ID = :matkul_id
LEFT JOIN ABSENSI a ON a.MAHASISWA_ID = m.MAHASISWA_ID AND a.JADWAL_ID = j.JADWAL_ID
GROUP BY m.MAHASISWA_ID, m.NIM, m.NAMA_LENGKAP, n.NILAI_AKHIR, n.GRADE
ORDER BY m.NAMA_LENGKAP";

$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":matkul_id", $matkul_id);
oci_execute($stid);

$rows = [];
while ($r = oci_fetch_assoc($stid)) {
    $rows[] = $r;
}

echo json_encode(["success" => true, "data" => $rows]);
oci_free_statement($stid);
oci_close($conn);
?>
