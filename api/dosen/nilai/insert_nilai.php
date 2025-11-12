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

$sql = "INSERT INTO NILAI (NILAI_ID, MAHASISWA_ID, MATKUL_ID, TAHUN_AJARAN,
            NILAI_TUGAS, NILAI_UTS, NILAI_UAS, NILAI_AKHIR, GRADE, STATUS, CREATED_AT)
        VALUES (SEQ_NILAI.NEXTVAL, :mahasiswa_id, :matkul_id, TO_CHAR(SYSDATE, 'YYYY'),
            :tugas, :uts, :uas, :akhir, :grade, 'Aktif', SYSDATE)";

$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":mahasiswa_id", $mahasiswa_id);
oci_bind_by_name($stid, ":matkul_id", $matkul_id);
oci_bind_by_name($stid, ":tugas", $tugas);
oci_bind_by_name($stid, ":uts", $uts);
oci_bind_by_name($stid, ":uas", $uas);
oci_bind_by_name($stid, ":akhir", $nilai_akhir);
oci_bind_by_name($stid, ":grade", $grade);

if (oci_execute($stid)) {
    // 🔹 panggil procedure biar nilai akhir & grade terupdate otomatis
    $proc = oci_parse($conn, "BEGIN sp_rekap_nilai(:mhs, :matkul); END;");
    oci_bind_by_name($proc, ":mhs", $mahasiswa_id);
    oci_bind_by_name($proc, ":matkul", $matkul_id);
    oci_execute($proc);
    oci_free_statement($proc);

    oci_commit($conn);
    echo json_encode(["success" => true, "message" => "Nilai berhasil ditambahkan dan diperbarui"]);
} else {
    echo json_encode(["success" => false, "message" => "Gagal menambah nilai"]);
}

oci_free_statement($stid);
oci_close($conn);
?>
