<?php /** @var array $actividad @var array $detalles */ ?>
<div class="page-header">
    <div>
        <h1>Editar Pruebas Físicas</h1>
        <div class="subtitle">Modificando registro del <?= e(date('d/m/Y', strtotime($actividad['fecha']))) ?></div>
    </div>
    <div style="display: flex; gap: 12px; align-items: center;">
        <a href="<?= e(url('/admin/resultados-pruebas/sesion/' . $actividad['actividad_id'])) ?>" class="btn btn-ghost">
            <i class="ph ph-caret-left"></i> Volver al Detalle
        </a>
    </div>
</div>

<form method="POST" action="<?= e(url('/admin/resultados-pruebas/sesion/' . $actividad['actividad_id'] . '/editar')) ?>" id="form-edit-pruebas-fisicas" novalidate>
    <?= csrf_field() ?>

    <div class="card" style="margin-bottom: 24px; padding: 24px;">
        <!-- Fila 1: Campos requeridos y lectura -->
        <div class="form-header-grid-3">
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Categoría a la que pertenece esta sesión. No es modificable." data-tooltip-pos="top">Categoría Deportiva</label>
                <input type="text" class="form-control" value="<?= e($actividad['nombre_categoria'] ?? 'Sin categoría') ?>" disabled style="background: var(--color-bg-alt);">
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Fecha en la que se realizaron las pruebas físicas" data-tooltip-pos="top"><span class="required">*</span> Fecha del Evento</label>
                <input type="date" name="fecha_evento" class="form-control" required value="<?= e($actividad['fecha']) ?>" min="2019-01-01" max="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group form-header-toggle-group" style="margin: 0;">
                <button type="button" id="btn-toggle-options" class="btn btn-ghost" style="height: 44px; width: 44px; display: inline-flex; align-items: center; justify-content: center; border: 1px dashed var(--color-border);" data-tooltip="ver opciones extra" data-tooltip-pos="top">
                    <i class="ph ph-sliders-horizontal" style="font-size: 20px;"></i>
                </button>
            </div>
        </div>

        <!-- Fila 2: Opciones extras (colapsada por defecto) -->
        <div id="row-opciones-extra" class="form-extra-grid" style="display: none; margin-top: 24px; padding-top: 24px; border-top: 1px dashed var(--color-border);">
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Lugar donde se realizaron las pruebas" data-tooltip-pos="top">Ubicación</label>
                <input type="text" name="ubicacion" class="form-control" placeholder="Cancha Principal" value="<?= e($actividad['ubicacion'] ?? 'Cancha Principal') ?>">
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Terreno de juego donde se realiza la actividad">Terreno de Juego</label>
                <select name="terreno" class="form-control">
                    <option value="">— Seleccione —</option>
                    <?php foreach (TERRENO_TIPO as $k => $v): ?>
                        <option value="<?= $k ?>" <?= (isset($actividad['terreno']) && (int)$actividad['terreno'] === $k) ? 'selected' : '' ?>><?= e($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Estado del clima observado" data-tooltip-pos="top">Clima</label>
                <select name="clima" class="form-control">
                    <option value="">— Seleccione —</option>
                    <?php foreach (CLIMA_TIPO as $k => $v): ?>
                        <option value="<?= $k ?>" <?= (isset($actividad['clima']) && (int)$actividad['clima'] === $k) ? 'selected' : '' ?>><?= e($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Hora de inicio de las pruebas" data-tooltip-pos="top">Hora Inicio</label>
                <input type="time" name="hora_inicio" class="form-control" value="<?= e($actividad['hora_inicio'] ?? '') ?>">
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Hora de finalización de las pruebas" data-tooltip-pos="top">Hora Fin</label>
                <input type="time" name="hora_fin" class="form-control" value="<?= e($actividad['hora_fin'] ?? '') ?>">
            </div>
        </div>
    </div>

    <div class="card" style="padding: 0; overflow: hidden; max-width: 100%;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center; background: var(--color-surface-2);">
            <h3 style="margin:0; font-size: 16px;"><i class="ph ph-users-three"></i> Lista de Atletas</h3>
            <div style="font-size: 13px; font-weight: 600; color: var(--color-primary);">
                <?= count($detalles) ?> Atletas en esta categoría
            </div>
        </div>
        
        <div class="pruebas-container-wrap">
            <!-- Cabecera de Escritorio -->
            <div class="prueba-headers-desktop" style="display: flex; align-items: center; padding: 12px 20px; background: var(--color-bg-alt); border-bottom: 1px solid var(--color-border); font-size: 12px; font-weight: 600; color: var(--color-text-muted); position: sticky; top: 0; z-index: 10; gap: 16px;">
                <div style="width: 280px; flex-shrink: 0; display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px; text-align: center;">
                        <input type="checkbox" id="check-all" style="transform: scale(1.2); cursor: pointer;" title="Seleccionar todos">
                    </div>
                    <div>Atleta</div>
                </div>
                <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; flex: 1;">
                    <div data-tooltip="Salto vertical CMJ medido en centímetros (Rango: 1 - 100)" data-tooltip-pos="top">Fuerza (CMJ)</div>
                    <div data-tooltip="Distancia recorrida en el test de Yo-Yo medida en metros (Rango: 1 - 10000)" data-tooltip-pos="top">Resist. (Yo-Yo)</div>
                    <div data-tooltip="Tiempo transcurrido en sprint de 30 metros medido en segundos (Rango: 1.00 - 10.00)" data-tooltip-pos="top">Veloc. (30m)</div>
                    <div data-tooltip="Tiempo en circuito de agilidad con conos medido en segundos (Rango: 1 - 200)" data-tooltip-pos="top">Coord. (Conos)</div>
                    <div data-tooltip="Tiempo de reacción promedio medido en milisegundos (Rango: 10 - 2000)" data-tooltip-pos="top">Reacc. (Cognit.)</div>
                </div>
            </div>
            
            <div id="atletas-list-wrap">
                <?php foreach ($detalles as $d): 
                    $isDis = in_array((int)$d['atleta_estatus'], [0, 3], true);
                    $isSelected = !$isDis && ($d['test_id'] !== null);
                    
                    $disAttr = $isDis ? 'disabled' : '';
                    $rowStyle = $isDis ? 'style="opacity: 0.65; background: var(--color-bg-alt);"' : '';
                    
                    $fuerzaVal = $d['test_de_fuerza'] !== null ? (float)$d['test_de_fuerza'] : '';
                    $resistenciaVal = $d['test_resistencia'] !== null ? (int)$d['test_resistencia'] : '';
                    $velocidadVal = $d['test_velocidad'] !== null ? (float)$d['test_velocidad'] : '';
                    $coordinacionVal = $d['test_coordinacion'] !== null ? (float)$d['test_coordinacion'] : '';
                    $reaccionVal = $d['test_de_reaccion'] !== null ? (int)$d['test_de_reaccion'] : '';
                ?>
                    <div class="prueba-row" <?= $rowStyle ?>>
                        <div class="prueba-row__athlete">
                            <div style="width: 36px; display: flex; justify-content: center; align-items: center; flex-shrink: 0;">
                                <input type="checkbox" name="selected_atletas[]" value="<?= (int)$d['atleta_id'] ?>" class="atleta-checkbox" style="transform: scale(1.2); cursor: <?= $isDis ? 'not-allowed' : 'pointer' ?>;" <?= $disAttr ?> <?= $isSelected ? 'checked' : '' ?>>
                            </div>
                            <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0;">
                                <?php if (!empty($d['foto'])): ?>
                                    <div style="width: 36px; height: 36px; padding: 2px; border: 1px solid var(--color-border); border-radius: 50%; background: var(--color-bg); flex-shrink: 0;">
                                        <img src="<?= e(url($d['foto'])) ?>" class="avatar-thumb" alt="" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; display: block;">
                                    </div>
                                <?php else: ?>
                                    <div class="avatar-placeholder" style="width: 36px; height: 36px; border-radius: 50%; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 11px; border: 1px solid var(--color-primary-light); flex-shrink: 0;">
                                        <?= e(mb_substr($d['nombre'], 0, 1) . mb_substr($d['apellido'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <span class="prueba-row__name" style="min-width: 0;">
                                    <span style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><?= e($d['nombre'] . ' ' . $d['apellido']) ?></span>
                                    <?php if ((int)$d['atleta_estatus'] === 0): ?>
                                        <span class="badge badge-danger" style="font-size: 9px; padding: 2px 6px; margin-left: 6px; border-radius: 4px; font-weight: 600;">Suspendido</span>
                                    <?php elseif ((int)$d['atleta_estatus'] === 3): ?>
                                        <span class="badge badge-outline" style="font-size: 9px; padding: 2px 6px; margin-left: 6px; border-radius: 4px; font-weight: 600; border-color: var(--color-text-muted); color: var(--color-text-muted);">Inactivo</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="prueba-row__inputs">
                            <div class="prueba-input-group">
                                <span class="prueba-input-label">Fuerza (CMJ)</span>
                                <input type="number" name="test_de_fuerza[<?= (int)$d['atleta_id'] ?>]" class="form-control test-input" min="1" max="100" step="0.1" placeholder="cm" value="<?= $fuerzaVal ?>" <?= $disAttr ?>>
                            </div>
                            <div class="prueba-input-group">
                                <span class="prueba-input-label">Resist. (Yo-Yo)</span>
                                <input type="number" name="test_resistencia[<?= (int)$d['atleta_id'] ?>]" class="form-control test-input" min="1" max="10000" step="1" placeholder="m" value="<?= $resistenciaVal ?>" <?= $disAttr ?>>
                            </div>
                            <div class="prueba-input-group">
                                <span class="prueba-input-label">Veloc. (30m)</span>
                                <input type="number" name="test_velocidad[<?= (int)$d['atleta_id'] ?>]" class="form-control test-input" min="1" max="10" step="0.01" placeholder="seg" value="<?= $velocidadVal ?>" <?= $disAttr ?>>
                            </div>
                            <div class="prueba-input-group">
                                <span class="prueba-input-label">Coord. (Conos)</span>
                                <input type="number" name="test_coordinacion[<?= (int)$d['atleta_id'] ?>]" class="form-control test-input" min="1" max="200" step="0.1" placeholder="seg" value="<?= $coordinacionVal ?>" <?= $disAttr ?>>
                            </div>
                            <div class="prueba-input-group">
                                <span class="prueba-input-label">Reacc. (Cognit.)</span>
                                <input type="number" name="test_de_reaccion[<?= (int)$d['atleta_id'] ?>]" class="form-control test-input" min="10" max="2000" step="1" placeholder="ms" value="<?= $reaccionVal ?>" <?= $disAttr ?>>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div id="atletas-pagination" style="display: flex; justify-content: center; margin-top: 24px; padding-bottom: 24px;"></div>
        </div>
    </div>

    <div class="form-actions-btn-group" style="margin-top: 24px;">
        <a href="<?= e(url('/admin/resultados-pruebas/sesion/' . $actividad['actividad_id'])) ?>" class="btn btn-ghost">Cancelar</a>
        <button type="submit" class="btn btn-primary" id="btn-save" disabled>
            <i class="ph ph-floppy-disk"></i> Guardar Cambios
        </button>
    </div>
</form>

<style>
.test-input {
    height: 36px;
    padding: 4px 8px;
    font-size: 13px;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    border-radius: 8px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const $btnToggle = document.getElementById('btn-toggle-options');
    const $rowExtra = document.getElementById('row-opciones-extra');
    if ($btnToggle && $rowExtra) {
        $btnToggle.addEventListener('click', () => {
            const isHidden = $rowExtra.style.display === 'none';
            $rowExtra.style.display = isHidden ? 'grid' : 'none';
            $btnToggle.classList.toggle('active', isHidden);
            $btnToggle.setAttribute('data-tooltip', isHidden ? 'ocultar opciones extra' : 'ver opciones extra');
        });
        // Si hay datos opcionales ya definidos, desplegar automáticamente al cargar
        const hasExtraData = document.querySelector('[name="ubicacion"]').value !== 'Cancha Principal' ||
                             document.querySelector('[name="terreno"]').value !== '' ||
                             document.querySelector('[name="clima"]').value !== '' ||
                             document.querySelector('[name="hora_inicio"]').value !== '' ||
                             document.querySelector('[name="hora_fin"]').value !== '';
        if (hasExtraData) {
            $rowExtra.style.display = 'grid';
            $btnToggle.classList.add('active');
        }
    }

    const $listWrap = document.getElementById('atletas-list-wrap');
    const $checkAll = document.getElementById('check-all');
    const $btnSave = document.getElementById('btn-save');
    const checkboxes = $listWrap.querySelectorAll('.atleta-checkbox');

    function updateRowStates() {
        let checkedCount = 0;

        checkboxes.forEach(cb => {
            const row = cb.closest('.prueba-row');
            const inputs = row.querySelectorAll('.test-input');

            if (cb.checked) {
                checkedCount++;
                row.style.background = 'rgba(var(--color-primary-rgb, 190, 18, 60), 0.02)';
                inputs.forEach(input => input.disabled = false);
            } else {
                row.style.background = '';
                inputs.forEach(input => {
                    input.disabled = true;
                    input.value = '';
                    if (typeof FormValidator !== 'undefined' && FormValidator.clearMark) {
                        FormValidator.clearMark(input);
                    }
                });
            }
        });

        $btnSave.disabled = checkedCount === 0;
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            if (!cb.checked && $checkAll) {
                $checkAll.checked = false;
            }
            updateRowStates();
        });
    });

    if ($checkAll) {
        $checkAll.addEventListener('change', () => {
            checkboxes.forEach(cb => {
                if (!cb.disabled) {
                    cb.checked = $checkAll.checked;
                }
            });
            updateRowStates();
        });
    }

    // Inicializar estado del formulario
    updateRowStates();

    // Inicializar validador estándar
    FormValidator.init('#form-edit-pruebas-fisicas', {
        custom: function(form) {
            const hInicio = form.querySelector('[name="hora_inicio"]');
            const hFin = form.querySelector('[name="hora_fin"]');
            const checkedAthletes = form.querySelectorAll('.atleta-checkbox:checked');
            const errors = [];

            if (hInicio.value && hFin.value && hInicio.value >= hFin.value) {
                errors.push({
                    element: hInicio,
                    label: 'La hora de inicio debe ser menor a la hora de fin.'
                });
            }

            // Validar que para cada atleta seleccionado, se haya completado al menos un input
            checkedAthletes.forEach(cb => {
                const row = cb.closest('.prueba-row');
                const inputs = Array.from(row.querySelectorAll('.test-input'));
                const hasOneValue = inputs.some(i => i.value.trim() !== '');

                if (!hasOneValue) {
                    errors.push({
                        element: inputs[0],
                        label: `Debes ingresar al menos el resultado de una prueba para el atleta.`
                    });
                }
            });

            return errors;
        }
    });

    document.getElementById('form-edit-pruebas-fisicas').addEventListener('submit', function (e) {
        const btn = document.getElementById('btn-save');
        if (!btn.disabled && btn.innerHTML.indexOf('spinning') === -1) {
            btn.disabled = true;
            btn.innerHTML = '<i class="ph ph-spinner-gap spinning"></i> Guardando...';
        }
    });

    CadaPagination({
        rowSelector: '.prueba-row',
        containerId: 'atletas-pagination'
    });
});
</script>
