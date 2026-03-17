<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/functions.php';

// Ensure user is logged in for protected pages
if (!defined('ALLOW_UNAUTHENTICATED') && !isLoggedIn()) {
    redirect('/GestionUniversite/index.php');
}

$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'admin';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Université - Espace <?php echo ucfirst(escape($role)); ?></title>
    <link rel="stylesheet" href="/GestionUniversite/assets/css/style.css">
    <link rel="stylesheet" href="/GestionUniversite/assets/css/dashboard.css">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        <!-- Sidebar is included dynamically per role -->
        <?php 
        if ($role !== 'guest') {
            include __DIR__ . "/sidebar_{$role}.php"; 
        }
        ?>
        
        <main class="main-content">
            <header class="topbar">
                <div class="topbar-left">
                    <button id="sidebar-toggle" class="btn-icon"><i class="fas fa-bars"></i></button>
                    <h2>Tableau de Bord</h2>
                </div>
                <div class="topbar-right">
                    <div class="user-profile">
                        <span class="user-name"><?php echo escape(ucfirst($username)); ?> (<?php echo escape(ucfirst($role)); ?>)</span>
                        <div class="avatar"><i class="fas fa-user"></i></div>
                    </div>
                    <a href="/GestionUniversite/logout.php" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                </div>
            </header>
            <div class="content-wrapper">
                <?php displayFlashMessage(); ?>
