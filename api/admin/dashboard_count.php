<?php
header("Content-Type: application/json; charset=utf-8");
require_once("../config.php");

$conn = getOracleConnection();
if (!$conn) {
  echo json_encode(["success" => false, "message" => "Koneksi ke database gagal"]);
  exit;
}

try {
  $sqlDosen = "SELECT COUNT(*) AS TOTAL FROM DOSEN";
  $stid = oci_parse($conn, $sqlDosen);
  oci_execute($stid);
  $row = oci_fetch_assoc($stid);
  $totalDosen = $row['TOTAL'];

  $sqlMhs = "SELECT COUNT(*) AS TOTAL FROM MAHASISWA";
  $stid = oci_parse($conn, $sqlMhs);
  oci_execute($stid);
  $row = oci_fetch_assoc($stid);
  $totalMahasiswa = $row['TOTAL'];

  $sqlMatkul = "SELECT COUNT(*) AS TOTAL FROM MATA_KULIAH";
  $stid = oci_parse($conn, $sqlMatkul);
  oci_execute($stid);
  $row = oci_fetch_assoc($stid);
  $totalMatkul = $row['TOTAL'];

  echo json_encode([
    "success" => true,
    "dosen" => $totalDosen,
    "mahasiswa" => $totalMahasiswa,
    "matkul" => $totalMatkul
  ]);

  oci_free_statement($stid);
  oci_close($conn);

} catch (Exception $e) {
  echo json_encode(["success" => false, "message" => "Terjadi kesalahan: " . $e->getMessage()]);
}
?>
