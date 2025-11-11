<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
require_once(__DIR__ . '/../../config.php');

$conn = getOracleConnection();
oci_execute(oci_parse($conn, "ALTER SESSION SET CURRENT_SCHEMA=UAS"));

$data = json_decode(file_get_contents("php://input"), true);
$mahasiswa_id = $data["mahasiswa_id"] ?? null;

if (!$mahasiswa_id) {
    echo json_encode(["success" => false, "message" => "ID mahasiswa wajib dikirim."]);
    exit;
}

$sql = "
SELECT 
  TO_CHAR(a.TANGGAL, 'DD-MM-YYYY') AS TANGGAL,
  mk.NAMA_MATKUL,
  d.NAMA_LENGKAP AS NAMA_DOSEN,
  a.STATUS_KEHADIRAN,
  a.KETERANGAN
FROM ABSENSI a
JOIN JADWAL_KULIAH j ON a.JADWAL_ID = j.JADWAL_ID
JOIN MATA_KULIAH mk ON j.MATKUL_ID = mk.MATKUL_ID
JOIN DOSEN d ON j.DOSEN_ID = d.DOSEN_ID
WHERE a.MAHASISWA_ID = :id
ORDER BY a.TANGGAL DESC
";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ":id", $mahasiswa_id);
oci_execute($stmt);

$data = [];
while ($row = oci_fetch_assoc($stmt)) {
    $data[] = $row;
}

echo json_encode(["success" => true, "data" => $data]);
?>
