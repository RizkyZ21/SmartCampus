const baseUrl = "../api/dosen/laporan/";
const dosenData = JSON.parse(localStorage.getItem("userData"));
const dosenId = dosenData?.DOSEN_ID;

document.addEventListener("DOMContentLoaded", () => {
  document.getElementById("namaDosen").textContent = `Selamat datang, ${dosenData.NAMA_LENGKAP}`;
  loadMatkul();

  document.getElementById("matkulSelect").addEventListener("change", () => {
    const matkulId = document.getElementById("matkulSelect").value;
    if (matkulId) loadLaporan(matkulId);
  });
});

async function loadMatkul() {
  const select = document.getElementById("matkulSelect");
  select.innerHTML = `<option value="">Memuat...</option>`;
  try {
    const res = await fetch(baseUrl + "get_matkul_dosen.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ dosen_id: dosenId }),
    });
    const data = await res.json();
    select.innerHTML = `<option value="">-- Pilih Mata Kuliah --</option>`;
    if (data.success && data.data.length > 0) {
      data.data.forEach((m) => {
        const opt = document.createElement("option");
        opt.value = m.MATKUL_ID;
        opt.textContent = `${m.NAMA_MATKUL} (${m.KODE_MATKUL})`;
        select.appendChild(opt);
      });
    } else {
      select.innerHTML = `<option value="">Tidak ada matkul</option>`;
    }
  } catch {
    select.innerHTML = `<option value="">Gagal memuat matkul</option>`;
  }
}

async function loadLaporan(matkulId) {
  const tbody = document.getElementById("laporanTable");
  tbody.innerHTML = `<tr><td colspan="7">Memuat data...</td></tr>`;

  try {
    const res = await fetch(baseUrl + "get_laporan_mahasiswa.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ matkul_id: matkulId }),
    });
    const data = await res.json();

    tbody.innerHTML = "";
    if (data.success && data.data.length > 0) {
      data.data.forEach((m, i) => {
        const hadir = m.HADIR ?? 0;
        const alpa = m.ALPA ?? 0;
        const akhir = m.NILAI_AKHIR ?? 0;
        const grade = m.GRADE ?? "-";

        tbody.innerHTML += `
          <tr>
            <td>${i + 1}</td>
            <td>${m.NAMA_LENGKAP}</td>
            <td>${m.NIM}</td>
            <td>${hadir}</td>
            <td>${alpa}</td>
            <td>${akhir}</td>
            <td>${grade}</td>
          </tr>
        `;
      });
    } else {
      tbody.innerHTML = `<tr><td colspan="7">Tidak ada data laporan</td></tr>`;
    }
  } catch {
    tbody.innerHTML = `<tr><td colspan="7">Gagal memuat laporan</td></tr>`;
  }
}
