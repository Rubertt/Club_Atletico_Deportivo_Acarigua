<?php /** @var array $pag */ ?>
<div class="page-header">
    <div><h1>Antropometría</h1><div class="subtitle">Selecciona un atleta para registrar o ver mediciones</div></div>
</div>

<div class="data-table-wrap">
    <table class="data-table">
        <thead><tr><th></th><th>Atleta</th><th>Cédula</th><th>Categoría</th><th style="width:160px">Acción</th></tr></thead>
        <tbody>
        <?php foreach ($pag['data'] as $a): ?>
            <tr>
                <td>
                    <?php if (!empty($a['foto'])): ?>
                        <img src="<?= e(url($a['foto'])) ?>" class="avatar-thumb" alt="">
                    <?php else: ?>
                        <span class="avatar-placeholder"><?= e(mb_substr($a['nombre'], 0, 1) . mb_substr($a['apellido'], 0, 1)) ?></span>
                    <?php endif; ?>
                </td>
                <td><strong><?= e($a['nombre'] . ' ' . $a['apellido']) ?></strong></td>
                <td><?= e($a['cedula'] ?? '—') ?></td>
                <td><?= e($a['nombre_categoria'] ?? '—') ?></td>
                <td><a href="<?= e(url("/admin/antropometria/atleta/{$a['atleta_id']}")) ?>" class="btn btn-sm btn-primary">Ver / Registrar</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($pag['data'])): ?><tr><td colspan="5" class="text-center text-muted" style="padding:32px">No hay atletas activos.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
