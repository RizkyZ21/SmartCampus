const baseUrl = "../api/dosen/nilai/";
const dosenData = JSON.parse(localStorage.getItem("userData"));
const dosenId = dosenData?.DOSEN_ID;

document.addEventListener("DOMContentLoaded", () => {
  document.getElementById("namaDosen").textContent = `Selamat datang, ${dosenData.NAMA_LENGKAP}`;
  loadMatkul();

  document.getElementById("matkulSelect").addEventListener("change", () => {
    const matkulId = document.getElementById("matkulSelect").value;
    if (matkulId) loadNilai(matkulId);
  });
});

// === Load daftar matkul dosen ===
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

// === Load daftar mahasiswa & nilai ===
async function loadNilai(matkulId) {
  const tbody = document.getElementById("nilaiTable");
  tbody.innerHTML = `<tr><td colspan="9">Memuat data...</td></tr>`;

  try {
    const res = await fetch(baseUrl + "get_mahasiswa_nilai.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ matkul_id: matkulId }),
    });

    const data = await res.json();
    tbody.innerHTML = "";
    if (data.success && data.data.length > 0) {
      data.data.forEach((mhs, i) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${i + 1}</td>
          <td>${mhs.NAMA_LENGKAP}</td>
          <td>${mhs.NIM}</td>
          <td><input type="number" min="0" max="100" value="${mhs.NILAI_TUGAS}" id="tugas_${mhs.MAHASISWA_ID}" class="inputNilai"></td>
          <td><input type="number" min="0" max="100" value="${mhs.NILAI_UTS}" id="uts_${mhs.MAHASISWA_ID}" class="inputNilai"></td>
          <td><input type="number" min="0" max="100" value="${mhs.NILAI_UAS}" id="uas_${mhs.MAHASISWA_ID}" class="inputNilai"></td>
          <td id="akhir_${mhs.MAHASISWA_ID}">${mhs.NILAI_AKHIR}</td>
          <td id="grade_${mhs.MAHASISWA_ID}">${mhs.GRADE}</td>
          <td>
            <button onclick="simpanNilai(${mhs.MAHASISWA_ID}, ${matkulId})">Simpan</button>
          </td>
        `;
        tbody.appendChild(tr);
      });
    } else {
      tbody.innerHTML = `<tr><td colspan="9">Tidak ada mahasiswa terdaftar</td></tr>`;
    }
  } catch {
    tbody.innerHTML = `<tr><td colspan="9">Gagal memuat data nilai</td></tr>`;
  }
}

// === Hitung nilai akhir & grade ===
function hitungNilai(tugas, uts, uas) {
  const akhir = tugas * 0.3 + uts * 0.3 + uas * 0.4;
  let grade = "E";
  if (akhir >= 85) grade = "A";
  else if (akhir >= 70) grade = "B";
  else if (akhir >= 55) grade = "C";
  else if (akhir >= 40) grade = "D";
  return { akhir: akhir.toFixed(2), grade };
}

// === Simpan nilai (insert/update otomatis) ===
async function simpanNilai(mahasiswa_id, matkul_id) {
  const tugas = parseFloat(document.getElementById(`tugas_${mahasiswa_id}`).value) || 0;
  const uts = parseFloat(document.getElementById(`uts_${mahasiswa_id}`).value) || 0;
  const uas = parseFloat(document.getElementById(`uas_${mahasiswa_id}`).value) || 0;

  const { akhir, grade } = hitungNilai(tugas, uts, uas);

  document.getElementById(`akhir_${mahasiswa_id}`).textContent = akhir;
  document.getElementById(`grade_${mahasiswa_id}`).textContent = grade;

  const payload = { mahasiswa_id, matkul_id, nilai_tugas: tugas, nilai_uts: uts, nilai_uas: uas };

  try {
    // Coba update dulu
    let res = await fetch(baseUrl + "update_nilai.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    let data = await res.json();

    // Kalau gagal update, berarti belum ada data → insert
    if (!data.success) {
      res = await fetch(baseUrl + "insert_nilai.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      data = await res.json();
    }

    alert(data.message);
  } catch (err) {
    alert("Gagal menyimpan nilai: " + err.message);
  }
}
