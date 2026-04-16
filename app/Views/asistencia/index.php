<?php /** @var array $eventos */ ?>
<div class="page-header">
    <div><h1>Asistencia</h1><div class="subtitle">Historial de pases de lista</div></div>
    <a href="<?= e(url('/admin/asistencia/pase')) ?>" class="btn btn-primary">+ Nuevo pase de lista</a>
</div>

<div class="data-table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Fecha</th><th>Tipo</th><th>Entrenador</th><th>Presentes / Total</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($eventos as $ev): ?>
            <tr>
                <td><?= e($ev['fecha_evento']) ?></td>
                <td><span class="badge badge-primary"><?= e($ev['tipo_evento']) ?></span></td>
                <td><?= e($ev['entrenador']) ?></td>
                <td><strong><?= (int) $ev['presentes'] ?></strong> de <?= (int) $ev['total'] ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($eventos)): ?><tr><td colspan="4" class="text-center text-muted" style="padding:32px">No hay registros de asistencia.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
