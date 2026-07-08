<?php /** @var array $actividad @var array $detalles */ ?>
<style>
@media (min-width: 851px) {
    .asig-atleta-row__dorsal-wrap {
        width: 80px;
        flex-shrink: 0;
        display: flex;
        justify-content: center;
        align-items: center;
    }
}
@media (max-width: 850px) {
    .asig-atleta-row__dorsal-wrap {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 4px;
        align-items: flex-start;
    }
}
@media (min-width: 481px) {
    .convocatoria-detalles-inputs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        flex: 1;
    }
}
@media (max-width: 480px) {
    .convocatoria-detalles-inputs {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
        flex: 1;
        width: 100%;
    }
}
</style>
<div class="page-header">
    <div>
        <h1>Detalle de Convocatoria</h1>
        <div class="subtitle">Partido del <?= e(date('d/m/Y', strtotime($actividad['fecha']))) ?></div>
    </div>
    <div style="display: flex; gap: 12px;">
        <a href="<?= e(url('/admin/convocatorias')) ?>" class="btn btn-ghost">
            <i class="ph ph-caret-left"></i> Volver al Listado
        </a>
        <a href="<?= e(url('/admin/convocatorias/' . $actividad['actividad_id'] . '/imprimir')) ?>" class="btn btn-outline" target="_blank">
            <i class="ph ph-printer"></i> Imprimir PDF
        </a>
        <button id="btn-compartir-whatsapp" class="btn btn-outline" data-url="<?= e(url('/admin/convocatorias/' . $actividad['actividad_id'] . '/imprimir')) ?>" data-filename="convocatoria_<?= preg_replace('/[^a-zA-Z0-9]/', '_', $actividad['nombre_categoria'] ?? 'partido') ?>_<?= date('Ymd', strtotime($actividad['fecha'])) ?>.pdf" data-id="<?= (int)$actividad['actividad_id'] ?>">
            <i class="ph ph-share-network"></i> Compartir
        </button>
        <a href="<?= e(url('/admin/convocatorias/' . $actividad['actividad_id'] . '/editar')) ?>" class="btn btn-outline">
            <i class="ph ph-pencil-simple"></i> Pase de Asistencia
        </a>
    </div>
</div>

<div class="asig-details-grid">
    <!-- Lista de Convocatorias -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--color-border); background: var(--color-surface-2);">
            <h3 style="margin:0; font-size: 16px;"><i class="ph ph-users-three"></i> Jugadores y Convocados</h3>
        </div>
        <div class="data-table-wrap card" style="padding: 0; border: none; border-radius: 0; border-top: 1px solid var(--color-border);">
            <!-- Cabeceras en PC -->
            <div class="asig-headers-desktop" style="display: flex; align-items: center; gap: 16px; padding: 12px 24px; background: var(--color-bg-alt); border-bottom: 1px solid var(--color-border); position: sticky; top: 0; z-index: 10; font-size: 13px; font-weight: 600; color: var(--color-text-muted);">
                <div style="width: 80px; flex-shrink: 0; text-align: center;">Dorsal</div>
                <div style="width: 320px; flex-shrink: 0; display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px;"></div>
                    <div>Atleta / Documento ID</div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; flex: 1;">
                    <div>Estado de Convocatoria</div>
                    <div>Asistencia</div>
                </div>
                <div style="width: 140px; text-align: right; flex-shrink: 0; padding-right: 12px;">Acciones</div>
            </div>

            <!-- Lista de Atletas -->
            <div class="asistencia-detalles-list">
                <?php foreach ($detalles as $d): ?>
                    <?php 
                        $originalEstatus = (int)$d['estatus'];
                        $rowStyle = ($originalEstatus === 2) ? 'style="opacity: 0.65; background: var(--color-bg-alt);"' : '';
                    ?>
                    <div class="asig-atleta-row detalle-row" <?= $rowStyle ?>>
                        <!-- Dorsal -->
                        <div class="asig-atleta-row__dorsal-wrap">
                            <span class="asig-input-label">Dorsal</span>
                            <span class="badge badge-outline" style="font-size: 13px; font-weight: 700; padding: 4px 10px;">
                                <?= $d['nun_dorsal'] !== null ? '#' . (int)$d['nun_dorsal'] : 'S/D' ?>
                            </span>
                        </div>

                        <div class="asig-atleta-row__athlete">
                            <div class="avatar-placeholder" style="width: 36px; height: 36px; border-radius: 50%; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 11px; border: 1px solid var(--color-primary-light); flex-shrink: 0;">
                                <?= e(mb_substr($d['nombre'], 0, 1) . mb_substr($d['apellido'], 0, 1)) ?>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 4px; min-width: 0;">
                                <div class="asig-atleta-row__name" style="font-size: 14px; font-weight: 600;"><?= e($d['nombre'] . ' ' . $d['apellido']) ?></div>
                                <div style="font-size: 12px; color: var(--color-text-muted);"><?= e($d['cedula'] ?? '—') ?></div>
                            </div>
                        </div>

                        <div class="asig-atleta-row__inputs convocatoria-detalles-inputs">
                            <!-- Columna 1: Estado de Convocatoria -->
                            <div class="asig-input-group">
                                <span class="asig-input-label">Estado de Convocatoria</span>
                                <?php 
                                    if ($originalEstatus === 1) {
                                        $statusText = 'Convocado';
                                        $badge = 'primary';
                                        $dotColor = 'var(--color-primary)';
                                    } else {
                                        $statusText = 'No Convocado';
                                        $badge = 'outline';
                                        $dotColor = 'var(--color-text-muted)';
                                    }
                                ?>
                                <div>
                                    <span class="badge badge-<?= $badge ?>" style="font-weight: 600; text-transform: uppercase; font-size: 11px; display: inline-block; align-self: flex-start; padding: 4px 10px; border-radius: 12px; <?= $originalEstatus === 2 ? 'border-color: var(--color-border); color: var(--color-text-muted);' : '' ?>">
                                        <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:<?= $dotColor ?>; margin-right:6px; vertical-align: middle;"></span>
                                        <?= e($statusText) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Columna 2: Asistencia -->
                            <div class="asig-input-group">
                                <span class="asig-input-label">Asistencia</span>
                                <?php 
                                    if ($originalEstatus === 2) {
                                        $asisText = 'No aplica';
                                        $asisBadge = 'outline';
                                        $asisDotColor = 'var(--color-text-muted)';
                                        $asisExtraStyle = 'border-color: var(--color-border); color: var(--color-text-muted);';
                                    } else {
                                        $originalAsistencia = $d['asistencia'] !== null ? (int)$d['asistencia'] : null;
                                        $asisExtraStyle = '';
                                        if ($originalAsistencia === 3) {
                                            $asisText = 'Asistió';
                                            $asisBadge = 'success';
                                            $asisDotColor = 'var(--color-success)';
                                        } elseif ($originalAsistencia === 4) {
                                            $asisText = 'No Asistió';
                                            $asisBadge = 'danger';
                                            $asisDotColor = 'var(--color-danger)';
                                        } else {
                                            $asisText = 'Pendiente';
                                            $asisBadge = 'outline';
                                            $asisDotColor = 'var(--color-text-muted)';
                                            $asisExtraStyle = 'border-color: var(--color-border); color: var(--color-text-muted);';
                                        }
                                    }
                                ?>
                                <div>
                                    <span class="badge badge-<?= $asisBadge ?>" style="font-weight: 600; text-transform: uppercase; font-size: 11px; display: inline-block; align-self: flex-start; padding: 4px 10px; border-radius: 12px; <?= $asisExtraStyle ?>">
                                        <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:<?= $asisDotColor ?>; margin-right:6px; vertical-align: middle;"></span>
                                        <?= e($asisText) ?>
                                    </span>
                                </div>
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
                    <div style="font-weight: 600; color: var(--color-primary);">Partido</div>
                </div>
                <div>
                    <label style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--color-text-muted); margin-bottom: 4px;">Enlistador</label>
                    <div style="font-weight: 500;"><?= e($actividad['entrenador'] ?? 'No definido') ?></div>
                </div>
                <div>
                    <label style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--color-text-muted); margin-bottom: 4px;">Fecha del Partido</label>
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
            <h3 style="margin-top: 0; font-size: 15px; color: #fff;">Resumen</h3>
            <?php 
                $totalJugadores = count($detalles);
                $convocados = count(array_filter($detalles, fn($x) => (int)$x['estatus'] === 1));
                $asistieron = count(array_filter($detalles, fn($x) => (int)$x['asistencia'] === 3));
                $finalizado = ((int)$actividad['estatus'] === 2 || count(array_filter($detalles, fn($x) => in_array((int)$x['asistencia'], [3, 4]))) > 0);
            ?>
            <div style="text-align: center; padding: 20px 0;">
                <div style="font-size: 48px; font-weight: 800;">
                    <?php if ($finalizado): ?>
                        <?= $convocados > 0 ? round(($asistieron / $convocados) * 100) : 0 ?>%
                    <?php else: ?>
                        <?= $convocados ?>
                    <?php endif; ?>
                </div>
                <div style="font-size: 13px; opacity: 0.9;">
                    <?= $finalizado ? 'Asistencia de Convocados' : 'Jugadores Convocados' ?>
                </div>
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px; font-size: 13px; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 12px;">
                <div style="display: flex; justify-content: space-between;">
                    <span>Convocados:</span>
                    <strong><?= $convocados ?></strong>
                </div>
                <?php if ($finalizado): ?>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Asistieron:</span>
                        <strong><?= $asistieron ?></strong>
                    </div>
                <?php endif; ?>
                <div style="display: flex; justify-content: space-between;">
                    <span>No Convocados:</span>
                    <strong><?= $totalJugadores - $convocados ?></strong>
                </div>
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

    // Lógica para compartir por WhatsApp
    const shareBtn = document.getElementById('btn-compartir-whatsapp');
    if (shareBtn) {
        shareBtn.addEventListener('click', async function () {
            const pdfUrl = this.getAttribute('data-url');
            const filename = this.getAttribute('data-filename');
            const originalText = this.innerHTML;
            
            this.disabled = true;
            this.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Preparando...';
            
            try {
                // Descargar el PDF en memoria
                const response = await fetch(pdfUrl);
                if (!response.ok) throw new Error('No se pudo descargar el reporte PDF.');
                
                const blob = await response.blob();
                const file = new File([blob], filename, { type: 'application/pdf' });
                
                // Si la API nativa de compartir es soportada
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    await navigator.share({
                        files: [file],
                        title: 'Convocatoria CADA',
                        text: 'Reporte de Convocatoria y Asistencia'
                    });
                } else {
                    // Fallback a enlace universal de WhatsApp
                    const shareText = "Hola, te comparto el reporte de la convocatoria de CADA: " + encodeURIComponent(window.location.origin + pdfUrl);
                    const whatsappUrl = "https://api.whatsapp.com/send?text=" + shareText;
                    window.open(whatsappUrl, '_blank');
                }
            } catch (error) {
                console.error('Error al compartir:', error);
                // Fallback por enlace si hay algún fallo de red
                const shareText = "Hola, te comparto el reporte de la convocatoria de CADA: " + encodeURIComponent(window.location.origin + pdfUrl);
                const whatsappUrl = "https://api.whatsapp.com/send?text=" + shareText;
                window.open(whatsappUrl, '_blank');
            } finally {
                this.disabled = false;
                this.innerHTML = originalText;
            }
        });
    }
});
</script>
