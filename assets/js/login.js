async function login(event) {
  event.preventDefault();

  const username = document.getElementById("username").value.trim();
  const password = document.getElementById("password").value.trim();
  const role = document.getElementById("role").value;

  if (!username || !password) {
    alert("Username dan password wajib diisi!");
    return;
  }

  // Tentukan endpoint API berdasarkan role
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
      const data = result.data;

      alert(result.message);

      // Simpan ke localStorage (data lengkap)
      localStorage.setItem("userData", JSON.stringify(data));

      // Simpan ke sessionStorage (khusus untuk dashboard)
      if (role === "admin") {
        sessionStorage.setItem("admin_id", data.USER_ID);
        sessionStorage.setItem("username_admin", data.USERNAME);
      } else if (role === "dosen") {
        sessionStorage.setItem("dosen_id", data.DOSEN_ID);
        sessionStorage.setItem("nama_dosen", data.NAMA_LENGKAP);
        console.log("✅ Login Dosen:", data.NAMA_LENGKAP, " | ID:", data.DOSEN_ID);
      } else if (role === "mahasiswa") {
        sessionStorage.setItem("mahasiswa_id", data.MAHASISWA_ID);
        sessionStorage.setItem("nama_mahasiswa", data.NAMA_LENGKAP);
      }

      // Redirect sesuai role
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
