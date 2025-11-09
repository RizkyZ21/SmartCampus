<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
require_once("../../config.php");

$conn = getOracleConnection();
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['mahasiswa_id'])) {
    echo json_encode(["success" => false, "message" => "Data tidak valid"]);
    exit;
}

$mahasiswa_id = $data['mahasiswa_id'];

try {
    // Ambil USER_ID berdasarkan MAHASISWA_ID
    $getUser = oci_parse($conn, "SELECT USER_ID FROM MAHASISWA WHERE MAHASISWA_ID = :MAHASISWA_ID");
    oci_bind_by_name($getUser, ":MAHASISWA_ID", $mahasiswa_id);
    oci_execute($getUser);
    $row = oci_fetch_assoc($getUser);
    $user_id = $row['USER_ID'] ?? null;

    if (!$user_id) {
        echo json_encode(["success" => false, "message" => "Data mahasiswa tidak ditemukan"]);
        exit;
    }

    // Hapus dari USERS (akan otomatis hapus dari MAHASISWA karena ON DELETE CASCADE)
    $sql = "DELETE FROM USERS WHERE USER_ID = :USER_ID";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":USER_ID", $user_id);
    oci_execute($stmt, OCI_NO_AUTO_COMMIT);

    oci_commit($conn);
    echo json_encode(["success" => true, "message" => "Mahasiswa berhasil dihapus"]);
} catch (Exception $e) {
    oci_rollback($conn);
    echo json_encode(["success" => false, "message" => "Gagal menghapus: " . $e->getMessage()]);
}

oci_close($conn);
?>
