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
    echo json_encode(["success" => false, "message" => "Mata kuliah ID wajib dikirim"]);
    exit;
}

$sql = "SELECT 
            m.MAHASISWA_ID,
            m.NIM,
            m.NAMA_LENGKAP,
            NVL(n.NILAI_TUGAS, 0) AS NILAI_TUGAS,
            NVL(n.NILAI_UTS, 0) AS NILAI_UTS,
            NVL(n.NILAI_UAS, 0) AS NILAI_UAS,
            NVL(n.NILAI_AKHIR, 0) AS NILAI_AKHIR,
            NVL(n.GRADE, '-') AS GRADE
        FROM MAHASISWA m
        LEFT JOIN NILAI n ON m.MAHASISWA_ID = n.MAHASISWA_ID AND n.MATKUL_ID = :matkul_id
        WHERE m.STATUS = 'Aktif'
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
