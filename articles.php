<?php
session_start();
include 'includes/db.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil artikel berdasarkan ID dari URL
if (!isset($_GET['id'])) {
    echo "data tidak ditemukan.";
    exit;
}
$article_id = $_GET['id'];

// Query untuk mendapatkan artikel berdasarkan ID
$sql = "SELECT articles.id, articles.title, articles.content, articles.created_at, users.username 
        FROM articles 
        JOIN users ON articles.user_id = users.id 
        WHERE articles.id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $article_id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika data tidak ditemukan
if (!$article) {
    echo "data tidak ditemukan.";
    exit;
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <a href="dashboard.php" class="btn btn-secondary mb-3">Kembali ke Dashboard</a>
    <h1><?php echo htmlspecialchars($article['title']); ?></h1>
    <p class="text-muted">Ditulis oleh <?php echo htmlspecialchars($article['username']); ?> pada <?php echo $article['created_at']; ?></p>
    <div class="card mb-4">
        <div class="card-body">
            <p><?php echo nl2br(htmlspecialchars($article['content'])); ?></p>
        </div>
    </div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
