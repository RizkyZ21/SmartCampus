<?php
header("Content-Type: application/json");
require_once "../../config.php";

$conn = getOracleConnection();
oci_execute(oci_parse($conn, "ALTER SESSION SET CURRENT_SCHEMA = UAS"));

$data = json_decode(file_get_contents("php://input"), true);

$nilai_id = $data["nilai_id"] ?? null;
$status   = $data["status"] ?? null;

if (!$nilai_id || !$status) {
    echo json_encode([
        "success" => false,
        "message" => "Parameter tidak lengkap."
    ]);
    exit;
}

$update_sql = "
    UPDATE NILAI
    SET STATUS = :status
    WHERE NILAI_ID = :nid
";
$update = oci_parse($conn, $update_sql);
oci_bind_by_name($update, ":status", $status);
oci_bind_by_name($update, ":nid", $nilai_id);
oci_execute($update);

echo json_encode([
    "success" => true,
    "message" => "Status nilai berhasil diubah menjadi $status"
]);

?>