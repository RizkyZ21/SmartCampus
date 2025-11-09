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
if (!$data || empty($data['dosen_id'])) {
    echo json_encode(["success" => false, "message" => "Data tidak valid"]);
    exit;
}

$dosen_id = $data['dosen_id'];

try {
    // 🔹 Ambil USER_ID berdasarkan DOSEN_ID
    $getUser = oci_parse($conn, "SELECT USER_ID FROM DOSEN WHERE DOSEN_ID = :DOSEN_ID");
    oci_bind_by_name($getUser, ":DOSEN_ID", $dosen_id);
    oci_execute($getUser);
    $row = oci_fetch_assoc($getUser);
    $user_id = $row['USER_ID'] ?? null;

    if (!$user_id) {
        echo json_encode(["success" => false, "message" => "Data dosen tidak ditemukan"]);
        exit;
    }

    // 🔹 Hapus dari USERS → otomatis hapus DOSEN (karena ON DELETE CASCADE)
    $sql = "DELETE FROM USERS WHERE USER_ID = :USER_ID";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":USER_ID", $user_id);
    oci_execute($stmt, OCI_NO_AUTO_COMMIT);

    // 🔹 Commit transaksi
    oci_commit($conn);

    echo json_encode(["success" => true, "message" => "Dosen berhasil dihapus"]);
} catch (Exception $e) {
    oci_rollback($conn);
    echo json_encode(["success" => false, "message" => "Gagal menghapus: " . $e->getMessage()]);
}

oci_close($conn);
?>
