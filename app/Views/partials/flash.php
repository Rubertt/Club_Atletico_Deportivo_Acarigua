<?php
$flashTypes = ['success', 'danger', 'warning', 'info', 'error'];
$flashFound = false;

foreach ($flashTypes as $type):
    $message = flash($type);
    if (!$message) continue;
    
    $flashFound = true;
    $typeMod = ($type === 'danger' || $type === 'error') ? 'error' : $type;
    $title = match($typeMod) {
        'success' => '¡Éxito!',
        'error'   => '¡Error!',
        'warning' => 'Atención',
        default   => 'Información'
    };
?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            <?php if ($typeMod === 'success'): ?>
            if (typeof CadaToast !== 'undefined') {
                CadaToast.success(<?= json_encode($message) ?>);
            } else if (typeof CadaModal !== 'undefined') {
                CadaModal.alert({
                    title: <?= json_encode($title) ?>,
                    text: <?= json_encode($message) ?>,
                    type: 'success',
                    confirmText: 'Aceptar'
                });
            }
            <?php else: ?>
            if (typeof CadaModal !== 'undefined') {
                CadaModal.alert({
                    title: <?= json_encode($title) ?>,
                    text: <?= json_encode($message) ?>,
                    type: <?= json_encode($typeMod) ?>,
                    confirmText: 'Aceptar'
                });
            }
            <?php endif; ?>
        });
    </script>
<?php endforeach; ?>

<?php if (!empty($_SESSION['_errors'])): 
    $flashFound = true;
    $errors = array_values($_SESSION['_errors']);
    $errorMsg = implode('\n• ', $errors);
?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof CadaModal !== 'undefined') {
                CadaModal.alert({
                    title: '¡Errores de Validación!',
                    text: <?= json_encode('• ' . implode("\n• ", $errors)) ?>,
                    type: 'error',
                    confirmText: 'Corregir ahora'
                });
            }
        });
    </script>
    <?php unset($_SESSION['_errors']); ?>
<?php endif; ?>

<?php unset($_SESSION['_old']); ?>
