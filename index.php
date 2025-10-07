<?php
require __DIR__ . '/config.php'; 

// ===== CEK LOGIN =====
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

function e($string) { //  XSS
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

$action = $_GET['action'] ?? null; 

// ==== TAMBAH DATA ====
if ($_SERVER['REQUEST_METHOD'] === 'POST' 
    && isset($_POST['title']) 
    && !isset($_POST['update_status']) 
    && !isset($_POST['update_prioritas'])) {

    $title = trim($_POST['title']);
    $deskripsi = trim($_POST['deskripsi']);
    $jatuh_tempo = $_POST['jatuh_tempo'] ?? null;

    if ($title !== '') {
        $stmt = $pdo->prepare('INSERT INTO todol (title, deskripsi, jatuh_tempo) VALUES (:title, :deskripsi, :jatuh_tempo)');
        $stmt->execute([':title'=>$title, ':deskripsi'=>$deskripsi, ':jatuh_tempo'=>$jatuh_tempo]);
    }
    header('Location: index.php?success=added');
    exit;
}

// ==== UPDATE STATUS ====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = (int)$_POST['id'];
    $newStatus = (int)$_POST['is_done'];
    $stmt = $pdo->prepare('UPDATE todol SET is_done = :is_done WHERE id = :id');
    $stmt->execute([':is_done'=>$newStatus, ':id'=>$id]);
    header('Location: index.php?success=status');
    exit;
}

// ==== UPDATE PRIORITAS ====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_prioritas'])) {
    $id = (int)$_POST['id'];
    $newPriority = (int)$_POST['prioritas'];
    $stmt = $pdo->prepare('UPDATE todol SET prioritas = :prioritas WHERE id = :id');
    $stmt->execute([':prioritas'=>$newPriority, ':id'=>$id]);
    header('Location: index.php?success=prioritas');
    exit;
}

// ==== HAPUS DATA ====
if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare('DELETE FROM todol WHERE id = :id');
    $stmt->execute([':id'=>$id]);
    header('Location: index.php?success=deleted');
    exit;
}

// ==== FITUR SEARCH ====
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM todol WHERE title LIKE :keyword ORDER BY Created_at DESC");
    $stmt->execute([':keyword' => "%$search%"]);
} else {
    $stmt = $pdo->prepare('SELECT * FROM todol ORDER BY Created_at DESC');
    $stmt->execute();
}
$todos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Todo List</title>
<style>
body {
    font-family: Arial;
    margin: 40px;
    background: #f8f8f8;
}
.card {
    max-width: 520px;
    margin: 0 auto;
    background: #fff;
    padding: 18px;
    border-radius: 8px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.05);
    text-align: center;
}
h1 {font-size: 20px; margin: 8px 0 12px;}
.logo {width: 80px; height: 80px; object-fit: contain; margin-bottom: 8px; border-radius: 10px;}
form {display: flex; flex-direction: column; gap: 8px; text-align: left;}
input[type=text], input[type=date], textarea {
    padding: 8px; border: 1px solid #ddd; border-radius: 6px; width: 100%;
}
button {
    padding: 8px 10px; border-radius: 6px; border: 0; cursor: pointer;
}
ul {list-style: none; padding: 0; margin: 12px 0;}
li {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 0; border-bottom: 1px solid #f0f0f0;
}
.left {display: flex; flex-direction: column; gap: 4px; text-align: left;}
.title {font-size: 15px; transition: all .3s ease;}
.done {text-decoration: line-through; color: #888;}
.status-select, .priority-select {
    padding: 4px 6px; border-radius: 5px; border: 1px solid #ccc; font-size: 13px;
}
.btn-delete {
    background: #d9534f; color: #fff; font-size: 13px;
    padding: 4px 8px; border-radius: 5px; border: 0; cursor: pointer;
}
.small, .timestamp {font-size: 12px; color: #666;}
.priority-label {font-size: 13px; color: #444;}
.alert {
    padding: 10px 14px; margin-bottom: 15px; border-radius: 6px;
    font-size: 14px; text-align: center;
}
.alert-success {
    background: #d1e7dd; color: #0f5132; border: 1px solid #badbcc;
}
.alert-delete {
    background: #f8d7da; color: #842029; border: 1px solid #f5c2c7;
}
.search-form {
    display: flex;
    gap: 6px;
    margin-bottom: 12px;
}
.search-form input[type=text] {
    flex: 1;
}
.logout-bar {
    text-align: right;
    margin-bottom: 10px;
    font-size: 14px;
}
.logout-bar a {
    color: #d9534f;
    text-decoration: none;
}
.logout-bar a:hover {
    text-decoration: underline;
}
</style>
</head>
<body>
<div class="card">
  <div class="logout-bar">
    helo woi, <strong><?= e($_SESSION['username']) ?></strong> |
    <a href="logout.php">Logout</a>
  </div>

  <img src="logo.png" alt="Logo" class="logo">
  <h1>Tugas Ujikom</h1>

  <!-- Form Pencarian -->
  <form method="get" class="search-form">
    <input type="text" name="search" placeholder="Cari tugas berdasarkan nama..." value="<?= e($search) ?>">
    <button type="submit">Cari</button>
  </form>

  <!-- Tombol reset pencarian -->
  <?php if($search !== ''): ?>
    <form method="get" style="margin-bottom:10px;">
      <button type="submit">Tampilkan Semua</button>
    </form>
  <?php endif; ?>

<?php if(isset($_GET['success'])): ?>
  <?php if($_GET['success']==='added'): ?><div class="alert alert-success">Tugas berhasil ditambahkan!</div>
  <?php elseif($_GET['success']==='deleted'): ?><div class="alert alert-delete">Tugas berhasil dihapus!</div>
  <?php elseif($_GET['success']==='status'): ?><div class="alert alert-success">Status tugas diperbarui!</div>
  <?php elseif($_GET['success']==='prioritas'): ?><div class="alert alert-success">Prioritas tugas diperbarui!</div>
  <?php endif; ?>
  <script>setTimeout(()=>{document.querySelector('.alert')?.remove()},3000);</script>
<?php endif; ?>

<form method="post"> <!-- tambah tugas baru -->
  <input type="text" name="title" placeholder="Tambah tugas baru..." required>
  <textarea name="deskripsi" placeholder="Tambah deskripsi..." required></textarea>
  <label class="small">Jatuh Tempo:</label>
  <input type="date" name="jatuh_tempo" required>
  <button type="submit">Tambah</button>
</form>

<ul>
<?php if(empty($todos)): ?>
  <li><span class="small">Belum ada tugas. Tambah yang pertama!</span></li>
<?php else: foreach($todos as $t): ?>
  <?php
    $deadlineLabel = ''; 
    $deadlineStyle = ''; 
    if (!$t['is_done'] && !empty($t['jatuh_tempo'])) {
        $today = new DateTime();
        $due = new DateTime($t['jatuh_tempo']);
        $diff = (int)$today->diff($due)->format('%r%a');

        if ($diff < 0) {
            $deadlineLabel = '<span style="color:#d9534f;font-weight:bold;">(Sudah Lewat Deadline!)</span>';
            $deadlineStyle = 'background:#ffe5e5;';
        } elseif ($diff <= 2) {
            $deadlineLabel = '<span style="color:#e67e22;font-weight:bold;">(Hampir Deadline!)</span>';
            $deadlineStyle = 'background:#fff3cd;';
        }
    }
  ?>
  <li style="<?= $deadlineStyle ?>">
    <div class="left">
      <span class="title <?= $t['is_done'] ? 'done':'' ?>"><?= e($t['title']) ?></span>
      <span class="timestamp">Deskripsi: <?= e($t['deskripsi']) ?></span>
      <span class="timestamp">
        Jatuh Tempo: <?= e($t['jatuh_tempo'] ?? '—') ?> <?= $deadlineLabel ?>
      </span>
      <span class="timestamp">
        Dibuat: <?= isset($t['Created_at']) ? e(date('d M Y, H:i', strtotime($t['Created_at']))) : '—' ?>
      </span>
      <span class="priority-label">
        Prioritas:
        <?php
          if(!isset($t['prioritas'])) echo '—';
          else echo match((int)$t['prioritas']){2=>'Tinggi',1=>'Sedang',default=>'Rendah'};
        ?>
      </span>
    </div>
    <div style="display:flex;align-items:center;gap:6px;">
      <form method="post" style="margin:0;">
        <input type="hidden" name="id" value="<?= e($t['id']) ?>">
        <input type="hidden" name="update_status" value="1">
        <select name="is_done" class="status-select" onchange="updateStatus(this)">
          <option value="0" <?= !$t['is_done']?'selected':'' ?>>Belum Selesai</option>
          <option value="1" <?= $t['is_done']?'selected':'' ?>>Sudah Selesai</option>
        </select>
      </form>

      <form method="post" style="margin:0;">
        <input type="hidden" name="id" value="<?= e($t['id']) ?>">
        <input type="hidden" name="update_prioritas" value="1">
        <select name="prioritas" class="priority-select" onchange="this.form.submit()">
          <option value="0" <?= (isset($t['prioritas']) && $t['prioritas']==0)?'selected':'' ?>>Rendah</option>
          <option value="1" <?= (isset($t['prioritas']) && $t['prioritas']==1)?'selected':'' ?>>Sedang</option>
          <option value="2" <?= (isset($t['prioritas']) && $t['prioritas']==2)?'selected':'' ?>>Tinggi</option>
        </select>
      </form>

      <a href="?action=delete&id=<?= e($t['id']) ?>" onclick="return confirm('Hapus tugas ini?')">
        <button class="btn-delete">Hapus</button>
      </a>
    </div>
  </li>
<?php endforeach; endif; ?>
</ul>
</div>

<script>
function updateStatus(select) {
  const li = select.closest('li');
  const title = li.querySelector('.title');
  if (select.value === '1') title.classList.add('done');
  else title.classList.remove('done');
  setTimeout(() => select.form.submit(), 200);
}
</script>
</body>
</html>
