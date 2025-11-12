<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
require_once(__DIR__ . '/../../config.php');

$conn = getOracleConnection();
oci_execute(oci_parse($conn, "ALTER SESSION SET CURRENT_SCHEMA=UAS"));

$data = json_decode(file_get_contents("php://input"), true);
$mahasiswa_id = $data["mahasiswa_id"] ?? null;
$matkul_id = $data["matkul_id"] ?? null;

if (!$mahasiswa_id || !$matkul_id) {
    echo json_encode(["success" => false, "message" => "Data tidak lengkap."]);
    exit;
}

$cek = oci_parse($conn, "SELECT 1 FROM NILAI WHERE MAHASISWA_ID = :mid AND MATKUL_ID = :mkid");
oci_bind_by_name($cek, ":mid", $mahasiswa_id);
oci_bind_by_name($cek, ":mkid", $matkul_id);
oci_execute($cek);

if (oci_fetch($cek)) {
    echo json_encode(["success" => false, "message" => "Mata kuliah ini sudah Anda ambil."]);
    exit;
}

$sql = "
INSERT INTO NILAI (NILAI_ID, MAHASISWA_ID, MATKUL_ID, TAHUN_AJARAN, NILAI_TUGAS, NILAI_UTS, NILAI_UAS, NILAI_AKHIR, STATUS, CREATED_AT)
VALUES (SEQ_NILAI.NEXTVAL, :mid, :mkid, '2025/2026', NULL, NULL, NULL, NULL, 'Aktif', SYSDATE)
";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ":mid", $mahasiswa_id);
oci_bind_by_name($stmt, ":mkid", $matkul_id);

if (oci_execute($stmt)) {
    echo json_encode(["success" => true, "message" => "Mata kuliah berhasil diambil!"]);
} else {
    $e = oci_error($stmt);
    echo json_encode(["success" => false, "message" => "Gagal mengambil mata kuliah: " . $e['message']]);
}
?>
