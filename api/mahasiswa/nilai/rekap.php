<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
require_once(__DIR__ . '/../../config.php');

$conn = getOracleConnection();
if (!$conn) {
    echo json_encode(["success" => false, "message" => "Koneksi database gagal"]);
    exit;
}

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
    NVL(n.NILAI_TUGAS, 0) AS NILAI_TUGAS,
    NVL(n.NILAI_UTS, 0) AS NILAI_UTS,
    NVL(n.NILAI_UAS, 0) AS NILAI_UAS,
    NVL(n.NILAI_AKHIR, 0) AS NILAI_AKHIR
FROM NILAI n
JOIN MATA_KULIAH mk ON n.MATKUL_ID = mk.MATKUL_ID
WHERE n.MAHASISWA_ID = :mid
ORDER BY mk.NAMA_MATKUL
";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ":mid", $mahasiswa_id);
oci_execute($stmt);

$data = [];
while ($row = oci_fetch_assoc($stmt)) {
    $data[] = [
        "KODE_MATKUL"   => $row["KODE_MATKUL"],
        "NAMA_MATKUL"   => $row["NAMA_MATKUL"],
        "NILAI_TUGAS"   => $row["NILAI_TUGAS"] ?? 0,
        "NILAI_UTS"     => $row["NILAI_UTS"] ?? 0,
        "NILAI_UAS"     => $row["NILAI_UAS"] ?? 0,
        "NILAI_AKHIR"   => $row["NILAI_AKHIR"] ?? 0
    ];
}

echo json_encode(["success" => true, "data" => $data]);

oci_free_statement($stmt);
oci_close($conn);
?>
