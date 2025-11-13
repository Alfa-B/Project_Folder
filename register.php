<?php
session_start();

// JIKA PENGGUNA SUDAH LOGIN, LEMPAR KE DASHBOARD
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error_message = "Password dan konfirmasi password tidak cocok!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "SELECT * FROM users WHERE username = :username";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $error_message = "Username sudah terdaftar!";
        } else {
            // Simpan pengguna baru (role otomatis menjadi 'user')
            $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['username' => $username, 'password' => $hashed_password]);

            // Set session setelah berhasil mendaftar
            $user_id = $conn->lastInsertId();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            // AMBIL DATA USER BARU UNTUK MENDAPATKAN ROLE DAN PROFILE PICTURE DEFAULT
            $sql_new_user = "SELECT * FROM users WHERE id = :id";
            $stmt_new_user = $conn->prepare($sql_new_user);
            $stmt_new_user->execute(['id' => $user_id]);
            $new_user = $stmt_new_user->fetch(PDO::FETCH_ASSOC);

            $_SESSION['profile_picture'] = $new_user['profile_picture'];
            $_SESSION['role'] = $new_user['role'];
            
            header("Location: dashboard.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="d-flex align-items-center py-4 bg-body-tertiary">
<main class="form-signin w-100 m-auto" style="max-width: 400px;">
    <form method="POST" class="card p-4">
        <h1 class="h3 mb-3 fw-normal text-center">Buat Akun Baru</h1>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message; ?></div>
        <?php endif; ?>
        <div class="form-floating mb-2">
            <input type="text" name="username" class="form-control" id="floatingInput" placeholder="Username" required>
            <label for="floatingInput">Username</label>
        </div>
        <div class="form-floating mb-2">
            <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password" required>
            <label for="floatingPassword">Password</label>
        </div>
        <div class="form-floating mb-3">
            <input type="password" name="confirm_password" class="form-control" id="floatingConfirm" placeholder="Konfirmasi Password" required>
            <label for="floatingConfirm">Konfirmasi Password</label>
        </div>
        <button class="btn btn-success w-100 py-2" type="submit">Daftar</button>
        <p class="mt-3 mb-0 text-center text-muted">Sudah punya akun? <a href="login.php">Login di sini</a></p>
    </form>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>