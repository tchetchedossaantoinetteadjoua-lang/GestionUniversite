<?php
// views/admin/departments.php
require_once __DIR__ . '/../../includes/header.php';
requireRole('admin');

$db = new Database();
$conn = $db->getConnection();

$faculty_id = $_GET['faculty_id'] ?? null;

if (!$faculty_id) {
    setFlashMessage('error', 'Faculté non spécifiée.');
    redirect('/GestionUniversite/views/admin/faculties.php');
}

// Fetch Faculty Details
$stmt_fac = $conn->prepare("SELECT * FROM faculties WHERE id = ?");
$stmt_fac->execute([$faculty_id]);
$faculty = $stmt_fac->fetch();

if (!$faculty) {
    redirect('/GestionUniversite/views/admin/faculties.php');
}

// Handle Dept & Sector creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verifyCsrfToken($_POST['csrf_token'])) {
        
        if (isset($_POST['add_dept'])) {
            $name = trim($_POST['name']);
            $code = trim($_POST['code']);
            try {
                $stmt = $conn->prepare("INSERT INTO departments (faculty_id, name, code) VALUES (?, ?, ?)");
                $stmt->execute([$faculty_id, $name, $code]);
                logAction($conn, $_SESSION['user_id'], 'Création Département', "Département $code ajouté à la faculté $faculty_id");
                setFlashMessage('success', 'Département ajouté.');
            } catch (PDOException $e) {
                setFlashMessage('error', 'Erreur lors de l\'ajout. Le code existe peut-être déjà.');
            }
        }
        
        if (isset($_POST['add_sector'])) {
            $dept_id = $_POST['department_id'];
            $name = trim($_POST['name']);
            $level = trim($_POST['level']);
            try {
                $stmt = $conn->prepare("INSERT INTO sectors (department_id, name, level) VALUES (?, ?, ?)");
                $stmt->execute([$dept_id, $name, $level]);
                logAction($conn, $_SESSION['user_id'], 'Création Filière', "Filière $name ($level) ajoutée au dépt $dept_id");
                setFlashMessage('success', 'Filière ajoutée.');
            } catch (PDOException $e) {
                setFlashMessage('error', 'Erreur lors de l\'ajout de la filière.');
            }
        }
    }
    redirect('/GestionUniversite/views/admin/departments.php?faculty_id=' . $faculty_id);
}

// Fetch Departments and their Sectors
$depts = $conn->prepare("SELECT * FROM departments WHERE faculty_id = ? ORDER BY name");
$depts->execute([$faculty_id]);
$departments = $depts->fetchAll();

?>

<div class="mb-4" style="margin-bottom: 2rem; display: flex; align-items: center; gap: 1rem;">
    <a href="/GestionUniversite/views/admin/faculties.php" class="btn btn-sm" style="background:#e2e8f0; color:#0f172a; text-decoration:none;">
        <i class="fas fa-arrow-left"></i> Retour aux facultés
    </a>
    <h2 style="margin:0;">Faculté : <?php echo escape($faculty['name']); ?> (<?php echo escape($faculty['code']); ?>)</h2>
</div>

<div class="row">
    <!-- List of Departments -->
    <div class="col card" style="grid-column: span 2;">
        <div class="card-header">
            <h3 class="card-title">Départements et Filières</h3>
            <button class="btn btn-primary btn-sm" onclick="document.getElementById('addDeptModal').style.display='block'">
                <i class="fas fa-plus"></i> Nouveau Département
            </button>
        </div>
        
        <?php if(empty($departments)): ?>
            <p style="text-align:center; padding: 2rem; color: var(--text-muted);">Aucun département dans cette faculté.</p>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; gap: 1.5rem;">
                <?php foreach($departments as $d): ?>
                    <div style="border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1.5rem;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 1rem; border-bottom: 1px dashed var(--border); padding-bottom: 1rem;">
                            <h4 style="font-size: 1.1rem; color: var(--primary-color);">
                                <?php echo escape($d['name']); ?> (<?php echo escape($d['code']); ?>)
                            </h4>
                            <button class="btn btn-sm" style="background:#10b981; color:#fff;" onclick="openSectorModal(<?php echo $d['id']; ?>, '<?php echo escape($d['name']); ?>')">
                                <i class="fas fa-plus"></i> Ajouter Filière
                            </button>
                        </div>
                        
                        <?php 
                        // Fetch sectors for this department
                        $sec_stmt = $conn->prepare("SELECT * FROM sectors WHERE department_id = ? ORDER BY level, name");
                        $sec_stmt->execute([$d['id']]);
                        $sectors = $sec_stmt->fetchAll();
                        ?>
                        
                        <?php if(empty($sectors)): ?>
                            <p style="font-size: 0.9rem; color: var(--text-muted); margin:0;">Aucune filière dans ce département.</p>
                        <?php else: ?>
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <?php foreach($sectors as $s): ?>
                                    <span style="background: #f1f5f9; border: 1px solid #cbd5e1; padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.85rem; font-weight: 500;">
                                        <?php echo escape($s['name']); ?> <span style="color:var(--primary-color);">[<?php echo escape($s['level']); ?>]</span>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Dept Modal -->
<style>
.modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
.modal-content { background-color: var(--surface); margin: 10% auto; padding: 2rem; border-radius: var(--radius-lg); width: 100%; max-width: 500px; box-shadow: var(--shadow-md); }
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.close { color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer; }
.close:hover { color: #000; }
</style>

<div id="addDeptModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Nouveau Département</h3>
            <span class="close" onclick="document.getElementById('addDeptModal').style.display='none'">&times;</span>
        </div>
        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="add_dept" value="1">
            
            <div class="form-group">
                <label>Code</label>
                <input type="text" name="code" class="form-control" required maxlength="20">
            </div>
            
            <div class="form-group">
                <label>Nom du Département</label>
                <input type="text" name="name" class="form-control" required maxlength="150">
            </div>
            
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </form>
    </div>
</div>

<!-- Add Sector Modal -->
<div id="addSectorModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Nouvelle Filière pour <span id="modalDeptName" style="color:var(--primary-color);"></span></h3>
            <span class="close" onclick="document.getElementById('addSectorModal').style.display='none'">&times;</span>
        </div>
        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="add_sector" value="1">
            <input type="hidden" name="department_id" id="modalDeptId" value="">
            
            <div class="form-group">
                <label>Nom de la Filière (Ex: Informatique de Gestion)</label>
                <input type="text" name="name" class="form-control" required maxlength="150">
            </div>
            
            <div class="form-group">
                <label>Niveau (Grade)</label>
                <select name="level" class="form-control" required>
                    <option value="">Sélectionner...</option>
                    <option value="L1">Licence 1 (L1)</option>
                    <option value="L2">Licence 2 (L2)</option>
                    <option value="L3">Licence 3 (L3)</option>
                    <option value="M1">Master 1 (M1)</option>
                    <option value="M2">Master 2 (M2)</option>
                    <option value="Doctorat">Doctorat</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary" style="background:#10b981; border-color:#10b981;">Ajouter Filière</button>
        </form>
    </div>
</div>

<script>
function openSectorModal(deptId, deptName) {
    document.getElementById('modalDeptId').value = deptId;
    document.getElementById('modalDeptName').textContent = deptName;
    document.getElementById('addSectorModal').style.display = 'block';
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
