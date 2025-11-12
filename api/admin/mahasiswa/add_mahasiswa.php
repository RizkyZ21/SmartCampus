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

try {
    $username       = $data['username'];
    $password       = $data['password'];
    $email          = $data['email'];
    $nama_lengkap   = $data['nama_lengkap'];
    $nim            = $data['nim'];
    $no_telepon     = $data['no_telepon'];
    $alamat         = $data['alamat'];
    $tanggal_lahir  = $data['tanggal_lahir'];
    $jenis_kelamin  = $data['jenis_kelamin'];
    $angkatan       = $data['angkatan'];
    $semester       = $data['semester'];
    $status         = $data['status'] ?? 'Aktif';

    $sql_user = "
        INSERT INTO USERS (USER_ID, USERNAME, PASSWORD, EMAIL, ROLE, CREATED_AT)
        VALUES (SEQ_USERS.NEXTVAL, :USERNAME, :PASSWORD, :EMAIL, 'mahasiswa', SYSDATE)
        RETURNING USER_ID INTO :NEW_USER_ID
    ";
    $stmt_user = oci_parse($conn, $sql_user);
    oci_bind_by_name($stmt_user, ":USERNAME", $username);
    oci_bind_by_name($stmt_user, ":PASSWORD", $password);
    oci_bind_by_name($stmt_user, ":EMAIL", $email);
    oci_bind_by_name($stmt_user, ":NEW_USER_ID", $new_user_id, -1, SQLT_INT);
    oci_execute($stmt_user, OCI_NO_AUTO_COMMIT);

    $sql_mhs = "
        INSERT INTO MAHASISWA (
            MAHASISWA_ID, USER_ID, NIM, NAMA_LENGKAP, EMAIL,
            NO_TELEPON, ALAMAT, TANGGAL_LAHIR, JENIS_KELAMIN,
            ANGKATAN, SEMESTER, STATUS, CREATED_AT
        ) VALUES (
            SEQ_MAHASISWA.NEXTVAL, :USER_ID, :NIM, :NAMA_LENGKAP, :EMAIL,
            :NO_TELEPON, :ALAMAT, TO_DATE(:TANGGAL_LAHIR, 'YYYY-MM-DD'),
            :JENIS_KELAMIN, :ANGKATAN, :SEMESTER, :STATUS, SYSDATE
        )
    ";
    $stmt_mhs = oci_parse($conn, $sql_mhs);
    oci_bind_by_name($stmt_mhs, ":USER_ID", $new_user_id);
    oci_bind_by_name($stmt_mhs, ":NIM", $nim);
    oci_bind_by_name($stmt_mhs, ":NAMA_LENGKAP", $nama_lengkap);
    oci_bind_by_name($stmt_mhs, ":EMAIL", $email);
    oci_bind_by_name($stmt_mhs, ":NO_TELEPON", $no_telepon);
    oci_bind_by_name($stmt_mhs, ":ALAMAT", $alamat);
    oci_bind_by_name($stmt_mhs, ":TANGGAL_LAHIR", $tanggal_lahir);
    oci_bind_by_name($stmt_mhs, ":JENIS_KELAMIN", $jenis_kelamin);
    oci_bind_by_name($stmt_mhs, ":ANGKATAN", $angkatan);
    oci_bind_by_name($stmt_mhs, ":SEMESTER", $semester);
    oci_bind_by_name($stmt_mhs, ":STATUS", $status);
    oci_execute($stmt_mhs, OCI_NO_AUTO_COMMIT);

    oci_commit($conn);

    echo json_encode(["success" => true, "message" => "Mahasiswa berhasil ditambahkan"]);
} catch (Exception $e) {
    oci_rollback($conn);
    echo json_encode(["success" => false, "message" => "Gagal menambah mahasiswa: " . $e->getMessage()]);
}

oci_close($conn);
?>
