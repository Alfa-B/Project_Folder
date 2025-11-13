<?php
session_start();
include 'includes/db.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Pastikan ID file dan ID artikel ada di URL
if (!isset($_GET['file_id']) || !isset($_GET['article_id'])) {
    header("Location: dashboard.php");
    exit;
}

$file_id = $_GET['file_id'];
$article_id = $_GET['article_id'];
$user_id = $_SESSION['user_id'];

// 1. Ambil path file dari database untuk memastikan file ini milik pengguna yang login
$sql = "SELECT af.file_path 
        FROM article_files af
        JOIN articles a ON af.article_id = a.id
        WHERE af.id = :file_id AND a.user_id = :user_id";

$stmt = $conn->prepare($sql);
$stmt->execute(['file_id' => $file_id, 'user_id' => $user_id]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika file ditemukan
if ($file) {
    // 2. Hapus file fisik dari server
    if (file_exists($file['file_path'])) {
        unlink($file['file_path']);
    }

    // 3. Hapus record file dari database
    $sql_delete = "DELETE FROM article_files WHERE id = :file_id";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->execute(['file_id' => $file_id]);
}

// 4. Redirect kembali ke halaman edit artikel
header("Location: edit_article.php?id=" . $article_id);
exit;