<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
require_once("../../config.php");

$conn = getOracleConnection();

$sql = "
    SELECT 
        M.MAHASISWA_ID,
        M.NIM,
        M.NAMA_LENGKAP,
        M.EMAIL,
        M.NO_TELEPON,
        M.ALAMAT,
        TO_CHAR(M.TANGGAL_LAHIR, 'YYYY-MM-DD') AS TANGGAL_LAHIR,
        M.JENIS_KELAMIN,
        M.ANGKATAN,
        M.SEMESTER,
        M.STATUS,
        U.USER_ID,
        U.USERNAME,
        U.EMAIL AS USER_EMAIL
    FROM MAHASISWA M
    JOIN USERS U ON M.USER_ID = U.USER_ID
    ORDER BY M.MAHASISWA_ID
";

$stid = oci_parse($conn, $sql);
oci_execute($stid);

$data = [];
while ($row = oci_fetch_assoc($stid)) {
    $data[] = $row;
}

if (count($data) > 0) {
    echo json_encode(["success" => true, "data" => $data]);
} else {
    echo json_encode(["success" => false, "message" => "Data mahasiswa tidak ditemukan"]);
}

oci_free_statement($stid);
oci_close($conn);
?>
