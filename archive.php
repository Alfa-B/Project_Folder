<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil semua data artikel untuk pengguna yang login
$sql = "SELECT id, title, DATE_FORMAT(created_at, '%d %M %Y, %H:%i') as formatted_date 
        FROM articles 
        WHERE user_id = :user_id 
        ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Arsip Data';
include 'header.php';
?>

<h1 class="mb-4">Arsip Data</h1>

<div class="card">
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Judul Data</th>
                    <th scope="col">Tanggal Dibuat</th>
                    <th scope="col" class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($articles) > 0): ?>
                    <?php $count = 1; ?>
                    <?php foreach ($articles as $article): ?>
                        <tr>
                            <th scope="row"><?= $count++ ?></th>
                            <td><?= htmlspecialchars($article['title']) ?></td>
                            <td><?= $article['formatted_date'] ?></td>
                            <td class="text-end">
                                <a href="view_article.php?id=<?= $article['id'] ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye"></i> Lihat
                                </a>
                                <a href="edit_article.php?id=<?= $article['id'] ?>" class="btn btn-outline-warning btn-sm">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">Anda belum memiliki data.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include 'footer.php';
?>