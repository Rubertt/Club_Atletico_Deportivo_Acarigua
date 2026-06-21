<?php /** @var array $items */
/** @var array $filters */
/** @var array $entrenadores */
/** @var array $dataCategorias */
/** @var array $dataDemografia */ ?>

<!-- Incluir local de Chart.js -->
<script src="<?= e(url('/assets/js/lib/chart.umd.js')) ?>"></script>

<div class="page-header">
    <div>
        <h1>Categorías Deportivas</h1>
        <div class="subtitle">Gestión y organización de grupos por rangos de edad</div>
    </div>
    <?php if (can('admin')): ?>
        <a href="<?= e(url('/admin/categorias/crear')) ?>" class="btn btn-primary">
            <i class="ph ph-plus"></i> Nueva Categoría
        </a>
    <?php endif; ?>
</div>

<?php 
$total = count($items);
$activas = count(array_filter($items, fn($i) => (int)$i['estatus'] === 1));
$totalAtletas = array_sum(array_column($items, 'total_atletas'));
?>

<!-- Sección Superior: Contadores y Gráficos -->
<div class="categories-top-section">
    <!-- Contadores -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?= $total ?></div>
            <div class="stat-label">Total Categorías</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: var(--color-success)"><?= $activas ?></div>
            <div class="stat-label">Activas</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: var(--color-primary)"><?= $totalAtletas ?></div>
            <div class="stat-label">Atletas Totales</div>
        </div>
    </div>

    <!-- Gráficas -->
    <div class="top-charts-container">
        <!-- Distribución por Categoría -->
        <div class="card chart-card">
            <h4>
                <i class="ph ph-tag" style="color: var(--color-primary);"></i> Distribución por Categoría
            </h4>
            <div class="canvas-wrapper">
                <canvas id="chart-categorias"></canvas>
            </div>
        </div>

        <!-- Pirámide Demográfica -->
        <div class="card chart-card">
            <h4>
                <i class="ph ph-gender-intersex" style="color: var(--color-info);"></i> Pirámide Demográfica
            </h4>
            <div class="canvas-wrapper">
                <canvas id="chart-demografia"></canvas>
            </div>
        </div>
    </div>
</div>

<form method="GET" class="table-filters card" style="display: flex; gap: 16px; align-items: flex-end; padding: 16px; margin-bottom: 24px; flex-wrap: wrap;">
    <div class="form-group" style="flex: 1; min-width: 250px; margin-bottom: 0;">
        <label class="form-label" for="q"><i class="ph ph-magnifying-glass"></i> Buscar Categoría</label>
        <input type="search" id="q" name="q" class="form-control" placeholder="Nombre de categoría..." value="<?= e($filters['q'] ?? '') ?>">
    </div>

    <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
        <label class="form-label" for="sexo"><i class="ph ph-gender-intersex"></i> Género</label>
        <select id="sexo" name="sexo" class="form-control">
            <option value="">Todos los géneros</option>
            <option value="M" <?= ($filters['sexo'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino</option>
            <option value="F" <?= ($filters['sexo'] ?? '') === 'F' ? 'selected' : '' ?>>Femenino</option>
            <option value="X" <?= ($filters['sexo'] ?? '') === 'X' ? 'selected' : '' ?>>Mixto</option>
        </select>
    </div>

    <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
        <label class="form-label" for="entrenador_id"><i class="ph ph-user"></i> Entrenador</label>
        <select id="entrenador_id" name="entrenador_id" class="form-control">
            <option value="">Todos los entrenadores</option>
            <?php foreach ($entrenadores as $ent): ?>
                <option value="<?= (int) $ent['usuario_id'] ?>" <?= ($filters['entrenador_id'] ?? '') == $ent['usuario_id'] ? 'selected' : '' ?>>
                    <?= e($ent['nombre'] . ' ' . $ent['apellido']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div style="display: flex; gap: 8px;">
        <a href="<?= e(url('/admin/categorias')) ?>" class="btn btn-outline" title="Limpiar filtros" style="height: 44px; display: inline-flex; align-items: center; justify-content: center;"><i class="ph ph-trash"></i> Limpiar</a>
    </div>
</form>

<div class="quick-grid" style="grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;">
    <?php if (empty($items)): ?>
        <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 80px 24px; background: var(--color-surface);">
            <div style="width: 80px; height: 80px; background: var(--color-surface-2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
                <i class="ph ph-shield-slash" style="font-size: 40px; color: var(--color-text-muted);"></i>
            </div>
            <h3 style="margin-bottom: 8px;">No hay categorías registradas</h3>
            <p class="text-muted" style="max-width: 400px; margin: 0 auto 24px;">Las categorías permiten agrupar a los atletas por edad y asignarles un entrenador específico.</p>
            <?php if (can('admin')): ?>
                <a href="<?= e(url('/admin/categorias/crear')) ?>" class="btn btn-outline">
                    <i class="ph ph-plus"></i> Crear Primera Categoría
                </a>
            <?php endif; ?>
        </div>
    <?php else: foreach ($items as $c): ?>
        <div class="card categoria-card" style="margin: 0; padding: 0; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s;">
            <!-- Header Card -->
            <div style="padding: 24px; border-bottom: 1px solid var(--color-border); position: relative;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <?php 
                            $isActiva = (int) $c['estatus'] === 1;
                            $statusText = $isActiva ? 'Activa' : 'Inactiva';
                            $badgeClass = $isActiva ? 'success' : 'warning';
                        ?>
                        <span class="badge badge-<?= $badgeClass ?>">
                            <?= e($statusText) ?>
                        </span>
                        <span style="font-size: 12px; color: var(--color-text-muted); font-weight: 600;">ID: #<?= $c['categoria_id'] ?></span>
                    </div>
                    <?php if (can('admin')): ?>
                        <div class="flex gap-sm">
                            <a href="<?= e(url("/admin/categorias/{$c['categoria_id']}/editar")) ?>" class="btn-edit-premium" title="Editar">
                                <i class="ph ph-pencil-simple"></i>
                            </a>
                            <form method="POST" action="<?= e(url("/admin/categorias/{$c['categoria_id']}/eliminar")) ?>" style="display:inline;">
                                <?= csrf_field() ?>
                                <button type="button" class="btn-delete-premium btn-eliminar-categoria" title="Eliminar" data-total-atletas="<?= (int) ($c['total_atletas'] ?? 0) ?>" data-nombre="<?= e($c['nombre_categoria']) ?>">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
                
                <h2 style="margin: 0 0 12px 0; font-family: var(--font-display); font-size: 22px; font-weight: 700; color: var(--color-text); text-align: center;">
                    <?= e($c['nombre_categoria']) ?>
                </h2>
                
                <div style="display: flex; justify-content: space-between; align-items: center; color: var(--color-text-muted); font-size: 13px; font-weight: 500;">
                    <span><i class="ph ph-users"></i> <?= (int) $c['edad_min'] ?> a <?= (int) $c['edad_max'] ?> años</span>
                    <span>
                        <i class="ph ph-gender-<?= strtolower($c['sexo_categoria'] ?? 'M') === 'f' ? 'female' : (strtolower($c['sexo_categoria'] ?? 'M') === 'm' ? 'male' : 'intersex') ?>"></i>
                        <?= $c['sexo_categoria'] === 'F' ? 'Femenino' : ($c['sexo_categoria'] === 'M' ? 'Masculino' : 'Mixto') ?>
                    </span>
                </div>
            </div>
            
            <!-- Info Section -->
            <div style="padding: 24px; flex: 1; display: flex; flex-direction: column; gap: 20px;">
                <!-- Entrenador -->
                <div style="background: var(--color-surface); padding: 12px 16px; border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
                    <div style="font-size: 11px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 8px;">Entrenador</div>
                    <?php if (!empty($c['entrenador'])): ?>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <?php if (!empty($c['entrenador_foto'])): ?>
                                <img src="<?= e(url($c['entrenador_foto'])) ?>" alt="" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                                <div style="width: 32px; height: 32px; background: var(--color-primary); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800;">
                                    <?= e(mb_substr($c['entrenador'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            <div style="font-weight: 600; font-size: 14px; color: var(--color-text);"><?= e($c['entrenador']) ?></div>
                        </div>
                    <?php else: ?>
                        <div style="display: flex; align-items: center; gap: 12px; color: var(--color-text-muted);">
                            <div style="width: 32px; height: 32px; background: var(--color-surface-2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="ph ph-user-minus" style="font-size: 14px;"></i>
                            </div>
                            <div style="font-size: 13px; font-style: italic;">Sin asignar</div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Capacidad / Atletas -->
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 8px;">
                        <div style="font-size: 13px; font-weight: 600; color: var(--color-text);">Atletas Inscritos</div>
                        <div style="font-size: 16px; font-weight: 800; color: var(--color-primary);"><?= (int) ($c['total_atletas'] ?? 0) ?></div>
                    </div>
                    <?php 
                        $maxRef = 30; // Referencia visual
                        $porcentaje = min(100, ((int) ($c['total_atletas'] ?? 0) / $maxRef) * 100);
                        $barColor = $porcentaje > 80 ? 'var(--color-danger)' : ($porcentaje > 50 ? 'var(--color-warning)' : 'var(--color-success)');
                    ?>
                    <div style="height: 8px; background: var(--color-surface-2); border-radius: 4px; overflow: hidden;">
                        <div style="height: 100%; width: <?= $porcentaje ?>%; background: <?= $barColor ?>; border-radius: 4px; transition: width 0.5s ease;"></div>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div style="padding: 16px 24px; background: var(--color-surface); border-top: 1px solid var(--color-border); display: flex; gap: 8px;">
                <a href="<?= e(url('/admin/categorias/' . $c['categoria_id'] . '/detalles')) ?>" class="btn btn-outline" style="flex: 1; font-size: 13px;">
                    <i class="ph ph-users"></i> Ver Detalles
                </a>
                <a href="<?= e(url('/admin/reportes/categoria/' . $c['categoria_id'])) ?>" class="btn btn-primary" style="flex: 1; font-size: 13px;" target="_blank">
                    <i class="ph ph-file-pdf"></i> Reporte
                </a>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>

<div id="categorias-pagination" style="display: flex; justify-content: center; margin-top: 24px;"></div>

<style>
.card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: var(--color-primary-light);
}

.categories-top-section {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
    margin-bottom: 24px;
}

.top-charts-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
}

.chart-card {
    display: flex;
    flex-direction: column;
    min-height: 280px;
    padding: 20px;
}

.chart-card h4 {
    margin: 0 0 16px 0;
    font-family: var(--font-display);
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--color-text);
}

.chart-card .canvas-wrapper {
    flex: 1;
    position: relative;
    width: 100%;
    min-height: 200px;
}

@media (min-width: 1024px) {
    .categories-top-section {
        grid-template-columns: 320px 1fr;
    }
    .categories-top-section .stats-grid {
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin-bottom: 0;
        justify-content: space-between;
    }
    .categories-top-section .stats-grid .stat-card {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 16px 20px;
        margin: 0;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Obtener y configurar tema/paleta de colores dinámicos
    const getColors = () => {
        const style = getComputedStyle(document.body);
        return {
            text: style.getPropertyValue('--color-text').trim() || '#e5e7eb',
            textMuted: style.getPropertyValue('--color-text-muted').trim() || '#9ca3af',
            border: style.getPropertyValue('--color-border').trim() || '#374151',
            primary: style.getPropertyValue('--color-primary').trim() || '#DE0A26',
            success: style.getPropertyValue('--color-success').trim() || '#10B981',
            info: style.getPropertyValue('--color-info').trim() || '#3B82F6',
            warning: style.getPropertyValue('--color-warning').trim() || '#F59E0B'
        };
    };

    let colors = getColors();

    // Datos inyectados desde el servidor
    const dataCategoriasRaw = <?= json_encode($dataCategorias) ?>;
    const dataDemografiaRaw = <?= json_encode($dataDemografia) ?>;

    const chartInstances = [];

    // 1. Gráfica de Dona: Distribución por Categoría
    const ctxCategorias = document.getElementById('chart-categorias').getContext('2d');
    const chartCategorias = new Chart(ctxCategorias, {
        type: 'doughnut',
        data: {
            labels: dataCategoriasRaw.map(x => x.nombre_categoria),
            datasets: [{
                data: dataCategoriasRaw.map(x => x.total),
                backgroundColor: [
                    '#DE0A26', '#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#6B7280'
                ],
                borderColor: 'transparent'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: colors.text }
                }
            }
        }
    });
    chartInstances.push(chartCategorias);

    // 2. Gráfica de Barras Apiladas: Pirámide Demográfica
    const rangosEdades = ['Sub-10', 'Sub-13', 'Sub-16', 'Sub-20/Mayores'];
    const dataM = rangosEdades.map(r => {
        const found = dataDemografiaRaw.find(x => x.sexo === 'M' && x.rango_edad === r);
        return found ? parseInt(found.total) : 0;
    });
    const dataF = rangosEdades.map(r => {
        const found = dataDemografiaRaw.find(x => x.sexo === 'F' && x.rango_edad === r);
        return found ? parseInt(found.total) : 0;
    });

    const ctxDemografia = document.getElementById('chart-demografia').getContext('2d');
    const chartDemografia = new Chart(ctxDemografia, {
        type: 'bar',
        data: {
            labels: rangosEdades,
            datasets: [
                { label: 'Masculino', data: dataM, backgroundColor: '#3B82F6', borderRadius: 4 },
                { label: 'Femenino', data: dataF, backgroundColor: '#EC4899', borderRadius: 4 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { stacked: true, grid: { color: colors.border }, ticks: { color: colors.text } },
                y: { stacked: true, grid: { color: colors.border }, ticks: { color: colors.text, precision: 0 } }
            },
            plugins: {
                legend: { labels: { color: colors.text } }
            }
        }
    });
    chartInstances.push(chartDemografia);

    // --- MANEJO DE CAMBIO DE TEMA DINÁMICO ---
    const updateChartThemes = () => {
        colors = getColors();
        chartInstances.forEach(chart => {
            if (chart.options.plugins && chart.options.plugins.legend && chart.options.plugins.legend.labels) {
                chart.options.plugins.legend.labels.color = colors.text;
            }
            if (chart.options.scales) {
                Object.keys(chart.options.scales).forEach(scaleKey => {
                    const scale = chart.options.scales[scaleKey];
                    if (scale.grid) scale.grid.color = colors.border;
                    if (scale.ticks) scale.ticks.color = colors.text;
                });
            }
            chart.update();
        });
    };

    document.querySelectorAll('[data-theme-toggle]').forEach(btn => {
        btn.addEventListener('click', () => {
            setTimeout(updateChartThemes, 100);
        });
    });

    // --- FILTROS AJAX Y ELIMINACIÓN ---
    const form = document.querySelector('.table-filters');
    if (form) {
        const qInput = form.querySelector('#q');
        const sexoSelect = form.querySelector('#sexo');
        const entrenadorSelect = form.querySelector('#entrenador_id');
        let debounceTimer;

        const performFilter = () => {
            const formData = new FormData(form);
            formData.append('ajax', '1');
            const queryString = new URLSearchParams(formData).toString();
            
            const newUrl = `${window.location.pathname}?${new URLSearchParams(new FormData(form)).toString()}`;
            window.history.replaceState({ path: newUrl }, '', newUrl);

            fetch(`${window.location.pathname}?${queryString}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const oldGrid = document.querySelector('.quick-grid');
                const newGrid = doc.querySelector('.quick-grid');
                if (oldGrid && newGrid) {
                    oldGrid.innerHTML = newGrid.innerHTML;
                }
                
                const oldStats = document.querySelector('.stats-grid');
                const newStats = doc.querySelector('.stats-grid');
                if (oldStats && newStats) {
                    oldStats.innerHTML = newStats.innerHTML;
                }

                bindDeleteButtons();
                initPagination();
            })
            .catch(err => console.error('Error al filtrar:', err));
        };

        if (sexoSelect) sexoSelect.addEventListener('change', performFilter);
        if (entrenadorSelect) entrenadorSelect.addEventListener('change', performFilter);
        if (qInput) {
            qInput.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(performFilter, 300);
            });
            qInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') e.preventDefault();
            });
        }
        
        form.addEventListener('submit', (e) => e.preventDefault());
    }

    function initPagination() {
        CadaPagination({
            rowSelector: '.categoria-card',
            containerId: 'categorias-pagination'
        });
    }

    function bindDeleteButtons() {
        document.querySelectorAll('.btn-eliminar-categoria').forEach(btn => {
            btn.onclick = () => {
                const form = btn.closest('form');
                const totalAtletas = parseInt(btn.getAttribute('data-total-atletas') || '0', 10);
                const nombre = btn.getAttribute('data-nombre');

                if (totalAtletas > 0) {
                    CadaModal.alert({
                        title: 'No se puede eliminar',
                        text: `La categoría <strong>${nombre}</strong> tiene <strong>${totalAtletas}</strong> atleta(s) asignado(s). Debe reasignar, desactivar o eliminar a los atletas antes de poder eliminar la categoría.`,
                        type: 'error',
                        confirmText: 'Entendido'
                    });
                    return;
                }

                CadaModal.confirm({
                    title: '¿Eliminar Categoría?',
                    text: `¿Estás seguro de que deseas eliminar la categoría <strong>${nombre}</strong>? Esta acción no se puede deshacer.`,
                    type: 'danger',
                    confirmText: 'Sí, Eliminar',
                    cancelText: 'Cancelar'
                }).then(confirmed => {
                    if (confirmed) form.submit();
                });
            };
        });
    }

    bindDeleteButtons();
    initPagination();
});
</script>
