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

$sql = "SELECT COUNT(*) AS JUMLAH FROM ABSENSI WHERE MAHASISWA_ID = :mid AND SESI_ID = :sid";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ":mid", $mahasiswa_id);
oci_bind_by_name($stmt, ":sid", $sesi_id);
oci_execute($stmt);

$row = oci_fetch_assoc($stmt);
$sudah = ($row["JUMLAH"] > 0);

echo json_encode(["success" => true, "data" => ["SUDAH_ABSEN" => $sudah]]);
?>
