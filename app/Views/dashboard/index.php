<?php /** @var array $stats @var array $dataAsistencia @var array $dataActividades @var array $dataEntrenadores @var array $topAtletas @var array $evolucionRoster @var array $consistenciaCategorias */ $user = auth() ?? []; ?>

<div class="welcome-card">
    <div class="wc-avatar"><?= strtoupper(mb_substr($user['nombre'] ?? '?', 0, 1)) ?></div>
    <div>
        <div class="wc-title">Bienvenido, <?= e(($user['nombre'] ?? '') . ' ' . ($user['apellido'] ?? '')) ?></div>
        <div class="wc-sub"><?= e($user['nombre_rol'] ?? 'Administrador') ?> — Club Atlético Deportivo Acarigua</div>
    </div>
</div>

<h3 style="font-family: var(--font-display); margin-bottom: 16px;">Accesos Rápidos</h3>
<div class="quick-grid">
    <a href="<?= e(url('/admin/atletas')) ?>" class="quick-card">
        <div class="qc-icon red"><i class="ph ph-users"></i></div>
        <div>
            <div class="qc-title">Atletas</div>
            <div class="qc-desc">Gestión del equipo</div>
        </div>
    </a>

    <a href="<?= e(url('/admin/asistencias/crear')) ?>" class="quick-card">
        <div class="qc-icon blue"><i class="ph ph-clipboard-text"></i></div>
        <div>
            <div class="qc-title">Asistencia</div>
            <div class="qc-desc">Registrar asistencia</div>
        </div>
    </a>

    <a href="<?= e(url('/admin/categorias')) ?>" class="quick-card">
        <div class="qc-icon red"><i class="ph ph-folders"></i></div>
        <div>
            <div class="qc-title">Categorías</div>
            <div class="qc-desc"><?= (int) $stats['categorias'] ?> activas</div>
        </div>
    </a>
</div>

<h3 style="font-family: var(--font-display); margin-bottom: 16px;">Contadores Generales</h3>
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number" style="color: var(--color-primary);"><?= (int) $stats['atletas'] ?></div>
        <div class="stat-label">Atletas registrados</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: var(--color-success);"><?= (int) $stats['activos'] ?></div>
        <div class="stat-label">Atletas activos</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: var(--color-info);"><?= (int) $stats['categorias'] ?></div>
        <div class="stat-label">Categorías activas</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: #F59E0B;"><?= (int) ($stats['lesionados'] ?? 0) ?></div>
        <div class="stat-label">Lesionados</div>
    </div>
</div>


<h3 style="font-family: var(--font-display); margin-top: 32px; margin-bottom: 16px;">Análisis General</h3>
<div class="dashboard-charts-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 320px), 1fr)); gap: 24px; margin-bottom: 32px;">
    <!-- Gráfica 5: Carga de Atletas por Entrenador -->
    <div class="card" style="padding: 20px; display: flex; flex-direction: column; min-height: 380px;">
        <h4 style="margin: 0 0 16px 0; font-family: var(--font-display); font-size: 15px; display: flex; align-items: center; gap: 8px;">
            <i class="ph ph-users-three" style="color: var(--color-primary);"></i> Carga por Entrenador
        </h4>
        <div style="flex: 1; position: relative; width: 100%;">
            <canvas id="chart-entrenadores"></canvas>
        </div>
    </div>

    <!-- Gráfica 4: Actividades del Mes Actual -->
    <div class="card" style="padding: 20px; display: flex; flex-direction: column; min-height: 380px;">
        <h4 style="margin: 0 0 16px 0; font-family: var(--font-display); font-size: 15px; display: flex; align-items: center; gap: 8px;">
            <i class="ph ph-calendar" style="color: var(--color-warning);"></i> Actividades de este Mes
        </h4>
        <div style="flex: 1; position: relative; width: 100%;">
            <canvas id="chart-actividades"></canvas>
        </div>
    </div>

    <!-- Gráfica Directiva 1: Top 5 Rendimiento Físico -->
    <div class="card" style="padding: 20px; display: flex; flex-direction: column; min-height: 380px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h4 style="margin: 0; font-family: var(--font-display); font-size: 15px; display: flex; align-items: center; gap: 8px;">
                <i class="ph ph-trophy" style="color: var(--color-warning);"></i> Top 5 Rendimiento Físico
            </h4>
            <select id="select-categoria-edad" style="background: var(--color-border); color: var(--color-text); border: 1px solid var(--color-border); padding: 4px 8px; border-radius: 4px; font-size: 12px; cursor: pointer; outline: none;">
                <option value="Sub-7">Sub-7</option>
                <option value="Sub-10">Sub-10</option>
                <option value="Sub-13">Sub-13</option>
                <option value="Sub-16" selected>Sub-16</option>
                <option value="Sub-19">Sub-19</option>
                <option value="Sub-40">Sub-40</option>
                <option value="Master-49">Master-49</option>
                <option value="Master-59">Master-59</option>
                <option value="Master-69">Master-69</option>
                <option value="Master-70+">Master-70+</option>
            </select>
        </div>
        <div style="flex: 1; position: relative; width: 100%;">
            <canvas id="chart-top-rendimiento"></canvas>
        </div>
    </div>

    <!-- Gráfica Directiva 2: Índice de Consistencia y Asistencia -->
    <div class="card" style="padding: 20px; display: flex; flex-direction: column; min-height: 380px;">
        <h4 style="margin: 0 0 16px 0; font-family: var(--font-display); font-size: 15px; display: flex; align-items: center; gap: 8px;">
            <i class="ph ph-shield-check" style="color: var(--color-success);"></i> Asistencia y Consistencia
        </h4>
        <div style="flex: 1; position: relative; width: 100%;">
            <canvas id="chart-consistencia-asistencia"></canvas>
        </div>
    </div>
</div>

<h3 style="font-family: var(--font-display); margin-top: 32px; margin-bottom: 16px;">Métricas Directivas</h3>
<div class="dashboard-charts-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 450px), 1fr)); gap: 24px; margin-bottom: 32px;">
    <!-- Gráfica 2: Asistencia por Categoría y Tipo de Actividad -->
    <div class="card" style="padding: 20px; display: flex; flex-direction: column; min-height: 380px;">
        <h4 style="margin: 0 0 16px 0; font-family: var(--font-display); font-size: 15px; display: flex; align-items: center; gap: 8px;">
            <i class="ph ph-chart-bar" style="color: var(--color-success);"></i> Asistencia por Categoría y Actividad
        </h4>
        <div style="flex: 1; position: relative; width: 100%; min-height: 250px;">
            <canvas id="chart-asistencia"></canvas>
        </div>
    </div>

    <!-- Gráfica Directiva 3: Crecimiento Histórico de Matrícula -->
    <div class="card" style="padding: 20px; display: flex; flex-direction: column; min-height: 380px;">
        <h4 style="margin: 0 0 16px 0; font-family: var(--font-display); font-size: 15px; display: flex; align-items: center; gap: 8px;">
            <i class="ph ph-trend-up" style="color: var(--color-info);"></i> Evolución del Roster
        </h4>
        <div style="flex: 1; position: relative; width: 100%; min-height: 250px;">
            <canvas id="chart-evolucion-roster"></canvas>
        </div>
    </div>
</div>

<!-- Incluir local de Chart.js -->
<script src="<?= e(url('/assets/js/lib/chart.umd.js')) ?>"></script>

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
    const dataAsistenciaRaw = <?= json_encode($dataAsistencia) ?>;
    const dataActividadesRaw = <?= json_encode($dataActividades) ?>;
    const dataEntrenadoresRaw = <?= json_encode($dataEntrenadores) ?>;
    
    // Datos directivos
    const topAtletasRaw = <?= json_encode($topAtletas) ?>;
    const evolucionRosterRaw = <?= json_encode($evolucionRoster) ?>;
    const consistenciaCategoriasRaw = <?= json_encode($consistenciaCategorias) ?>;

    const chartInstances = [];

    // --- CONFIGURACIÓN DE GRÁFICOS ---

    // 3. Gráfica de Barras Horizontal: Carga por Entrenador
    const ctxEntrenadores = document.getElementById('chart-entrenadores').getContext('2d');
    const chartEntrenadores = new Chart(ctxEntrenadores, {
        type: 'bar',
        data: {
            labels: dataEntrenadoresRaw.map(x => x.entrenador),
            datasets: [{
                label: 'Atletas',
                data: dataEntrenadoresRaw.map(x => x.total_atletas),
                backgroundColor: colors.primary,
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { grid: { color: colors.border }, ticks: { color: colors.text, precision: 0 } },
                y: { grid: { color: colors.border }, ticks: { color: colors.text } }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
    chartInstances.push(chartEntrenadores);

    // 4. Gráfica de Línea: Actividades de este Mes (Agrupado por día)
    const serverYear = <?= date('Y') ?>;
    const serverMonth = <?= date('m') - 1 ?>; // 0-indexed for JS Date
    const totalDays = new Date(serverYear, serverMonth + 1, 0).getDate();
    
    const labelsActividades = [];
    const diasKeys = [];
    for (let i = 1; i <= totalDays; i++) {
        labelsActividades.push(i);
        diasKeys.push(String(i).padStart(2, '0'));
    }

    const tiposActividades = [
        { id: 0, label: 'Partido', color: '#10B981' },
        { id: 1, label: 'Entrenamiento', color: '#3B82F6' },
        { id: 2, label: 'Pruebas Físicas', color: '#F59E0B' },
        { id: 3, label: 'Evento Especial', color: '#8B5CF6' }
    ];

    const datasetsActividades = tiposActividades.map(t => ({
        label: t.label,
        data: diasKeys.map(d => {
            const found = dataActividadesRaw.find(x => x.dia === d && parseInt(x.tipo_actividad) === t.id);
            return found ? parseInt(found.total) : 0;
        }),
        borderColor: t.color,
        backgroundColor: t.color + '22',
        fill: true,
        tension: 0.3
    }));

    const ctxActividades = document.getElementById('chart-actividades').getContext('2d');
    const chartActividades = new Chart(ctxActividades, {
        type: 'line',
        data: {
            labels: labelsActividades,
            datasets: datasetsActividades
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { grid: { color: colors.border }, ticks: { color: colors.text } },
                y: { grid: { color: colors.border }, ticks: { color: colors.text, precision: 0 } }
            },
            plugins: {
                legend: { labels: { color: colors.text } }
            }
        }
    });
    chartInstances.push(chartActividades);

    // 5. Gráfica de Barras Apiladas: Asistencia por Categoría y Tipo de Actividad
    const categoriasAsistenciaBase = [...new Set(dataAsistenciaRaw.map(x => x.nombre_categoria))].sort();
    
    // Generar nombres con contador total de asistencia sin importar tipo
    const categoriasAsistencia = categoriasAsistenciaBase.map(cat => {
        const totalPresentes = dataAsistenciaRaw
            .filter(x => x.nombre_categoria === cat)
            .reduce((sum, x) => sum + parseInt(x.presentes), 0);
        return `${cat} (${totalPresentes})`;
    });

    const tiposActividadesMap = [
        { id: 0, label: 'Partido', color: '#10B981' },
        { id: 1, label: 'Entrenamiento', color: '#3B82F6' },
        { id: 2, label: 'Pruebas Físicas', color: '#F59E0B' },
        { id: 3, label: 'Evento Especial', color: '#8B5CF6' }
    ];

    const datasetsAsistencia = tiposActividadesMap.map(t => ({
        label: t.label,
        data: categoriasAsistencia.map(catLabel => {
            const cat = catLabel.split(' (')[0];
            const found = dataAsistenciaRaw.find(x => x.nombre_categoria === cat && parseInt(x.tipo_actividad) === t.id);
            return found ? parseInt(found.presentes) : 0;
        }),
        backgroundColor: t.color,
        borderRadius: 4
    }));

    const ctxAsistencia = document.getElementById('chart-asistencia').getContext('2d');
    const chartAsistencia = new Chart(ctxAsistencia, {
        type: 'bar',
        data: {
            labels: categoriasAsistencia,
            datasets: datasetsAsistencia
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { 
                    stacked: true, 
                    grid: { color: colors.border }, 
                    ticks: { color: colors.text } 
                },
                y: { 
                    stacked: true, 
                    grid: { color: colors.border }, 
                    ticks: { color: colors.text },
                    title: {
                        display: true,
                        text: 'Asistencias (Presentes)',
                        color: colors.textMuted
                    }
                }
            },
            plugins: {
                legend: { labels: { color: colors.text } },
                tooltip: {
                    callbacks: {
                        label: context => {
                            const catLabel = context.label;
                            const cat = catLabel.split(' (')[0];
                            const label = context.dataset.label;
                            const val = context.raw;
                            const typeObj = tiposActividadesMap.find(x => x.label === label);
                            if (typeObj) {
                                const found = dataAsistenciaRaw.find(x => x.nombre_categoria === cat && parseInt(x.tipo_actividad) === typeObj.id);
                                if (found && parseInt(found.total_registros) > 0) {
                                    const rate = Math.round((parseInt(found.presentes) * 100) / parseInt(found.total_registros));
                                    return `${label}: ${val} presentes (Tasa: ${rate}%)`;
                                }
                            }
                            return `${label}: ${val} presentes`;
                        }
                    }
                }
            }
        }
    });
    chartInstances.push(chartAsistencia);


    // --- NUEVOS GRÁFICOS DIRECTIVOS ---

    // 1. Gráfica de Barras Horizontales: Top 5 Rendimiento Físico con filtro dinámico
    const ctxTopRendimiento = document.getElementById('chart-top-rendimiento').getContext('2d');
    const selectCategoriaEdad = document.getElementById('select-categoria-edad');
    
    // Función para obtener datasets correspondientes al rango de edad seleccionado
    const getTopRendimientoData = (rango) => {
        const atletas = topAtletasRaw[rango] || [];
        // Ordenamos inverso para que las barras queden de mayor (arriba) a menor (abajo) en el eje Y
        const sorted = [...atletas].reverse();
        return {
            labels: sorted.map(x => x.nombre),
            data: sorted.map(x => x.promedio)
        };
    };

    const initialRango = selectCategoriaEdad.value;
    const initialChartData = getTopRendimientoData(initialRango);

    const chartTopRendimiento = new Chart(ctxTopRendimiento, {
        type: 'bar',
        data: {
            labels: initialChartData.labels,
            datasets: [{
                label: 'Promedio General',
                data: initialChartData.data,
                backgroundColor: colors.warning,
                borderRadius: 4,
                barThickness: 20
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { 
                    grid: { color: colors.border }, 
                    ticks: { color: colors.text },
                    min: 0,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Puntaje Promedio (%)',
                        color: colors.textMuted,
                        font: { size: 11 }
                    }
                },
                y: { 
                    grid: { display: false }, 
                    ticks: { color: colors.text } 
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: context => `Puntaje: ${context.raw}%`
                    }
                }
            }
        }
    });
    chartInstances.push(chartTopRendimiento);

    // Escuchar cambios de categoría de edad para actualizar gráfico dinámicamente
    selectCategoriaEdad.addEventListener('change', (e) => {
        const newData = getTopRendimientoData(e.target.value);
        chartTopRendimiento.data.labels = newData.labels;
        chartTopRendimiento.data.datasets[0].data = newData.data;
        chartTopRendimiento.update();
    });

    // 2. Gráfica Mixta (Línea/Área + Barras): Evolución del Roster
    const ctxEvolucionRoster = document.getElementById('chart-evolucion-roster').getContext('2d');
    
    // Crear degradado para el área de la línea del roster
    const getRosterGradient = (color) => {
        const grad = ctxEvolucionRoster.createLinearGradient(0, 0, 0, 300);
        grad.addColorStop(0, color + '66'); // Con opacidad
        grad.addColorStop(1, color + '00');
        return grad;
    };

    const chartEvolucionRoster = new Chart(ctxEvolucionRoster, {
        type: 'bar', // Tipo base mixto
        data: {
            labels: evolucionRosterRaw.map(x => x.mes),
            datasets: [
                {
                    type: 'line',
                    label: 'Roster Acumulado (Total)',
                    data: evolucionRosterRaw.map(x => x.acumulado),
                    borderColor: colors.info,
                    borderWidth: 3,
                    backgroundColor: getRosterGradient(colors.info),
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'y'
                },
                {
                    type: 'bar',
                    label: 'Nuevos Registros',
                    data: evolucionRosterRaw.map(x => x.nuevos),
                    backgroundColor: colors.primary + 'bb',
                    borderColor: colors.primary,
                    borderWidth: 1,
                    borderRadius: 4,
                    barThickness: 20,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { 
                    grid: { color: colors.border }, 
                    ticks: { color: colors.text } 
                },
                y: {
                    type: 'linear',
                    position: 'left',
                    grid: { color: colors.border },
                    ticks: { color: colors.text, precision: 0 },
                    title: {
                        display: true,
                        text: 'Total Atletas',
                        color: colors.textMuted
                    }
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    grid: { drawOnChartArea: false }, // Evitar rejilla encimada
                    ticks: { color: colors.text, precision: 0 },
                    title: {
                        display: true,
                        text: 'Nuevos Registros',
                        color: colors.textMuted
                    }
                }
            },
            plugins: {
                legend: {
                    labels: { color: colors.text }
                }
            }
        }
    });
    chartInstances.push(chartEvolucionRoster);

    // 3. Gráfica de Barras Agrupadas: Consistencia y Asistencia
    const ctxConsistencia = document.getElementById('chart-consistencia-asistencia').getContext('2d');
    const chartConsistencia = new Chart(ctxConsistencia, {
        type: 'bar',
        data: {
            labels: consistenciaCategoriasRaw.map(x => x.nombre_categoria),
            datasets: [
                {
                    label: 'Promedio Asistencia',
                    data: consistenciaCategoriasRaw.map(x => x.tasa_asistencia_promedio),
                    backgroundColor: colors.success + 'dd',
                    borderRadius: 4
                },
                {
                    label: 'Índice Consistencia (>=80%)',
                    data: consistenciaCategoriasRaw.map(x => x.indice_consistencia),
                    backgroundColor: colors.primary + 'dd',
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { 
                    grid: { color: colors.border }, 
                    ticks: { color: colors.text } 
                },
                y: { 
                    grid: { color: colors.border }, 
                    ticks: { 
                        color: colors.text,
                        callback: val => val + '%'
                    },
                    max: 100
                }
            },
            plugins: {
                legend: {
                    labels: { color: colors.text }
                },
                tooltip: {
                    callbacks: {
                        title: (items) => {
                            const idx = items[0].dataIndex;
                            const item = consistenciaCategoriasRaw[idx];
                            return `${item.nombre_categoria}\nEntrenador: ${item.entrenador}`;
                        },
                        label: context => `${context.dataset.label}: ${context.raw}%`
                    }
                }
            }
        }
    });
    chartInstances.push(chartConsistencia);


    // --- MANEJO DE CAMBIO DE TEMA DINÁMICO ---
    const updateChartThemes = () => {
        colors = getColors();
        chartInstances.forEach(chart => {
            // Actualizar color de la leyenda
            if (chart.options.plugins && chart.options.plugins.legend && chart.options.plugins.legend.labels) {
                chart.options.plugins.legend.labels.color = colors.text;
            }
            // Actualizar color de ejes
            if (chart.options.scales) {
                Object.keys(chart.options.scales).forEach(scaleKey => {
                    const scale = chart.options.scales[scaleKey];
                    if (scale.grid) scale.grid.color = colors.border;
                    if (scale.ticks) scale.ticks.color = colors.text;
                    if (scale.title) scale.title.color = colors.textMuted;
                });
            }
            // Actualizar datasets individuales si usan colores de marca del tema
            if (chart === chartEntrenadores) {
                chart.data.datasets[0].backgroundColor = colors.primary;
            }
            if (chart === chartTopRendimiento) {
                chart.data.datasets[0].backgroundColor = colors.warning;
            }
            if (chart === chartEvolucionRoster) {
                chart.data.datasets[0].backgroundColor = getRosterGradient(colors.info);
                chart.data.datasets[0].borderColor = colors.info;
                chart.data.datasets[1].backgroundColor = colors.primary + 'bb';
                chart.data.datasets[1].borderColor = colors.primary;
            }
            if (chart === chartConsistencia) {
                chart.data.datasets[0].backgroundColor = colors.success + 'dd';
                chart.data.datasets[1].backgroundColor = colors.primary + 'dd';
            }
            chart.update();
        });
    };

    // Escuchar el evento click en el botón de cambiar tema
    document.querySelectorAll('[data-theme-toggle]').forEach(btn => {
        btn.addEventListener('click', () => {
            setTimeout(updateChartThemes, 100);
        });
    });
});
</script>