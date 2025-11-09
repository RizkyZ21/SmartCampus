const baseUrl = "../api/admin/jadwal/";

document.addEventListener("DOMContentLoaded", loadJadwal);

async function loadJadwal() {
  const tbody = document.getElementById("jadwalTable");
  tbody.innerHTML = "<tr><td colspan='8'>Memuat data...</td></tr>";

  try {
    const res = await fetch(baseUrl + "get_jadwal.php");
    const data = await res.json();

    tbody.innerHTML = "";
    if (data.success && data.data.length > 0) {
      data.data.forEach((j, i) => {
        const tr = `
          <tr>
            <td>${i + 1}</td>
            <td>${j.NAMA_MATKUL}</td>
            <td>${j.NAMA_DOSEN}</td>
            <td>${j.NAMA_RUANG}</td>
            <td>${j.HARI}</td>
            <td>${j.JAM_MULAI} - ${j.JAM_SELESAI}</td>
            <td>${j.TAHUN_AJARAN}</td>
            <td>
              <button onclick="showEditModal(${j.JADWAL_ID}, ${j.MATKUL_ID}, ${j.DOSEN_ID}, ${j.RUANG_ID}, '${j.HARI}', '${j.JAM_MULAI}', '${j.JAM_SELESAI}', '${j.TAHUN_AJARAN}')">Edit</button>
              <button onclick="deleteJadwal(${j.JADWAL_ID})">Hapus</button>
            </td>
          </tr>`;
        tbody.insertAdjacentHTML("beforeend", tr);
      });
    } else {
      tbody.innerHTML = "<tr><td colspan='8'>Tidak ada data</td></tr>";
    }
  } catch {
    tbody.innerHTML = "<tr><td colspan='8'>Gagal memuat data</td></tr>";
  }
}

// === MODAL ===
async function showAddModal() {
  const modal = document.getElementById("formModal");
  modal.classList.add("show");
  document.getElementById("modalTitle").innerText = "Tambah Jadwal";
  resetForm();
  await loadDropdowns();
  document.getElementById("saveBtn").onclick = addJadwal;
}

async function showEditModal(id, matkul, dosen, ruang, hari, mulai, selesai, tahun) {
  const modal = document.getElementById("formModal");
  modal.classList.add("show");
  document.getElementById("modalTitle").innerText = "Edit Jadwal";
  document.getElementById("jadwalId").value = id;
  await loadDropdowns(matkul, dosen, ruang);
  document.getElementById("hari").value = hari;
  document.getElementById("jam_mulai").value = mulai;
  document.getElementById("jam_selesai").value = selesai;
  document.getElementById("tahun_ajaran").value = tahun;
  document.getElementById("saveBtn").onclick = updateJadwal;
}

function closeModal() {
  document.getElementById("formModal").classList.remove("show");
}

function resetForm() {
  document.querySelectorAll(".modal-box input").forEach(i => i.value = "");
}

// === Dropdown ===
async function loadDropdowns(selectedMatkul = "", selectedDosen = "", selectedRuang = "") {
  await loadDropdown("get_matkul_list.php", "matkul_id", selectedMatkul);
  await loadDropdown("get_dosen_list.php", "dosen_id", selectedDosen);
  await loadDropdown("get_ruang_list.php", "ruang_id", selectedRuang);
}

async function loadDropdown(api, selectId, selected) {
  const select = document.getElementById(selectId);
  select.innerHTML = "<option>Memuat...</option>";
  const res = await fetch(baseUrl + api);
  const data = await res.json();
  select.innerHTML = '<option value="">-- Pilih --</option>';
  if (data.success) {
    data.data.forEach(d => {
      const opt = document.createElement("option");
      opt.value = d.ID;
      opt.textContent = d.NAMA;
      if (selected == d.ID) opt.selected = true;
      select.appendChild(opt);
    });
  }
}

// === CRUD ===
async function addJadwal() {
  const payload = getFormData();
  const res = await fetch(baseUrl + "add_jadwal.php", {
    method: "POST", headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  });
  const data = await res.json();
  alert(data.message);
  closeModal();
  loadJadwal();
}

async function updateJadwal() {
  const payload = getFormData();
  payload.jadwal_id = document.getElementById("jadwalId").value;
  const res = await fetch(baseUrl + "update_jadwal.php", {
    method: "POST", headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  });
  const data = await res.json();
  alert(data.message);
  closeModal();
  loadJadwal();
}

async function deleteJadwal(id) {
  if (!confirm("Yakin ingin menghapus jadwal ini?")) return;
  const res = await fetch(baseUrl + "delete_jadwal.php", {
    method: "POST", headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ jadwal_id: id })
  });
  const data = await res.json();
  alert(data.message);
  if (data.success) loadJadwal();
}

function getFormData() {
  return {
    matkul_id: document.getElementById("matkul_id").value,
    dosen_id: document.getElementById("dosen_id").value,
    ruang_id: document.getElementById("ruang_id").value,
    hari: document.getElementById("hari").value,
    jam_mulai: document.getElementById("jam_mulai").value,
    jam_selesai: document.getElementById("jam_selesai").value,
    tahun_ajaran: document.getElementById("tahun_ajaran").value
  };
}
