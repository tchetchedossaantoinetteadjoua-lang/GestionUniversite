<?php
// includes/sidebar_student.php
?>
<aside class="sidebar" style="background-color: #0f172a;">
    <div class="sidebar-brand">
        <i class="fas fa-university"></i>
        <span>Espace Étudiant</span>
    </div>
    <ul class="sidebar-nav">
        <li class="nav-item">
            <a href="/GestionUniversite/views/student/dashboard.php" class="nav-link">
                <i class="fas fa-home"></i> <span>Mon Tableau de bord</span>
            </a>
        </li>
        <li class="nav-header">Scolarité</li>
        <li class="nav-item">
            <a href="/GestionUniversite/views/student/my_courses.php" class="nav-link">
                <i class="fas fa-book-reader"></i> <span>Mes Cours (UE)</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/GestionUniversite/views/student/results.php" class="nav-link">
                <i class="fas fa-graduation-cap"></i> <span>Notes & Résultats</span>
            </a>
        </li>
        <li class="nav-header">Organisation</li>
        <li class="nav-item">
            <a href="/GestionUniversite/views/student/timetable.php" class="nav-link">
                <i class="fas fa-calendar-alt"></i> <span>Emploi du temps</span>
            </a>
        </li>
    </ul>
</aside>
