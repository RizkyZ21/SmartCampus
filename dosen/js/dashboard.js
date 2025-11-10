const baseUrl = "../api/dosen/dashboard/";

document.addEventListener("DOMContentLoaded", loadDashboard);

async function loadDashboard() {
  const dosenId = sessionStorage.getItem("dosen_id");
  const hariFilter = document.getElementById("hariFilter")?.value || "";

  const tbody = document.getElementById("jadwalTable");
  tbody.innerHTML = "<tr><td colspan='6'>Memuat data...</td></tr>";

  try {
    const res = await fetch(`${baseUrl}get_dashboard.php?dosen_id=${dosenId}&hari=${hariFilter}`);
    const data = await res.json();

    if (data.success) {
      document.getElementById("totalMatkul").textContent = "Mata Kuliah: " + data.total_matkul;
      document.getElementById("totalMahasiswa").textContent = "Mahasiswa: " + data.total_mahasiswa;

      tbody.innerHTML = "";
      if (data.jadwal.length > 0) {
        data.jadwal.forEach((j, i) => {
          tbody.innerHTML += `
            <tr>
              <td>${i + 1}</td>
              <td>${j.NAMA_MATKUL}</td>
              <td>${j.HARI}</td>
              <td>${j.JAM_MULAI} - ${j.JAM_SELESAI}</td>
              <td>${j.NAMA_RUANG}</td>
              <td>${j.TAHUN_AJARAN}</td>
            </tr>
          `;
        });
      } else {
        tbody.innerHTML = `<tr><td colspan="6">Tidak ada jadwal untuk hari ini</td></tr>`;
      }
    }
  } catch (err) {
    tbody.innerHTML = "<tr><td colspan='6'>Gagal memuat data</td></tr>";
  }
}
