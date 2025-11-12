document.addEventListener("DOMContentLoaded", function() {
  const mahasiswaId = sessionStorage.getItem("mahasiswa_id");
  if (!mahasiswaId) {
    alert("Silakan login ulang.");
    window.location.href = "../index.html";
    return;
  }

  fetch("http://localhost/SmartCampus/api/mahasiswa/laporan/rekap_absensi.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ mahasiswa_id: mahasiswaId })
  })
  .then(res => res.json())
  .then(data => {
    const tbody = document.querySelector("#tabelAbsensi tbody");
    tbody.innerHTML = "";

    if (!data.success || data.data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="5">Belum ada data absensi.</td></tr>`;
      return;
    }

    data.data.forEach(a => {
      const row = `
        <tr>
          <td>${a.NAMA_MATKUL}</td>
          <td>${a.NAMA_DOSEN}</td>
          <td>${a.JUMLAH_HADIR}</td>
          <td>${a.TOTAL_PERTEMUAN}</td>
          <td>${parseFloat(a.PERSEN_KEHADIRAN).toFixed(2)}%</td>
        </tr>
      `;
      tbody.insertAdjacentHTML("beforeend", row);
    });
  })
  .catch(() => {
    document.querySelector("#tabelAbsensi tbody").innerHTML =
      `<tr><td colspan="5">Gagal memuat data absensi.</td></tr>`;
  });

  fetch("http://localhost/SmartCampus/api/mahasiswa/laporan/rekap_nilai.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ mahasiswa_id: mahasiswaId })
  })
  .then(res => res.json())
  .then(data => {
    const tbody = document.querySelector("#tabelNilai tbody");
    const rataElem = document.getElementById("rataRata");
    tbody.innerHTML = "";

    if (!data.success || data.data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6">Belum ada data nilai.</td></tr>`;
      rataElem.textContent = "-";
      return;
    }

    let total = 0, count = 0;
    data.data.forEach(n => {
      const akhir = parseFloat(n.NILAI_AKHIR) || 0;
      total += akhir;
      count++;

      const row = `
        <tr>
          <td>${n.KODE_MATKUL}</td>
          <td>${n.NAMA_MATKUL}</td>
          <td>${n.NILAI_TUGAS ?? '-'}</td>
          <td>${n.NILAI_UTS ?? '-'}</td>
          <td>${n.NILAI_UAS ?? '-'}</td>
          <td>${akhir.toFixed(2)}</td>
        </tr>
      `;
      tbody.insertAdjacentHTML("beforeend", row);
    });

    const rata = count > 0 ? (total / count).toFixed(2) : "-";
    rataElem.textContent = rata;
  })
  .catch(() => {
    document.querySelector("#tabelNilai tbody").innerHTML =
      `<tr><td colspan="6">Gagal memuat data nilai.</td></tr>`;
  });
});
