const baseUrl = "../api/dosen/absensi/";
const dosenData = JSON.parse(localStorage.getItem("userData"));
const dosenId = dosenData?.DOSEN_ID;

document.addEventListener("DOMContentLoaded", () => {
  document.getElementById("namaDosen").textContent = `Selamat datang, ${dosenData.NAMA_LENGKAP}`;
  loadJadwal();
  loadSesiAktif();
  loadRiwayat();

  document.getElementById("btnBuka").addEventListener("click", bukaSesi);
  document.getElementById("btnTutup").addEventListener("click", tutupSesi);
});

async function loadJadwal() {
  const select = document.getElementById("jadwalSelect");
  select.innerHTML = `<option value="">Memuat...</option>`;
  try {
    const res = await fetch(baseUrl + "get_jadwal_dosen.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ dosen_id: dosenId })
    });
    const data = await res.json();
    select.innerHTML = `<option value="">-- Pilih Jadwal --</option>`;
    if (data.success && data.data.length > 0) {
      data.data.forEach(j => {
        const opt = document.createElement("option");
        opt.value = j.JADWAL_ID;
        opt.textContent = `${j.NAMA_MATKUL} (${j.HARI} - ${j.JAM_MULAI}-${j.JAM_SELESAI})`;
        select.appendChild(opt);
      });
    } else {
      select.innerHTML = `<option value="">Tidak ada jadwal</option>`;
    }
  } catch {
    select.innerHTML = `<option value="">Gagal memuat</option>`;
  }
}

async function bukaSesi() {
  const jadwal_id = document.getElementById("jadwalSelect").value;
  if (!jadwal_id) return alert("Pilih jadwal terlebih dahulu!");
  try {
    const res = await fetch(baseUrl + "buka_sesi.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ dosen_id: dosenId, jadwal_id })
    });
    const data = await res.json();
    alert(data.message);
    loadSesiAktif();
    loadRiwayat();
  } catch {
    alert("Gagal membuka sesi!");
  }
}

async function tutupSesi() {
  const sesiTable = document.querySelectorAll("#sesiAktifTable tr[data-sesi-id]");
  if (sesiTable.length === 0) return alert("Tidak ada sesi aktif!");

  for (const row of sesiTable) {
    const sesiId = row.dataset.sesiId;
    if (!sesiId) continue;

    const konfirmasi = confirm(`Tutup sesi absensi untuk ${row.children[1].textContent}?`);
    if (!konfirmasi) continue;

    try {
      const res = await fetch(baseUrl + "tutup_sesi.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ sesi_id: sesiId })
      });
      const data = await res.json();
      alert(data.message);
    } catch {
      alert("Gagal menutup sesi!");
    }
  }

  loadSesiAktif();
  loadRiwayat();
}

async function loadSesiAktif() {
  const tbody = document.getElementById("sesiAktifTable");
  tbody.innerHTML = "<tr><td colspan='6'>Memuat...</td></tr>";
  try {
    const res = await fetch(baseUrl + "get_sesi_aktif.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ dosen_id: dosenId })
    });
    const data = await res.json();
    tbody.innerHTML = "";
    if (data.success && data.data.length > 0) {
      data.data.forEach((s, i) => {
        const tr = document.createElement("tr");
        tr.dataset.sesiId = s.SESI_ID;
        tr.innerHTML = `
          <td>${i + 1}</td>
          <td>${s.NAMA_MATKUL}</td>
          <td>${s.HARI}</td>
          <td>${s.JAM_MULAI} - ${s.JAM_SELESAI}</td>
          <td>${s.STATUS}</td>
          <td>${s.TANGGAL}</td>
        `;
        tbody.appendChild(tr);
      });
    } else {
      tbody.innerHTML = "<tr><td colspan='6'>Tidak ada sesi aktif</td></tr>";
    }
  } catch {
    tbody.innerHTML = "<tr><td colspan='6'>Gagal memuat data</td></tr>";
  }
}

async function loadRiwayat() {
  const tbody = document.getElementById("riwayatTable");
  tbody.innerHTML = "<tr><td colspan='6'>Memuat...</td></tr>";
  try {
    const res = await fetch(baseUrl + "get_riwayat_sesi.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ dosen_id: dosenId })
    });
    const data = await res.json();
    tbody.innerHTML = "";
    if (data.success && data.data.length > 0) {
      data.data.forEach((s, i) => {
        const tr = `
          <tr>
            <td>${i + 1}</td>
            <td>${s.NAMA_MATKUL}</td>
            <td>${s.HARI}</td>
            <td>${s.JAM_MULAI}-${s.JAM_SELESAI}</td>
            <td>${s.STATUS}</td>
            <td>${s.TANGGAL}</td>
          </tr>`;
        tbody.insertAdjacentHTML("beforeend", tr);
      });
    } else {
      tbody.innerHTML = "<tr><td colspan='6'>Belum ada sesi absensi</td></tr>";
    }
  } catch {
    tbody.innerHTML = "<tr><td colspan='6'>Gagal memuat data</td></tr>";
  }
}
