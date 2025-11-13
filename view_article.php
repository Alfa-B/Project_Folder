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

// 2. Ambil data artikel hanya berdasarkan ID artikel
$sql_article = "SELECT *, DATE_FORMAT(created_at, '%d %M %Y') as formatted_date FROM articles WHERE id = :id";
$stmt_article = $conn->prepare($sql_article);
$stmt_article->execute(['id' => $article_id]);
$article = $stmt_article->fetch(PDO::FETCH_ASSOC);

// 3. Cek apakah artikelnya ada
if (!$article) {
    exit("Data tidak ditemukan.");
}

// 4. Otorisasi: Periksa apakah pengguna adalah 'admin' ATAU pemilik asli data
if ($user_role !== 'admin' && $article['user_id'] != $user_id) {
    exit("Anda tidak memiliki izin untuk melihat data ini.");
}

// Mengambil semua file yang terhubung dengan artikel ini
$sql_files = "SELECT * FROM article_files WHERE article_id = :article_id";
$stmt_files = $conn->prepare($sql_files);
$stmt_files->execute(['article_id' => $article_id]);
$files = $stmt_files->fetchAll(PDO::FETCH_ASSOC);

$page_title = htmlspecialchars($article['title']);
include 'header.php';
?>

<div class="card">
    <div class="card-body p-4 p-md-5">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h1 class="card-title mb-1"><?= htmlspecialchars($article['title']) ?></h1>
                <p class="text-muted">Dibuat pada: <?= $article['formatted_date'] ?></p>
            </div>
            <a href="/project_folder/edit_article.php?id=<?= $article['id'] ?>" class="btn btn-warning flex-shrink-0"><i class="bi bi-pencil-fill"></i> Edit</a>
        </div>
        <hr>
        <p class="lead"><?= nl2br(htmlspecialchars($article['content'])) ?></p>

        <?php if (count($files) > 0): ?>
            <h4 class="mt-5">Lampiran</h4>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
                <?php foreach ($files as $file): ?>
                    <div class="col">
                        <?php
                        $file_path = $file['file_path'];
                        $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
                        $image_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                        ?>
                        <a href="/project_folder/view_file.php?id=<?= $file['id'] ?>" class="card text-decoration-none">
                            <?php if (in_array($file_extension, $image_extensions)): ?>
                                <img src="/project_folder/<?= htmlspecialchars($file_path) ?>" class="card-img-top" alt="Gambar Lampiran" style="height: 150px; object-fit: cover;">
                                <div class="card-body text-center p-2">
                                    <p class="card-text text-truncate small"><?= htmlspecialchars(basename($file_path)) ?></p>
                                </div>
                            <?php else: ?>
                                <div class="card-body text-center p-3">
                                    <i class="bi bi-file-earmark-text" style="font-size: 2rem;"></i>
                                    <p class="card-text text-truncate mt-2"><?= htmlspecialchars(basename($file_path)) ?></p>
                                </div>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
include 'footer.php';
?>