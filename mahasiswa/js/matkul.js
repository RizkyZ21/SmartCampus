document.addEventListener("DOMContentLoaded", function() {
  const mahasiswaId = sessionStorage.getItem("mahasiswa_id");
  if (!mahasiswaId) {
    alert("Silakan login ulang.");
    window.location.href = "../index.html";
    return;
  }

  fetch("http://localhost/SmartCampus/api/mahasiswa/matkul/list.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ mahasiswa_id: mahasiswaId })
  })
  .then(res => res.json())
  .then(data => {
    const tbody = document.querySelector("#tabelMatkul tbody");
    tbody.innerHTML = "";

    if (!data.success || data.data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="5">Tidak ada mata kuliah tersedia.</td></tr>`;
      return;
    }

    data.data.forEach(matkul => {
      const row = `
        <tr>
          <td>${matkul.KODE_MATKUL}</td>
          <td>${matkul.NAMA_MATKUL}</td>
          <td>${matkul.SKS}</td>
          <td>${matkul.NAMA_DOSEN}</td>
          <td>
            <button class="btn-ambil" data-id="${matkul.MATKUL_ID}">Ambil</button>
          </td>
        </tr>
      `;
      tbody.insertAdjacentHTML("beforeend", row);
    });

    document.querySelectorAll(".btn-ambil").forEach(btn => {
      btn.addEventListener("click", function() {
        const matkulId = this.getAttribute("data-id");
        if (!confirm("Yakin ingin mengambil mata kuliah ini?")) return;

        fetch("http://localhost/SmartCampus/api/mahasiswa/matkul/ambil.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            mahasiswa_id: mahasiswaId,
            matkul_id: matkulId
          })
        })
        .then(res => res.json())
        .then(result => {
          alert(result.message);
          if (result.success) location.reload();
        });
      });
    });
  })
  .catch(err => {
    console.error("Error:", err);
    alert("Gagal memuat daftar mata kuliah.");
  });
});
