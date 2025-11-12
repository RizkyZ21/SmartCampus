<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
require_once(__DIR__ . '/../../config.php');

$conn = getOracleConnection();
if (!$conn) {
    echo json_encode(["success" => false, "message" => "Koneksi database gagal."]);
    exit;
}

oci_execute(oci_parse($conn, "ALTER SESSION SET CURRENT_SCHEMA=UAS"));

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Gunakan metode POST."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$mahasiswa_id = $data["mahasiswa_id"] ?? null;

if (!$mahasiswa_id) {
    echo json_encode(["success" => false, "message" => "ID Mahasiswa wajib dikirim."]);
    exit;
}

$sql = "
SELECT 
    j.JADWAL_ID,
    mk.KODE_MATKUL,
    mk.NAMA_MATKUL,
    mk.SKS,
    d.NAMA_LENGKAP AS DOSEN,
    j.HARI,
    j.JAM_MULAI,
    j.JAM_SELESAI,
    rk.NAMA_RUANG AS RUANGAN
FROM JADWAL_KULIAH j
JOIN MATA_KULIAH mk ON j.MATKUL_ID = mk.MATKUL_ID
JOIN DOSEN d ON j.DOSEN_ID = d.DOSEN_ID
JOIN RUANG_KELAS rk ON j.RUANG_ID = rk.RUANG_ID
WHERE mk.MATKUL_ID IN (
    SELECT MATKUL_ID FROM NILAI WHERE MAHASISWA_ID = :id
)
ORDER BY j.HARI, j.JAM_MULAI
";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ":id", $mahasiswa_id);
oci_execute($stmt);

$data = [];
while ($row = oci_fetch_assoc($stmt)) {
    $data[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $data
]);
?>
