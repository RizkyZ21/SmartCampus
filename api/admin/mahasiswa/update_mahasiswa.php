<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
require_once("../../config.php");

$conn = getOracleConnection();
$data = json_decode(file_get_contents("php://input"), true);

$mahasiswa_id  = $data['mahasiswa_id'];

try {
    $getUser = oci_parse($conn, "SELECT USER_ID FROM MAHASISWA WHERE MAHASISWA_ID = :MAHASISWA_ID");
    oci_bind_by_name($getUser, ":MAHASISWA_ID", $mahasiswa_id);
    oci_execute($getUser);
    $row = oci_fetch_assoc($getUser);
    $user_id = $row['USER_ID'] ?? null;

    if (!$user_id) {
        echo json_encode(["success" => false, "message" => "Data mahasiswa tidak ditemukan"]);
        exit;
    }

    $sql_user = "
        UPDATE USERS 
        SET USERNAME = :USERNAME,
            PASSWORD = :PASSWORD,
            EMAIL = :EMAIL,
            UPDATED_AT = SYSDATE
        WHERE USER_ID = :USER_ID
    ";
    $stmt_user = oci_parse($conn, $sql_user);
    oci_bind_by_name($stmt_user, ":USERNAME", $data['username']);
    oci_bind_by_name($stmt_user, ":PASSWORD", $data['password']);
    oci_bind_by_name($stmt_user, ":EMAIL", $data['email']);
    oci_bind_by_name($stmt_user, ":USER_ID", $user_id);
    oci_execute($stmt_user, OCI_NO_AUTO_COMMIT);

    $sql_mhs = "
        UPDATE MAHASISWA 
        SET NIM = :NIM,
            NAMA_LENGKAP = :NAMA_LENGKAP,
            EMAIL = :EMAIL,
            NO_TELEPON = :NO_TELEPON,
            ALAMAT = :ALAMAT,
            TANGGAL_LAHIR = TO_DATE(:TANGGAL_LAHIR, 'YYYY-MM-DD'),
            JENIS_KELAMIN = :JENIS_KELAMIN,
            ANGKATAN = :ANGKATAN,
            SEMESTER = :SEMESTER,
            STATUS = :STATUS,
            UPDATED_AT = SYSDATE
        WHERE MAHASISWA_ID = :MAHASISWA_ID
    ";
    $stmt_mhs = oci_parse($conn, $sql_mhs);
    oci_bind_by_name($stmt_mhs, ":NIM", $data['nim']);
    oci_bind_by_name($stmt_mhs, ":NAMA_LENGKAP", $data['nama_lengkap']);
    oci_bind_by_name($stmt_mhs, ":EMAIL", $data['email']);
    oci_bind_by_name($stmt_mhs, ":NO_TELEPON", $data['no_telepon']);
    oci_bind_by_name($stmt_mhs, ":ALAMAT", $data['alamat']);
    oci_bind_by_name($stmt_mhs, ":TANGGAL_LAHIR", $data['tanggal_lahir']);
    oci_bind_by_name($stmt_mhs, ":JENIS_KELAMIN", $data['jenis_kelamin']);
    oci_bind_by_name($stmt_mhs, ":ANGKATAN", $data['angkatan']);
    oci_bind_by_name($stmt_mhs, ":SEMESTER", $data['semester']);
    oci_bind_by_name($stmt_mhs, ":STATUS", $data['status']);
    oci_bind_by_name($stmt_mhs, ":MAHASISWA_ID", $mahasiswa_id);
    oci_execute($stmt_mhs, OCI_NO_AUTO_COMMIT);

    oci_commit($conn);
    echo json_encode(["success" => true, "message" => "Mahasiswa berhasil diperbarui"]);
} catch (Exception $e) {
    oci_rollback($conn);
    echo json_encode(["success" => false, "message" => "Gagal memperbarui: " . $e->getMessage()]);
}

oci_close($conn);
?>
