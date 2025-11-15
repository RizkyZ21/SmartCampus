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
    NVL(SUM(CASE WHEN a.STATUS_KEHADIRAN = 'Hadir' THEN 1 ELSE 0 END), 0) AS HADIR,
    NVL(SUM(CASE WHEN a.STATUS_KEHADIRAN = 'Alpa' THEN 1 ELSE 0 END), 0) AS ALPA,
    NVL(n.NILAI_AKHIR, 0) AS NILAI_AKHIR,
    NVL(n.GRADE, '-') AS GRADE
FROM NILAI n
JOIN MAHASISWA m ON m.MAHASISWA_ID = n.MAHASISWA_ID
LEFT JOIN JADWAL_KULIAH j ON j.MATKUL_ID = n.MATKUL_ID
LEFT JOIN ABSENSI a 
       ON a.MAHASISWA_ID = m.MAHASISWA_ID
      AND a.JADWAL_ID = j.JADWAL_ID
WHERE n.MATKUL_ID = :matkul_id
GROUP BY
    m.MAHASISWA_ID,
    m.NIM,
    m.NAMA_LENGKAP,
    n.NILAI_AKHIR,
    n.GRADE
ORDER BY m.NAMA_LENGKAP
";

$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":matkul_id", $matkul_id);
oci_execute($stid);

$rows = [];
while ($r = oci_fetch_assoc($stid)) {
    $rows[] = [
        "MAHASISWA_ID" => $r["MAHASISWA_ID"],
        "NIM" => $r["NIM"],
        "NAMA_LENGKAP" => $r["NAMA_LENGKAP"],
        "HADIR" => (int)$r["HADIR"],
        "ALPA" => (int)$r["ALPA"],
        "NILAI_AKHIR" => (float)$r["NILAI_AKHIR"],
        "GRADE" => $r["GRADE"]
    ];
}

echo json_encode(["success" => true, "data" => $rows]);

oci_free_statement($stid);
oci_close($conn);
?>
