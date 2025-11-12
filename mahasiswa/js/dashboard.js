document.addEventListener("DOMContentLoaded", function() {
  const mahasiswaId = sessionStorage.getItem("mahasiswa_id");

  if (!mahasiswaId) {
    alert("ID Mahasiswa tidak ditemukan. Silakan login ulang.");
    window.location.href = "../index.html";
    return;
  }

  fetch("http://localhost/SmartCampus/api/mahasiswa/dashboard/jadwal.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ mahasiswa_id: mahasiswaId })
  })
  .then(res => res.json())
  .then(data => {
    const tbody = document.querySelector("#tabelJadwal tbody");
    tbody.innerHTML = "";

    if (!data.success || data.data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6">Tidak ada jadwal kuliah hari ini.</td></tr>`;
      return;
    }

    data.data.forEach(item => {
      const row = `
        <tr>
          <td>${item.KODE_MATKUL}</td>
          <td>${item.NAMA_MATKUL}</td>
          <td>${item.DOSEN}</td>
          <td>${item.HARI}</td>
          <td>${item.JAM_MULAI} - ${item.JAM_SELESAI}</td>
          <td>${item.RUANGAN}</td>
        </tr>
      `;
      tbody.insertAdjacentHTML("beforeend", row);
    });
  })
  .catch(err => {
    console.error("Error:", err);
    const tbody = document.querySelector("#tabelJadwal tbody");
    tbody.innerHTML = `<tr><td colspan="6">Gagal memuat data jadwal.</td></tr>`;
  });
});
