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
            <td>${m.SEMESTER}</td>
            <td class="action-btns">
              <button onclick="showEditModal(${m.MAHASISWA_ID}, '${escapeStr(m.NAMA_LENGKAP)}', '${escapeStr(m.NIM)}', '${escapeStr(m.EMAIL)}', '${escapeStr(m.NO_TELEPON || '')}', '${escapeStr(m.ALAMAT || '')}', '${escapeStr(m.TANGGAL_LAHIR || '')}', '${escapeStr(m.JENIS_KELAMIN || '')}', ${m.ANGKATAN || 0}, ${m.SEMESTER || 1}, '${escapeStr(m.STATUS || 'Aktif')}')">Edit</button>
              <button onclick="deleteMahasiswa(${m.MAHASISWA_ID})">Hapus</button>
            </td>
          </tr>`;
        tbody.insertAdjacentHTML("beforeend", tr);
      });
    } else {
      tbody.innerHTML = "<tr><td colspan='6'>Tidak ada data mahasiswa</td></tr>";
    }
  } catch (err) {
    tbody.innerHTML = "<tr><td colspan='6'>Gagal memuat data</td></tr>";
  }
}

// === Modal Handling ===
function showAddModal() {
  const modal = document.getElementById("formModal");
  modal.classList.add("show");
  document.getElementById("modalTitle").innerText = "Tambah Mahasiswa";
  resetForm();
  document.getElementById("saveBtn").onclick = addMahasiswa;
}

function showEditModal(id, nama, nim, email, telepon, alamat, tgl, gender, angkatan, semester, status) {
  const modal = document.getElementById("formModal");
  modal.classList.add("show");
  document.getElementById("modalTitle").innerText = "Edit Mahasiswa";
  document.getElementById("mahasiswaId").value = id;
  document.getElementById("nama").value = unescapeStr(nama);
  document.getElementById("nim").value = unescapeStr(nim);
  document.getElementById("email").value = unescapeStr(email);
  document.getElementById("telepon").value = unescapeStr(telepon);
  document.getElementById("alamat").value = unescapeStr(alamat);
  document.getElementById("tgl_lahir").value = unescapeStr(tgl);
  document.getElementById("gender").value = gender || "Laki-laki";
  document.getElementById("angkatan").value = angkatan;
  document.getElementById("semester").value = semester;
  document.getElementById("status").value = status;
  document.getElementById("saveBtn").onclick = updateMahasiswa;
}

function closeModal() {
  document.getElementById("formModal").classList.remove("show");
}

document.addEventListener("click", e => {
  const modal = document.getElementById("formModal");
  const box = document.querySelector(".modal-box");
  if (modal.classList.contains("show") && e.target === modal) {
    closeModal();
  }
});

// === Reset Form ===
function resetForm() {
  document.querySelectorAll(".modal-box input").forEach(i => i.value = "");
  document.getElementById("gender").value = "Laki-laki";
  document.getElementById("status").value = "Aktif";
}

// === CRUD ===
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

// === Helper ===
function getFormData() {
  return {
    nama_lengkap: document.getElementById("nama").value,
    nim: document.getElementById("nim").value,
    email: document.getElementById("email").value,
    no_telepon: document.getElementById("telepon").value,
    alamat: document.getElementById("alamat").value,
    tanggal_lahir: document.getElementById("tgl_lahir").value,
    jenis_kelamin: document.getElementById("gender").value,
    angkatan: document.getElementById("angkatan").value,
    semester: document.getElementById("semester").value,
    status: document.getElementById("status").value,
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
