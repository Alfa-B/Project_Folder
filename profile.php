<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Cek tombol mana yang diklik
    if (isset($_POST['update_photo'])) {
        // Proses update gambar profil
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/profiles/';
            if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
            
            $file_name = time() . '_' . basename($_FILES["profile_picture"]["name"]);
            $target_file = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                $sql_old_pic = "SELECT profile_picture FROM users WHERE id = :id";
                $stmt_old_pic = $conn->prepare($sql_old_pic);
                $stmt_old_pic->execute(['id' => $user_id]);
                $old_pic = $stmt_old_pic->fetchColumn();
                if ($old_pic != 'assets/default-profile.png' && file_exists($old_pic)) {
                    unlink($old_pic);
                }

                $sql = "UPDATE users SET profile_picture = :profile_picture WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['profile_picture' => $target_file, 'id' => $user_id]);
                
                $_SESSION['profile_picture'] = $target_file;
                $message = "Foto profil berhasil diperbarui!";
            } else {
                $error = "Gagal mengunggah foto profil.";
            }
        } else {
            $error = "Pilih file gambar terlebih dahulu.";
        }
    } elseif (isset($_POST['update_password'])) {
        // Proses update password
        if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            $sql_user = "SELECT password FROM users WHERE id = :id";
            $stmt_user = $conn->prepare($sql_user);
            $stmt_user->execute(['id' => $user_id]);
            $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

            if (password_verify($current_password, $user['password'])) {
                if ($new_password === $confirm_password) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $sql_update_pass = "UPDATE users SET password = :password WHERE id = :id";
                    $stmt_update_pass = $conn->prepare($sql_update_pass);
                    $stmt_update_pass->execute(['password' => $hashed_password, 'id' => $user_id]);
                    $message = "Password berhasil diubah!";
                } else {
                    $error = "Konfirmasi password baru tidak cocok.";
                }
            } else {
                $error = "Password saat ini salah.";
            }
        } else {
            $error = "Semua field password harus diisi.";
        }
    }
}

$page_title = 'Perbarui Profil';
include 'header.php';
?>

<h1 class="mb-4">Perbarui Profil</h1>

<?php if ($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<div class="row">
    <div class="col-md-4 text-center">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Foto Profil</h5>
                <img src="<?= htmlspecialchars($_SESSION['profile_picture']); ?>" class="img-fluid rounded-circle mb-3" style="width: 200px; height: 200px; object-fit: cover;">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <input class="form-control" type="file" id="profile_picture" name="profile_picture" accept="image/*" required>
                    </div>
                    <button type="submit" name="update_photo" class="btn btn-primary">Unggah Foto</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Ubah Password</h5>
                <p class="card-text text-muted small">Kosongkan jika tidak ingin mengubah password.</p>
                <form method="POST">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Password Saat Ini</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Password Baru</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                    <button type="submit" name="update_password" class="btn btn-warning">Ubah Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include 'footer.php';
?>