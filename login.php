<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role_selection = $_POST['role']; // Ambil role yang dipilih

    $sql = "SELECT * FROM users WHERE username = :username";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifikasi password DAN peran (role)
    if ($user && password_verify($password, $user['password']) && $user['role'] === $role_selection) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['profile_picture'] = $user['profile_picture'];
        $_SESSION['role'] = $user['role'];

        // Arahkan ke dasbor yang sesuai
        if ($user['role'] === 'admin') {
            header("Location: admin/admin_dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    } else {
        $error_message = "Username, password, atau peran tidak cocok!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="d-flex align-items-center py-4 bg-body-tertiary">
<main class="form-signin w-100 m-auto" style="max-width: 400px;">
    <form method="POST" class="card p-4">
        <h1 class="h3 mb-3 fw-normal text-center">Silakan Login</h1>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message; ?></div>
        <?php endif; ?>

        <div class="form-floating mb-2">
            <input type="text" name="username" class="form-control" id="floatingInput" placeholder="Username" required>
            <label for="floatingInput">Username</label>
        </div>
        <div class="form-floating mb-3">
            <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password" required>
            <label for="floatingPassword">Password</label>
        </div>

        <div class="mb-3 text-center">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="role" id="roleUser" value="user" checked>
                <label class="form-check-label" for="roleUser">User</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="role" id="roleAdmin" value="admin">
                <label class="form-check-label" for="roleAdmin">Admin</label>
            </div>
        </div>

        <button class="btn btn-primary w-100 py-2" type="submit">Login</button>
        <p class="mt-3 mb-0 text-center text-muted">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
    </form>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>