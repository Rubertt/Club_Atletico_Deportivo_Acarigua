<?php /** @var array $categorias */ ?>
<div class="page-header">
    <div>
        <h1>Registrar Pruebas Físicas</h1>
        <div class="subtitle">Selecciona la categoría y registra los resultados de las pruebas físicas de hoy</div>
    </div>
    <div style="display: flex; gap: 12px; align-items: center;">
        <a href="<?= e(url('/admin/resultados-pruebas')) ?>" class="btn btn-ghost">
            <i class="ph ph-caret-left"></i> Directorio de Pruebas Físicas
        </a>
    </div>
</div>

<form method="POST" action="<?= e(url('/admin/resultados-pruebas/crear')) ?>" id="form-pruebas-fisicas" novalidate>
    <?= csrf_field() ?>

    <div class="card" style="margin-bottom: 24px; padding: 24px;">
        <!-- Fila 1: Campos requeridos -->
        <div class="form-header-grid-3">
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Selecciona la categoría de atletas a evaluar" data-tooltip-pos="top"><span class="required">*</span> Categoría Deportiva</label>
                <select id="sel-cat" name="categoria_id" class="form-control" required>
                    <option value="">— Seleccione —</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= (int) $c['categoria_id'] ?>" <?= (int)old('categoria_id') === (int)$c['categoria_id'] ? 'selected' : '' ?>><?= e($c['nombre_categoria']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Fecha en la que se realizaron las pruebas físicas" data-tooltip-pos="top"><span class="required">*</span> Fecha del Evento</label>
                <input type="date" name="fecha_evento" class="form-control" required value="<?= e(old('fecha_evento', date('Y-m-d'))) ?>" min="2019-01-01" max="<?= date('Y-m-d') ?>">
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
                <input type="text" name="ubicacion" class="form-control" placeholder="Cancha Principal" value="<?= e(old('ubicacion', 'Cancha Principal')) ?>">
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Terreno de juego donde se realiza la actividad" data-tooltip-pos="top">Terreno de Juego</label>
                <select name="terreno" class="form-control">
                    <option value="">— Seleccione —</option>
                    <?php foreach (TERRENO_TIPO as $k => $v): ?>
                        <option value="<?= $k ?>" <?= old('terreno') !== '' && (int)old('terreno') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Estado del clima observado" data-tooltip-pos="top">Clima</label>
                <select name="clima" class="form-control">
                    <option value="">— Seleccione —</option>
                    <?php foreach (CLIMA_TIPO as $k => $v): ?>
                        <option value="<?= $k ?>" <?= old('clima') !== '' && (int)old('clima') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Hora de inicio de las pruebas" data-tooltip-pos="top">Hora Inicio</label>
                <input type="time" name="hora_inicio" class="form-control" value="<?= e(old('hora_inicio')) ?>">
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Hora de finalización de las pruebas" data-tooltip-pos="top">Hora Fin</label>
                <input type="time" name="hora_fin" class="form-control" value="<?= e(old('hora_fin')) ?>">
            </div>
        </div>
    </div>

    <div id="atletas-container" style="display: none;">
        <div class="card" style="padding: 0; overflow: hidden; max-width: 100%;">
            <div style="padding: 20px 24px; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center; background: var(--color-surface-2);">
                <h3 style="margin:0; font-size: 16px;"><i class="ph ph-users-three"></i> Lista de Atletas</h3>
                <div id="stats-atletas" style="font-size: 13px; font-weight: 600; color: var(--color-primary);">
                    Cargando atletas...
                </div>
            </div>
            
            <div class="pruebas-container-wrap" style="max-height: 600px; overflow-y: auto;">
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
                
                <div id="atletas-list-wrap"></div>
            </div>
        </div>

        <div class="form-actions-btn-group" style="margin-top: 24px;">
            <a href="<?= e(url('/admin/resultados-pruebas')) ?>" class="btn btn-ghost">Cancelar</a>
            <button type="submit" class="btn btn-primary" id="btn-save" data-tooltip="Guardar todos los resultados registrados en la base de datos" data-tooltip-pos="top" disabled>
                <i class="ph ph-check-circle"></i> Guardar Resultados
            </button>
        </div>
    </div>

    <div id="no-atletas" class="card" style="display: none; text-align: center; padding: 48px;">
        <i class="ph ph-user-minus" style="font-size: 48px; opacity: 0.2;"></i>
        <p style="margin-top: 16px; color: var(--color-text-muted);">No hay atletas registrados y activos en esta categoría.</p>
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
(function () {
    const $cat = document.getElementById('sel-cat');
    const $container = document.getElementById('atletas-container');

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

    const $noAtletas = document.getElementById('no-atletas');
    const $listWrap = document.getElementById('atletas-list-wrap');
    const $stats = document.getElementById('stats-atletas');
    const $checkAll = document.getElementById('check-all');
    const $btnSave = document.getElementById('btn-save');

    // Carga de viejos datos (old) si existen
    const oldSelectedAtletas = <?= json_encode(old('selected_atletas') ?? []) ?>;
    const oldFuerza = <?= json_encode(old('test_de_fuerza') ?? []) ?>;
    const oldResistencia = <?= json_encode(old('test_resistencia') ?? []) ?>;
    const oldVelocidad = <?= json_encode(old('test_velocidad') ?? []) ?>;
    const oldCoordinacion = <?= json_encode(old('test_coordinacion') ?? []) ?>;
    const oldReaccion = <?= json_encode(old('test_de_reaccion') ?? []) ?>;

    const escapeHtml = (str) => String(str).replace(/"/g, '&quot;').replace(/'/g, '&#39;');

    function updateRowStates() {
        let checkedCount = 0;
        const checkboxes = $listWrap.querySelectorAll('.atleta-checkbox');

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

    $cat.addEventListener('change', async () => {
        const id = $cat.value;
        if (!id) {
            $container.style.display = 'none';
            $noAtletas.style.display = 'none';
            return;
        }

        try {
            $stats.textContent = 'Cargando...';
            // Reusamos el API endpoint de categorías de asistencias para traer a los atletas
            const atletas = await API.get(`<?= e(url('/api/asistencias/categoria')) ?>/${id}`);
            
            if (!atletas || !atletas.length) {
                $container.style.display = 'none';
                $noAtletas.style.display = 'block';
                return;
            }

            $noAtletas.style.display = 'none';
            $container.style.display = 'block';
            $stats.textContent = `${atletas.length} Atletas encontrados`;

            $listWrap.innerHTML = atletas.map(a => {
                const isDis = parseInt(a.atleta_estatus) === 0 || parseInt(a.atleta_estatus) === 3;
                const statusBadge = parseInt(a.atleta_estatus) === 0 
                    ? '<span class="badge badge-danger" style="font-size: 9px; padding: 2px 6px; margin-left: 6px; border-radius: 4px; font-weight: 600;">Suspendido</span>' 
                    : (parseInt(a.atleta_estatus) === 3 
                        ? '<span class="badge badge-outline" style="font-size: 9px; padding: 2px 6px; margin-left: 6px; border-radius: 4px; font-weight: 600; border-color: var(--color-text-muted); color: var(--color-text-muted);">Inactivo</span>' 
                        : '');
                const disAttr = isDis ? 'disabled' : '';
                const rowStyle = isDis ? 'style="opacity: 0.65; background: var(--color-bg-alt);"' : '';

                const athleteIdStr = String(a.atleta_id);
                const isSelected = !isDis && (oldSelectedAtletas.includes(athleteIdStr) || oldSelectedAtletas.includes(a.atleta_id));
                
                const currentFuerza = isSelected && oldFuerza[athleteIdStr] ? oldFuerza[athleteIdStr] : '';
                const currentResistencia = isSelected && oldResistencia[athleteIdStr] ? oldResistencia[athleteIdStr] : '';
                const currentVelocidad = isSelected && oldVelocidad[athleteIdStr] ? oldVelocidad[athleteIdStr] : '';
                const currentCoordinacion = isSelected && oldCoordinacion[athleteIdStr] ? oldCoordinacion[athleteIdStr] : '';
                const currentReaccion = isSelected && oldReaccion[athleteIdStr] ? oldReaccion[athleteIdStr] : '';

                const photoUrl = a.foto ? `<?= e(url('')) ?>/${a.foto}` : '';

                const avatarHtml = photoUrl 
                    ? `<div style="width: 36px; height: 36px; padding: 2px; border: 1px solid var(--color-border); border-radius: 50%; background: var(--color-bg); flex-shrink: 0;"><img src="${escapeHtml(photoUrl)}" class="avatar-thumb" alt="" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; display: block;"></div>`
                    : `<div class="avatar-placeholder" style="width: 36px; height: 36px; border-radius: 50%; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 11px; border: 1px solid var(--color-primary-light); flex-shrink: 0;">${a.nombre[0]}${a.apellido[0]}</div>`;

                return `
                <div class="prueba-row" ${rowStyle}>
                    <div class="prueba-row__athlete">
                        <div style="width: 36px; display: flex; justify-content: center; align-items: center; flex-shrink: 0;">
                            <input type="checkbox" name="selected_atletas[]" value="${a.atleta_id}" class="atleta-checkbox" style="transform: scale(1.2); cursor: ${isDis ? 'not-allowed' : 'pointer'};" ${disAttr} ${isSelected ? 'checked' : ''}>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0;">
                            ${avatarHtml}
                            <span class="prueba-row__name" style="min-width: 0;">
                                <span style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">${a.nombre} ${a.apellido}</span>
                                ${statusBadge}
                            </span>
                        </div>
                    </div>
                    
                    <div class="prueba-row__inputs">
                        <div class="prueba-input-group">
                            <span class="prueba-input-label">Fuerza (CMJ)</span>
                            <input type="number" name="test_de_fuerza[${a.atleta_id}]" class="form-control test-input" min="1" max="100" step="0.1" placeholder="cm" value="${escapeHtml(currentFuerza)}" disabled>
                        </div>
                        <div class="prueba-input-group">
                            <span class="prueba-input-label">Resist. (Yo-Yo)</span>
                            <input type="number" name="test_resistencia[${a.atleta_id}]" class="form-control test-input" min="1" max="10000" step="1" placeholder="m" value="${escapeHtml(currentResistencia)}" disabled>
                        </div>
                        <div class="prueba-input-group">
                            <span class="prueba-input-label">Veloc. (30m)</span>
                            <input type="number" name="test_velocidad[${a.atleta_id}]" class="form-control test-input" min="1" max="10" step="0.01" placeholder="seg" value="${escapeHtml(currentVelocidad)}" disabled>
                        </div>
                        <div class="prueba-input-group">
                            <span class="prueba-input-label">Coord. (Conos)</span>
                            <input type="number" name="test_coordinacion[${a.atleta_id}]" class="form-control test-input" min="1" max="200" step="0.1" placeholder="seg" value="${escapeHtml(currentCoordinacion)}" disabled>
                        </div>
                        <div class="prueba-input-group">
                            <span class="prueba-input-label">Reacc. (Cognit.)</span>
                            <input type="number" name="test_de_reaccion[${a.atleta_id}]" class="form-control test-input" min="10" max="2000" step="1" placeholder="ms" value="${escapeHtml(currentReaccion)}" disabled>
                        </div>
                    </div>
                </div>
                `;
            }).join('');

            // Agregar listeners a los checkboxes de atletas
            $listWrap.querySelectorAll('.atleta-checkbox').forEach(cb => {
                cb.addEventListener('change', () => {
                    if (!cb.checked && $checkAll) {
                        $checkAll.checked = false;
                    }
                    updateRowStates();
                });
            });

            // Disparar update inicial para habilitar campos de atletas pre-seleccionados por old()
            updateRowStates();

        } catch (e) {
            console.error(e);
            CadaModal.alert({ title: 'Error', text: 'No se pudo cargar la lista de atletas.', type: 'danger' });
        }
    });

    if ($checkAll) {
        $checkAll.addEventListener('change', () => {
            const checkboxes = $listWrap.querySelectorAll('.atleta-checkbox');
            checkboxes.forEach(cb => {
                if (!cb.disabled) {
                    cb.checked = $checkAll.checked;
                }
            });
            updateRowStates();
        });
    }

    // Inicializar validador estándar
    FormValidator.init('#form-pruebas-fisicas', {
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
                        element: inputs[0], // Apuntamos al primer input de la fila
                        label: `Debes ingresar al menos el resultado de una prueba para el atleta.`
                    });
                }
            });

            return errors;
        }
    });

    document.getElementById('form-pruebas-fisicas').addEventListener('submit', function (e) {
        // Spinner al enviar
        const btn = document.getElementById('btn-save');
        if (!btn.disabled && btn.innerHTML.indexOf('spinning') === -1) {
            btn.disabled = true;
            btn.innerHTML = '<i class="ph ph-spinner-gap spinning"></i> Guardando...';
        }
    });

    // Auto-disparar cambio si hay categoría por old()
    function autoTrigger() {
        if (typeof API !== 'undefined') {
            if ($cat.value) {
                $cat.dispatchEvent(new Event('change'));
            }
        } else {
            setTimeout(autoTrigger, 50);
        }
    }
    if (document.readyState === 'complete') {
        autoTrigger();
    } else {
        window.addEventListener('load', autoTrigger);
    }
})();
</script>
