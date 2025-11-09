const baseUrl = "../api/admin/matakuliah/";

document.addEventListener("DOMContentLoaded", () => {
  loadMatkul();
});

// === LOAD DATA MATA KULIAH ===
async function loadMatkul() {
  const tbody = document.getElementById("matkulTable");
  tbody.innerHTML = "<tr><td colspan='7'>Memuat data...</td></tr>";

  try {
    const res = await fetch(baseUrl + "get_matakuliah.php");
    const data = await res.json();

    tbody.innerHTML = "";
    if (data.success && data.data.length > 0) {
      data.data.forEach((m, i) => {
        const tr = `
          <tr>
            <td>${i + 1}</td>
            <td>${m.KODE_MATKUL}</td>
            <td>${m.NAMA_MATKUL}</td>
            <td>${m.SKS}</td>
            <td>${m.SEMESTER}</td>
            <td>${m.NAMA_DOSEN || '-'}</td>
            <td class="action-btns">
              <button onclick="showEditModal(${m.MATKUL_ID}, '${escapeStr(m.KODE_MATKUL)}', '${escapeStr(m.NAMA_MATKUL)}', ${m.SKS}, ${m.SEMESTER}, ${m.DOSEN_ID}, '${escapeStr(m.JENIS_MATKUL || '')}', '${escapeStr(m.DESKRIPSI || '')}')">Edit</button>
              <button onclick="deleteMatkul(${m.MATKUL_ID})">Hapus</button>
            </td>
          </tr>`;
        tbody.insertAdjacentHTML("beforeend", tr);
      });
    } else {
      tbody.innerHTML = "<tr><td colspan='7'>Tidak ada data mata kuliah</td></tr>";
    }
  } catch {
    tbody.innerHTML = "<tr><td colspan='7'>Gagal memuat data</td></tr>";
  }
}

// === MODAL ===
async function showAddModal() {
  const modal = document.getElementById("formModal");
  modal.classList.add("show");
  document.getElementById("modalTitle").innerText = "Tambah Mata Kuliah";
  resetForm();
  await loadDosenDropdown();
  document.getElementById("saveBtn").onclick = addMatkul;
}

async function showEditModal(id, kode, nama, sks, semester, dosen, jenis, deskripsi) {
  const modal = document.getElementById("formModal");
  modal.classList.add("show");
  document.getElementById("modalTitle").innerText = "Edit Mata Kuliah";
  document.getElementById("matkulId").value = id;
  await loadDosenDropdown(dosen);

  document.getElementById("kode_matkul").value = unescapeStr(kode);
  document.getElementById("nama_matkul").value = unescapeStr(nama);
  document.getElementById("sks").value = sks;
  document.getElementById("semester").value = semester;
  document.getElementById("jenis_matkul").value = unescapeStr(jenis);
  document.getElementById("deskripsi").value = unescapeStr(deskripsi);
  document.getElementById("saveBtn").onclick = updateMatkul;
}

function closeModal() {
  document.getElementById("formModal").classList.remove("show");
}

async function loadDosenDropdown(selectedId = "") {
  const select = document.getElementById("dosen_id");
  select.innerHTML = "<option>Memuat daftar dosen...</option>";

  try {
    const res = await fetch(baseUrl + "get_dosen_list.php");
    const data = await res.json();
    select.innerHTML = '<option value="">-- Pilih Dosen --</option>';

    if (data.success && data.data.length > 0) {
      data.data.forEach(d => {
        const opt = document.createElement("option");
        opt.value = d.DOSEN_ID;
        opt.textContent = d.NAMA_LENGKAP;
        if (selectedId == d.DOSEN_ID) opt.selected = true;
        select.appendChild(opt);
      });
    }
  } catch {
    select.innerHTML = "<option>Gagal memuat dosen</option>";
  }
}

function resetForm() {
  document.querySelectorAll(".modal-box input, .modal-box textarea").forEach(i => i.value = "");
}

// === CRUD ===
async function addMatkul() {
  const payload = getFormData();
  const res = await fetch(baseUrl + "add_matakuliah.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  });
  const data = await res.json();
  alert(data.message);
  closeModal();
  loadMatkul();
}

async function updateMatkul() {
  const payload = getFormData();
  payload.matkul_id = document.getElementById("matkulId").value;
  const res = await fetch(baseUrl + "update_matakuliah.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  });
  const data = await res.json();
  alert(data.message);
  closeModal();
  loadMatkul();
}

async function deleteMatkul(id) {
  if (!confirm("Yakin ingin menghapus mata kuliah ini?")) return;
  const res = await fetch(baseUrl + "delete_matakuliah.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ matkul_id: id })
  });
  const data = await res.json();
  alert(data.message);
  if (data.success) loadMatkul();
}

function getFormData() {
  return {
    kode_matkul: document.getElementById("kode_matkul").value,
    nama_matkul: document.getElementById("nama_matkul").value,
    sks: document.getElementById("sks").value,
    semester: document.getElementById("semester").value,
    dosen_id: document.getElementById("dosen_id").value,
    jenis_matkul: document.getElementById("jenis_matkul").value,
    deskripsi: document.getElementById("deskripsi").value
  };
}

function escapeStr(str) {
  return str ? str.replace(/'/g, "\\'").replace(/"/g, '\\"') : "";
}
function unescapeStr(str) {
  return str ? str.replace(/\\'/g, "'").replace(/\\"/g, '"') : "";
}
