<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
require_once(__DIR__ . '/../../config.php');

$conn = getOracleConnection();
oci_execute(oci_parse($conn, "ALTER SESSION SET CURRENT_SCHEMA=UAS"));

$data = json_decode(file_get_contents("php://input"), true);
$mahasiswa_id = $data["mahasiswa_id"] ?? null;

if (!$mahasiswa_id) {
    echo json_encode(["success" => false, "message" => "ID Mahasiswa tidak ditemukan."]);
    exit;
}

$sql = "
SELECT 
    sa.SESI_ID,
    sa.JADWAL_ID,
    sa.TANGGAL,
    sa.STATUS,
    mk.NAMA_MATKUL,
    d.NAMA_LENGKAP AS NAMA_DOSEN,
    r.NAMA_RUANG
FROM SESI_ABSENSI sa
JOIN JADWAL_KULIAH jk ON sa.JADWAL_ID = jk.JADWAL_ID
JOIN MATA_KULIAH mk ON jk.MATKUL_ID = mk.MATKUL_ID
JOIN DOSEN d ON jk.DOSEN_ID = d.DOSEN_ID
LEFT JOIN RUANG_KELAS r ON jk.RUANG_ID = r.RUANG_ID
WHERE sa.STATUS = 'OPEN'
AND mk.MATKUL_ID IN (
    SELECT MATKUL_ID FROM NILAI WHERE MAHASISWA_ID = :mid
)
ORDER BY sa.DIBUKA_PADA DESC
";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ":mid", $mahasiswa_id);
oci_execute($stmt);

$sessions = [];
while ($row = oci_fetch_assoc($stmt)) {
    $sessions[] = $row;
}

if (empty($sessions)) {
    echo json_encode(["success" => false, "message" => "Tidak ada sesi absensi aktif untuk Anda."]);
} else {
    echo json_encode(["success" => true, "data" => $sessions]);
}
?>
