<?php
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username !== '' && $password !== '') {
        // cek apakah username sudah ada
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :u");
        $stmt->execute([':u' => $username]);
        if ($stmt->fetch()) {
            $error = "Username sudah digunakan!";
        } else {
            // simpan user baru
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:u, :p)");
            $stmt->execute([':u' => $username, ':p' => $hash]);
            header('Location: login.php?success=registered');
            exit;
        }
    } else {
        $error = "Isi semua kolom!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Register</title>
<style>
body{font-family:Arial;background:#f8f8f8;margin:40px;text-align:center}
form{max-width:300px;margin:auto;background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.1)}
input{width:100%;padding:8px;margin:6px 0}
button{padding:8px 12px;border:0;background:#007bff;color:#fff;border-radius:6px;cursor:pointer}
a{font-size:13px;color:#007bff;text-decoration:none}
.error{color:#d9534f}
</style>
</head>
<body>
<h2>Daftar Akun Baru</h2>
<?php if(isset($error)): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post">
  <input type="text" name="username" placeholder="Username" required>
  <input type="password" name="password" placeholder="Password" required>
  <button type="submit">Daftar</button>
</form>
<p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
</body>
</html>
