document.addEventListener("DOMContentLoaded", function() {
  const mahasiswaId = sessionStorage.getItem("mahasiswa_id");
  if (!mahasiswaId) {
    alert("Silakan login ulang.");
    window.location.href = "../index.html";
    return;
  }

  fetch("http://localhost/SmartCampus/api/mahasiswa/nilai/rekap.php", {
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
      tbody.innerHTML = `<tr><td colspan="6">Belum ada nilai untuk ditampilkan.</td></tr>`;
      rataElem.textContent = "-";
      return;
    }

    let total = 0, count = 0;

    data.data.forEach(item => {
      const akhir = parseFloat(item.NILAI_AKHIR) || 0;
      total += akhir;
      count++;

      const row = `
        <tr>
          <td>${item.KODE_MATKUL}</td>
          <td>${item.NAMA_MATKUL}</td>
          <td>${item.NILAI_TUGAS ?? '-'}</td>
          <td>${item.NILAI_UTS ?? '-'}</td>
          <td>${item.NILAI_UAS ?? '-'}</td>
          <td>${akhir ? akhir.toFixed(2) : '-'}</td>
        </tr>
      `;
      tbody.insertAdjacentHTML("beforeend", row);
    });

    const rata = count > 0 ? (total / count).toFixed(2) : "-";
    rataElem.textContent = rata;
  })
  .catch(err => {
    console.error("Error:", err);
    const tbody = document.querySelector("#tabelNilai tbody");
    tbody.innerHTML = `<tr><td colspan="6">Gagal memuat data nilai.</td></tr>`;
  });
});
