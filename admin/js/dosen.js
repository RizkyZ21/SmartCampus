const baseUrl = "../api/admin/dosen/";

document.addEventListener("DOMContentLoaded", loadDosen);

// ========== LOAD DATA DOSEN ==========
async function loadDosen() {
  const tbody = document.getElementById("dosenTable");
  tbody.innerHTML = "<tr><td colspan='5'>Memuat data...</td></tr>";

  try {
    const res = await fetch(baseUrl + "get_dosen.php");
    const data = await res.json();

    tbody.innerHTML = "";
    if (data.success && data.data.length > 0) {
      data.data.forEach((d, i) => {
        const tr = `
          <tr>
            <td>${i + 1}</td>
            <td>${d.NAMA_LENGKAP}</td>
            <td>${d.NIP}</td>
            <td>${d.EMAIL}</td>
            <td class="action-btns">
              <button onclick="showEditModal(
                ${d.DOSEN_ID}, 
                '${escapeStr(d.NAMA_LENGKAP)}',
                '${escapeStr(d.NIP)}',
                '${escapeStr(d.EMAIL)}',
                '${escapeStr(d.NO_TELEPON || '')}',
                '${escapeStr(d.ALAMAT || '')}',
                '${escapeStr(d.JENIS_KELAMIN || '')}',
                '${escapeStr(d.TANGGAL_LAHIR || '')}'
              )">Edit</button>
              <button onclick="deleteDosen(${d.DOSEN_ID})">Hapus</button>
            </td>
          </tr>`;
        tbody.insertAdjacentHTML("beforeend", tr);
      });
    } else {
      tbody.innerHTML = "<tr><td colspan='5'>Tidak ada data dosen</td></tr>";
    }
  } catch {
    tbody.innerHTML = "<tr><td colspan='5'>Gagal memuat data</td></tr>";
  }
}

// ========== MODAL HANDLER ==========
function showAddModal() {
  const modal = document.getElementById("formModal");
  modal.classList.add("show");
  document.getElementById("modalTitle").innerText = "Tambah Dosen";
  resetForm();
  document.getElementById("saveBtn").onclick = addDosen;
}

function showEditModal(id, nama, nip, email, telepon, alamat, gender, tgl_lahir) {
  const modal = document.getElementById("formModal");
  modal.classList.add("show");
  document.getElementById("modalTitle").innerText = "Edit Dosen";

  document.getElementById("dosenId").value = id;
  document.getElementById("nama").value = unescapeStr(nama);
  document.getElementById("nip").value = unescapeStr(nip);
  document.getElementById("email").value = unescapeStr(email);
  document.getElementById("telepon").value = unescapeStr(telepon);
  document.getElementById("alamat").value = unescapeStr(alamat);
  document.getElementById("gender").value = unescapeStr(gender) || "Laki-laki";
  document.getElementById("tgl_lahir").value = tgl_lahir ? tgl_lahir.split("T")[0] : "";

  document.getElementById("saveBtn").onclick = updateDosen;
}

function closeModal() {
  document.getElementById("formModal").classList.remove("show");
}

window.onclick = e => {
  const modal = document.getElementById("formModal");
  if (e.target === modal) closeModal();
};

// ========== FORM RESET ==========
function resetForm() {
  document.querySelectorAll(".modal-box input").forEach(i => i.value = "");
  document.getElementById("gender").value = "Laki-laki";
}

// ========== CRUD OPERATIONS ==========
async function addDosen() {
  const payload = getFormData();

  try {
    const res = await fetch(baseUrl + "add_dosen.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    alert(data.message);
    closeModal();
    loadDosen();
  } catch (err) {
    alert("Gagal menambah dosen: " + err.message);
  }
}

async function updateDosen() {
  const payload = getFormData();
  payload.dosen_id = document.getElementById("dosenId").value;

  try {
    const res = await fetch(baseUrl + "update_dosen.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    alert(data.message);
    closeModal();
    loadDosen();
  } catch (err) {
    alert("Gagal memperbarui dosen: " + err.message);
  }
}

async function deleteDosen(id) {
  if (!confirm("Yakin ingin menghapus dosen ini?")) return;

  try {
    const res = await fetch(baseUrl + "delete_dosen.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ dosen_id: id })
    });

    const data = await res.json();
    alert(data.message);
    if (data.success) loadDosen();
  } catch (err) {
    alert("Gagal menghapus dosen: " + err.message);
  }
}

// ========== HELPER FUNCTIONS ==========
function getFormData() {
  return {
    nama_lengkap: document.getElementById("nama").value,
    nip: document.getElementById("nip").value,
    email: document.getElementById("email").value,
    no_telepon: document.getElementById("telepon").value,
    alamat: document.getElementById("alamat").value,
    tanggal_lahir: document.getElementById("tgl_lahir").value,
    jenis_kelamin: document.getElementById("gender").value,
    username: document.getElementById("username").value,
    password: document.getElementById("password").value
  };
}

function escapeStr(str) {
  return str ? str.replace(/'/g, "\\'").replace(/"/g, '\\"') : "";
}

function unescapeStr(str) {
  return str ? str.replace(/\\'/g, "'").replace(/\\"/g, '"') : "";
}

function formatDate(dateStr) {
  try {
    const date = new Date(dateStr);
    return date.toISOString().split("T")[0];
  } catch {
    return "-";
  }
}
