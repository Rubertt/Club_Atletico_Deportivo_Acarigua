<?php /** @var array $actividad @var array $detalles */ ?>
<div class="page-header">
    <div>
        <h1>Detalle de Asistencia</h1>
        <div class="subtitle">Sesión del <?= e(date('d/m/Y', strtotime($actividad['fecha']))) ?></div>
    </div>
    <div style="display: flex; gap: 12px;">
        <a href="<?= e(url('/admin/asistencias')) ?>" class="btn btn-ghost">
            <i class="ph ph-caret-left"></i> Volver al Listado
        </a>
        <?php if (can('admin')): ?>
            <a href="<?= e(url('/admin/asistencias/' . $actividad['actividad_id'] . '/editar')) ?>" class="btn btn-outline">
                <i class="ph ph-pencil-simple"></i> Editar Registro
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="asig-details-grid">
    <!-- Lista de Asistencia -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--color-border); background: var(--color-surface-2);">
            <h3 style="margin:0; font-size: 16px;"><i class="ph ph-users-three"></i> Lista de Atletas</h3>
        </div>
        <div class="data-table-wrap card" style="padding: 0; border: none; border-radius: 0; border-top: 1px solid var(--color-border);">
            <!-- Cabeceras en PC -->
            <div class="asig-headers-desktop" style="display: flex; align-items: center; gap: 16px; padding: 12px 24px; background: var(--color-bg-alt); border-bottom: 1px solid var(--color-border); position: sticky; top: 0; z-index: 10; font-size: 13px; font-weight: 600; color: var(--color-text-muted);">
                <div style="width: 320px; flex-shrink: 0; display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px;"></div>
                    <div>Atleta / Cédula</div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 16px; flex: 1;">
                    <div>Estado de Asistencia</div>
                    <div>Observaciones</div>
                </div>
                <div style="width: 140px; text-align: right; flex-shrink: 0; padding-right: 12px;">Acciones</div>
            </div>

            <!-- Lista de Atletas -->
            <div class="asistencia-detalles-list">
                <?php foreach ($detalles as $d): ?>
                    <div class="asig-atleta-row detalle-row">
                        <div class="asig-atleta-row__athlete">
                            <div class="avatar-placeholder" style="width: 36px; height: 36px; border-radius: 50%; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 11px; border: 1px solid var(--color-primary-light); flex-shrink: 0;">
                                <?= e(mb_substr($d['nombre'], 0, 1) . mb_substr($d['apellido'], 0, 1)) ?>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 4px; min-width: 0;">
                                <div class="asig-atleta-row__name" style="font-size: 14px;"><?= e($d['nombre'] . ' ' . $d['apellido']) ?></div>
                                <div style="font-size: 12px; color: var(--color-text-muted);">C.I: <?= e($d['cedula'] ?? '—') ?></div>
                            </div>
                        </div>

                        <div class="asig-atleta-row__inputs asig-atleta-row__inputs--asistencia">
                            <div class="asig-input-group">
                                <span class="asig-input-label">Estado de Asistencia</span>
                                <?php 
                                    $status = match ((int)$d['estatus']) { 1 => 'Presente', 2 => 'Justificado', default => 'Ausente' };
                                    $badge = match ((int)$d['estatus']) { 1 => 'success', 2 => 'warning', default => 'danger' };
                                ?>
                                <div>
                                    <span class="badge badge-<?= $badge ?>" style="font-weight: 600; text-transform: uppercase; font-size: 11px; display: inline-block; align-self: flex-start; padding: 4px 10px; border-radius: 12px;">
                                        <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:currentColor; margin-right:6px; vertical-align: middle;"></span>
                                        <?= e($status) ?>
                                    </span>
                                </div>
                            </div>

                            <div class="asig-input-group">
                                <span class="asig-input-label">Observaciones</span>
                                <span style="font-size: 13px; color: var(--color-text-muted);">
                                    <?= e($d['observaciones'] ?? '—') ?>
                                </span>
                            </div>
                        </div>

                        <div class="asig-atleta-row__actions">
                            <a href="<?= e(url('/admin/atletas/' . $d['atleta_id'])) ?>" class="btn btn-sm btn-ghost" title="Ver Perfil Atleta" style="display: inline-flex; align-items: center; gap: 6px;">
                                <i class="ph ph-user"></i> Ver Perfil
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div id="detalles-pagination" style="display: flex; justify-content: center; margin-top: 24px; margin-bottom: 24px;"></div>
    </div>

    <!-- Resumen de la Sesión -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <div class="card">
            <h3 style="margin-top: 0; font-size: 15px;"><i class="ph ph-info"></i> Información General</h3>
            <div style="display: flex; flex-direction: column; gap: 16px; margin-top: 20px;">
                <div>
                    <label style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--color-text-muted); margin-bottom: 4px;">Categoría</label>
                    <div style="font-weight: 600; color: var(--color-text);"><?= e($actividad['nombre_categoria'] ?? 'General') ?></div>
                </div>
                <div>
                    <label style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--color-text-muted); margin-bottom: 4px;">Tipo de Actividad</label>
                    <?php 
                        $tipoLabel = TIPO_ACTIVIDAD[(int)($actividad['tipo_actividad'] ?? 1)] ?? 'General';
                    ?>
                    <div style="font-weight: 600; color: var(--color-primary);"><?= e($tipoLabel) ?></div>
                </div>
                <div>
                    <label style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--color-text-muted); margin-bottom: 4px;">Enlistador</label>
                    <div style="font-weight: 500;"><?= e($actividad['entrenador'] ?? 'No definido') ?></div>
                </div>
                <div>
                    <label style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--color-text-muted); margin-bottom: 4px;">Fecha de Registro</label>
                    <div style="font-weight: 500;"><?= e(date('d/m/Y', strtotime($actividad['fecha']))) ?></div>
                </div>
                <div>
                    <label style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--color-text-muted); margin-bottom: 4px;">Ubicación</label>
                    <div style="font-weight: 500;"><?= e($actividad['ubicacion'] ?? '—') ?></div>
                </div>
                <?php if (isset($actividad['terreno']) && isset(TERRENO_TIPO[(int)$actividad['terreno']])): ?>
                <div>
                    <label style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--color-text-muted); margin-bottom: 4px;">Terreno de Juego</label>
                    <div style="font-weight: 500;"><?= e(TERRENO_TIPO[(int)$actividad['terreno']]) ?></div>
                </div>
                <?php endif; ?>
                <?php if (isset($actividad['clima']) && isset(CLIMA_TIPO[(int)$actividad['clima']])): ?>
                <div>
                    <label style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--color-text-muted); margin-bottom: 4px;">Clima</label>
                    <div style="font-weight: 500;"><?= e(CLIMA_TIPO[(int)$actividad['clima']]) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($actividad['hora_inicio']) || !empty($actividad['hora_fin'])): ?>
                <div>
                    <label style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--color-text-muted); margin-bottom: 4px;">Horario</label>
                    <div style="font-weight: 500;">
                        <?= e(date('h:i A', strtotime($actividad['hora_inicio'] ?? '00:00'))) ?> 
                        - <?= e(date('h:i A', strtotime($actividad['hora_fin'] ?? '00:00'))) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card" style="background: var(--color-primary); color: #fff;">
            <h3 style="margin-top: 0; font-size: 15px; color: #fff;">Estadísticas</h3>
            <?php 
                $total = count($detalles);
                $presentes = count(array_filter($detalles, fn($x) => (int)$x['estatus'] === 1));
                $porcentaje = $total > 0 ? round(($presentes / $total) * 100) : 0;
            ?>
            <div style="text-align: center; padding: 20px 0;">
                <div style="font-size: 48px; font-weight: 800;"><?= $porcentaje ?>%</div>
                <div style="font-size: 13px; opacity: 0.9;">Asistencia Total</div>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 13px; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 12px;">
                <span>Presentes: <strong><?= $presentes ?></strong></span>
                <span>Total: <strong><?= $total ?></strong></span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    CadaPagination({
        rowSelector: '.detalle-row',
        containerId: 'detalles-pagination'
    });
});
</script>
