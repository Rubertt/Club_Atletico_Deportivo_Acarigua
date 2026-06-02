<?php /** @var array $categorias @var array $entrenadores */ ?>
<div class="page-header">
    <div>
        <h1>Registrar Asistencia</h1>
        <div class="subtitle">Selecciona la categoría y registra la asistencia de hoy</div>
    </div>
    <div style="display: flex; gap: 12px; align-items: center;">
        <a href="<?= e(url('/admin/asistencias')) ?>" class="btn btn-ghost">
            <i class="ph ph-caret-left"></i> Directorio de Asistencias
        </a>
    </div>
</div>

<form method="POST" action="<?= e(url('/admin/asistencias/crear')) ?>" id="form-asistencia" novalidate>
    <?= csrf_field() ?>

    <div class="card" style="margin-bottom: 24px; padding: 24px;">
        <!-- Fila 1: Campos requeridos -->
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 24px; align-items: end;">
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
                <label class="form-label" data-tooltip="Fecha en la que se realizó la actividad" data-tooltip-pos="top"><span class="required">*</span> Fecha del Evento</label>
                <input type="date" name="fecha_evento" class="form-control" required value="<?= e(old('fecha_evento', date('Y-m-d'))) ?>" min="2019-01-01" max="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Tipo de actividad: Entrenamiento, Partido, etc." data-tooltip-pos="top"><span class="required">*</span> Tipo de Actividad</label>
                <select name="tipo_evento" class="form-control" required>
                    <option value="">— Seleccione —</option>
                    <?php foreach (TIPO_EVENTO as $op): ?>
                        <option value="<?= e($op) ?>" <?= old('tipo_evento') === $op ? 'selected' : '' ?>><?= e($op) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin: 0;">
                <button type="button" id="btn-toggle-options" class="btn btn-ghost" style="height: 44px; width: 44px; display: inline-flex; align-items: center; justify-content: center; border: 1px dashed var(--color-border);" data-tooltip="ver opciones extra" data-tooltip-pos="top">
                    <i class="ph ph-sliders-horizontal" style="font-size: 20px;"></i>
                </button>
            </div>
        </div>

        <!-- Fila 2: Opciones extras (colapsada por defecto) -->
        <div id="row-opciones-extra" style="display: none; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 24px; margin-top: 24px; padding-top: 24px; border-top: 1px dashed var(--color-border);">
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Lugar donde se lleva a cabo el evento" data-tooltip-pos="top">Ubicación</label>
                <input type="text" name="ubicacion" class="form-control" placeholder="Cancha UPTP" value="<?= e(old('ubicacion', 'Cancha UPTP')) ?>">
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
                <label class="form-label" data-tooltip="Hora de inicio de la sesión" data-tooltip-pos="top">Hora Inicio</label>
                <input type="time" name="hora_inicio" class="form-control" value="<?= e(old('hora_inicio')) ?>">
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Hora de finalización de la sesión" data-tooltip-pos="top">Hora Fin</label>
                <input type="time" name="hora_fin" class="form-control" value="<?= e(old('hora_fin')) ?>">
            </div>
        </div>
    </div>

    <div id="atletas-container" style="display: none;">
        <div class="card" style="padding: 0; overflow: hidden; max-width: 100%;">
            <div style="padding: 20px 24px; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center; background: var(--color-surface-2);">
                <h3 style="margin:0; font-size: 16px;"><i class="ph ph-users-three"></i> Lista de Atletas</h3>
                <div id="stats-asistencia" style="font-size: 13px; font-weight: 600; color: var(--color-primary);">
                    Cargando atletas...
                </div>
            </div>
            <div id="atletas-list-wrap" style="overflow: hidden;"></div>
        </div>

        <div style="display: flex; justify-content: flex-end; align-items: center; margin-top: 24px; gap: 12px;">
            <button type="reset" class="btn btn-ghost" data-tooltip="Restablecer el formulario" data-tooltip-pos="top">Cancelar</button>
            <button type="submit" class="btn btn-primary btn-lg" id="btn-save" data-tooltip="Guardar todos los registros de asistencia en la base de datos" data-tooltip-pos="top" style="padding: 12px 32px;">
                <i class="ph ph-check-circle"></i> Guardar Asistencia
            </button>
            <button type="button" class="btn-help" id="btn-help-asistencia" data-tooltip="Ver guía de ayuda con imágenes" data-tooltip-pos="top" title="¿Cómo registrar asistencia?" style="width: 44px; height: 44px;">
                <i class="ph ph-question"></i>
            </button>
        </div>
    </div>

    <div id="no-atletas" class="card" style="display: none; text-align: center; padding: 48px;">
        <i class="ph ph-user-minus" style="font-size: 48px; opacity: 0.2;"></i>
        <p style="margin-top: 16px; color: var(--color-text-muted);">No hay atletas registrados en esta categoría.</p>
    </div>
</form>

<style>
.asistencia-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto auto;
    gap: 16px;
    padding: 16px 20px;
    border-bottom: 1px solid var(--color-border);
    align-items: center;
    transition: background 0.2s;
    max-width: 100%;
    box-sizing: border-box;
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
.status-btn.active[data-val="Presente"] { background: var(--color-success); color: #fff; }
.status-btn.active[data-val="Ausente"] { background: var(--color-danger); color: #fff; }
.status-btn.active[data-val="Justificado"] { background: var(--color-warning); color: #fff; }

.obs-input {
    width: 180px;
    min-width: 120px;
    max-width: 250px;
    border-radius: 8px;
    font-size: 13px;
    box-sizing: border-box;
}

/* —— Estilos de Tooltip [data-tooltip] Scoped a la Vista ————————————————— */
[data-tooltip] {
    position: relative;
    cursor: pointer;
}
[data-tooltip]::before,
[data-tooltip]::after {
    position: absolute;
    opacity: 0;
    pointer-events: none;
    transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 999999;
}
[data-tooltip]::before {
    content: attr(data-tooltip);
    background: var(--color-surface-2, #1f2937);
    color: var(--color-text, #f3f4f6);
    font-size: 11px;
    font-weight: 500;
    padding: 6px 12px;
    border-radius: 6px;
    white-space: nowrap;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.45);
    border: 1px solid var(--color-border, rgba(255, 255, 255, 0.08));
}
[data-tooltip]::after {
    content: '';
    border: 5px solid transparent;
}

/* Tooltip Posición Superior (Predeterminado) */
[data-tooltip]:not([data-tooltip-pos])::before,
[data-tooltip][data-tooltip-pos="top"]::before {
    bottom: 100%;
    left: 50%;
    transform: translate(-50%, 8px);
}
[data-tooltip]:not([data-tooltip-pos])::after,
[data-tooltip][data-tooltip-pos="top"]::after {
    bottom: 100%;
    left: 50%;
    transform: translate(-50%, 8px);
    border-top-color: var(--color-surface-2, #1f2937);
    margin-bottom: -10px;
}
[data-tooltip]:not([data-tooltip-pos]):hover::before,
[data-tooltip]:not([data-tooltip-pos]):hover::after,
[data-tooltip][data-tooltip-pos="top"]:hover::before,
[data-tooltip][data-tooltip-pos="top"]:hover::after {
    opacity: 1;
    transform: translate(-50%, -6px);
}

/* Tooltip Posición Inferior */
[data-tooltip][data-tooltip-pos="bottom"]::before {
    top: 100%;
    left: 50%;
    transform: translate(-50%, -8px);
}
[data-tooltip][data-tooltip-pos="bottom"]::after {
    top: 100%;
    left: 50%;
    transform: translate(-50%, -8px);
    border-bottom-color: var(--color-surface-2, #1f2937);
    margin-top: -10px;
}
[data-tooltip][data-tooltip-pos="bottom"]:hover::before,
[data-tooltip][data-tooltip-pos="bottom"]:hover::after {
    opacity: 1;
    transform: translate(-50%, 6px);
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
    const $stats = document.getElementById('stats-asistencia');

    const oldAtletas = <?= json_encode(old('atletas') ?? []) ?>;
    const oldEstatus = <?= json_encode(old('estatus') ?? []) ?>;
    const oldObservaciones = <?= json_encode(old('observaciones') ?? []) ?>;

    const escapeHtml = (str) => String(str).replace(/"/g, '&quot;').replace(/'/g, '&#39;');

    $cat.addEventListener('change', async () => {
        const id = $cat.value;
        if (!id) {
            $container.style.display = 'none';
            $noAtletas.style.display = 'none';
            return;
        }

        try {
            $stats.textContent = 'Cargando...';
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
                const disCursor = isDis ? 'style="cursor: not-allowed;"' : '';
                const rowStyle = isDis ? 'style="opacity: 0.65; background: var(--color-bg-alt);"' : '';

                const athleteIdStr = String(a.atleta_id);
                const isOld = oldAtletas.includes(athleteIdStr) || oldAtletas.includes(a.atleta_id);
                const defaultStatus = isDis ? 'Ausente' : 'Presente';
                const currentStatus = isOld && oldEstatus[athleteIdStr] ? oldEstatus[athleteIdStr] : defaultStatus;
                const currentObs = isOld && oldObservaciones[athleteIdStr] ? oldObservaciones[athleteIdStr] : '';

                return `
                <div class="asistencia-row" ${rowStyle}>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;">
                            ${a.nombre[0]}${a.apellido[0]}
                        </div>
                        <div>
                            <div style="font-weight: 600; color: var(--color-text); display: flex; align-items: center;">
                                ${a.nombre} ${a.apellido}
                                ${statusBadge}
                            </div>
                        </div>
                    </div>
                    
                    <div class="status-options" data-atleta="${a.atleta_id}" style="${isDis ? 'cursor: not-allowed; opacity: 0.7;' : ''}">
                        <input type="hidden" name="estatus[${a.atleta_id}]" value="${currentStatus}" class="status-val" ${disAttr}>
                        <button type="button" class="status-btn ${!isDis && currentStatus === 'Presente' ? 'active' : ''}" data-val="Presente" data-tooltip="Asistió a la actividad" data-tooltip-pos="top" ${disAttr} ${disCursor}>Presente</button>
                        <button type="button" class="status-btn ${isDis || currentStatus === 'Ausente' ? 'active' : ''}" data-val="Ausente" data-tooltip="No asistió a la actividad" data-tooltip-pos="top" ${disAttr} ${disCursor}>Ausente</button>
                        <button type="button" class="status-btn ${!isDis && currentStatus === 'Justificado' ? 'active' : ''}" data-val="Justificado" data-tooltip="Inasistencia justificada (ej. lesión, permiso)" data-tooltip-pos="top" ${disAttr} ${disCursor}>Justificado</button>
                    </div>

                    <div>
                        <input type="text" name="observaciones[${a.atleta_id}]" class="form-control obs-input" placeholder="${isDis ? 'No disponible' : 'Observación opcional...'}" value="${escapeHtml(currentObs)}" data-tooltip="Indique cualquier observación relevante sobre el atleta" data-tooltip-pos="top" ${disAttr} ${disCursor}>
                    </div>
                    
                    <input type="hidden" name="atletas[]" value="${a.atleta_id}" ${disAttr}>
                </div>
                `;
            }).join('');

            // Lógica de botones de estado
            $listWrap.querySelectorAll('.status-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const wrap = this.parentElement;
                    wrap.querySelectorAll('.status-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    wrap.querySelector('.status-val').value = this.dataset.val;
                });
            });

        } catch (e) {
            console.error(e);
            CadaModal.alert({ title: 'Error', text: 'No se pudo cargar la lista de atletas.', type: 'danger' });
        }
    });

    // Botón de ayuda [?]
    document.getElementById('btn-help-asistencia')?.addEventListener('click', () => {
        FormValidator.showHelp(
            'Guía: Registro de Asistencia',
            '<?= e(asset("img/ayuda/formulario_asistencia.png")) ?>'
        );
    });

    // Validación estándar al submit con custom validation para hora de inicio/fin
    FormValidator.init('#form-asistencia', {
        custom: function(form) {
            const hInicio = form.querySelector('[name="hora_inicio"]');
            const hFin = form.querySelector('[name="hora_fin"]');
            if (hInicio.value && hFin.value && hInicio.value >= hFin.value) {
                return [
                    {
                        element: hInicio,
                        label: 'La hora de inicio debe ser menor a la hora de fin.'
                    },
                    {
                        element: hFin,
                        label: 'La hora de fin debe ser mayor a la hora de inicio.'
                    }
                ];
            }
            return [];
        }
    });

    document.getElementById('form-asistencia').addEventListener('submit', function(e) {
        // FormValidator preventDefault() e.stopImmediatePropagation() si falla.
        // Si no falla, este listener corre y pone el spinner.
        const btn = document.getElementById('btn-save');
        btn.disabled = true;
        btn.innerHTML = '<i class="ph ph-spinner-gap spinning"></i> Guardando...';
    });

    // Si hay una categoría seleccionada previamente (por old()), disparar el cambio después de que carguen todos los scripts y la API esté disponible
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
