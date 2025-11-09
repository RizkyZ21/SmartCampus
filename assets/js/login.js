// =====================================================
// LOGIN HANDLER UNTUK SMARTCAMPUS
// by ChatGPT (GPT-5)
// =====================================================

async function login(event) {
  event.preventDefault();

  const username = document.getElementById("username").value.trim();
  const password = document.getElementById("password").value.trim();
  const role = document.getElementById("role").value;

  if (!username || !password) {
    alert("Username dan password wajib diisi!");
    return;
  }

  // tentukan endpoint API berdasarkan role
  let apiUrl = "";
  switch (role) {
    case "admin":
      apiUrl = "api/admin/login.php";
      break;
    case "dosen":
      apiUrl = "api/dosen/login.php";
      break;
    case "mahasiswa":
      apiUrl = "api/mahasiswa/login.php";
      break;
    default:
      alert("Role tidak dikenali!");
      return;
  }

  try {
    const response = await fetch(apiUrl, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ username, password }),
    });

    const result = await response.json();

    if (result.success) {
      alert(result.message);

      // simpan data user ke localStorage
      localStorage.setItem("userData", JSON.stringify(result.data));

      // redirect ke halaman sesuai role
      if (role === "admin") window.location.href = "admin/dashboard.html";
      else if (role === "dosen") window.location.href = "dosen/dashboard.html";
      else if (role === "mahasiswa") window.location.href = "mahasiswa/dashboard.html";
    } else {
      alert(result.message || "Login gagal, periksa data Anda");
    }
  } catch (error) {
    console.error("❌ Error:", error);
    alert("Terjadi kesalahan koneksi ke server!");
  }
}
