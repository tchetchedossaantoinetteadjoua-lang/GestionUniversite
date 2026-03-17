<?php
// views/teacher/dashboard.php
require_once __DIR__ . '/../../includes/header.php';
requireRole('teacher');

$db = new Database();
$conn = $db->getConnection();

// Get teacher profile
$stmt = $conn->prepare("SELECT * FROM teachers WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$teacher = $stmt->fetch();

if (!$teacher) {
    die("Profil enseignant introuvable.");
}

// Fetch assigned courses count
$course_count = $conn->prepare("SELECT COUNT(*) FROM course_teacher WHERE teacher_id = ?");
$course_count->execute([$teacher['id']]);
$total_courses = $course_count->fetchColumn();

// Fetch assigned courses details
$my_courses = $conn->prepare("
    SELECT c.code, c.name, sec.name as sector_name, sec.level
    FROM courses c
    JOIN course_teacher ct ON c.id = ct.course_id
    JOIN sectors sec ON c.sector_id = sec.id
    WHERE ct.teacher_id = ?
    ORDER BY c.name
");
$my_courses->execute([$teacher['id']]);
$courses = $my_courses->fetchAll();

?>

<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-icon purple">
            <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <div class="stat-info">
            <p>Mon Profil</p>
            <h3 style="font-size: 1.25rem;"><?php echo escape($teacher['first_name'] . ' ' . $teacher['last_name']); ?></h3>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-book-open"></i>
        </div>
        <div class="stat-info">
            <p>Matières Enseignées</p>
            <h3><?php echo number_format($total_courses); ?></h3>
        </div>
    </div>
</div>

<div class="row">
    <div class="col card" style="grid-column: span 2;">
        <div class="card-header">
            <h3 class="card-title">Mes Unités d'Enseignement</h3>
        </div>
        <div class="table-responsive">
            <table class="table" style="width:100%; text-align:left; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border);">
                        <th style="padding: 12px;">Code UE</th>
                        <th style="padding: 12px;">Intitulé</th>
                        <th style="padding: 12px;">Filière / Niveau</th>
                        <th style="padding: 12px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($courses)): ?>
                        <tr><td colspan="4" style="text-align:center; padding: 20px;">Aucune matière ne vous est attribuée pour le moment.</td></tr>
                    <?php else: ?>
                        <?php foreach($courses as $c): ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 12px; font-weight:600; color:var(--primary-color);"><?php echo escape($c['code']); ?></td>
                            <td style="padding: 12px;"><?php echo escape($c['name']); ?></td>
                            <td style="padding: 12px;"><?php echo escape($c['sector_name'] . ' [' . $c['level'] . ']'); ?></td>
                            <td style="padding: 12px;">
                                <a href="/GestionUniversite/views/teacher/grades.php?course_code=<?php echo $c['code']; ?>" class="btn btn-sm" style="background:var(--success); color:white; text-decoration:none;">
                                    <i class="fas fa-edit"></i> Saisir Notes
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
