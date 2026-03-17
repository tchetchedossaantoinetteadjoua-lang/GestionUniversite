<?php
// views/admin/faculties.php
require_once __DIR__ . '/../../includes/header.php';
requireRole('admin');

$db = new Database();
$conn = $db->getConnection();

// Handle Faculty creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_faculty'])) {
    if (verifyCsrfToken($_POST['csrf_token'])) {
        $name = trim($_POST['name']);
        $code = trim($_POST['code']);
        $desc = trim($_POST['description']);
        
        try {
            $stmt = $conn->prepare("INSERT INTO faculties (name, code, description) VALUES (?, ?, ?)");
            $stmt->execute([$name, $code, $desc]);
            logAction($conn, $_SESSION['user_id'], 'Création Faculté', "Faculté ajoutée : $code");
            setFlashMessage('success', 'Faculté ajoutée avec succès.');
        } catch (PDOException $e) {
            setFlashMessage('error', 'Erreur lors de l\'ajout de la faculté. Le code existe peut-être déjà.');
        }
    }
    redirect('/GestionUniversite/views/admin/faculties.php');
}

// Fetch faculties
$faculties = $conn->query("SELECT * FROM faculties ORDER BY name")->fetchAll();

?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Gestion des Facultés</h3>
        <button class="btn btn-primary btn-sm" onclick="document.getElementById('addModal').style.display='block'">
            <i class="fas fa-plus"></i> Nouvelle Faculté
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="table" style="width:100%; text-align:left; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border);">
                    <th style="padding: 12px;">Code</th>
                    <th style="padding: 12px;">Nom de la Faculté</th>
                    <th style="padding: 12px;">Description</th>
                    <th style="padding: 12px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($faculties)): ?>
                    <tr><td colspan="4" style="text-align:center; padding: 20px;">Aucune faculté enregistrée.</td></tr>
                <?php else: ?>
                    <?php foreach($faculties as $f): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 12px; font-weight:600;"><?php echo escape($f['code']); ?></td>
                        <td style="padding: 12px;"><?php echo escape($f['name']); ?></td>
                        <td style="padding: 12px; color:var(--text-muted);"><?php echo escape($f['description']); ?></td>
                        <td style="padding: 12px;">
                            <a href="/GestionUniversite/views/admin/departments.php?faculty_id=<?php echo $f['id']; ?>" class="btn btn-sm" style="background:#e2e8f0; color:#0f172a; text-decoration:none;">
                                <i class="fas fa-sitemap"></i> Départements
                            </a>
                            <!-- Actions like Edit and Delete would go here -->
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal logic (simple vanilla JS implementation for speed and no dependencies) -->
<style>
.modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
.modal-content { background-color: var(--surface); margin: 10% auto; padding: 2rem; border-radius: var(--radius-lg); width: 100%; max-width: 500px; box-shadow: var(--shadow-md); }
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.close { color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer; }
.close:hover { color: #000; }
</style>

<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Ajouter une Faculté</h3>
            <span class="close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
        </div>
        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="add_faculty" value="1">
            
            <div class="form-group">
                <label>Code (ex: FST)</label>
                <input type="text" name="code" class="form-control" required maxlength="20">
            </div>
            
            <div class="form-group">
                <label>Nom Complet</label>
                <input type="text" name="name" class="form-control" required maxlength="150">
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
