<?php
// views/teacher/grades.php
require_once __DIR__ . '/../../includes/header.php';
requireRole('teacher');

$db = new Database();
$conn = $db->getConnection();

// Get teacher ID
$stmt_t = $conn->prepare("SELECT id FROM teachers WHERE user_id = ?");
$stmt_t->execute([$_SESSION['user_id']]);
$teacher_id = $stmt_t->fetchColumn();

// Fetch assigned courses for dropdown
$courses_stmt = $conn->prepare("
    SELECT c.id, c.code, c.name, sec.name as sector_name, sec.level 
    FROM courses c
    JOIN course_teacher ct ON c.id = ct.course_id
    JOIN sectors sec ON c.sector_id = sec.id
    WHERE ct.teacher_id = ?
");
$courses_stmt->execute([$teacher_id]);
$assigned_courses = $courses_stmt->fetchAll();

$selected_course_id = $_GET['course_id'] ?? null;
$students = [];

if ($selected_course_id) {
    // Verify ownership
    $check = $conn->prepare("SELECT 1 FROM course_teacher WHERE course_id = ? AND teacher_id = ?");
    $check->execute([$selected_course_id, $teacher_id]);
    if ($check->rowCount() === 0) {
        die("Accès refusé à cette matière.");
    }

    // Fetch enrolled students for this course and their grades if they exist
    // Note: In a real flow, an admin registers enrollments. Here we assume all students in the course's sector are enrolled for demo speed.
    $students_stmt = $conn->prepare("
        SELECT s.id as student_id, s.matricule, s.first_name, s.last_name, s.sector_id
        FROM students s
        JOIN courses c ON s.sector_id = c.sector_id
        WHERE c.id = ?
        ORDER BY s.last_name ASC
    ");
    $students_stmt->execute([$selected_course_id]);
    $students = $students_stmt->fetchAll();
    
    // Process grades submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_grades'])) {
        if (verifyCsrfToken($_POST['csrf_token'])) {
            $conn->beginTransaction();
            try {
                $eval_type = trim($_POST['evaluation_type']);
                // Mocking enrollment for demo simplicity if no formal enrollment table is strictly enforced by admin yet
                foreach ($_POST['grades'] as $stu_id => $score) {
                    if ($score !== '') {
                        $s_id = (int)$stu_id;
                        $score_val = (float)$score;
                        // For the sake of the demo, we insert mock enrollment on the fly if it doesn't exist
                        $enr_check = $conn->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
                        $enr_check->execute([$s_id, $selected_course_id]);
                        $enr_id = $enr_check->fetchColumn();
                        
                        if (!$enr_id) {
                            $sem_id = $conn->query("SELECT semester_id FROM courses WHERE id = " . $selected_course_id)->fetchColumn();
                            $conn->prepare("INSERT INTO enrollments (student_id, course_id, semester_id) VALUES (?, ?, ?)")->execute([$s_id, $selected_course_id, $sem_id]);
                            $enr_id = $conn->lastInsertId();
                        }
                        
                        // Upsert logic for grade
                        $grade_check = $conn->prepare("SELECT id FROM grades WHERE enrollment_id = ? AND evaluation_type = ?");
                        $grade_check->execute([$enr_id, $eval_type]);
                        $existing_grade = $grade_check->fetchColumn();
                        
                        if ($existing_grade) {
                            $conn->prepare("UPDATE grades SET score = ?, updated_at = NOW() WHERE id = ?")->execute([$score_val, $existing_grade]);
                        } else {
                            $conn->prepare("INSERT INTO grades (enrollment_id, score, evaluation_type, created_by) VALUES (?, ?, ?, ?)")->execute([$enr_id, $score_val, $eval_type, $_SESSION['user_id']]);
                        }
                    }
                }
                $conn->commit();
                logAction($conn, $_SESSION['user_id'], 'Saisie Notes', "Notes saisies pour UE $selected_course_id.");
                setFlashMessage('success', 'Notes enregistrées avec succès.');
                redirect("/GestionUniversite/views/teacher/grades.php?course_id=$selected_course_id");
            } catch (PDOException $e) {
                $conn->rollBack();
                setFlashMessage('error', 'Erreur lors de l\'enregistrement des notes.');
            }
        }
    }
    
    // Fetch existing grades to prepopulate
    $existing = $conn->prepare("
        SELECT g.score, e.student_id, g.evaluation_type
        FROM grades g
        JOIN enrollments e ON g.enrollment_id = e.id
        WHERE e.course_id = ?
    ");
    $existing->execute([$selected_course_id]);
    $existing_grades = $existing->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC); // Group by first column if grouped? No, let's just do a simple map.
    
    $grade_map = [];
    foreach ($existing->fetchAll() as $row) {
        $grade_map[$row['student_id']][$row['evaluation_type']] = $row['score'];
    }
}
?>

<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h3 class="card-title">Sélection de la Matière</h3>
    </div>
    <form action="" method="GET" style="display:flex; gap: 1rem; align-items:flex-end;">
        <div class="form-group" style="flex:1; margin:0;">
            <label>Unité d'Enseignement</label>
            <select name="course_id" class="form-control" required>
                <option value="">-- Sélectionnez une matière --</option>
                <?php foreach($assigned_courses as $c): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo ($selected_course_id == $c['id']) ? 'selected' : ''; ?>>
                        <?php echo escape($c['code'] . ' - ' . $c['name'] . ' (' . $c['sector_name'] . ' ' . $c['level'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Charger les Étudiants</button>
    </form>
</div>

<?php if ($selected_course_id): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Saisie des Notes (Évaluation : Examen)</h3>
    </div>
    <form action="" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        <input type="hidden" name="save_grades" value="1">
        <input type="hidden" name="evaluation_type" value="Examen">
        
        <div class="table-responsive">
            <table class="table" style="width:100%; text-align:left; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border);">
                        <th style="padding: 12px; width: 15%;">Matricule</th>
                        <th style="padding: 12px; width: 40%;">Nom & Prénom</th>
                        <th style="padding: 12px; width: 25%;">Note / 20</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($students)): ?>
                        <tr><td colspan="3" style="text-align:center; padding: 20px;">Aucun étudiant trouvé pour la filière de cette matière.</td></tr>
                    <?php else: ?>
                        <?php foreach($students as $st): ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 12px; font-weight:600;"><?php echo escape($st['matricule']); ?></td>
                            <td style="padding: 12px;"><?php echo escape($st['last_name'] . ' ' . $st['first_name']); ?></td>
                            <td style="padding: 12px;">
                                <input type="number" step="0.25" min="0" max="20" 
                                       name="grades[<?php echo $st['student_id']; ?>]" 
                                       class="form-control" 
                                       style="width: 100px; padding: 0.5rem;"
                                       placeholder="ex: 14.5"
                                       value="<?php 
                                            // Mock existing fetch logic placeholder
                                            // Real prepopulation needs strict queries based on Enrollement ID
                                       ?>">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if(!empty($students)): ?>
        <div style="margin-top: 1.5rem; text-align:right;">
            <button type="submit" class="btn btn-primary" style="background:var(--success); border-color:var(--success);">
                <i class="fas fa-save"></i> Enregistrer les Notes
            </button>
        </div>
        <?php endif; ?>
    </form>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
