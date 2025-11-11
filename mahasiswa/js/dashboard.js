document.addEventListener("DOMContentLoaded", () => {
  const nim = localStorage.getItem("nim") || prompt("Masukkan NIM:");
  const apiUrl = "../api/mahasiswa/get_jadwal.php";

  fetch(apiUrl, {
    method: "POST",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify({ nim })
  })
    .then(res => res.json())
    .then(data => {
      const tbody = document.getElementById("jadwal-body");
      tbody.innerHTML = "";

      if (data.success) {
        data.data.forEach(row => {
          const tr = document.createElement("tr");
          tr.innerHTML = `
            <td>${row.KODE_MATKUL}</td>
            <td>${row.NAMA_MATKUL}</td>
            <td>${row.SKS}</td>
            <td>${row.DOSEN}</td>
            <td>${row.NAMA_RUANG}</td>
            <td>${row.HARI}</td>
            <td>${row.JAM_MULAI} - ${row.JAM_SELESAI}</td>
            <td>${row.TAHUN_AJARAN}</td>
          `;
          tbody.appendChild(tr);
        });
      } else {
        tbody.innerHTML = `<tr><td colspan="8">${data.message}</td></tr>`;
      }
    })
    .catch(err => {
      document.getElementById("jadwal-body").innerHTML =
        `<tr><td colspan="8">Terjadi kesalahan: ${err.message}</td></tr>`;
    });
});
