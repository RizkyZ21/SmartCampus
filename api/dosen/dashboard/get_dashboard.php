<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");

require_once("../../config.php");
$conn = getOracleConnection();

if (!$conn) {
  echo json_encode(["success" => false, "message" => "Koneksi database gagal"]);
  exit;
}

$dosen_id = $_GET['dosen_id'] ?? null;
$hari_filter = $_GET['hari'] ?? null;

if (!$dosen_id) {
  echo json_encode(["success" => false, "message" => "ID dosen tidak ditemukan"]);
  exit;
}

try {
  // === Total Mata Kuliah ===
  $q1 = oci_parse($conn, "SELECT COUNT(*) AS TOTAL FROM MATA_KULIAH WHERE DOSEN_ID = :id");
  oci_bind_by_name($q1, ":id", $dosen_id);
  oci_execute($q1);
  $row1 = oci_fetch_assoc($q1);
  $total_matkul = $row1["TOTAL"] ?? 0;

  // === Total Mahasiswa ===
  $q2 = oci_parse($conn, "
    SELECT COUNT(DISTINCT N.MAHASISWA_ID) AS TOTAL
    FROM NILAI N
    JOIN MATA_KULIAH M ON N.MATKUL_ID = M.MATKUL_ID
    WHERE M.DOSEN_ID = :id
  ");
  oci_bind_by_name($q2, ":id", $dosen_id);
  oci_execute($q2);
  $row2 = oci_fetch_assoc($q2);
  $total_mahasiswa = $row2["TOTAL"] ?? 0;

  // === Jadwal Berdasarkan Filter Hari ===
  $sql_jadwal = "
    SELECT 
      J.JADWAL_ID,
      M.NAMA_MATKUL,
      J.HARI,
      J.JAM_MULAI,
      J.JAM_SELESAI,
      R.NAMA_RUANG,
      J.TAHUN_AJARAN
    FROM JADWAL_KULIAH J
    JOIN MATA_KULIAH M ON J.MATKUL_ID = M.MATKUL_ID
    JOIN RUANG_KELAS R ON J.RUANG_ID = R.RUANG_ID
    WHERE J.DOSEN_ID = :id
  ";

  if ($hari_filter) {
    $sql_jadwal .= " AND LOWER(J.HARI) = LOWER(:hari)";
  }

  $sql_jadwal .= "
    ORDER BY 
      CASE 
        WHEN LOWER(J.HARI) = 'senin' THEN 1
        WHEN LOWER(J.HARI) = 'selasa' THEN 2
        WHEN LOWER(J.HARI) = 'rabu' THEN 3
        WHEN LOWER(J.HARI) = 'kamis' THEN 4
        WHEN LOWER(J.HARI) = 'jumat' THEN 5
        WHEN LOWER(J.HARI) = 'sabtu' THEN 6
        WHEN LOWER(J.HARI) = 'minggu' THEN 7
        ELSE 8
      END, J.JAM_MULAI
  ";

  $q3 = oci_parse($conn, $sql_jadwal);
  oci_bind_by_name($q3, ":id", $dosen_id);
  if ($hari_filter) oci_bind_by_name($q3, ":hari", $hari_filter);
  oci_execute($q3);

  $jadwal = [];
  while ($row = oci_fetch_assoc($q3)) {
    $jadwal[] = $row;
  }

  echo json_encode([
    "success" => true,
    "total_matkul" => $total_matkul,
    "total_mahasiswa" => $total_mahasiswa,
    "jadwal" => $jadwal
  ]);

} catch (Exception $e) {
  echo json_encode(["success" => false, "message" => "Terjadi kesalahan: " . $e->getMessage()]);
}

oci_close($conn);
?>
