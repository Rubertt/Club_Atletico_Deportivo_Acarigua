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
            Valores numéricos (escala 0-10 recomendada, pero el sistema acepta cualquier métrica consistente).
        </p>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Fuerza</label><input type="number" step="0.01" name="test_de_fuerza" class="form-control"></div>
            <div class="form-group"><label class="form-label">Resistencia</label><input type="number" step="0.01" name="test_resistencia" class="form-control"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Velocidad</label><input type="number" step="0.01" name="test_velocidad" class="form-control"></div>
            <div class="form-group"><label class="form-label">Coordinación</label><input type="number" step="0.01" name="test_coordinacion" class="form-control"></div>
        </div>
        <div class="form-group"><label class="form-label">Reacción</label><input type="number" step="0.01" name="test_de_reaccion" class="form-control"></div>
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
            <thead><tr><th>Fecha</th><th>Fuerza</th><th>Resistencia</th><th>Velocidad</th><th>Coordinación</th><th>Reacción</th></tr></thead>
            <tbody>
            <?php foreach ($historial as $h): ?>
                <tr>
                    <td><?= e($h['fecha_evento']) ?></td>
                    <td><?= e($h['test_de_fuerza']) ?></td>
                    <td><?= e($h['test_resistencia']) ?></td>
                    <td><?= e($h['test_velocidad']) ?></td>
                    <td><?= e($h['test_coordinacion']) ?></td>
                    <td><?= e($h['test_de_reaccion']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($historial)): ?><tr><td colspan="6" class="text-center text-muted">Sin datos</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
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
        options: { responsive: true, scales: { r: { beginAtZero: true } } }
    });
})();
</script>
<?php endif; ?>

<style>@media (max-width: 900px) { .pruebas-grid { grid-template-columns: 1fr !important; } }</style>
