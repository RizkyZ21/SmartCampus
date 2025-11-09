<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
require_once("../../config.php");

$conn = getOracleConnection();

if (!$conn) {
    echo json_encode(["success" => false, "message" => "Koneksi database gagal"]);
    exit;
}

$sql = "
    SELECT 
        D.DOSEN_ID, D.NIP, D.NAMA_LENGKAP, D.EMAIL, D.NO_TELEPON, D.ALAMAT,
        D.JENIS_KELAMIN, D.USER_ID, U.USERNAME, U.EMAIL AS USER_EMAIL, U.ROLE
    FROM DOSEN D
    JOIN USERS U ON D.USER_ID = U.USER_ID
    ORDER BY D.DOSEN_ID ASC
";

$stmt = oci_parse($conn, $sql);
oci_execute($stmt);

$dosen = [];
while ($row = oci_fetch_assoc($stmt)) {
    $dosen[] = $row;
}

oci_free_statement($stmt);
oci_close($conn);

echo json_encode(["success" => true, "data" => $dosen]);
?>
