<?php
session_start();
include 'includes/db.php';

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Periksa apakah parameter id tersedia
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$article_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// 1. Ambil semua path file yang akan dihapus dari tabel 'article_files'
//    Kita juga memastikan artikel ini milik pengguna yang sedang login.
$sql_files = "SELECT af.file_path 
              FROM article_files af
              JOIN articles a ON af.article_id = a.id
              WHERE af.article_id = :article_id AND a.user_id = :user_id";
              
$stmt_files = $conn->prepare($sql_files);
$stmt_files->execute(['article_id' => $article_id, 'user_id' => $user_id]);
$files_to_delete = $stmt_files->fetchAll(PDO::FETCH_ASSOC);

// Jika query tidak menghasilkan apa-apa (artikel tidak ada atau bukan milik user)
// $files_to_delete akan kosong, tapi proses delete di bawah tetap aman.

// 2. Hapus setiap file fisik dari server
foreach ($files_to_delete as $file) {
    if (file_exists($file['file_path'])) {
        unlink($file['file_path']); // Fungsi PHP untuk menghapus file
    }
}

// 3. Hapus data artikel dari tabel 'articles'
//    Karena kita sudah mengatur 'ON DELETE CASCADE', semua data yang terkait
//    di tabel 'article_files' akan otomatis ikut terhapus juga.
$sql_delete_article = "DELETE FROM articles WHERE id = :id AND user_id = :user_id";
$stmt_delete = $conn->prepare($sql_delete_article);

if ($stmt_delete->execute(['id' => $article_id, 'user_id' => $user_id])) {
    header("Location: dashboard.php?message=Artikel dan semua file berhasil dihapus");
    exit;
} else {
    header("Location: dashboard.php?message=Gagal menghapus artikel");
    exit;
}
?>