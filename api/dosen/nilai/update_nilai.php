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

$mahasiswa_id = $data["mahasiswa_id"] ?? "";
$matkul_id = $data["matkul_id"] ?? "";
$tugas = $data["nilai_tugas"] ?? 0;
$uts = $data["nilai_uts"] ?? 0;
$uas = $data["nilai_uas"] ?? 0;

if (empty($mahasiswa_id) || empty($matkul_id)) {
    echo json_encode(["success" => false, "message" => "Data wajib diisi"]);
    exit;
}

$nilai_akhir = ($tugas * 0.3) + ($uts * 0.3) + ($uas * 0.4);

if ($nilai_akhir >= 85) $grade = "A";
elseif ($nilai_akhir >= 70) $grade = "B";
elseif ($nilai_akhir >= 55) $grade = "C";
elseif ($nilai_akhir >= 40) $grade = "D";
else $grade = "E";

$sql = "UPDATE NILAI 
        SET NILAI_TUGAS = :tugas,
            NILAI_UTS = :uts,
            NILAI_UAS = :uas,
            NILAI_AKHIR = :akhir,
            GRADE = :grade,
            UPDATED_AT = SYSDATE
        WHERE MAHASISWA_ID = :mahasiswa_id AND MATKUL_ID = :matkul_id";

$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":tugas", $tugas);
oci_bind_by_name($stid, ":uts", $uts);
oci_bind_by_name($stid, ":uas", $uas);
oci_bind_by_name($stid, ":akhir", $nilai_akhir);
oci_bind_by_name($stid, ":grade", $grade);
oci_bind_by_name($stid, ":mahasiswa_id", $mahasiswa_id);
oci_bind_by_name($stid, ":matkul_id", $matkul_id);

if (oci_execute($stid)) {
    oci_commit($conn);
    echo json_encode(["success" => true, "message" => "Nilai berhasil diperbarui"]);
} else {
    echo json_encode(["success" => false, "message" => "Gagal memperbarui nilai"]);
}

oci_free_statement($stid);
oci_close($conn);
?>
