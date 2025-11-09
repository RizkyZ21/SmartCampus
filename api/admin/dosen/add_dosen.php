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
if (!$data) {
    echo json_encode(["success" => false, "message" => "Data tidak valid"]);
    exit;
}

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';
$email = $data['email'] ?? '';
$nip = $data['nip'] ?? '';
$nama = $data['nama_lengkap'] ?? '';
$telepon = $data['no_telepon'] ?? '';
$alamat = $data['alamat'] ?? '';
$gender = $data['jenis_kelamin'] ?? 'Laki-laki';

try {
    // Mulai transaksi
    oci_execute(oci_parse($conn, "BEGIN NULL; END;")); // dummy start

    // Insert ke USERS
    $sqlUser = "
        INSERT INTO USERS (USER_ID, USERNAME, PASSWORD, EMAIL, ROLE, IS_ACTIVE, CREATED_AT)
        VALUES (SEQ_USERS.NEXTVAL, :username, :password, :email, 'dosen', 1, SYSDATE)
        RETURNING USER_ID INTO :user_id
    ";
    $stmtUser = oci_parse($conn, $sqlUser);
    oci_bind_by_name($stmtUser, ":username", $username);
    oci_bind_by_name($stmtUser, ":password", $password);
    oci_bind_by_name($stmtUser, ":email", $email);
    oci_bind_by_name($stmtUser, ":user_id", $user_id, 32);
    oci_execute($stmtUser);

    // Insert ke DOSEN pakai USER_ID yang baru
    $sqlDosen = "
    INSERT INTO DOSEN (DOSEN_ID, USER_ID, NIP, NAMA_LENGKAP, EMAIL, NO_TELEPON, ALAMAT, TANGGAL_LAHIR, JENIS_KELAMIN, CREATED_AT)
    VALUES (SEQ_DOSEN.NEXTVAL, :user_id, :nip, :nama, :email, :telepon, :alamat, TO_DATE(:tgl_lahir, 'YYYY-MM-DD'), :gender, SYSDATE)
    ";

    $stmtDosen = oci_parse($conn, $sqlDosen);
    oci_bind_by_name($stmtDosen, ":user_id", $user_id);
    oci_bind_by_name($stmtDosen, ":nip", $nip);
    oci_bind_by_name($stmtDosen, ":nama", $nama);
    oci_bind_by_name($stmtDosen, ":email", $email);
    oci_bind_by_name($stmtDosen, ":telepon", $telepon);
    oci_bind_by_name($stmtDosen, ":alamat", $alamat);
    oci_bind_by_name($stmtDosen, ":tgl_lahir", $data['tanggal_lahir']);
    oci_bind_by_name($stmtDosen, ":gender", $gender);
    oci_execute($stmtDosen);

    oci_commit($conn);
    echo json_encode(["success" => true, "message" => "Dosen berhasil ditambahkan"]);
} catch (Exception $e) {
    oci_rollback($conn);
    echo json_encode(["success" => false, "message" => "Gagal menambah dosen: " . $e->getMessage()]);
}
oci_close($conn);
?>
