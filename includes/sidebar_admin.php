<?php
// includes/sidebar_admin.php
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-university"></i>
        <span>GesUni Pro</span>
    </div>
    <ul class="sidebar-nav">
        <li class="nav-item">
            <a href="/GestionUniversite/views/admin/dashboard.php" class="nav-link active">
                <i class="fas fa-home"></i> <span>Tableau de bord</span>
            </a>
        </li>
        <li class="nav-header">Académique</li>
        <li class="nav-item">
            <a href="/GestionUniversite/views/admin/faculties.php" class="nav-link">
                <i class="fas fa-building"></i> <span>Facultés & Dépts</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/GestionUniversite/views/admin/courses.php" class="nav-link">
                <i class="fas fa-book"></i> <span>Matières (UE)</span>
            </a>
        </li>
        <li class="nav-header">Utilisateurs</li>
        <li class="nav-item">
            <a href="/GestionUniversite/views/admin/students.php" class="nav-link">
                <i class="fas fa-user-graduate"></i> <span>Étudiants</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/GestionUniversite/views/admin/teachers.php" class="nav-link">
                <i class="fas fa-chalkboard-teacher"></i> <span>Enseignants</span>
            </a>
        </li>
        <li class="nav-header">Gestion Avancée</li>
        <li class="nav-item">
            <a href="/GestionUniversite/views/admin/timetables.php" class="nav-link">
                <i class="fas fa-calendar-alt"></i> <span>Emplois du temps</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/GestionUniversite/views/admin/logs.php" class="nav-link">
                <i class="fas fa-history"></i> <span>Journal Système</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/GestionUniversite/views/admin/settings.php" class="nav-link">
                <i class="fas fa-cogs"></i> <span>Paramètres</span>
            </a>
        </li>
    </ul>
</aside>
