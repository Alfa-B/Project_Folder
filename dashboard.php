<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Proses unggah artikel (tetap sama)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];
    if (!empty($title) && !empty($content)) {
        $sql = "INSERT INTO articles (user_id, title, content) VALUES (:user_id, :title, :content)";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id, 'title' => $title, 'content' => $content]);
        $article_id = $conn->lastInsertId();
        if (isset($_FILES['files'])) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
            foreach ($_FILES['files']['name'] as $key => $name) {
                if ($_FILES['files']['error'][$key] == UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES['files']['tmp_name'][$key];
                    $file_name = time() . '_' . basename($name);
                    $target_file = $upload_dir . $file_name;
                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $sql_file = "INSERT INTO article_files (article_id, file_path) VALUES (:article_id, :file_path)";
                        $stmt_file = $conn->prepare($sql_file);
                        $stmt_file->execute(['article_id' => $article_id, 'file_path' => $target_file]);
                    }
                }
            }
        }
        header("Location: dashboard.php");
        exit;
    }
}

// Pencarian AJAX (tetap sama)
if (isset($_GET['ajax_search'])) {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $sql = "SELECT * FROM articles WHERE user_id = :user_id";
    if ($search) { $sql .= " AND (title LIKE :search OR content LIKE :search)"; }
    $sql .= " ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    if ($search) { $stmt->execute(['user_id' => $_SESSION['user_id'], 'search' => "%$search%"]); } 
    else { $stmt->execute(['user_id' => $_SESSION['user_id']]); }
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($articles);
    exit;
}

// Query awal (tetap sama)
$sql = "SELECT * FROM articles WHERE user_id = :user_id ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set judul halaman dan panggil header
$page_title = 'Dashboard';
include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="welcome-header">Dashboard Anda</h1>
    <form class="d-flex" style="width: 300px;" onsubmit="return false;">
        <input type="text" id="search-input" class="form-control" placeholder="Cari data...">
    </form>
</div>

<p>
  <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#addArticleForm">
    <i class="bi bi-plus-lg"></i> Tambah Data Baru
  </button>
</p>
<div class="collapse mb-4" id="addArticleForm">
  <div class="card card-body">
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3"><label for="title" class="form-label">Judul Data</label><input type="text" name="title" id="title" class="form-control" required></div>
        <div class="mb-3"><label for="content" class="form-label">Isi Data</label><textarea name="content" id="content" class="form-control" required></textarea></div>
        <div class="mb-3"><label for="files" class="form-label">Unggah File</label><input class="form-control" type="file" name="files[]" id="files" multiple></div>
        <button type="submit" class="btn btn-success">Simpan Data</button>
    </form>
  </div>
</div>

<h2><i class="bi bi-list-ul"></i> Data Anda</h2>
<hr>
<div id="articles-container" class="row">
    <?php if (count($articles) > 0): ?>
        <?php foreach ($articles as $article): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column article-card-body">
                        <h5 class="card-title"><?= htmlspecialchars($article['title']) ?></h5>
                        <p class="card-text text-muted flex-grow-1"><?= htmlspecialchars(substr($article['content'], 0, 100)) ?>...</p>
                        <div class="mt-auto">
                            <a href="view_article.php?id=<?= $article['id'] ?>" class="btn btn-outline-primary btn-sm">Lihat</a>
                            <a href="edit_article.php?id=<?= $article['id'] ?>" class="btn btn-outline-warning btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                            <a href="delete_article.php?id=<?= $article['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Yakin?');"><i class="bi bi-trash"></i> Hapus</a>
                            <a href="share.php?id=<?= $article['id'] ?>" class="btn btn-success btn-sm"><i class="bi bi-share"></i> Share</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12"><p>Tidak ada Data yang ditemukan.</p></div>
    <?php endif; ?>
</div>

<script>
document.getElementById('search-input').addEventListener('input', function() {
    const query = this.value;
    fetch(`dashboard.php?ajax_search=1&search=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('articles-container');
            container.innerHTML = ''; 
            if (data.length > 0) {
                data.forEach(article => {
                    const title = article.title.replace(/</g, "&lt;").replace(/>/g, "&gt;");
                    const content = article.content.replace(/</g, "&lt;").replace(/>/g, "&gt;");
                    const articleHtml = `
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column article-card-body">
                                <h5 class="card-title">${title}</h5>
                                <p class="card-text text-muted flex-grow-1">${content.substring(0, 100)}...</p>
                                <div class="mt-auto">
                                    <a href="view_article.php?id=${article.id}" class="btn btn-outline-primary btn-sm">Lihat</a>
                                    <a href="edit_article.php?id=${article.id}" class="btn btn-outline-warning btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                                    <a href="delete_article.php?id=${article.id}" class="btn btn-outline-danger btn-sm" onclick="return confirm('Yakin?');"><i class="bi bi-trash"></i> Hapus</a>
                                    <a href="share.php?id=<?= $article['id'] ?>" class="btn btn-success btn-sm"><i class="bi bi-share"></i> Share</a>
                                </div>
                            </div>
                        </div>
                    </div>`;
                    container.innerHTML += articleHtml;
                });
            } else {
                container.innerHTML = '<div class="col-12"><p>Tidak ada Data yang ditemukan.</p></div>';
            }
        });
});
</script>

<?php
include 'footer.php'; // Panggil footer
?>