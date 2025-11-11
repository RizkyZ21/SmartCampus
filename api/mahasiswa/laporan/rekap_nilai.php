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
    mk.KODE_MATKUL,
    mk.NAMA_MATKUL,
    n.NILAI_TUGAS,
    n.NILAI_UTS,
    n.NILAI_UAS,
    n.NILAI_AKHIR
FROM NILAI n
JOIN MATA_KULIAH mk ON n.MATKUL_ID = mk.MATKUL_ID
WHERE n.MAHASISWA_ID = :id
ORDER BY mk.NAMA_MATKUL
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
