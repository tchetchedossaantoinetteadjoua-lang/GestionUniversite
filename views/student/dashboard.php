<?php
// views/student/dashboard.php
require_once __DIR__ . '/../../includes/header.php';
requireRole('student');

$db = new Database();
$conn = $db->getConnection();

// Get student profile
$stmt = $conn->prepare("
    SELECT s.*, sec.name as section_name, sec.level, d.name as dept_name 
    FROM students s
    JOIN sectors sec ON s.sector_id = sec.id
    JOIN departments d ON sec.department_id = d.id
    WHERE s.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();

if (!$student) {
    die("Profil étudiant introuvable.");
}

// Fetch enrolled courses for the student's sector
$courses_stmt = $conn->prepare("
    SELECT c.code, c.name, c.credits, c.coefficient, sem.name as sem_name
    FROM courses c
    JOIN semesters sem ON c.semester_id = sem.id
    WHERE c.sector_id = ?
    ORDER BY sem.academic_year DESC, sem.id ASC, c.name ASC
");
$courses_stmt->execute([$student['sector_id']]);
$my_courses = $courses_stmt->fetchAll();

?>

<div class="row" style="margin-bottom: 2rem;">
    <div class="col card" style="background: linear-gradient(135deg, var(--primary-color) 0%, #3b82f6 100%); color: white; border: none;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div>
                <h2 style="margin-bottom: 0.5rem; font-size: 1.8rem;">Bonjour, <?php echo escape($student['first_name']); ?> !</h2>
                <p style="opacity: 0.9; margin-bottom: 1rem;">Matricule : <strong><?php echo escape($student['matricule']); ?></strong></p>
                
                <div style="background: rgba(255,255,255,0.2); padding: 10px 15px; border-radius: var(--radius-md); display: inline-block; margin-top: 10px;">
                    <i class="fas fa-graduation-cap"></i> Filière : <strong><?php echo escape($student['section_name'] . ' [' . $student['level'] . ']'); ?></strong><br>
                    <small><?php echo escape($student['dept_name']); ?></small>
                </div>
            </div>
            <div style="font-size: 4rem; opacity: 0.2;">
                <i class="fas fa-user-graduate"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col card" style="grid-column: span 2;">
        <div class="card-header">
            <h3 class="card-title">Programme d'Études (Mes Unités d'Enseignement)</h3>
        </div>
        
        <div class="table-responsive">
            <table class="table" style="width:100%; text-align:left; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border);">
                        <th style="padding: 12px;">Code UE</th>
                        <th style="padding: 12px;">Intitulé</th>
                        <th style="padding: 12px;">Semestre</th>
                        <th style="padding: 12px;">Crédits</th>
                        <th style="padding: 12px;">Coefficient</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($my_courses)): ?>
                        <tr><td colspan="5" style="text-align:center; padding: 20px;">Aucun cours enregistré pour votre filière.</td></tr>
                    <?php else: ?>
                        <?php foreach($my_courses as $c): ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 12px; font-weight:600; color:var(--primary-color);"><?php echo escape($c['code']); ?></td>
                            <td style="padding: 12px;"><?php echo escape($c['name']); ?></td>
                            <td style="padding: 12px; color:var(--text-muted);"><?php echo escape($c['sem_name']); ?></td>
                            <td style="padding: 12px;"><?php echo escape($c['credits']); ?></td>
                            <td style="padding: 12px;"><?php echo escape($c['coefficient']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
