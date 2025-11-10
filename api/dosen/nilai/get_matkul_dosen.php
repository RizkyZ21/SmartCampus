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
$dosen_id = $data["dosen_id"] ?? "";

if (empty($dosen_id)) {
    echo json_encode(["success" => false, "message" => "Dosen ID wajib dikirim"]);
    exit;
}

$sql = "SELECT MATKUL_ID, KODE_MATKUL, NAMA_MATKUL, SKS, SEMESTER 
        FROM MATA_KULIAH 
        WHERE DOSEN_ID = :dosen_id
        ORDER BY SEMESTER, NAMA_MATKUL";

$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":dosen_id", $dosen_id);
oci_execute($stid);

$rows = [];
while ($r = oci_fetch_assoc($stid)) {
    $rows[] = $r;
}

echo json_encode(["success" => true, "data" => $rows]);
oci_free_statement($stid);
oci_close($conn);
?>
