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
    mk.MATKUL_ID,
    mk.KODE_MATKUL,
    mk.NAMA_MATKUL,
    mk.SKS,
    d.NAMA_LENGKAP AS NAMA_DOSEN
FROM MATA_KULIAH mk
LEFT JOIN DOSEN d ON mk.DOSEN_ID = d.DOSEN_ID
WHERE mk.MATKUL_ID NOT IN (
    SELECT MATKUL_ID FROM NILAI WHERE MAHASISWA_ID = :mid
)
ORDER BY mk.SEMESTER, mk.NAMA_MATKUL
";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ":mid", $mahasiswa_id);
oci_execute($stmt);

$data = [];
while ($row = oci_fetch_assoc($stmt)) {
    $data[] = $row;
}

echo json_encode(["success" => true, "data" => $data]);
?>
