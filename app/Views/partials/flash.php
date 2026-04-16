<?php
$flashTypes = ['success', 'danger', 'warning', 'info', 'error'];
foreach ($flashTypes as $type):
    $message = flash($type);
    if (!$message) continue;
    $css = $type === 'error' ? 'danger' : $type;
?>
    <div class="alert alert-<?= e($css) ?>" style="max-width:1100px; margin:16px auto;">
        <?= e($message) ?>
    </div>
<?php endforeach; ?>

<?php if (!empty($_SESSION['_errors'])): ?>
    <div class="alert alert-danger" style="max-width:1100px; margin:16px auto;">
        <strong>Por favor, corrige los siguientes errores:</strong>
        <ul style="margin:8px 0 0 20px;">
        <?php foreach ($_SESSION['_errors'] as $field => $msg): ?>
            <li><?= e($msg) ?></li>
        <?php endforeach; ?>
        </ul>
    </div>
    <?php unset($_SESSION['_errors']); ?>
<?php endif; ?>
