<?php
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :u");
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: index.php');
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Login</title>
<style>
body{font-family:Arial;background:#f8f8f8;margin:40px;text-align:center}
form{max-width:300px;margin:auto;background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.1)}
input{width:100%;padding:8px;margin:6px 0}
button{padding:8px 12px;border:0;background:#007bff;color:#fff;border-radius:6px;cursor:pointer}
a{font-size:13px;color:#007bff;text-decoration:none}
.error{color:#d9534f}
.success{color:#0f5132}
</style>
</head>
<body>
<h2>Login</h2>
<?php if(isset($_GET['success']) && $_GET['success']==='registered'): ?>
  <p class="success">Pendaftaran berhasil! Silakan login.</p>
<?php endif; ?>
<?php if(isset($error)): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post">
  <input type="text" name="username" placeholder="Username" required>
  <input type="password" name="password" placeholder="Password" required>
  <button type="submit">Login</button>
</form>
<p>Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
</body>
</html>
