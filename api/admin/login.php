<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

require_once("../config.php");

$conn = getOracleConnection();
if (!$conn) {
    echo json_encode(["success" => false, "message" => "Koneksi database gagal"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$username = trim($input['username'] ?? '');
$password = trim($input['password'] ?? '');

if (empty($username) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Username dan password wajib diisi"]);
    exit;
}

$sql = "SELECT USER_ID, USERNAME, EMAIL, ROLE
        FROM USERS
        WHERE USERNAME = :username AND PASSWORD = :password AND ROLE = 'admin' AND IS_ACTIVE = 1";

$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":username", $username);
oci_bind_by_name($stid, ":password", $password);
oci_execute($stid);

$user = oci_fetch_assoc($stid);

if ($user) {
    echo json_encode([
        "success" => true,
        "message" => "Login admin berhasil",
        "data" => $user
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Login gagal, periksa username/password atau role Anda"
    ]);
}

oci_free_statement($stid);
oci_close($conn);
?>
