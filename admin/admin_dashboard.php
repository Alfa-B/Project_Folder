<?php
session_start();
// Periksa apakah pengguna adalah admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit;
}

include '../includes/db.php';

// Statistik untuk Admin
$stmt_total_users = $conn->prepare("SELECT COUNT(*) FROM users");
$stmt_total_users->execute();
$total_users = $stmt_total_users->fetchColumn();

$stmt_total_articles = $conn->prepare("SELECT COUNT(*) FROM articles");
$stmt_total_articles->execute();
$total_articles = $stmt_total_articles->fetchColumn();

// Ambil 5 data terakhir dari semua user
$stmt_recent_articles = $conn->prepare("
    SELECT a.id, a.title, u.username 
    FROM articles a JOIN users u ON a.user_id = u.id 
    ORDER BY a.created_at DESC LIMIT 5
");
$stmt_recent_articles->execute();
$recent_articles = $stmt_recent_articles->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Admin Dashboard';
include '../header.php';
?>

<h1 class="mb-4">Admin Dashboard</h1>
<p class="lead">Selamat datang, Admin <?= htmlspecialchars($_SESSION['username']) ?>. Anda memiliki kontrol penuh atas sistem.</p>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card text-white bg-primary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <i class="bi bi-people-fill" style="font-size: 3rem;"></i>
                    <div class="text-end">
                        <p class="display-4 fw-bold mb-0"><?= $total_users ?></p>
                        <p class="mb-0">Total Pengguna Terdaftar</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card text-white bg-success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <i class="bi bi-journal-text" style="font-size: 3rem;"></i>
                    <div class="text-end">
                        <p class="display-4 fw-bold mb-0"><?= $total_articles ?></p>
                        <p class="mb-0">Total Data Dibuat</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-clock-history"></i> Aktivitas Data Terakhir (dari semua user)
    </div>
    <div class="card-body">
        <ul class="list-group list-group-flush">
            <?php foreach ($recent_articles as $article): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <a href="../view_article.php?id=<?= $article['id'] ?>"><?= htmlspecialchars($article['title']) ?></a>
                        <small class="text-muted d-block">oleh: <?= htmlspecialchars($article['username']) ?></small>
                    </div>
                    <a href="../edit_article.php?id=<?= $article['id'] ?>" class="btn btn-sm btn-outline-secondary">Kelola</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<?php include '../footer.php'; ?>