<?php
// includes/sidebar_teacher.php
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-university"></i>
        <span>Espace Enseignant</span>
    </div>
    <ul class="sidebar-nav">
        <li class="nav-item">
            <a href="/GestionUniversite/views/teacher/dashboard.php" class="nav-link">
                <i class="fas fa-home"></i> <span>Tableau de bord</span>
            </a>
        </li>
        <li class="nav-header">Enseignement</li>
        <li class="nav-item">
            <a href="/GestionUniversite/views/teacher/my_courses.php" class="nav-link">
                <i class="fas fa-book"></i> <span>Mes Matières (UE)</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/GestionUniversite/views/teacher/grades.php" class="nav-link">
                <i class="fas fa-edit"></i> <span>Saisie des Notes</span>
            </a>
        </li>
        <li class="nav-header">Organisation</li>
        <li class="nav-item">
            <a href="/GestionUniversite/views/teacher/timetable.php" class="nav-link">
                <i class="fas fa-calendar-alt"></i> <span>Emploi du temps</span>
            </a>
        </li>
    </ul>
</aside>
