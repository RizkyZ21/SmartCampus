<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");

require_once(__DIR__ . '/../../config.php');

$conn = getOracleConnection();

// pastikan pakai schema UAS
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
    COUNT(CASE WHEN a.STATUS_KEHADIRAN = 'Hadir' THEN 1 END) AS JUMLAH_HADIR,
    COUNT(a.ABSENSI_ID) AS TOTAL_PERTEMUAN,
    ROUND(
      (COUNT(CASE WHEN a.STATUS_KEHADIRAN = 'Hadir' THEN 1 END) / 
       NULLIF(COUNT(a.ABSENSI_ID), 0)) * 100, 2
    ) AS PERSEN_KEHADIRAN
FROM ABSENSI a
JOIN JADWAL_KULIAH j ON a.JADWAL_ID = j.JADWAL_ID
JOIN MATA_KULIAH mk ON j.MATKUL_ID = mk.MATKUL_ID
JOIN DOSEN d ON j.DOSEN_ID = d.DOSEN_ID
WHERE a.MAHASISWA_ID = :id
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
