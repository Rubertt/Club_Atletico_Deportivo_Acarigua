<?php /** @var array $atleta */ ?>
<div class="page-header">
    <div>
        <h1>Perfil del Atleta</h1>
        <div class="subtitle">Expediente integral y seguimiento deportivo</div>
    </div>
    <div class="flex gap">
        <a href="<?= e(url('/admin/atletas')) ?>" class="btn btn-ghost"><i class="ph ph-arrow-left"></i> Directorio</a>
        <button onclick="window.print()" class="btn btn-outline" title="Imprimir Expediente"><i class="ph ph-printer"></i> Imprimir</button>
        <a href="<?= e(url("/admin/reportes/atleta/{$atleta['atleta_id']}")) ?>" class="btn btn-outline" target="_blank"><i class="ph ph-file-pdf"></i> Generar PDF</a>
        <?php if (can('admin')): ?>
            <a href="<?= e(url("/admin/atletas/{$atleta['atleta_id']}/editar")) ?>" class="btn btn-primary"><i class="ph ph-pencil-simple"></i> Editar Perfil</a>
        <?php endif; ?>
    </div>
</div>

<div style="display:grid; grid-template-columns:300px 1fr; gap:24px;" class="show-layout">
    <!-- Panel Izquierdo (Resumen) -->
    <div style="display:flex; flex-direction:column; gap:24px;">
        <div class="card" style="text-align:center; padding-top: 32px;">
            <?php if (!empty($atleta['foto'])): ?>
                <img src="<?= e(url($atleta['foto'])) ?>" style="width:160px; height:160px; border-radius:50%; object-fit:cover; margin:0 auto 16px; border: 4px solid var(--color-bg); box-shadow: 0 0 0 2px var(--color-primary-light);">
            <?php else: ?>
                <div class="avatar-placeholder" style="width:160px; height:160px; font-size:48px; margin:0 auto 16px; background: var(--color-primary-light); color: var(--color-primary);">
                    <?= e(mb_substr($atleta['nombre'], 0, 1) . mb_substr($atleta['apellido'], 0, 1)) ?>
                </div>
            <?php endif; ?>
            <h2 style="margin:0 0 4px; font-family: var(--font-display);"><?= e($atleta['nombre'] . ' ' . $atleta['apellido']) ?></h2>
            <div style="color: var(--color-text-muted); font-size: 14px; margin-bottom: 16px;">C.I: <?= e($atleta['cedula'] ?? '—') ?></div>
            
            <?php 
                $badge = match ($atleta['estatus']) {
                    'Activo' => 'success', 'Lesionado' => 'warning', 'Suspendido' => 'danger', default => 'primary'
                }; 
            ?>
            <span class="badge badge-<?= $badge ?>" style="padding: 6px 16px; border-radius: 20px; font-weight: 600;">
                <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:currentColor; margin-right:6px;"></span>
                <?= e($atleta['estatus']) ?>
            </span>
            
            <hr style="border:none; border-top:1px solid var(--color-border); margin: 24px 0;">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; text-align: left;">
                <div>
                    <div style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600;">Categoría</div>
                    <div style="font-weight: 500; display:flex; align-items:center; gap:4px; margin-top:4px;">
                        <i class="ph ph-shield-chevron text-muted"></i> <?= e($atleta['nombre_categoria'] ?? 'Sin asignar') ?>
                    </div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600;">Posición</div>
                    <div style="font-weight: 500; display:flex; align-items:center; gap:4px; margin-top:4px;">
                        <i class="ph ph-t-shirt text-muted"></i> <?= e($atleta['nombre_posicion'] ?? 'No definida') ?>
                    </div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600;">Edad</div>
                    <div style="font-weight: 500; display:flex; align-items:center; gap:4px; margin-top:4px;">
                        <i class="ph ph-calendar-blank text-muted"></i> 
                        <?php 
                            $nac = new DateTime($atleta['fecha_nacimiento']);
                            $hoy = new DateTime();
                            echo $hoy->diff($nac)->y . ' años';
                        ?>
                    </div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600;">Pierna Háb.</div>
                    <div style="font-weight: 500; display:flex; align-items:center; gap:4px; margin-top:4px;">
                        <i class="ph ph-sneaker text-muted"></i> <?= e(ucfirst($atleta['pierna_dominante'] ?? '—')) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-top:0; font-size: 16px; border-bottom: 1px solid var(--color-border); padding-bottom: 12px;"><i class="ph ph-phone-call"></i> Contacto</h3>
            <div style="margin-top: 16px;">
                <div style="display:flex; align-items:center; gap: 12px; margin-bottom: 12px;">
                    <div style="width:36px; height:36px; border-radius:8px; background:var(--color-bg-alt); display:flex; align-items:center; justify-content:center; color:var(--color-primary);"><i class="ph ph-whatsapp-logo" style="font-size:20px;"></i></div>
                    <div>
                        <div style="font-size: 12px; color: var(--color-text-muted);">Teléfono Personal</div>
                        <div style="font-weight: 500;"><?= e($atleta['telefono'] ?? 'No registrado') ?></div>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap: 12px;">
                    <div style="width:36px; height:36px; border-radius:8px; background:var(--color-bg-alt); display:flex; align-items:center; justify-content:center; color:var(--color-primary);"><i class="ph ph-map-pin" style="font-size:20px;"></i></div>
                    <div>
                        <div style="font-size: 12px; color: var(--color-text-muted);">Ubicación</div>
                        <div style="font-weight: 500; font-size: 13px; line-height: 1.3;">
                            <?= e($atleta['localidad'] ?? '') ?>, <?= e($atleta['municipio'] ?? '') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Derecho (Contenido Principal con Tabs) -->
    <div class="card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
        <div class="profile-tabs" style="display: flex; background: var(--color-bg-alt); border-bottom: 1px solid var(--color-border); padding: 0 24px; overflow-x: auto;">
            <button class="tab-btn active" data-target="tab-general"><i class="ph ph-user-list"></i> Datos Generales</button>
            <button class="tab-btn" data-target="tab-antropometria"><i class="ph ph-rulers"></i> Antropometría</button>
            <button class="tab-btn" data-target="tab-pruebas"><i class="ph ph-chart-line-up"></i> Pruebas Físicas</button>
            <button class="tab-btn" data-target="tab-medica"><i class="ph ph-heartbeat"></i> Historial Médico</button>
        </div>

        <div style="padding: 32px; flex: 1;">
            
            <!-- Tab: General -->
            <div id="tab-general" class="tab-content active">
                <h3 style="margin-top: 0;"><i class="ph ph-users"></i> Información del Representante Legal</h3>
                <?php if (!empty($atleta['tutor_nombres'] ?? $atleta['rep_nombre'])): ?>
                    <div style="background: var(--color-bg-alt); border-radius: var(--radius); padding: 24px; display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px;">
                        <div>
                            <div style="font-size: 12px; color: var(--color-text-muted);">Nombre Completo</div>
                            <div style="font-weight: 600; font-size: 16px; margin-top: 4px;"><?= e($atleta['rep_nombre'] ?? ($atleta['tutor_nombres'] . ' ' . $atleta['tutor_apellidos'])) ?></div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: var(--color-text-muted);">Parentesco</div>
                            <div style="font-weight: 500; margin-top: 4px; display: inline-block; padding: 2px 8px; background: var(--color-border); border-radius: 12px; font-size: 13px;"><?= e(ucfirst($atleta['rep_relacion'] ?? $atleta['tutor_relacion'] ?? 'No definido')) ?></div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: var(--color-text-muted);">Cédula de Identidad</div>
                            <div style="font-weight: 500; margin-top: 4px;"><i class="ph ph-identification-card text-muted"></i> <?= e($atleta['rep_cedula'] ?? $atleta['tutor_cedula'] ?? '—') ?></div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: var(--color-text-muted);">Teléfono de Contacto</div>
                            <div style="font-weight: 500; margin-top: 4px;"><i class="ph ph-phone text-muted"></i> <?= e($atleta['rep_telefono'] ?? $atleta['tutor_telefono'] ?? '—') ?></div>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="background: var(--color-bg-alt); border-radius: var(--radius); padding: 32px; text-align: center; margin-bottom: 32px;">
                        <i class="ph ph-user-circle-minus text-muted" style="font-size: 48px; opacity: 0.5;"></i>
                        <p class="text-muted" style="margin-top: 12px;">No hay representante registrado para este atleta.</p>
                    </div>
                <?php endif; ?>

                <h3 style="margin-top: 0;"><i class="ph ph-map-pin-line"></i> Dirección Detallada</h3>
                <div style="background: var(--color-bg-alt); border-radius: var(--radius); padding: 24px;">
                    <p style="margin: 0; line-height: 1.6;">
                        <strong>Estado:</strong> <?= e($atleta['estado'] ?? '—') ?> <br>
                        <strong>Municipio:</strong> <?= e($atleta['municipio'] ?? '—') ?> <br>
                        <strong>Parroquia:</strong> <?= e($atleta['parroquia'] ?? '—') ?> <br>
                        <strong>Localidad:</strong> <?= e($atleta['localidad'] ?? '—') ?> <br>
                        <strong>Vivienda:</strong> <?= e(ucfirst($atleta['tipo_vivienda'] ?? '—')) ?> - <?= e($atleta['ubicacion_vivienda'] ?? '—') ?>
                    </p>
                </div>
            </div>

            <!-- Tab: Antropometría -->
            <div id="tab-antropometria" class="tab-content" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <h3 style="margin: 0;">Evolución Física</h3>
                    <button class="btn btn-primary btn-sm"><i class="ph ph-plus"></i> Nueva Medición</button>
                </div>
                
                <!-- Mock Chart Container -->
                <div style="height: 300px; background: var(--color-bg-alt); border-radius: var(--radius); border: 1px solid var(--color-border); margin-bottom: 24px; position: relative;" id="chart-antropometria">
                    <!-- ECharts renders here -->
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Peso (kg)</th>
                            <th>Altura (m)</th>
                            <th>% Grasa</th>
                            <th>IMC</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Mocks para visualizar la UI -->
                        <tr>
                            <td>12/03/2026</td>
                            <td>68.5</td>
                            <td>1.75</td>
                            <td>12.4%</td>
                            <td><span class="badge badge-success">22.4 (Normal)</span></td>
                        </tr>
                        <tr>
                            <td>15/01/2026</td>
                            <td>67.2</td>
                            <td>1.74</td>
                            <td>13.1%</td>
                            <td><span class="badge badge-success">22.2 (Normal)</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Tab: Pruebas Físicas -->
            <div id="tab-pruebas" class="tab-content" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <h3 style="margin: 0;">Rendimiento Físico</h3>
                    <button class="btn btn-primary btn-sm"><i class="ph ph-plus"></i> Registrar Prueba</button>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
                    <div style="height: 350px; background: var(--color-bg-alt); border-radius: var(--radius); border: 1px solid var(--color-border);" id="chart-radar-pruebas"></div>
                    <div style="background: var(--color-bg-alt); border-radius: var(--radius); padding: 24px;">
                        <h4 style="margin-top: 0;">Última Evaluación</h4>
                        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 16px;">
                            <li>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 13px;"><span>Fuerza</span> <strong>85/100</strong></div>
                                <div style="height: 6px; background: var(--color-border); border-radius: 3px; overflow: hidden;"><div style="height: 100%; width: 85%; background: var(--color-primary);"></div></div>
                            </li>
                            <li>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 13px;"><span>Velocidad (30m)</span> <strong>4.2s</strong></div>
                                <div style="height: 6px; background: var(--color-border); border-radius: 3px; overflow: hidden;"><div style="height: 100%; width: 90%; background: #10B981;"></div></div>
                            </li>
                            <li>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 13px;"><span>Resistencia (Cooper)</span> <strong>2800m</strong></div>
                                <div style="height: 6px; background: var(--color-border); border-radius: 3px; overflow: hidden;"><div style="height: 100%; width: 75%; background: #F59E0B;"></div></div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Tab: Historial Médico -->
            <div id="tab-medica" class="tab-content" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <h3 style="margin: 0;">Ficha Médica Base</h3>
                    <button class="btn btn-outline btn-sm"><i class="ph ph-printer"></i> Imprimir Ficha</button>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px;">
                    <div style="background: #FEF2F2; border: 1px solid #FCA5A5; border-radius: var(--radius); padding: 20px;">
                        <div style="color: #DC2626; font-weight: 600; display: flex; align-items: center; gap: 8px; margin-bottom: 12px;"><i class="ph ph-drop"></i> Grupo Sanguíneo</div>
                        <div style="font-size: 24px; font-weight: 800; color: #991B1B;"><?= e($atleta['grupo_sanguineo'] ?? 'No especificado') ?></div>
                    </div>
                    <div style="background: #FFFBEB; border: 1px solid #FCD34D; border-radius: var(--radius); padding: 20px;">
                        <div style="color: #D97706; font-weight: 600; display: flex; align-items: center; gap: 8px; margin-bottom: 8px;"><i class="ph ph-warning"></i> Alergias Conocidas</div>
                        <div style="color: #92400E;"><?= !empty($atleta['alergias']) ? e($atleta['alergias']) : 'Ninguna registrada.' ?></div>
                    </div>
                </div>

                <div style="background: var(--color-bg-alt); border-radius: var(--radius); padding: 24px;">
                    <div style="margin-bottom: 20px;">
                        <h4 style="margin: 0 0 8px; color: var(--color-text); font-size: 14px;">Condiciones Crónicas</h4>
                        <p style="margin: 0; color: var(--color-text-muted);"><?= !empty($atleta['condicion_cronica']) ? e($atleta['condicion_cronica']) : 'Sin observaciones.' ?></p>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <h4 style="margin: 0 0 8px; color: var(--color-text); font-size: 14px;">Antecedentes Quirúrgicos</h4>
                        <p style="margin: 0; color: var(--color-text-muted);"><?= !empty($atleta['antecedentes_quirurgicos']) ? e($atleta['antecedentes_quirurgicos']) : 'Sin observaciones.' ?></p>
                    </div>
                    <div>
                        <h4 style="margin: 0 0 8px; color: var(--color-text); font-size: 14px;">Medicación Actual</h4>
                        <p style="margin: 0; color: var(--color-text-muted);"><?= !empty($atleta['medicacion_actual']) ? e($atleta['medicacion_actual']) : 'Ninguna medicación en curso.' ?></p>
                    </div>
                </div>

                <hr style="border:none; border-top:1px solid var(--color-border); margin: 32px 0;">
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h3 style="margin: 0;"><i class="ph ph-bandaids"></i> Historial de Lesiones y Atenciones</h3>
                    <?php if (can('admin') || can('medico')): ?>
                        <button class="btn btn-primary btn-sm"><i class="ph ph-plus"></i> Nueva Atención</button>
                    <?php endif; ?>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Diagnóstico</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" class="text-center text-muted" style="padding: 32px;">No hay atenciones médicas registradas para este atleta.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<style>
.profile-tabs .tab-btn {
    padding: 16px 24px;
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    color: var(--color-text-muted);
    font-weight: 500;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
    white-space: nowrap;
}
.profile-tabs .tab-btn:hover { color: var(--color-primary); }
.profile-tabs .tab-btn.active {
    color: var(--color-primary);
    border-bottom-color: var(--color-primary);
    font-weight: 600;
}

@media (max-width: 900px) {
    .show-layout { grid-template-columns: 1fr !important; }
}
@media print {
    .page-header .btn, .profile-tabs { display: none !important; }
    .card { box-shadow: none !important; border: 1px solid #ccc !important; }
    .tab-content { display: block !important; page-break-inside: avoid; margin-bottom: 30px; }
}
</style>

<!-- Inclusión de ECharts para gráficos -->
<script src="<?= e(url('/assets/js/lib/echarts.min.js')) ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Manejo de Pestañas
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.style.display = 'none');
            
            tab.classList.add('active');
            const targetId = tab.getAttribute('data-target');
            document.getElementById(targetId).style.display = 'block';

            // Redimensionar gráficos si están en la pestaña activa
            if(targetId === 'tab-antropometria' && chartAntro) {
                setTimeout(() => chartAntro.resize(), 50);
            }
            if(targetId === 'tab-pruebas' && chartRadar) {
                setTimeout(() => chartRadar.resize(), 50);
            }
        });
    });

    // 2. Gráfica Mock de Antropometría (Peso vs Altura)
    let chartAntro = null;
    const chartAntroDOM = document.getElementById('chart-antropometria');
    if (chartAntroDOM && typeof echarts !== 'undefined') {
        chartAntro = echarts.init(chartAntroDOM);
        const optionAntro = {
            tooltip: { trigger: 'axis' },
            legend: { data: ['Peso (kg)'], bottom: 0 },
            grid: { left: '3%', right: '4%', bottom: '15%', top: '10%', containLabel: true },
            xAxis: { type: 'category', boundaryGap: false, data: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'] },
            yAxis: { type: 'value', min: 60, max: 75 },
            series: [
                {
                    name: 'Peso (kg)',
                    type: 'line',
                    smooth: true,
                    lineStyle: { color: 'var(--color-primary)', width: 3 },
                    itemStyle: { color: 'var(--color-primary)' },
                    areaStyle: {
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                            { offset: 0, color: 'rgba(37, 99, 235, 0.3)' },
                            { offset: 1, color: 'rgba(37, 99, 235, 0)' }
                        ])
                    },
                    data: [66.5, 67.2, 67.8, 68.0, 68.3, 68.5]
                }
            ]
        };
        chartAntro.setOption(optionAntro);
    }

    // 3. Gráfica Mock Radar de Pruebas Físicas
    let chartRadar = null;
    const chartRadarDOM = document.getElementById('chart-radar-pruebas');
    if (chartRadarDOM && typeof echarts !== 'undefined') {
        chartRadar = echarts.init(chartRadarDOM);
        const optionRadar = {
            tooltip: {},
            radar: {
                indicator: [
                    { name: 'Fuerza', max: 100 },
                    { name: 'Velocidad', max: 100 },
                    { name: 'Resistencia', max: 100 },
                    { name: 'Coordinación', max: 100 },
                    { name: 'Reacción', max: 100 }
                ],
                radius: '65%',
                axisName: { color: 'var(--color-text-muted)' }
            },
            series: [{
                name: 'Rendimiento',
                type: 'radar',
                data: [
                    {
                        value: [85, 90, 75, 80, 88],
                        name: 'Evaluación Actual',
                        itemStyle: { color: 'var(--color-primary)' },
                        areaStyle: { color: 'rgba(37, 99, 235, 0.4)' }
                    }
                ]
            }]
        };
        chartRadar.setOption(optionRadar);
    }

    window.addEventListener('resize', () => {
        if(chartAntro) chartAntro.resize();
        if(chartRadar) chartRadar.resize();
    });
});
</script>
