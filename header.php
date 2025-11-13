<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Aplikasi Saya' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/project_folder/css.css"> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
<div class="d-flex" id="wrapper">
    <div class="bg-white border-end" id="sidebar-wrapper">
        <div class="sidebar-heading border-bottom bg-light">Aplikasi Data</div>
        <div class="list-group list-group-flush">
            <a class="list-group-item list-group-item-action list-group-item-light p-3" href="/project_folder/dashboard.php"><i class="bi bi-house-door-fill me-2"></i> Home</a>
            <a class="list-group-item list-group-item-action list-group-item-light p-3" href="/project_folder/statistics.php"><i class="bi bi-bar-chart-fill me-2"></i> Statistik</a>
            <a class="list-group-item list-group-item-action list-group-item-light p-3" href="/project_folder/archive.php"><i class="bi bi-archive-fill me-2"></i> Arsip</a>
            
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a class="list-group-item list-group-item-action list-group-item-danger p-3" href="/project_folder/admin/admin_dashboard.php"><i class="bi bi-shield-lock-fill me-2"></i> Admin Panel</a>
            <?php endif; ?>
        </div>
    </div>
    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
            <div class="container-fluid">
                <button class="btn btn-primary" id="sidebarToggle"><i class="bi bi-list"></i></button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img src="/project_folder/<?= htmlspecialchars($_SESSION['profile_picture'] ?? 'assets/default-profile.png'); ?>" class="profile-pic me-2">
                                <?= htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="/project_folder/profile.php">Perbarui Profil</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="/project_folder/logout.php">Logout</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <main class="container-fluid p-4">