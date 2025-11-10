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

$sql = "SELECT MATKUL_ID, NAMA_MATKUL, KODE_MATKUL 
        FROM MATA_KULIAH 
        WHERE DOSEN_ID = :dosen_id
        ORDER BY NAMA_MATKUL";

$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":dosen_id", $dosen_id);
oci_execute($stid);

$dataResult = [];
while ($r = oci_fetch_assoc($stid)) {
    $dataResult[] = $r;
}

echo json_encode(["success" => true, "data" => $dataResult]);
oci_free_statement($stid);
oci_close($conn);
?>
