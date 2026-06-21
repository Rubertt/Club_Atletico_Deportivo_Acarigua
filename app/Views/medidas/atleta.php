<?php /** @var array $atleta @var array $historial */ ?>
<div class="page-header">
    <div style="display: flex; align-items: center; gap: 20px;">
        <?php if (!empty($atleta['foto'])): ?>
            <img src="<?= e(url($atleta['foto'])) ?>" style="width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: var(--shadow-sm);">
        <?php else: ?>
            <div class="avatar-placeholder" style="width: 64px; height: 64px; font-size: 20px;">
                <?= e(mb_substr($atleta['nombre'], 0, 1) . mb_substr($atleta['apellido'], 0, 1)) ?>
            </div>
        <?php endif; ?>
        <div>
            <h1><?= e($atleta['nombre'] . ' ' . $atleta['apellido']) ?></h1>
            <div class="subtitle"><?= e($atleta['nombre_categoria'] ?? 'Sin Categoría') ?> · Antropometría</div>
        </div>
    </div>
    <div style="display: flex; gap: 12px;">
        <a href="<?= e(url('/admin/medidas')) ?>" class="btn btn-ghost">
            <i class="ph ph-caret-left"></i> Volver
        </a>
        <a href="<?= e(url("/admin/atletas/{$atleta['atleta_id']}")) ?>" class="btn btn-outline">
            <i class="ph ph-user"></i> Perfil Completo
        </a>
    </div>
</div>

<div class="antro-layout">
    <!-- Formulario de Registro -->
    <div class="card" style="grid-area: form;">
        <h3 style="margin-top:0; font-size: 16px; margin-bottom: 20px;"><i class="ph ph-plus-circle"></i> Nueva Medición</h3>
        <form method="POST" action="<?= e(url("/admin/medidas/atleta/{$atleta['atleta_id']}")) ?>" id="form-medida">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label class="form-label"><span class="required">*</span> Fecha de Medición</label>
                <input type="date" name="fecha_medicion" class="form-control" required value="<?= e(date('Y-m-d')) ?>">
            </div>

            <div class="af-grid af-grid--2">
                <div class="form-group">
                    <label class="form-label">Peso (kg)</label>
                    <input type="number" step="0.01" name="peso" class="form-control" placeholder="0.00" min="0" max="250">
                </div>
                <div class="form-group">
                    <label class="form-label">Altura (cm)</label>
                    <input type="number" step="0.01" name="altura" class="form-control" placeholder="0.00" min="0" max="250">
                </div>
            </div>

            <div class="af-grid af-grid--2">
                <div class="form-group">
                    <label class="form-label">% Grasa Corporal</label>
                    <input type="number" step="0.01" name="porcentaje_grasa" class="form-control" placeholder="0.00 %" min="0" max="100">
                </div>
                <div class="form-group">
                    <label class="form-label">% Masa Muscular</label>
                    <input type="number" step="0.01" name="porcentaje_musculatura" class="form-control" placeholder="0.00 %" min="0" max="100">
                </div>
            </div>

            <div class="af-grid af-grid--3" style="margin-top: 12px; padding-top: 16px; border-top: 1px dashed var(--color-border);">
                <div class="form-group">
                    <label class="form-label">Envergadura (cm)</label>
                    <input type="number" step="0.01" name="envergadura" class="form-control" placeholder="0" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Largo Pierna (cm)</label>
                    <input type="number" step="0.01" name="largo_de_pierna" class="form-control" placeholder="0" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Largo Torso (cm)</label>
                    <input type="number" step="0.01" name="largo_de_torso" class="form-control" placeholder="0" min="0">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top: 24px;">
                <i class="ph ph-floppy-disk"></i> Guardar Mediciones
            </button>
        </form>
    </div>

    <!-- Gráfico de Evolución -->
    <div class="card" style="grid-area: chart; display: flex; flex-direction: column;">
        <h3 style="margin-top:0; font-size: 16px; margin-bottom: 20px;"><i class="ph ph-chart-line"></i> Evolución Temporal</h3>
        <?php if (empty($historial)): ?>
            <div style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--color-text-muted);">
                <i class="ph ph-chart-line-up" style="font-size: 48px; opacity: 0.2; margin-bottom: 12px;"></i>
                No hay datos suficientes para graficar
            </div>
        <?php else: ?>
            <div id="chart-medidas" style="flex: 1; min-height: 350px;"></div>
        <?php endif; ?>
    </div>

    <!-- Historial -->
    <div class="card" style="grid-area: history; padding: 0; overflow: hidden;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--color-border); background: var(--color-surface-2); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin:0; font-size: 16px;"><i class="ph ph-clock-counter-clockwise"></i> Historial de Mediciones</h3>
            <span class="badge badge-primary"><?= count($historial) ?> registros</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding-left: 24px;">Fecha</th>
                        <th>Peso (kg)</th>
                        <th>Altura (cm)</th>
                        <th>IMC</th>
                        <th>% Grasa</th>
                        <th>% Músculo</th>
                        <th style="text-align: right; padding-right: 24px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historial as $m): ?>
                    <tr class="medida-row">
                        <td style="padding-left: 24px;">
                            <div style="font-weight: 600;"><?= e(date('d/m/Y', strtotime($m['fecha_medicion']))) ?></div>
                        </td>
                        <td><?= e($m['peso'] ?: '—') ?></td>
                        <td><?= e($m['altura'] ?: '—') ?></td>
                        <td>
                            <?php 
                                if ($m['peso'] && $m['altura']) {
                                    $imc = $m['peso'] / (($m['altura']/100) ** 2);
                                    $imcColor = $imc < 18.5 ? '#3B82F6' : ($imc < 25 ? '#10B981' : ($imc < 30 ? '#F59E0B' : '#EF4444'));
                                    echo '<span style="color:'.$imcColor.'; font-weight:700;">' . number_format($imc, 1) . '</span>';
                                } else {
                                    echo '—';
                                }
                            ?>
                        </td>
                        <td><?= e($m['porcentaje_grasa'] ? $m['porcentaje_grasa'] . '%' : '—') ?></td>
                        <td><?= e($m['porcentaje_musculatura'] ? $m['porcentaje_musculatura'] . '%' : '—') ?></td>
                        <td style="text-align: right; padding-right: 24px;">
                            <button class="btn-delete-premium btn-delete-medida" data-id="<?= $m['medidas_id'] ?>">
                                <i class="ph ph-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div id="medidas-pagination" style="display: flex; justify-content: center; margin-top: 24px; padding-bottom: 24px;"></div>
    </div>
</div>

<style>
.antro-layout {
    display: grid;
    grid-template-columns: 380px 1fr;
    grid-template-areas: 
        "form chart"
        "history history";
    gap: 24px;
}
@media (max-width: 1000px) {
    .antro-layout {
        grid-template-columns: 1fr;
        grid-template-areas: 
            "form"
            "chart"
            "history";
    }
}
.btn-block { width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; }
</style>

<?php if (!empty($historial)): ?>
<!-- Incluir local de ECharts -->
<script src="<?= e(url('/assets/js/lib/echarts.min.js')) ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartDom = document.getElementById('chart-medidas');
    if (!chartDom) return;
    const myChart = echarts.init(chartDom);
    
    const rawData = <?= json_encode(array_reverse($historial)) ?>;
    const dates = rawData.map(i => i.fecha_medicion);
    
    const option = {
        tooltip: { trigger: 'axis' },
        legend: { data: ['Peso (kg)', 'Altura (cm)', '% Grasa'], bottom: 0 },
        grid: { left: '3%', right: '4%', bottom: '10%', containLabel: true },
        xAxis: { type: 'category', boundaryGap: false, data: dates },
        yAxis: [
            { type: 'value', name: 'Peso / %', position: 'left' },
            { type: 'value', name: 'Altura (cm)', position: 'right', min: 100 }
        ],
        series: [
            {
                name: 'Peso (kg)',
                type: 'line',
                smooth: true,
                data: rawData.map(i => i.peso),
                color: '#EF4444',
                areaStyle: { opacity: 0.1 }
            },
            {
                name: 'Altura (cm)',
                type: 'line',
                smooth: true,
                yAxisIndex: 1,
                data: rawData.map(i => i.altura),
                color: '#3B82F6'
            },
            {
                name: '% Grasa',
                type: 'line',
                smooth: true,
                data: rawData.map(i => i.porcentaje_grasa),
                color: '#F59E0B'
            }
        ]
    };

    myChart.setOption(option);
    window.addEventListener('resize', () => myChart.resize());

    // Borrado de medidas
    document.querySelectorAll('.btn-delete-medida').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const atletaId = "<?= $atleta['atleta_id'] ?>";
            
            CadaModal.confirm({
                title: '¿Eliminar Medición?',
                text: '¿Estás seguro de eliminar este registro físico? Esta acción no se puede deshacer.',
                type: 'danger',
                confirmText: 'Sí, Eliminar',
                cancelText: 'Cancelar'
            }).then((confirmed) => {
                if (confirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `<?= url('/admin/medidas') ?>/${id}/eliminar?atleta_id=${atletaId}`;
                    
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_csrf';
                    csrf.value = document.querySelector('meta[name="csrf-token"]').content;
                    
                    form.appendChild(csrf);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });
});
</script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    CadaPagination({
        rowSelector: '.medida-row',
        containerId: 'medidas-pagination'
    });
});
</script>
