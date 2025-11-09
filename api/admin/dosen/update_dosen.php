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

$dosen_id       = $data['dosen_id'];
$nama_lengkap   = $data['nama_lengkap'] ?? '';
$nip            = $data['nip'] ?? '';
$email          = $data['email'] ?? '';
$no_telepon     = $data['no_telepon'] ?? '';
$alamat         = $data['alamat'] ?? '';
$tanggal_lahir  = $data['tanggal_lahir'] ?? '';
$jenis_kelamin  = $data['jenis_kelamin'] ?? '';
$username       = $data['username'] ?? '';
$password       = $data['password'] ?? '';

try {
    $getUser = oci_parse($conn, "SELECT USER_ID FROM DOSEN WHERE DOSEN_ID = :ID");
    oci_bind_by_name($getUser, ":ID", $dosen_id);
    oci_execute($getUser);
    $row = oci_fetch_assoc($getUser);
    $user_id = $row['USER_ID'] ?? null;

    if (!$user_id) {
        echo json_encode(["success" => false, "message" => "Data dosen tidak ditemukan"]);
        exit;
    }

    // Update USERS
    $sql_user = "
        UPDATE USERS 
        SET USERNAME = :USERNAME,
            PASSWORD = :PASSWORD,
            EMAIL = :EMAIL,
            UPDATED_AT = SYSDATE
        WHERE USER_ID = :USER_ID
    ";
    $stmt_user = oci_parse($conn, $sql_user);
    oci_bind_by_name($stmt_user, ":USERNAME", $username);
    oci_bind_by_name($stmt_user, ":PASSWORD", $password);
    oci_bind_by_name($stmt_user, ":EMAIL", $email);
    oci_bind_by_name($stmt_user, ":USER_ID", $user_id);
    oci_execute($stmt_user, OCI_NO_AUTO_COMMIT);

    // Update DOSEN
    $sql_dosen = "
        UPDATE DOSEN 
        SET NIP = :NIP,
            NAMA_LENGKAP = :NAMA_LENGKAP,
            EMAIL = :EMAIL,
            NO_TELEPON = :NO_TELEPON,
            ALAMAT = :ALAMAT,
            JENIS_KELAMIN = :JENIS_KELAMIN,
            TANGGAL_LAHIR = TO_DATE(:TANGGAL_LAHIR, 'YYYY-MM-DD'),
            UPDATED_AT = SYSDATE
        WHERE DOSEN_ID = :DOSEN_ID
    ";
    $stmt_dosen = oci_parse($conn, $sql_dosen);
    oci_bind_by_name($stmt_dosen, ":NIP", $nip);
    oci_bind_by_name($stmt_dosen, ":NAMA_LENGKAP", $nama_lengkap);
    oci_bind_by_name($stmt_dosen, ":EMAIL", $email);
    oci_bind_by_name($stmt_dosen, ":NO_TELEPON", $no_telepon);
    oci_bind_by_name($stmt_dosen, ":ALAMAT", $alamat);
    oci_bind_by_name($stmt_dosen, ":JENIS_KELAMIN", $jenis_kelamin);
    oci_bind_by_name($stmt_dosen, ":TANGGAL_LAHIR", $tanggal_lahir);
    oci_bind_by_name($stmt_dosen, ":DOSEN_ID", $dosen_id);
    oci_execute($stmt_dosen, OCI_NO_AUTO_COMMIT);

    oci_commit($conn);
    echo json_encode(["success" => true, "message" => "Dosen berhasil diperbarui"]);
} catch (Exception $e) {
    oci_rollback($conn);
    echo json_encode(["success" => false, "message" => "Gagal memperbarui: " . $e->getMessage()]);
}

oci_close($conn);
?>
