<?php
session_start();
include 'includes/db.php';
include 'includes/email_config.php';

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: dashboard.php");
    exit;
}

// Ambil artikel dan pastikan milik user saat ini
$sql = "SELECT * FROM articles WHERE id = :id AND user_id = :user_id LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id, 'user_id' => $_SESSION['user_id']]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$article) {
    header("Location: dashboard.php");
    exit;
}

// Buat link yang bisa dibagikan
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$base = rtrim(dirname($_SERVER['REQUEST_URI']), '/\\');
$share_link = $scheme . '://' . $host . $base . '/view_article.php?id=' . $id;

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $message = trim($_POST['message'] ?? '');
    
    if (!$to) {
        $error = 'Alamat email tidak valid.';
    } else {
        try {
            $mail = new PHPMailer(true);
            
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            
            // Email settings
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($to);
            $mail->CharSet = 'UTF-8';
            
            // Content
            $mail->isHTML(false);
            $mail->Subject = 'Saya membagikan data: ' . $article['title'];
            $mail->Body = "Halo,\n\n" .
                         "Saya ingin membagikan data berikut dengan Anda:\n\n" .
                         "Judul: " . $article['title'] . "\n\n" .
                         "Link: " . $share_link . "\n\n" .
                         ($message ? "Pesan:\n" . $message . "\n\n" : "") .
                         "Salam.";
            
            $mail->send();
            $success = 'Email berhasil dikirim ke ' . htmlspecialchars($to) . '.';
            
        } catch (Exception $e) {
            $error = 'Gagal mengirim email: ' . $mail->ErrorInfo . '. Coba gunakan tombol Salin Link sebagai alternatif.';
        }
    }
}

$page_title = 'Share Data';
include 'header.php';
?>
<div class="container mt-4">
    <h1>Share: <?= htmlspecialchars($article['title']) ?></h1>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Ini tugas</h5>
            <p><?= nl2br(htmlspecialchars($article['content'])) ?></p>
            
            <hr>
            
            <p><strong>Link share:</strong></p>
            <div class="input-group mb-3">
                <input type="text" id="shareLink" class="form-control" value="<?= htmlspecialchars($share_link) ?>" readonly>
                <button class="btn btn-outline-secondary" id="copyBtn" type="button">Salin Link</button>
            </div>

            <form method="POST" class="mb-0">
                <div class="mb-3">
                    <label for="email" class="form-label">Kirim ke (email)</label>
                    <input type="email" name="email" id="email" class="form-control" required 
                           placeholder="contoh@email.com">
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">Pesan (opsional)</label>
                    <textarea name="message" id="message" class="form-control" rows="3" 
                              placeholder="Tambahkan pesan personal..."></textarea>
                </div>
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-envelope"></i> Kirim Email
                </button>
                <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('copyBtn').addEventListener('click', function(){
    const el = document.getElementById('shareLink');
    el.select();
    el.setSelectionRange(0, 99999);
    try {
        document.execCommand('copy');
        this.textContent = '✓ Tersalin';
        this.classList.remove('btn-outline-secondary');
        this.classList.add('btn-success');
        setTimeout(() => {
            this.textContent = 'Salin Link';
            this.classList.remove('btn-success');
            this.classList.add('btn-outline-secondary');
        }, 2000);
    } catch(e) {
        alert('Gagal menyalin. Salin manual: ' + el.value);
    }
});
</script>

<?php include 'footer.php'; ?>