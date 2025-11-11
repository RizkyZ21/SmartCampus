<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
require_once(__DIR__ . '/../../config.php');

$conn = getOracleConnection();
oci_execute(oci_parse($conn, "ALTER SESSION SET CURRENT_SCHEMA=UAS"));

$data = json_decode(file_get_contents("php://input"), true);
$mahasiswa_id = $data["mahasiswa_id"] ?? null;
$sesi_id = $data["sesi_id"] ?? null;

if (!$mahasiswa_id || !$sesi_id) {
    echo json_encode(["success" => false, "message" => "Data tidak lengkap."]);
    exit;
}

// 🔹 Ambil data sesi aktif
$sql_sesi = "SELECT SESI_ID, JADWAL_ID, STATUS FROM SESI_ABSENSI WHERE SESI_ID = :sid";
$stmt_sesi = oci_parse($conn, $sql_sesi);
oci_bind_by_name($stmt_sesi, ":sid", $sesi_id);
oci_execute($stmt_sesi);
$sesi = oci_fetch_assoc($stmt_sesi);

if (!$sesi) {
    echo json_encode(["success" => false, "message" => "Sesi absensi tidak ditemukan."]);
    exit;
}
if (strtoupper($sesi["STATUS"]) !== 'OPEN') {
    echo json_encode(["success" => false, "message" => "Sesi absensi sudah ditutup."]);
    exit;
}

// 🔹 Ambil MATKUL_ID dari JADWAL
$sql_matkul = "SELECT MATKUL_ID FROM JADWAL_KULIAH WHERE JADWAL_ID = :jid";
$stmt_matkul = oci_parse($conn, $sql_matkul);
oci_bind_by_name($stmt_matkul, ":jid", $sesi["JADWAL_ID"]);
oci_execute($stmt_matkul);
$row_matkul = oci_fetch_assoc($stmt_matkul);

if (!$row_matkul) {
    echo json_encode(["success" => false, "message" => "Data jadwal tidak valid."]);
    exit;
}

$matkul_id = $row_matkul["MATKUL_ID"];

// 🔒 Cek apakah mahasiswa benar-benar mengambil matkul ini
$sql_ambil = "SELECT COUNT(*) AS JUMLAH FROM NILAI WHERE MAHASISWA_ID = :mid AND MATKUL_ID = :mkid";
$stmt_ambil = oci_parse($conn, $sql_ambil);
oci_bind_by_name($stmt_ambil, ":mid", $mahasiswa_id);
oci_bind_by_name($stmt_ambil, ":mkid", $matkul_id);
oci_execute($stmt_ambil);
$row_ambil = oci_fetch_assoc($stmt_ambil);

if ($row_ambil["JUMLAH"] == 0) {
    echo json_encode(["success" => false, "message" => "Anda tidak terdaftar di mata kuliah ini."]);
    exit;
}

// 🔹 Cek apakah sudah absen sebelumnya
$sql_check = "SELECT COUNT(*) AS JUMLAH FROM ABSENSI WHERE MAHASISWA_ID = :mid AND SESI_ID = :sid";
$check = oci_parse($conn, $sql_check);
oci_bind_by_name($check, ":mid", $mahasiswa_id);
oci_bind_by_name($check, ":sid", $sesi_id);
oci_execute($check);
$row = oci_fetch_assoc($check);
if ($row && $row["JUMLAH"] > 0) {
    echo json_encode(["success" => false, "message" => "Anda sudah melakukan absensi di sesi ini."]);
    exit;
}

// 🔹 Simpan absensi
$sql_insert = "
INSERT INTO ABSENSI (
    ABSENSI_ID, SESI_ID, JADWAL_ID, MAHASISWA_ID, TANGGAL, STATUS_KEHADIRAN, CREATED_AT
) VALUES (
    SEQ_ABSENSI.NEXTVAL, :sid, :jid, :mid, SYSDATE, 'Hadir', SYSDATE
)
";
$insert = oci_parse($conn, $sql_insert);
oci_bind_by_name($insert, ":sid", $sesi["SESI_ID"]);
oci_bind_by_name($insert, ":jid", $sesi["JADWAL_ID"]);
oci_bind_by_name($insert, ":mid", $mahasiswa_id);

if (oci_execute($insert)) {
    echo json_encode(["success" => true, "message" => "Absensi berhasil disimpan!"]);
} else {
    $e = oci_error($insert);
    echo json_encode(["success" => false, "message" => "Gagal menyimpan absensi: " . $e['message']]);
}
?>
