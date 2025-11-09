const baseUrl = "../api/admin/matakuliah/";

document.addEventListener("DOMContentLoaded", loadMatkul);

async function loadMatkul() {
  const tbody = document.getElementById("matkulTable");
  tbody.innerHTML = "<tr><td colspan='6'>Memuat data...</td></tr>";

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
            <td>${m.DOSEN_ID || '-'}</td>
            <td class="action-btns">
              <button onclick="showEditModal(${m.MATKUL_ID}, '${m.KODE_MATKUL}', '${m.NAMA_MATKUL}', '${m.SKS}', '${m.SEMESTER}', '${m.DOSEN_ID || ''}', '${m.JENIS_MATKUL || ''}', '${m.DESKRIPSI || ''}')">Edit</button>
              <button onclick="deleteMatkul(${m.MATKUL_ID})">Hapus</button>
            </td>
          </tr>`;
        tbody.insertAdjacentHTML("beforeend", tr);
      });
    } else {
      tbody.innerHTML = "<tr><td colspan='6'>Tidak ada data</td></tr>";
    }
  } catch {
    tbody.innerHTML = "<tr><td colspan='6'>Gagal memuat data</td></tr>";
  }
}

// ===== Modal =====
function showAddModal() {
  const modal = document.getElementById("formModal");
  modal.classList.add("show");
  document.getElementById("modalTitle").innerText = "Tambah Mata Kuliah";
  resetForm();
  document.getElementById("saveBtn").onclick = addMatkul;
}

function showEditModal(id, kode, nama, sks, semester, dosenId, jenis, deskripsi) {
  const modal = document.getElementById("formModal");
  modal.classList.add("show");
  document.getElementById("modalTitle").innerText = "Edit Mata Kuliah";
  document.getElementById("matkulId").value = id;
  document.getElementById("kode").value = kode;
  document.getElementById("nama").value = nama;
  document.getElementById("sks").value = sks;
  document.getElementById("semester").value = semester;
  document.getElementById("dosenId").value = dosenId;
  document.getElementById("jenis").value = jenis;
  document.getElementById("deskripsi").value = deskripsi;
  document.getElementById("saveBtn").onclick = updateMatkul;
}

function closeModal() {
  document.getElementById("formModal").classList.remove("show");
}

window.onclick = e => {
  const modal = document.getElementById("formModal");
  if (e.target === modal) closeModal();
};

function resetForm() {
  document.querySelectorAll(".modal-box input, .modal-box textarea").forEach(i => i.value = "");
}

// ===== CRUD =====
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
  try {
    const res = await fetch(baseUrl + "delete_matakuliah.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ matkul_id: id })
    });
    const data = await res.json();
    alert(data.message);
    if (data.success) loadMatkul();
  } catch (err) {
    alert("Gagal menghapus mata kuliah: " + err.message);
  }
}

function getFormData() {
  return {
    kode_matkul: document.getElementById("kode").value,
    nama_matkul: document.getElementById("nama").value,
    sks: document.getElementById("sks").value,
    semester: document.getElementById("semester").value,
    dosen_id: document.getElementById("dosenId").value,
    jenis_matkul: document.getElementById("jenis").value,
    deskripsi: document.getElementById("deskripsi").value
  };
}
