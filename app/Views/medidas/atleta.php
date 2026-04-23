<?php /** @var array $atleta @var array $historial */ ?>
<div class="page-header">
    <div>
        <h1>Antropometría</h1>
        <div class="subtitle"><?= e($atleta['nombre'] . ' ' . $atleta['apellido']) ?> · <?= e($atleta['nombre_categoria'] ?? 'Sin categoría') ?></div>
    </div>
    <a href="<?= e(url("/admin/atletas/{$atleta['atleta_id']}")) ?>" class="btn btn-ghost">← Ver atleta</a>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px;" class="antro-grid">
    <form method="POST" action="<?= e(url("/admin/antropometria/atleta/{$atleta['atleta_id']}")) ?>" class="card">
        <?= csrf_field() ?>
        <h3 style="margin-top:0;">Nueva medición</h3>
        <div class="form-group">
            <label class="form-label"><span class="required">*</span> Fecha</label>
            <input type="date" name="fecha_medicion" class="form-control" required value="<?= e(date('Y-m-d')) ?>">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Peso (kg)</label>
                <input type="number" step="0.01" name="peso" class="form-control" min="0" max="200">
            </div>
            <div class="form-group">
                <label class="form-label">Altura (cm)</label>
                <input type="number" step="0.01" name="altura" class="form-control" min="0" max="250">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">% Grasa</label>
                <input type="number" step="0.01" name="porcentaje_grasa" class="form-control" min="0" max="100">
            </div>
            <div class="form-group">
                <label class="form-label">% Musculatura</label>
                <input type="number" step="0.01" name="porcentaje_musculatura" class="form-control" min="0" max="100">
            </div>
        </div>
        <div class="form-row-3">
            <div class="form-group">
                <label class="form-label">Envergadura</label>
                <input type="number" step="0.01" name="envergadura" class="form-control" min="0">
            </div>
            <div class="form-group">
                <label class="form-label">Largo pierna</label>
                <input type="number" step="0.01" name="largo_de_pierna" class="form-control" min="0">
            </div>
            <div class="form-group">
                <label class="form-label">Largo torso</label>
                <input type="number" step="0.01" name="largo_de_torso" class="form-control" min="0">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Guardar medición</button>
    </form>

    <div class="card">
        <h3 style="margin-top:0;">Evolución</h3>
        <?php if (empty($historial)): ?>
            <p class="text-muted">Sin mediciones registradas aún.</p>
        <?php else: ?>
            <canvas id="chart-antro" height="200"></canvas>
        <?php endif; ?>
    </div>
</div>

<div class="card mt">
    <h3 style="margin-top:0;">Historial</h3>
    <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr><th>Fecha</th><th>Peso</th><th>Altura</th><th>% Grasa</th><th>% Músculo</th><th>Envergadura</th></tr>
            </thead>
            <tbody>
            <?php foreach (array_reverse($historial) as $m): ?>
                <tr>
                    <td><?= e($m['fecha_medicion']) ?></td>
                    <td><?= e($m['peso']) ?></td>
                    <td><?= e($m['altura']) ?></td>
                    <td><?= e($m['porcentaje_grasa']) ?></td>
                    <td><?= e($m['porcentaje_musculatura']) ?></td>
                    <td><?= e($m['envergadura']) ?></td>
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
    const data = await API.get('<?= e(url("/api/antropometria/atleta/{$atleta['atleta_id']}")) ?>');
    const labels = data.map(d => d.fecha_medicion);
    const ctx = document.getElementById('chart-antro');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                { label: 'Peso (kg)',   data: data.map(d => d.peso),   borderColor: '#DC2626', tension: 0.3 },
                { label: 'Altura (cm)', data: data.map(d => d.altura), borderColor: '#2563EB', tension: 0.3, yAxisID: 'y1' },
                { label: '% Grasa',     data: data.map(d => d.porcentaje_grasa), borderColor: '#F97316', tension: 0.3 },
            ]
        },
        options: {
            responsive: true,
            scales: {
                y:  { position: 'left' },
                y1: { position: 'right', grid: { drawOnChartArea: false } },
            }
        }
    });
})();
</script>
<?php endif; ?>

<style>
@media (max-width: 900px) { .antro-grid { grid-template-columns: 1fr !important; } }
</style>
