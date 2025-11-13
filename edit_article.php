<?php
session_start();
include 'includes/db.php';

// 1. Cek login dan parameter ID
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php");
    exit; 
}
if (!isset($_GET['id'])) { 
    header("Location: dashboard.php");
    exit; 
}

$article_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$message = '';
$error = '';

// 2. Ambil data artikel hanya berdasarkan ID artikel
$sql_article = "SELECT * FROM articles WHERE id = :id";
$stmt_article = $conn->prepare($sql_article);
$stmt_article->execute(['id' => $article_id]);
$article = $stmt_article->fetch(PDO::FETCH_ASSOC);

// 3. Cek apakah artikelnya ada
if (!$article) {
    exit("Data tidak ditemukan.");
}

// 4. Otorisasi: Periksa apakah pengguna adalah 'admin' ATAU pemilik asli data
if ($user_role !== 'admin' && $article['user_id'] != $user_id) {
    exit("Anda tidak memiliki izin untuk mengedit data ini.");
}

// 5. Proses form saat disubmit (logika ini sebagian besar sama)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_all_changes'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];

    // Update judul dan konten (aman karena user sudah diotorisasi)
    $sql_update_article = "UPDATE articles SET title = :title, content = :content WHERE id = :id";
    $stmt_update_article = $conn->prepare($sql_update_article);
    $stmt_update_article->execute(['title' => $title, 'content' => $content, 'id' => $article_id]);
    $message = "Perubahan berhasil disimpan!";

    // Proses jika ada file baru yang diunggah
    if (isset($_FILES['files'])) {
        foreach ($_FILES['files']['name'] as $key => $name) {
            if ($_FILES['files']['error'][$key] == UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/';
                $tmp_name = $_FILES['files']['tmp_name'][$key];
                $file_name = time() . '_' . basename($name);
                $target_file = $upload_dir . $file_name;
                if (move_uploaded_file($tmp_name, $target_file)) {
                    $sql_insert_file = "INSERT INTO article_files (article_id, file_path) VALUES (:article_id, :file_path)";
                    $stmt_insert_file = $conn->prepare($sql_insert_file);
                    $stmt_insert_file->execute(['article_id' => $article_id, 'file_path' => $target_file]);
                    $message .= " File baru berhasil diunggah.";
                }
            }
        }
    }
    // Refresh data artikel setelah update
    $stmt_article->execute(['id' => $article_id]);
    $article = $stmt_article->fetch(PDO::FETCH_ASSOC);
}

// Mengambil data file lampiran
$sql_files = "SELECT * FROM article_files WHERE article_id = :article_id";
$stmt_files = $conn->prepare($sql_files);
$stmt_files->execute(['article_id' => $article_id]);
$files = $stmt_files->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Edit Data';
include 'header.php';
?>

<!-- Pesan Notifikasi -->
<?php if ($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <div class="row g-4">
        <!-- Kolom Edit Konten & Tambah File -->
        <div class="col-lg-8">
            <h2 class="mb-4">Edit Konten</h2>
            <div class="card">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label for="title" class="form-label">Judul Data</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($article['title']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Isi Data</label>
                        <textarea name="content" class="form-control" rows="8" required><?= htmlspecialchars($article['content']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="files" class="form-label">Tambah File Baru</label>
                        <input class="form-control" type="file" name="files[]" id="files" multiple>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Daftar Lampiran -->
        <div class="col-lg-4">
            <h2 class="mb-4">Manajemen Lampiran</h2>
            <div class="card">
                <div class="card-body">
                    <p><strong>File Saat Ini:</strong></p>
                    <?php if (count($files) > 0): ?>
                        <ul class="list-group">
                            <?php foreach ($files as $file): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="text-truncate" title="<?= htmlspecialchars(basename($file['file_path'])) ?>">
                                        <?= htmlspecialchars(basename($file['file_path'])) ?>
                                    </span>
                                    <a href="/project_folder/delete_file.php?file_id=<?= $file['id'] ?>&article_id=<?= $article_id ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Yakin?');"><i class="bi bi-trash"></i></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">Belum ada lampiran.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Tombol Aksi Terpusat -->
    <div class="mt-4">
        <button type="submit" name="save_all_changes" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Semua Perubahan</button>
        <a href="/project_folder/dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
    </div>
</form>

<?php include 'footer.php'; ?>