<?php
// views/admin/courses.php
require_once __DIR__ . '/../../includes/header.php';
requireRole('admin');

$db = new Database();
$conn = $db->getConnection();

// Handle Course (UE) creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    if (verifyCsrfToken($_POST['csrf_token'])) {
        $sector_id = (int)$_POST['sector_id'];
        $semester_id = (int)$_POST['semester_id'];
        $name = trim($_POST['name']);
        $code = trim($_POST['code']);
        $credits = (int)$_POST['credits'];
        $coefficient = (float)$_POST['coefficient'];
        
        try {
            $stmt = $conn->prepare("INSERT INTO courses (sector_id, semester_id, name, code, credits, coefficient) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$sector_id, $semester_id, $name, $code, $credits, $coefficient]);
            logAction($conn, $_SESSION['user_id'], 'Création UE', "UE $code ajoutée.");
            setFlashMessage('success', 'Unité d\'Enseignement ajoutée avec succès.');
        } catch (PDOException $e) {
            setFlashMessage('error', 'Erreur lors de l\'ajout de l\'UE. Le code existe peut-être déjà.');
        }
    }
    redirect('/GestionUniversite/views/admin/courses.php');
}

// Fetch all courses with their relates sector/department/semester
$query = "
    SELECT c.*, sec.name as sector_name, sec.level as sector_level, dep.name as dept_name, sem.name as sem_name, sem.academic_year
    FROM courses c
    JOIN sectors sec ON c.sector_id = sec.id
    JOIN departments dep ON sec.department_id = dep.id
    JOIN semesters sem ON c.semester_id = sem.id
    ORDER BY sem.academic_year DESC, c.code ASC
";
$courses = $conn->query($query)->fetchAll();

// Fetch lookups for the form
$sectors = $conn->query("SELECT s.id, s.name, s.level, d.code as dept_code FROM sectors s JOIN departments d ON s.department_id = d.id ORDER BY d.code, s.level")->fetchAll();
$semesters = $conn->query("SELECT id, name, academic_year FROM semesters ORDER BY id DESC")->fetchAll();

?>

<div class="mb-4" style="margin-bottom: 1.5rem; display:flex; justify-content:space-between; align-items:center;">
    <h2>Unités d'Enseignement (UE)</h2>
    <a href="/GestionUniversite/views/admin/semesters.php" class="btn btn-sm" style="background:var(--secondary-color); color:white;">
        <i class="fas fa-calendar-alt"></i> Gérer les Semestres
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Liste des UE</h3>
        <button class="btn btn-primary btn-sm" onclick="document.getElementById('addCourseModal').style.display='block'">
            <i class="fas fa-plus"></i> Nouvelle UE
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="table" style="width:100%; text-align:left; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border);">
                    <th style="padding: 12px;">Code</th>
                    <th style="padding: 12px;">Nom de l'UE</th>
                    <th style="padding: 12px;">Filière (Dépt)</th>
                    <th style="padding: 12px;">Semestre</th>
                    <th style="padding: 12px;">Crédits</th>
                    <th style="padding: 12px;">Coeff</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($courses)): ?>
                    <tr><td colspan="6" style="text-align:center; padding: 20px;">Aucune Unité d'Enseignement enregistrée.</td></tr>
                <?php else: ?>
                    <?php foreach($courses as $c): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 12px; font-weight:600; color:var(--primary-color);"><?php echo escape($c['code']); ?></td>
                        <td style="padding: 12px;"><?php echo escape($c['name']); ?></td>
                        <td style="padding: 12px;">
                            <?php echo escape($c['sector_name'] . ' [' . $c['sector_level'] . ']'); ?><br>
                            <small style="color:var(--text-muted);"><?php echo escape($c['dept_name']); ?></small>
                        </td>
                        <td style="padding: 12px;">
                            <?php echo escape($c['sem_name']); ?><br>
                            <small style="color:var(--text-muted);"><?php echo escape($c['academic_year']); ?></small>
                        </td>
                        <td style="padding: 12px; font-weight:500;"><?php echo escape($c['credits']); ?></td>
                        <td style="padding: 12px; font-weight:500; color:var(--danger);"><?php echo escape($c['coefficient']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
.modal-content { background-color: var(--surface); margin: 5% auto; padding: 2rem; border-radius: var(--radius-lg); width: 100%; max-width: 600px; box-shadow: var(--shadow-md); }
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.close { color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer; }
.close:hover { color: #000; }
</style>

<div id="addCourseModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Ajouter une Unité d'Enseignement</h3>
            <span class="close" onclick="document.getElementById('addCourseModal').style.display='none'">&times;</span>
        </div>
        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="add_course" value="1">
            
            <div class="form-group">
                <label>Code UE (ex: INF301)</label>
                <input type="text" name="code" class="form-control" required maxlength="20">
            </div>
            
            <div class="form-group">
                <label>Intitulé de l'UE</label>
                <input type="text" name="name" class="form-control" required maxlength="150" placeholder="ex: Algorithmique Avancée">
            </div>
            
            <div class="form-group">
                <label>Filière Assignée</label>
                <select name="sector_id" class="form-control" required>
                    <option value="">Sélectionner une filière...</option>
                    <?php foreach($sectors as $sec): ?>
                        <option value="<?php echo $sec['id']; ?>">
                            <?php echo escape($sec['dept_code'] . ' - ' . $sec['name'] . ' [' . $sec['level'] . ']'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Semestre</label>
                <select name="semester_id" class="form-control" required>
                    <option value="">Sélectionner un semestre...</option>
                    <?php foreach($semesters as $sem): ?>
                        <option value="<?php echo $sem['id']; ?>">
                            <?php echo escape($sem['academic_year'] . ' - ' . $sem['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="display:flex; gap: 1rem;">
                <div class="form-group" style="flex:1;">
                    <label>Crédits (ECTS)</label>
                    <input type="number" name="credits" class="form-control" required min="1" max="30" value="3">
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Coefficient</label>
                    <input type="number" name="coefficient" class="form-control" required min="0.5" max="10" step="0.5" value="1">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Enregistrer l'UE</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
