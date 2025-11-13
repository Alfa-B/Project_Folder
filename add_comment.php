<?php
session_start();
include 'includes/db.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Proses menyimpan komentar
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $comment = $_POST['comment'];
    $article_id = $_POST['article_id'];
    $user_id = $_SESSION['user_id'];

    // Validasi komentar tidak boleh kosong
    if (empty(trim($comment))) {
        echo "Komentar tidak boleh kosong.";
        exit;
    }

    // Simpan komentar ke database
    $sql = "INSERT INTO comments (article_id, user_id, comment) VALUES (:article_id, :user_id, :comment)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'article_id' => $article_id,
        'user_id' => $user_id,
        'comment' => $comment
    ]);

    // Redirect kembali ke artikel
    header("Location: articles.php?id=$article_id");
    exit;
}
?>
