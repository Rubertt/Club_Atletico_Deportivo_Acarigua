<?php /** @var array $actividad @var array $detalles */ ?>
<div class="page-header">
    <div>
        <h1>Asistencia de Partido</h1>
        <div class="subtitle">Registrar asistencia para el partido del <?= e(date('d/m/Y', strtotime($actividad['fecha']))) ?></div>
    </div>
    <a href="<?= e(url('/admin/convocatorias/' . $actividad['actividad_id'])) ?>" class="btn btn-ghost">
        <i class="ph ph-caret-left"></i> Volver al Detalle
    </a>
</div>

<form method="POST" action="<?= e(url('/admin/convocatorias/' . $actividad['actividad_id'] . '/editar')) ?>" id="form-edit-convocatoria" novalidate>
    <?= csrf_field() ?>

    <div class="card" style="margin-bottom: 24px; padding: 24px;">
        <!-- Fila 1: Campos requeridos y lectura -->
        <div class="form-header-grid">
            <div class="form-group" style="margin: 0;">
                <label class="form-label">Categoría Deportiva</label>
                <input type="text" class="form-control" value="<?= e($actividad['nombre_categoria'] ?? 'Sin categoría') ?>" disabled>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Fecha del partido" data-tooltip-pos="top"><span class="required">*</span> Fecha del Partido</label>
                <input type="date" name="fecha_evento" class="form-control" required value="<?= e($actividad['fecha']) ?>" 
                min="<?= date('Y-m-d', strtotime('+1 day')) ?>" max="<?= date('Y-m-d', strtotime('+3 months')) ?>">
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Escribe el nombre o apellido del atleta para buscar" data-tooltip-pos="top">Buscar Atleta</label>
                <input type="text" id="input-buscar" class="form-control" placeholder="Escribe nombre o apellido...">
            </div>
            <div class="form-group form-header-toggle-group" style="margin: 0;">
                <button type="button" id="btn-toggle-options" class="btn btn-ghost active" style="height: 44px; width: 44px; display: inline-flex; align-items: center; justify-content: center; border: 1px dashed var(--color-border);" data-tooltip="ocultar opciones extra" data-tooltip-pos="top">
                    <i class="ph ph-sliders-horizontal" style="font-size: 20px;"></i>
                </button>
            </div>
        </div>

        <!-- Fila 2: Opciones extras (desplegada por defecto) -->
        <div id="row-opciones-extra" class="form-extra-grid" style="display: grid; margin-top: 24px; padding-top: 24px; border-top: 1px dashed var(--color-border);">
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Estadio o cancha donde se juega el partido">Ubicación</label>
                <input type="text" name="ubicacion" class="form-control" placeholder="Cancha UPTP" value="<?= e($actividad['ubicacion'] ?? 'Cancha UPTP') ?>">
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Terreno de juego donde se realiza el partido">Terreno de Juego</label>
                <select name="terreno" class="form-control">
                    <option value="">— Seleccione —</option>
                    <?php foreach (TERRENO_TIPO as $k => $v): ?>
                        <option value="<?= $k ?>" <?= (isset($actividad['terreno']) && (int)$actividad['terreno'] === $k) ? 'selected' : '' ?>><?= e($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Estado del clima observado">Clima</label>
                <select name="clima" class="form-control">
                    <option value="">Selecciona...</option>
                    <?php foreach (CLIMA_TIPO as $k => $v): ?>
                        <option value="<?= $k ?>" <?= (isset($actividad['clima']) && (int)$actividad['clima'] === $k) ? 'selected' : '' ?>><?= e($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Hora de inicio del partido (obligatorio)"><span class="required">*</span>Hora Inicio</label>
                <input type="time" name="hora_inicio" class="form-control" value="<?= e($actividad['hora_inicio'] ?? '') ?>" required>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Hora de finalización del partido (obligatorio)"><span class="required">*</span>Hora Fin</label>
                <input type="time" name="hora_fin" class="form-control" value="<?= e($actividad['hora_fin'] ?? '') ?>" required>
            </div>
        </div>
    </div>

    <div class="card" style="padding: 0; overflow: hidden; max-width: 100%;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--color-border); background: var(--color-surface-2); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin:0; font-size: 16px;"><i class="ph ph-soccer-ball"></i> Control de Asistencia del Partido</h3>
            <span style="font-size: 13px; font-weight: 600; color: var(--color-text-muted);">
                (Solo los convocados pueden registrar asistencia)
            </span>
        </div>
        
        <div class="data-table-wrap card" style="padding: 0; border: none; border-radius: 0; border-top: 1px solid var(--color-border); margin: 0;">
            <!-- Cabeceras en PC -->
            <div class="asig-headers-desktop" style="display: flex; align-items: center; gap: 16px; padding: 12px 24px; background: var(--color-bg-alt); border-bottom: 1px solid var(--color-border); position: sticky; top: 0; z-index: 10; font-size: 13px; font-weight: 600; color: var(--color-text-muted);">
                <div style="width: 80px; flex-shrink: 0; text-align: center;">Dorsal</div>
                <div style="width: 44px; flex-shrink: 0; text-align: center;">Foto</div>
                <div style="width: 280px; flex-shrink: 0; display: flex; align-items: center;">Atleta / Cédula</div>
                <div style="width: 140px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <label class="switch" title="inactivo" data-tooltip="activo significa convocado, inactivo significa no convocado" data-tooltip-pos="top" style="position: relative; display: inline-block; width: 44px; height: 24px;">
                        <input type="checkbox" id="global-convocado-toggle" style="opacity: 0; width: 0; height: 0;">
                        <span class="slider"></span>
                    </label>
                    <span style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Todos</span>
                </div>
                <div style="flex: 1; text-align: center;">Asistencia</div>
            </div>
            
            <div id="atletas-list-wrap" style="overflow: hidden;">
                <?php foreach ($detalles as $d): ?>
                    <?php 
                    $originalEstatus = (int)$d['estatus'];
                    $isConvocado = ($originalEstatus === 1);
                    $originalAsistencia = $d['asistencia'] !== null ? (int)$d['asistencia'] : null;
                    $rowStyle = !$isConvocado ? 'style="opacity: 0.65; background: var(--color-bg-alt);"' : '';
                    ?>
                    <div class="asistencia-row detalle-row" <?= $rowStyle ?> data-atleta-id="<?= (int)$d['atleta_id'] ?>">
                        <!-- Columna 1: Dorsal -->
                        <div class="col-dorsal" style="width: 80px; flex-shrink: 0; display: flex; justify-content: center; align-items: center;">
                            <span class="asig-input-label">Dorsal</span>
                            <span class="badge badge-outline" style="font-size: 13px; font-weight: 700; padding: 4px 10px;">
                                <?= $d['nun_dorsal'] !== null ? '#' . (int)$d['nun_dorsal'] : 'S/D' ?>
                            </span>
                        </div>

                        <!-- Columna 2: Foto -->
                        <div class="col-foto" style="width: 44px; flex-shrink: 0; display: flex; justify-content: center; align-items: center;">
                            <?php if (!empty($d['foto'])): ?>
                                <div style="position: relative; width: 36px; height: 36px; padding: 2px; border: 1px solid var(--color-border); border-radius: 50%; background: var(--color-bg); flex-shrink: 0;">
                                    <img src="<?= e(url($d['foto'])) ?>" class="avatar-thumb" alt="" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; display: block;">
                                </div>
                            <?php else: ?>
                                <div class="avatar-placeholder" style="width: 36px; height: 36px; border-radius: 50%; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 11px; border: 1px solid var(--color-primary-light); flex-shrink: 0;">
                                    <?= e(mb_substr($d['nombre'], 0, 1) . mb_substr($d['apellido'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Columna 3: Nombre, apellido y cédula -->
                        <div class="col-info" style="width: 280px; flex-shrink: 0; display: flex; flex-direction: column; gap: 4px; min-width: 0; justify-content: center;">
                            <span class="asig-input-label">Atleta</span>
                            <div class="asig-atleta-row__name" style="font-size: 14px; font-weight: 600;"><?= e($d['nombre'] . ' ' . $d['apellido']) ?></div>
                            <div style="font-size: 12px; color: var(--color-text-muted);"><?= e($d['cedula'] ?? '—') ?></div>
                        </div>

                        <!-- Columna 4: Checkbox de Convocado / No Convocado -->
                        <div class="col-checkbox" style="width: 140px; flex-shrink: 0; display: flex; justify-content: center; align-items: center; gap: 8px;">
                            <span class="asig-input-label">Convocado</span>
                            <label class="switch" title="<?= $isConvocado ? 'activo' : 'inactivo' ?>" style="position: relative; display: inline-block; width: 44px; height: 24px;">
                                <input type="hidden" name="estatus[<?= (int)$d['atleta_id'] ?>]" value="2">
                                <input type="checkbox" class="convocado-checkbox" name="estatus[<?= (int)$d['atleta_id'] ?>]" value="1" <?= $isConvocado ? 'checked' : '' ?> style="opacity: 0; width: 0; height: 0;">
                                <span class="slider"></span>
                            </label>
                            <span class="convocado-label" style="font-size: 12px; font-weight: 600; min-width: 80px;"><?= $isConvocado ? 'Convocado' : 'No Convocado' ?></span>
                        </div>

                        <!-- Columna 5: Asistencia -->
                        <div class="col-asistencia" style="flex: 1; display: flex; justify-content: center; align-items: center;">
                            <div class="status-options" data-atleta="<?= (int)$d['atleta_id'] ?>">
                                <?php 
                                $currentAsistencia = ($originalAsistencia === null) ? 3 : $originalAsistencia; 
                                ?>
                                <input type="hidden" name="asistencia[<?= (int)$d['atleta_id'] ?>]" value="<?= $currentAsistencia ?>" class="status-val" <?= !$isConvocado ? 'disabled' : '' ?>>
                                <button type="button" class="status-btn btn-asistio <?= $currentAsistencia === 3 ? 'active' : '' ?>" data-val="3" data-tooltip="Asistió al partido" data-tooltip-pos="top" <?= !$isConvocado ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : '' ?>>Asistió</button>
                                <button type="button" class="status-btn btn-noasistio <?= $currentAsistencia === 4 ? 'active' : '' ?>" data-val="4" data-tooltip="No asistió al partido" data-tooltip-pos="top" <?= !$isConvocado ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : '' ?>>No Asistió</button>
                            </div>
                        </div>

                        <input type="hidden" name="atletas[]" value="<?= (int)$d['atleta_id'] ?>">
                    </div>
                <?php endforeach; ?>
            </div>
            <div id="atletas-pagination" style="display: flex; justify-content: center; margin-top: 24px; padding-bottom: 24px;"></div>
        </div>
    </div>

    <div class="form-actions-btn-group" style="margin-top: 24px;">
        <a href="<?= e(url('/admin/convocatorias/' . $actividad['actividad_id'])) ?>" class="btn btn-ghost">Cancelar</a>
        <button type="submit" class="btn btn-primary" id="btn-save">
            <i class="ph ph-floppy-disk"></i> Guardar Cambios
        </button>
    </div>
</form>

<style>
@media (min-width: 851px) {
    .asistencia-row {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 24px;
        border-bottom: 1px solid var(--color-border);
        transition: background 0.2s;
        background: var(--color-surface);
    }
    .asig-input-label {
        display: none;
    }
}
@media (max-width: 850px) {
    .asig-headers-desktop {
        display: none !important;
    }
    .asistencia-row {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
        padding: 16px;
        border-bottom: 1px solid var(--color-border);
        background: var(--color-surface);
    }
    .asistencia-row > div {
        width: 100% !important;
        display: flex;
        flex-direction: column;
        align-items: flex-start !important;
        justify-content: flex-start !important;
        gap: 4px;
    }
    .col-checkbox {
        flex-direction: row !important;
        align-items: center !important;
        gap: 12px !important;
    }
    .asig-input-label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        color: var(--color-text-muted);
        text-transform: uppercase;
    }
    .col-foto {
        display: none !important;
    }
}

.asistencia-row:hover { background: var(--color-bg-alt); }
.asistencia-row:last-child { border-bottom: 0; }

.status-options {
    display: flex;
    background: var(--color-surface-2);
    padding: 4px;
    border-radius: 8px;
    gap: 4px;
}
.status-btn {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    border: 0;
    background: transparent;
    color: var(--color-text-muted);
    transition: all 0.2s;
}
.status-btn.active[data-val="3"] { background: var(--color-success); color: #fff; }
.status-btn.active[data-val="4"] { background: var(--color-danger); color: #fff; }


</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle para opciones extra
    const $btnToggle = document.getElementById('btn-toggle-options');
    const $rowExtra = document.getElementById('row-opciones-extra');
    if ($btnToggle && $rowExtra) {
        $btnToggle.addEventListener('click', () => {
            const isHidden = $rowExtra.style.display === 'none';
            $rowExtra.style.display = isHidden ? 'grid' : 'none';
            $btnToggle.classList.toggle('active', isHidden);
            $btnToggle.setAttribute('data-tooltip', isHidden ? 'ocultar opciones extra' : 'ver opciones extra');
        });
    }

    // Toggle global para Convocado / No Convocado
    const globalToggle = document.getElementById('global-convocado-toggle');
    if (globalToggle) {
        globalToggle.addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.convocado-checkbox').forEach(cb => {
                if (cb.checked !== isChecked) {
                    cb.checked = isChecked;
                    cb.dispatchEvent(new Event('change'));
                }
            });
        });
    }

    // Toggle para los switches de Convocado/No Convocado
    document.querySelectorAll('.convocado-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const row = this.closest('.asistencia-row');
            const label = row.querySelector('.convocado-label');
            const asisValue = row.querySelector('.status-val');
            const buttons = row.querySelectorAll('.status-btn');
            const isChecked = this.checked;

            if (isChecked) {
                row.style.opacity = '1';
                row.style.background = '';
                label.textContent = 'Convocado';
                asisValue.disabled = false;
                buttons.forEach(btn => {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    btn.style.cursor = 'pointer';
                });
            } else {
                row.style.opacity = '0.65';
                row.style.background = 'var(--color-bg-alt)';
                label.textContent = 'No Convocado';
                asisValue.disabled = true;
                buttons.forEach(btn => {
                    btn.disabled = true;
                    btn.style.opacity = '0.5';
                    btn.style.cursor = 'not-allowed';
                });
            }
        });
    });

    // Control de los botones de asistencia
    document.querySelectorAll('.status-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.disabled) return;
            const wrap = this.parentElement;
            wrap.querySelectorAll('.status-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            wrap.querySelector('.status-val').value = parseInt(this.dataset.val);
        });
    });

    // Validación del horario al enviar
    document.getElementById('form-edit-convocatoria').addEventListener('submit', function(e) {
        const hInicio = document.querySelector('[name="hora_inicio"]').value;
        const hFin = document.querySelector('[name="hora_fin"]').value;
        if (hInicio && hFin && hInicio >= hFin) {
            e.preventDefault();
            CadaModal.alert({ title: 'Error en Horario', text: 'La hora de inicio debe ser menor a la hora de fin.', type: 'danger' });
            return;
        }

        const btn = document.getElementById('btn-save');
        btn.disabled = true;
        btn.innerHTML = '<i class="ph ph-spinner-gap spinning"></i> Guardando Asistencia...';
    });

    const $buscar = document.getElementById('input-buscar');
    if ($buscar) {
        $buscar.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            const rows = document.querySelectorAll('.detalle-row');
            const pagination = document.getElementById('atletas-pagination');

            if (!query) {
                if (pagination) pagination.style.display = 'flex';
                CadaPagination({
                    rowSelector: '.detalle-row',
                    containerId: 'atletas-pagination'
                });
                return;
            }

            if (pagination) pagination.style.display = 'none';
            rows.forEach(row => {
                const nameEl = row.querySelector('div[style*="font-weight: 600"]') || 
                               row.querySelector('div[style*="font-weight:600"]') ||
                               row.querySelector('.prueba-row__name') || 
                               row.querySelector('.asig-atleta-row__name');
                const text = nameEl ? nameEl.textContent : row.textContent;
                const normalizedText = text.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                if (normalizedText.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    CadaPagination({
        rowSelector: '.detalle-row',
        containerId: 'atletas-pagination'
    });
});
</script>
