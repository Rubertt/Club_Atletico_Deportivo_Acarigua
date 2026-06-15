<?php /** @var array $atleta @var array $historial */ ?>
<div class="page-header">
    <div>
        <h1>Pruebas físicas</h1>
        <div class="subtitle"><?= e($atleta['nombre'] . ' ' . $atleta['apellido']) ?></div>
    </div>
    <a href="<?= e(url("/admin/atletas/{$atleta['atleta_id']}")) ?>" class="btn btn-ghost">← Ver atleta</a>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;" class="pruebas-grid">
    <form method="POST" action="<?= e(url("/admin/pruebas/atleta/{$atleta['atleta_id']}")) ?>" class="card">
        <?= csrf_field() ?>
        <h3 style="margin-top:0;">Nueva prueba</h3>
        <p class="text-muted" style="font-size:13px;">
            Valores físicos reales de rendimiento: Fuerza (Salto CMJ en cm), Resistencia (Yo-Yo Test en metros), Velocidad (Sprint 30m en seg), Coordinación (Circuito Conos en seg), Reacción (App Cognitiva en ms).
        </p>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Fuerza (Salto CMJ - cm)</label><input type="number" step="0.01" name="test_de_fuerza" class="form-control" min="1" max="100" placeholder="Rango Élite (100%): 20 - 45 cm"></div>
            <div class="form-group"><label class="form-label">Resistencia (Yo-Yo Test - m)</label><input type="number" step="1" name="test_resistencia" class="form-control" min="1" max="1000" placeholder="Rango Élite (100%): 600 - 2200 m"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Velocidad (Sprint 30m - s)</label><input type="number" step="0.01" name="test_velocidad" class="form-control" min="1" max="10" placeholder="Rango Élite (100%): 5.20 - 4.10 s"></div>
            <div class="form-group"><label class="form-label">Coordinación (Circuito Conos - s)</label><input type="number" step="0.01" name="test_coordinacion" class="form-control" min="1" max="100" placeholder="Rango Élite (100%): 22.50 - 16.50 s"></div>
        </div>
        <div class="form-group"><label class="form-label">Reacción (App Cognitiva - ms)</label><input type="number" step="1" name="test_de_reaccion" class="form-control" min="100" max="1000" placeholder="Rango Élite (100%): 450 - 220 ms"></div>
        <button type="submit" class="btn btn-primary">Guardar prueba</button>
    </form>

    <div class="card">
        <h3 style="margin-top:0;">Última evaluación</h3>
        <?php if (empty($historial)): ?>
            <p class="text-muted">Sin pruebas registradas.</p>
        <?php else: ?>
            <canvas id="chart-radar" height="260"></canvas>
        <?php endif; ?>
    </div>
</div>

<div class="card mt">
    <h3 style="margin-top:0;">Historial</h3>
    <div class="data-table-wrap">
        <table class="data-table">
            <thead><tr><th>Fecha</th><th>Fuerza (CMJ)</th><th>Resistencia (Yo-Yo)</th><th>Velocidad (30m)</th><th>Coordinación (Conos)</th><th>Reacción (Cognitiva)</th></tr></thead>
            <tbody>
            <?php foreach ($historial as $h): ?>
                <tr class="historial-row">
                    <td><?= e(date('d/m/Y', strtotime($h['fecha_evento']))) ?></td>
                    <td><?= e($h['test_de_fuerza_raw'] !== null ? $h['test_de_fuerza_raw'] . ' cm (' . $h['test_de_fuerza'] . '/100)' : '—') ?></td>
                    <td><?= e($h['test_resistencia_raw'] !== null ? $h['test_resistencia_raw'] . ' m (' . $h['test_resistencia'] . '/100)' : '—') ?></td>
                    <td><?= e($h['test_velocidad_raw'] !== null ? $h['test_velocidad_raw'] . ' s (' . $h['test_velocidad'] . '/100)' : '—') ?></td>
                    <td><?= e($h['test_coordinacion_raw'] !== null ? $h['test_coordinacion_raw'] . ' s (' . $h['test_coordinacion'] . '/100)' : '—') ?></td>
                    <td><?= e($h['test_de_reaccion_raw'] !== null ? $h['test_de_reaccion_raw'] . ' ms (' . $h['test_de_reaccion'] . '/100)' : '—') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($historial)): ?><tr><td colspan="6" class="text-center text-muted">Sin datos</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <div id="historial-pagination" style="display: flex; justify-content: center; margin-top: 24px;"></div>
</div>

<?php if (!empty($historial)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
(async () => {
    const data = await API.get('<?= e(url("/api/pruebas/atleta/{$atleta['atleta_id']}")) ?>');
    if (!data.length) return;
    const u = data[0]; // más reciente
    new Chart(document.getElementById('chart-radar'), {
        type: 'radar',
        data: {
            labels: ['Fuerza', 'Resistencia', 'Velocidad', 'Coordinación', 'Reacción'],
            datasets: [{
                label: 'Última evaluación',
                data: [u.test_de_fuerza ?? 0, u.test_resistencia ?? 0, u.test_velocidad ?? 0, u.test_coordinacion ?? 0, u.test_de_reaccion ?? 0],
                backgroundColor: 'rgba(220,38,38,0.25)',
                borderColor: '#DC2626',
                pointBackgroundColor: '#DC2626'
            }]
        },
        options: { responsive: true, scales: { r: { min: 0, max: 100, beginAtZero: true } } }
    });
})();
</script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    CadaPagination({
        rowSelector: '.historial-row',
        containerId: 'historial-pagination'
    });
});
</script>

<style>@media (max-width: 900px) { .pruebas-grid { grid-template-columns: 1fr !important; } }</style>
