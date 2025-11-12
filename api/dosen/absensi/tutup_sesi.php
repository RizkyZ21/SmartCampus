<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
require_once(__DIR__ . '/../../config.php');

$conn = getOracleConnection();
oci_execute(oci_parse($conn, "ALTER SESSION SET CURRENT_SCHEMA=UAS"));

$data = json_decode(file_get_contents("php://input"), true);
$sesi_id = $data["sesi_id"] ?? null;

if (!$sesi_id) {
    echo json_encode(["success" => false, "message" => "ID sesi tidak ditemukan."]);
    exit;
}

// 🔹 Tutup sesi absensi
$sql_close = "UPDATE SESI_ABSENSI SET STATUS = 'CLOSED', DITUTUP_PADA = SYSDATE WHERE SESI_ID = :sid";
$stmt_close = oci_parse($conn, $sql_close);
oci_bind_by_name($stmt_close, ":sid", $sesi_id);

if (!oci_execute($stmt_close)) {
    $e = oci_error($stmt_close);
    echo json_encode(["success" => false, "message" => "Gagal menutup sesi: " . $e['message']]);
    exit;
}

// 🔹 Ambil JADWAL_ID dari sesi ini
$sql_jadwal = "SELECT JADWAL_ID FROM SESI_ABSENSI WHERE SESI_ID = :sid";
$stmt_jadwal = oci_parse($conn, $sql_jadwal);
oci_bind_by_name($stmt_jadwal, ":sid", $sesi_id);
oci_execute($stmt_jadwal);
$row_jadwal = oci_fetch_assoc($stmt_jadwal);
$jadwal_id = $row_jadwal['JADWAL_ID'] ?? null;

if (!$jadwal_id) {
    echo json_encode(["success" => false, "message" => "Jadwal tidak ditemukan untuk sesi ini."]);
    exit;
}

// 🔹 Tambahkan absensi otomatis 'Alpa' untuk mahasiswa yang belum absen
$sql_insert_alpa = "
INSERT INTO ABSENSI (ABSENSI_ID, SESI_ID, JADWAL_ID, MAHASISWA_ID, TANGGAL, STATUS_KEHADIRAN, CREATED_AT)
SELECT SEQ_ABSENSI.NEXTVAL, :sid, :jid, n.MAHASISWA_ID, SYSDATE, 'Alpa', SYSDATE
FROM NILAI n
JOIN JADWAL_KULIAH j ON n.MATKUL_ID = j.MATKUL_ID
WHERE j.JADWAL_ID = :jid
AND n.MAHASISWA_ID NOT IN (
  SELECT MAHASISWA_ID FROM ABSENSI WHERE SESI_ID = :sid
)
";

$stmt_insert = oci_parse($conn, $sql_insert_alpa);
oci_bind_by_name($stmt_insert, ":sid", $sesi_id);
oci_bind_by_name($stmt_insert, ":jid", $jadwal_id);

if (!oci_execute($stmt_insert)) {    $e = oci_error($stmt_insert);
    echo json_encode(["success" => false, "message" => "Sesi ditutup, tapi gagal menandai Alpa: " . $e['message']]);
    exit;
}

echo json_encode(["success" => true, "message" => "Sesi absensi berhasil ditutup."]);
?>
