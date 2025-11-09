const baseUrl = "../api/admin/mahasiswa/";

document.addEventListener("DOMContentLoaded", loadMahasiswa);

async function loadMahasiswa() {
  const tbody = document.getElementById("mahasiswaTable");
  tbody.innerHTML = "<tr><td colspan='6'>Memuat data...</td></tr>";

  try {
    const res = await fetch(baseUrl + "get_mahasiswa.php");
    const data = await res.json();

    tbody.innerHTML = "";
    if (data.success && data.data.length > 0) {
      data.data.forEach((m, i) => {
        const tr = `
          <tr>
            <td>${i + 1}</td>
            <td>${m.NAMA_LENGKAP}</td>
            <td>${m.NIM}</td>
            <td>${m.EMAIL}</td>
            <td>${m.SEMESTER || "-"}</td>
            <td class="action-btns">
              <button onclick="showEditModal(${m.MAHASISWA_ID}, '${m.NAMA_LENGKAP}', '${m.NIM}', '${m.EMAIL}', '${m.NO_TELEPON || ''}', '${m.ALAMAT || ''}', '${m.JENIS_KELAMIN || ''}', '${m.SEMESTER || ''}')">Edit</button>
              <button onclick="deleteMahasiswa(${m.MAHASISWA_ID})">Hapus</button>
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
  document.getElementById("modalTitle").innerText = "Tambah Mahasiswa";
  resetForm();
  document.getElementById("saveBtn").onclick = addMahasiswa;
}

function showEditModal(id, nama, nim, email, telepon, alamat, gender, semester) {
  const modal = document.getElementById("formModal");
  modal.classList.add("show");
  document.getElementById("modalTitle").innerText = "Edit Mahasiswa";
  document.getElementById("mahasiswaId").value = id;
  document.getElementById("nama").value = nama;
  document.getElementById("nim").value = nim;
  document.getElementById("email").value = email;
  document.getElementById("telepon").value = telepon;
  document.getElementById("alamat").value = alamat;
  document.getElementById("gender").value = gender || "Laki-laki";
  document.getElementById("semester").value = semester || "";
  document.getElementById("saveBtn").onclick = updateMahasiswa;
}

function closeModal() {
  document.getElementById("formModal").classList.remove("show");
}

window.onclick = e => {
  const modal = document.getElementById("formModal");
  if (e.target === modal) closeModal();
};

function resetForm() {
  document.querySelectorAll(".modal-box input").forEach(i => i.value = "");
  document.getElementById("gender").value = "Laki-laki";
}

// ===== CRUD =====
async function addMahasiswa() {
  const payload = getFormData();
  const res = await fetch(baseUrl + "add_mahasiswa.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  });
  const data = await res.json();
  alert(data.message);
  closeModal();
  loadMahasiswa();
}

async function updateMahasiswa() {
  const payload = getFormData();
  payload.mahasiswa_id = document.getElementById("mahasiswaId").value;
  const res = await fetch(baseUrl + "update_mahasiswa.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  });
  const data = await res.json();
  alert(data.message);
  closeModal();
  loadMahasiswa();
}

async function deleteMahasiswa(id) {
  if (!confirm("Yakin ingin menghapus mahasiswa ini?")) return;
  try {
    const res = await fetch(baseUrl + "delete_mahasiswa.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ mahasiswa_id: id })
    });
    const data = await res.json();
    alert(data.message);
    if (data.success) loadMahasiswa();
  } catch (err) {
    alert("Gagal menghapus mahasiswa: " + err.message);
  }
}

function getFormData() {
  return {
    nama_lengkap: document.getElementById("nama").value,
    nim: document.getElementById("nim").value,
    email: document.getElementById("email").value,
    no_telepon: document.getElementById("telepon").value,
    alamat: document.getElementById("alamat").value,
    jenis_kelamin: document.getElementById("gender").value,
    semester: document.getElementById("semester").value,
    username: document.getElementById("username").value,
    password: document.getElementById("password").value
  };
}
