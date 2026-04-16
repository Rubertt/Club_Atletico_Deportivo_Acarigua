<?php /** @var array $stats @var array $atletas */ ?>
<div class="page-header">
    <div><h1>Reportes</h1><div class="subtitle">Estadísticas globales y fichas individuales</div></div>
</div>

<?php if (!empty($stats)): ?>
<div class="quick-grid" style="margin-bottom:24px;">
    <div class="card text-center">
        <div style="font-size:32px; font-weight:800; color:var(--color-primary);"><?= (int) ($stats['atletas'] ?? 0) ?></div>
        <div class="text-muted">Atletas totales</div>
    </div>
    <div class="card text-center">
        <div style="font-size:32px; font-weight:800; color:var(--color-success);"><?= (int) ($stats['activos'] ?? 0) ?></div>
        <div class="text-muted">Activos</div>
    </div>
    <div class="card text-center">
        <div style="font-size:32px; font-weight:800; color:var(--color-info);"><?= (int) ($stats['categorias'] ?? 0) ?></div>
        <div class="text-muted">Categorías activas</div>
    </div>
    <div class="card text-center">
        <div style="font-size:32px; font-weight:800; color:var(--color-warning);"><?= (int) ($stats['eventos_30dias'] ?? 0) ?></div>
        <div class="text-muted">Eventos (30 días)</div>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <h3 style="margin-top:0;">📄 Ficha técnica individual (PDF)</h3>
    <p class="text-muted">Genera una ficha técnica completa por atleta con datos personales, antropometría, pruebas físicas, resumen de asistencia y ficha médica.</p>

    <div class="data-table-wrap">
        <table class="data-table">
            <thead><tr><th>Atleta</th><th>Cédula</th><th style="width:160px">Acción</th></tr></thead>
            <tbody>
            <?php foreach ($atletas as $a): ?>
                <tr>
                    <td><strong><?= e($a['nombre'] . ' ' . $a['apellido']) ?></strong></td>
                    <td><?= e($a['cedula'] ?? '—') ?></td>
                    <td><a href="<?= e(url("/admin/reportes/atleta/{$a['atleta_id']}")) ?>" class="btn btn-sm btn-primary" target="_blank">📄 Generar</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($atletas)): ?><tr><td colspan="3" class="text-center text-muted" style="padding:32px">No hay atletas.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
