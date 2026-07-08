<?php /** @var array $actividad @var array $detalles */ ?>
<div class="page-header">
    <div>
        <h1>Detalle de Pruebas Físicas</h1>
        <div class="subtitle">Sesión de evaluación realizada el <?= e(date('d/m/Y', strtotime($actividad['fecha']))) ?></div>
    </div>
    <div style="display: flex; gap: 12px; align-items: center;">
        <a href="<?= e(url('/admin/resultados-pruebas')) ?>" class="btn btn-ghost">
            <i class="ph ph-caret-left"></i> Volver al Listado
        </a>
        <a href="<?= e(url('/admin/resultados-pruebas/sesion/' . $actividad['actividad_id'] . '/imprimir')) ?>" class="btn btn-outline" target="_blank">
            <i class="ph ph-printer"></i> Imprimir PDF
        </a>
        <button id="btn-compartir-whatsapp" class="btn btn-outline" data-url="<?= e(url('/admin/resultados-pruebas/sesion/' . $actividad['actividad_id'] . '/imprimir')) ?>" data-filename="pruebas_fisicas_<?= preg_replace('/[^a-zA-Z0-9]/', '_', $actividad['nombre_categoria'] ?? 'sesion') ?>_<?= date('Ymd', strtotime($actividad['fecha'])) ?>.pdf" data-id="<?= (int)$actividad['actividad_id'] ?>">
            <i class="ph ph-share-network"></i> Compartir
        </button>
        <a href="<?= e(url('/admin/resultados-pruebas/sesion/' . $actividad['actividad_id'] . '/editar')) ?>" class="btn btn-primary">
            <i class="ph ph-pencil-simple"></i> Editar Sesión
        </a>
    </div>
</div>

<div class="card" style="margin-bottom: 24px; padding: 24px;">
    <h3 style="margin-top: 0; margin-bottom: 16px; font-size: 16px; border-bottom: 1px dashed var(--color-border); padding-bottom: 12px; color: var(--color-primary);">
        <i class="ph ph-info"></i> Información General
    </h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
        <div>
            <div style="font-size: 12px; color: var(--color-text-muted); font-weight: 500;">Categoría Deportiva</div>
            <div style="font-size: 15px; font-weight: 600; margin-top: 4px;">
                <i class="ph ph-users-three text-muted"></i> <?= e($actividad['nombre_categoria'] ?? 'Sin Categoría') ?>
            </div>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--color-text-muted); font-weight: 500;">Fecha de Evaluación</div>
            <div style="font-size: 15px; font-weight: 600; margin-top: 4px;">
                <i class="ph ph-calendar text-muted"></i> <?= e(date('d/m/Y', strtotime($actividad['fecha']))) ?>
            </div>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--color-text-muted); font-weight: 500;">Evaluador / Entrenador</div>
            <div style="font-size: 15px; font-weight: 600; margin-top: 4px;">
                <i class="ph ph-user-circle text-muted"></i> <?= e($actividad['entrenador'] ?? 'No definido') ?>
            </div>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--color-text-muted); font-weight: 500;">Ubicación</div>
            <div style="font-size: 15px; font-weight: 600; margin-top: 4px;">
                <i class="ph ph-map-pin text-muted"></i> <?= e($actividad['ubicacion'] ?? '—') ?>
            </div>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--color-text-muted); font-weight: 500;">Terreno de Juego</div>
            <div style="font-size: 15px; font-weight: 600; margin-top: 4px;">
                <i class="ph ph-soccer-ball text-muted"></i> 
                <?= isset($actividad['terreno']) ? e(TERRENO_TIPO[(int)$actividad['terreno']] ?? '—') : '—' ?>
            </div>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--color-text-muted); font-weight: 500;">Clima</div>
            <div style="font-size: 15px; font-weight: 600; margin-top: 4px;">
                <i class="ph ph-cloud-sun text-muted"></i> 
                <?= isset($actividad['clima']) ? e(CLIMA_TIPO[(int)$actividad['clima']] ?? '—') : '—' ?>
            </div>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--color-text-muted); font-weight: 500;">Horario</div>
            <div style="font-size: 15px; font-weight: 600; margin-top: 4px;">
                <i class="ph ph-clock text-muted"></i> 
                <?= (!empty($actividad['hora_inicio']) && !empty($actividad['hora_fin'])) ? e(date('h:i A', strtotime($actividad['hora_inicio'])) . ' - ' . date('h:i A', strtotime($actividad['hora_fin']))) : '—' ?>
            </div>
        </div>
    </div>
</div>
<style>
@media (min-width: 851px) {
    .prueba-row__inputs {
        grid-template-columns: repeat(6, 1fr) !important;
    }
}
@media (max-width: 850px) {
    .prueba-row__actions {
        width: 100% !important;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px dashed var(--color-border);
        display: flex;
        justify-content: flex-end;
    }
}
</style>
<div class="card" style="padding: 0; overflow: hidden; max-width: 100%;">
    <div style="padding: 20px 24px; border-bottom: 1px solid var(--color-border); background: var(--color-surface-2); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin:0; font-size: 16px;"><i class="ph ph-users-three"></i> Resultados de Pruebas Físicas</h3>
        <span class="badge badge-primary" style="font-size: 12px; font-weight: 600; padding: 4px 10px;">
            <?= count($detalles) ?> Evaluados
        </span>
    </div>
    
    <div class="data-table-wrap card" style="padding: 0; border: none; border-radius: 0; border-top: 1px solid var(--color-border);">
        <!-- Cabeceras en PC -->
        <div class="prueba-headers-desktop" style="display: flex; align-items: center; gap: 16px; padding: 12px 24px; background: var(--color-bg-alt); border-bottom: 1px solid var(--color-border); position: sticky; top: 0; z-index: 10; font-size: 13px; font-weight: 600; color: var(--color-text-muted);">
            <div style="width: 280px; flex-shrink: 0; display: flex; align-items: center; gap: 12px;">
                <div style="width: 36px;"></div>
                <div>Atleta / Documento ID</div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 16px; flex: 1;">
                <div>Fuerza (CMJ)</div>
                <div>Resist. (Yo-Yo)</div>
                <div>Veloc. (30m)</div>
                <div>Coord. (Conos)</div>
                <div>Reacc. (Cognit.)</div>
                <div style="font-weight: bold; color: var(--color-primary);">Promedio</div>
            </div>
            <div style="width: 140px; text-align: right; flex-shrink: 0; padding-right: 12px;">Acciones</div>
        </div>

        <!-- Resultados List -->
        <div class="prueba-detalles-list">
            <?php if (empty($detalles)): ?>
                <div style="padding: 64px 24px; text-align: center; background: var(--color-surface);">
                    <h3 class="text-muted" style="margin: 0 0 8px;">No hay resultados</h3>
                    <p class="text-muted" style="font-size: 14px; max-width: 400px; margin: 0 auto;">No hay resultados registrados en esta sesión.</p>
                </div>
            <?php else: foreach ($detalles as $d): ?>
                <div class="prueba-row">
                    <div class="prueba-row__athlete">
                        <?php if (!empty($d['foto'])): ?>
                            <div style="width: 36px; height: 36px; padding: 2px; border: 1px solid var(--color-border); border-radius: 50%; background: var(--color-bg); flex-shrink: 0;">
                                <img src="<?= e(url($d['foto'])) ?>" class="avatar-thumb" alt="" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; display: block;">
                            </div>
                        <?php else: ?>
                            <div class="avatar-placeholder" style="width: 36px; height: 36px; border-radius: 50%; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 11px; border: 1px solid var(--color-primary-light); flex-shrink: 0;">
                                <?= e(mb_substr($d['nombre'], 0, 1) . mb_substr($d['apellido'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <div style="display: flex; flex-direction: column; gap: 4px; min-width: 0;">
                            <div class="prueba-row__name" style="font-size: 14px;"><?= e($d['nombre'] . ' ' . $d['apellido']) ?></div>
                            <div style="font-size: 12px; color: var(--color-text-muted);"><?= e($d['cedula'] ?? '—') ?></div>
                        </div>
                    </div>

                    <div class="prueba-row__inputs">
                        <div class="prueba-input-group">
                            <span class="prueba-input-label">Fuerza (CMJ)</span>
                            <span style="font-weight: 600; font-size: 14px; color: var(--color-text);"><?= $d['test_de_fuerza'] !== null ? e(number_format((float)$d['test_de_fuerza'], 1)) . ' cm' : '<span class="text-muted">—</span>' ?></span>
                        </div>
                        <div class="prueba-input-group">
                            <span class="prueba-input-label">Resist. (Yo-Yo)</span>
                            <span style="font-weight: 600; font-size: 14px; color: var(--color-text);"><?= $d['test_resistencia'] !== null ? e((int)$d['test_resistencia']) . ' m' : '<span class="text-muted">—</span>' ?></span>
                        </div>
                        <div class="prueba-input-group">
                            <span class="prueba-input-label">Veloc. (30m)</span>
                            <span style="font-weight: 600; font-size: 14px; color: var(--color-text);"><?= $d['test_velocidad'] !== null ? e(number_format((float)$d['test_velocidad'], 2)) . ' seg' : '<span class="text-muted">—</span>' ?></span>
                        </div>
                        <div class="prueba-input-group">
                            <span class="prueba-input-label">Coord. (Conos)</span>
                            <span style="font-weight: 600; font-size: 14px; color: var(--color-text);"><?= $d['test_coordinacion'] !== null ? e(number_format((float)$d['test_coordinacion'], 1)) . ' seg' : '<span class="text-muted">—</span>' ?></span>
                        </div>
                        <div class="prueba-input-group">
                            <span class="prueba-input-label">Reacc. (Cognit.)</span>
                            <span style="font-weight: 600; font-size: 14px; color: var(--color-text);"><?= $d['test_de_reaccion'] !== null ? e((int)$d['test_de_reaccion']) . ' ms' : '<span class="text-muted">—</span>' ?></span>
                        </div>
                        <div class="prueba-input-group">
                            <span class="prueba-input-label" style="font-weight: bold; color: var(--color-primary);">Promedio</span>
                            <span style="font-weight: 700; font-size: 14px; color: var(--color-primary);"><?= $d['promedio'] !== null ? e(number_format((float)$d['promedio'], 1)) . ' pts' : '<span class="text-muted">—</span>' ?></span>
                        </div>
                    </div>

                    <div class="prueba-row__actions" style="width: 140px; flex-shrink: 0; display: flex; gap: 8px; justify-content: flex-end; align-items: center; padding-right: 12px;">
                        <a href="<?= e(url('/admin/atletas/' . $d['atleta_id'])) ?>?tab=tab-pruebas" class="btn btn-sm btn-ghost" title="Ver Perfil Atleta" style="display: inline-flex; align-items: center; gap: 6px;">
                            <i class="ph ph-user"></i> Ver Perfil
                        </a>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
        <div id="pruebas-pagination" style="display: flex; justify-content: center; margin-top: 24px; padding-bottom: 24px;"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    CadaPagination({
        rowSelector: '.prueba-row',
        containerId: 'pruebas-pagination'
    });

    // Lógica para compartir por WhatsApp / Web Share
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
                        title: 'Resultados de Pruebas Físicas CADA',
                        text: 'Reporte de Sesión de Pruebas Físicas'
                    });
                } else {
                    // Fallback a enlace universal de WhatsApp
                    const shareText = "Hola, te comparto los resultados de las pruebas físicas de CADA: " + encodeURIComponent(window.location.origin + pdfUrl);
                    const whatsappUrl = "https://api.whatsapp.com/send?text=" + shareText;
                    window.open(whatsappUrl, '_blank');
                }
            } catch (error) {
                console.error('Error al compartir:', error);
                // Fallback por enlace si hay algún fallo de red
                const shareText = "Hola, te comparto los resultados de las pruebas físicas de CADA: " + encodeURIComponent(window.location.origin + pdfUrl);
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
