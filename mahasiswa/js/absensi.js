document.addEventListener("DOMContentLoaded", () => {
  const mahasiswaId = sessionStorage.getItem("mahasiswa_id");
  if (!mahasiswaId) {
    alert("Silakan login ulang.");
    window.location.href = "../index.html";
    return;
  }

  const sesiDiv = document.getElementById("sesiAktif");
  const tbody = document.querySelector("#tabelAbsensi tbody");

  fetch("http://localhost/SmartCampus/api/mahasiswa/absensi/sesi_aktif.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ mahasiswa_id: mahasiswaId })
  })
    .then(res => res.json())
    .then(async data => {
      sesiDiv.innerHTML = "";

      if (!data.success || !data.data || data.data.length === 0) {
        sesiDiv.innerHTML = "<p>Tidak ada sesi absensi aktif saat ini.</p>";
        return;
      }

      for (const sesi of data.data) {
        const cekRes = await fetch("http://localhost/SmartCampus/api/mahasiswa/absensi/cek_status.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ mahasiswa_id: mahasiswaId, sesi_id: sesi.SESI_ID })
        });
        const cekData = await cekRes.json();
        const sudahAbsen = cekData.success && cekData.data?.SUDAH_ABSEN === true;

        sesiDiv.insertAdjacentHTML(
          "beforeend",
          `
          <div class="sesi-box">
            <p><strong>${sesi.NAMA_MATKUL}</strong></p>
            <p>Dosen: ${sesi.NAMA_DOSEN}</p>
            <p>Ruang: ${sesi.NAMA_RUANG ?? "-"}</p>
            <p>Status: <b>${sesi.STATUS}</b></p>
            <button class="btn-absen" data-sesi="${sesi.SESI_ID}" ${sudahAbsen ? "disabled" : ""}>
              ${sudahAbsen ? "Sudah Absen ✅" : "Absen Sekarang"}
            </button>
          </div>
        `
        );
      }

      document.querySelectorAll(".btn-absen").forEach((btn) => {
        btn.addEventListener("click", () => {
          const sesiId = btn.dataset.sesi;
          btn.disabled = true;
          btn.textContent = "Mengirim absensi...";

          fetch("http://localhost/SmartCampus/api/mahasiswa/absensi/absen.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
              mahasiswa_id: mahasiswaId,
              sesi_id: sesiId
            })
          })
            .then((res) => res.json())
            .then((result) => {
              if (result.success) {
                btn.textContent = "Sudah Absen ✅";
                btn.disabled = true;
              } else {
                alert(result.message || "Gagal absen");
                btn.textContent = "Absen Sekarang";
                btn.disabled = false;
              }
            })
            .catch((err) => {
              alert("Gagal mengirim absensi: " + err.message);
              btn.textContent = "Absen Sekarang";
              btn.disabled = false;
            });
        });
      });
    })
    .catch((err) => {
      sesiDiv.innerHTML = `<p style="color:red;">Gagal memuat sesi absensi (${err.message})</p>`;
    });

  fetch("http://localhost/SmartCampus/api/mahasiswa/absensi/history.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ mahasiswa_id: mahasiswaId })
  })
    .then((res) => res.json())
    .then((data) => {
      tbody.innerHTML = "";

      if (!data.success || data.data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5">Belum ada riwayat absensi.</td></tr>`;
        return;
      }

      data.data.forEach((a) => {
        tbody.insertAdjacentHTML(
          "beforeend",
          `
          <tr>
            <td>${a.TANGGAL}</td>
            <td>${a.NAMA_MATKUL}</td>
            <td>${a.NAMA_DOSEN}</td>
            <td>${a.STATUS_KEHADIRAN}</td>
            <td>${a.KETERANGAN ?? "-"}</td>
          </tr>
        `
        );
      });
    })
    .catch((err) => {
      tbody.innerHTML = `<tr><td colspan="5">Gagal memuat riwayat: ${err.message}</td></tr>`;
    });

  window.tutupSesi = function (sesiId) {
    if (!confirm("Apakah Anda yakin ingin menutup sesi absensi ini?\nMahasiswa yang belum absen akan otomatis ditandai Alpa.")) {
      return;
    }

    fetch("http://localhost/SmartCampus/api/dosen/absensi/tutup_sesi.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ sesi_id: sesiId })
    })
      .then((res) => res.json())
      .then((result) => {
        alert(result.message);
        if (result.success) {
          window.location.reload();
        }
      })
      .catch((err) => {
        alert("Gagal menutup sesi: " + err.message);
      });
  };
});
