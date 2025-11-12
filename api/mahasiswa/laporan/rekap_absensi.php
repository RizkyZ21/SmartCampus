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
    mk.NAMA_MATKUL,
    d.NAMA_LENGKAP AS NAMA_DOSEN,
    NVL(COUNT(CASE WHEN a.STATUS_KEHADIRAN = 'Hadir' THEN 1 END), 0) AS JUMLAH_HADIR,
    NVL(COUNT(a.ABSENSI_ID), 0) AS TOTAL_PERTEMUAN,
    NVL(ROUND(
      (COUNT(CASE WHEN a.STATUS_KEHADIRAN = 'Hadir' THEN 1 END) / 
       NULLIF(COUNT(a.ABSENSI_ID), 0)) * 100, 2
    ), 0) AS PERSEN_KEHADIRAN
FROM NILAI n
JOIN MATA_KULIAH mk ON n.MATKUL_ID = mk.MATKUL_ID
JOIN DOSEN d ON mk.DOSEN_ID = d.DOSEN_ID
LEFT JOIN JADWAL_KULIAH j ON mk.MATKUL_ID = j.MATKUL_ID
LEFT JOIN ABSENSI a 
       ON a.JADWAL_ID = j.JADWAL_ID 
      AND a.MAHASISWA_ID = n.MAHASISWA_ID
WHERE n.MAHASISWA_ID = :id
GROUP BY mk.NAMA_MATKUL, d.NAMA_LENGKAP
ORDER BY mk.NAMA_MATKUL
";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ":id", $mahasiswa_id);

if (!oci_execute($stmt)) {
    $e = oci_error($stmt);
    echo json_encode([
        "success" => false,
        "message" => "Query gagal dijalankan.",
        "error" => $e['message']
    ]);
    exit;
}

$data = [];
while ($row = oci_fetch_assoc($stmt)) {
    $data[] = [
        "NAMA_MATKUL" => $row["NAMA_MATKUL"],
        "NAMA_DOSEN" => $row["NAMA_DOSEN"],
        "JUMLAH_HADIR" => $row["JUMLAH_HADIR"],
        "TOTAL_PERTEMUAN" => $row["TOTAL_PERTEMUAN"],
        "PERSEN_KEHADIRAN" => $row["PERSEN_KEHADIRAN"]
    ];
}

echo json_encode(["success" => true, "data" => $data]);
?>
