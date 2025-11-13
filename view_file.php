<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
if (!isset($_GET['id'])) {
    echo "File tidak ditemukan.";
    exit;
}

$file_id = $_GET['id'];
$sql = "SELECT * FROM article_files WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $file_id]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file) {
    echo "File tidak ditemukan.";
    exit;
}

$file_path = $file['file_path'];
$file_name = basename($file_path);
$file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

// Daftar ekstensi file yang dikenali
$image_extensions = ['jpg', 'jpeg', 'png', 'gif'];
$excel_extensions = ['xls', 'xlsx'];

$page_title = "Lihat File: " . htmlspecialchars($file_name);
include 'header.php';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-truncate" style="max-width: 70%;">
            <i class="bi bi-file-earmark"></i> <?= htmlspecialchars($file_name) ?>
        </h5>
        <div>
            <a href="<?= htmlspecialchars($file_path) ?>" class="btn btn-success" download>
                <i class="bi bi-download"></i> Unduh
            </a>
            <a href="view_article.php?id=<?= $file['article_id'] ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
    <div class="card-body text-center p-4 bg-light">
        <?php if (in_array($file_extension, $image_extensions)): ?>
            <img src="<?= htmlspecialchars($file_path) ?>" class="img-fluid rounded" alt="Pratinjau Gambar" style="max-height: 80vh;">
        
        <?php elseif ($file_extension == 'pdf'): ?>
            <div class="embed-responsive" style="height: 80vh;">
                <object data="<?= htmlspecialchars($file_path) ?>" type="application/pdf" width="100%" height="100%">
                    <p>Browser Anda tidak mendukung pratinjau PDF. Silakan <a href="<?= htmlspecialchars($file_path) ?>">unduh file</a>.</p>
                </object>
            </div>

        <?php elseif (in_array($file_extension, $excel_extensions)): ?>
            <div class="my-5">
                <i class="bi bi-file-earmark-excel text-success" style="font-size: 6rem;"></i>
                <p class="h4 mt-3">Pratinjau tidak tersedia untuk file Excel.</p>
                <p>Silakan klik tombol "Unduh" di pojok kanan atas untuk membuka file ini.</p>
            </div>

        <?php else: ?>
            <div class="my-5">
                <i class="bi bi-file-earmark-text" style="font-size: 6rem;"></i>
                <p class="h4 mt-3">Pratinjau tidak tersedia.</p>
                <p>Silakan klik tombol "Unduh" untuk melihat file.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
include 'footer.php';
?>