<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
require_once("../../config.php");

$conn = getOracleConnection();
if (!$conn) { echo json_encode(["success"=>false,"message"=>"Koneksi database gagal"]); exit; }

$sql = "
  SELECT J.JADWAL_ID,
         J.MATKUL_ID,
         M.NAMA_MATKUL,
         J.DOSEN_ID,
         D.NAMA_LENGKAP AS NAMA_DOSEN,
         J.RUANG_ID,
         R.NAMA_RUANG,
         J.HARI,
         J.JAM_MULAI,
         J.JAM_SELESAI,
         J.TAHUN_AJARAN
  FROM JADWAL_KULIAH J
  LEFT JOIN MATA_KULIAH M ON J.MATKUL_ID = M.MATKUL_ID
  LEFT JOIN DOSEN D ON J.DOSEN_ID = D.DOSEN_ID
  LEFT JOIN RUANG_KELAS R ON J.RUANG_ID = R.RUANG_ID
  ORDER BY J.JADWAL_ID DESC
";

$stid = oci_parse($conn, $sql);
oci_execute($stid);

$data = [];
while ($row = oci_fetch_assoc($stid)) {
  $data[] = $row;
}

echo json_encode(["success"=>true,"data"=>$data]);

oci_free_statement($stid);
oci_close($conn);
?>
