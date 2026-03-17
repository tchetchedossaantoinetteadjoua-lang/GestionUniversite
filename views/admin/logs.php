<?php
// views/admin/logs.php
require_once __DIR__ . '/../../includes/header.php';
requireRole('admin');

$db = new Database();
$conn = $db->getConnection();

// Fetch recent active logs
$stmt = $conn->query("
    SELECT l.*, u.username, u.role
    FROM system_logs l
    LEFT JOIN users u ON l.user_id = u.id
    ORDER BY l.created_at DESC
    LIMIT 100
");
$logs = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Journal Système (100 dernières actions)</h3>
    </div>
    
    <div class="table-responsive">
        <table class="table" style="width:100%; text-align:left; border-collapse: collapse; font-family: monospace; font-size: 0.9rem;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); background: #f8fafc;">
                    <th style="padding: 10px;">Date & Heure</th>
                    <th style="padding: 10px;">Utilisateur</th>
                    <th style="padding: 10px;">Action</th>
                    <th style="padding: 10px;">Détails</th>
                    <th style="padding: 10px;">IP</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($logs)): ?>
                    <tr><td colspan="5" style="text-align:center; padding: 20px;">Aucun log trouvé.</td></tr>
                <?php else: ?>
                    <?php foreach($logs as $log): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 10px; color: var(--text-muted);"><?php echo escape($log['created_at']); ?></td>
                        <td style="padding: 10px; font-weight:600;">
                            <?php echo escape($log['username'] ?? 'Système'); ?>
                            <?php if ($log['role']): ?>
                                <span style="font-size: 0.75rem; background: var(--secondary-color); color: white; padding: 2px 6px; border-radius: 4px; margin-left: 5px;"><?php echo escape($log['role']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 10px;">
                            <span style="background: #e0f2fe; color: #0284c7; padding: 2px 6px; border-radius: 4px;"><?php echo escape($log['action']); ?></span>
                        </td>
                        <td style="padding: 10px;"><?php echo escape($log['details']); ?></td>
                        <td style="padding: 10px; color: var(--text-muted);"><?php echo escape($log['ip_address']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
