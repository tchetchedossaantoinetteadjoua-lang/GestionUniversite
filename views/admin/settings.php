<?php
// views/admin/settings.php
require_once __DIR__ . '/../../includes/header.php';
requireRole('admin');
?>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header">
        <h3 class="card-title">Paramètres Généraux et Maintenance</h3>
    </div>
    
    <div style="padding: 1rem 0;">
        <h4 style="margin-bottom: 1rem; color: var(--primary-color);">Maintenance de la Base de Données</h4>
        <p style="color: var(--text-muted); margin-bottom: 1.5rem;">
            Il est recommandé de générer régulièrement une sauvegarde de la base de données. Vous pouvez automatiser l'exécution de <code>utils/backup.php</code> via une tâche Cron sur le serveur, ou déclencher une sauvegarde manuelle ci-dessous.
        </p>
        
        <a href="/GestionUniversite/utils/backup.php" class="btn btn-primary" style="background: var(--success); border-color: var(--success);">
            <i class="fas fa-database"></i> Générer et Télécharger la Sauvegarde SQL
        </a>
    </div>
    
    <hr style="border: none; border-top: 1px solid var(--border); margin: 2rem 0;">
    
    <div style="padding: 1rem 0;">
        <h4 style="margin-bottom: 1rem; color: var(--primary-color);">Informations Système</h4>
        <table class="table" style="width: 100%; text-align: left; border: 1px solid var(--border); border-radius: var(--radius-md);">
            <tr>
                <td style="padding: 10px; font-weight: 500; border-bottom: 1px solid var(--border);">Version PHP</td>
                <td style="padding: 10px; border-bottom: 1px solid var(--border);"><?php echo phpversion(); ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: 500; border-bottom: 1px solid var(--border);">Serveur Web</td>
                <td style="padding: 10px; border-bottom: 1px solid var(--border);"><?php echo isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'N/A'; ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: 500; border-bottom: 1px solid var(--border);">Répertoire d'installation</td>
                <td style="padding: 10px; border-bottom: 1px solid var(--border);"><?php echo __DIR__; ?></td>
            </tr>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
