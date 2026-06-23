<?php /** @var array $categorias */ ?>
<div class="page-header">
    <div>
        <h1>Programar Convocatoria</h1>
        <div class="subtitle">Selecciona la categoría y registra la lista de convocados para el partido</div>
    </div>
    <div style="display: flex; gap: 12px; align-items: center;">
        <a href="<?= e(url('/admin/convocatorias')) ?>" class="btn btn-ghost">
            <i class="ph ph-caret-left"></i> Directorio de Convocatorias
        </a>
    </div>
</div>

<form method="POST" action="<?= e(url('/admin/convocatorias/crear')) ?>" id="form-convocatoria" novalidate>
    <?= csrf_field() ?>

    <div class="card" style="margin-bottom: 24px; padding: 24px;">
        <!-- Fila 1: Campos requeridos -->
        <div class="form-header-grid">
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Selecciona la categoría de atletas a convocar" data-tooltip-pos="top"><span class="required">*</span> Categoría Deportiva</label>
                <select id="sel-cat" name="categoria_id" class="form-control" required>
                    <option value="">— Seleccione —</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= (int) $c['categoria_id'] ?>" <?= (int)old('categoria_id') === (int)$c['categoria_id'] ? 'selected' : '' ?>><?= e($c['nombre_categoria']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Fecha del partido" data-tooltip-pos="top"><span class="required">*</span> Fecha del Partido</label>
                <input type="date" name="fecha_evento" class="form-control" required value="<?= e(old('fecha_evento', date('Y-m-d', strtotime('+1 day')))) ?>" 
                min="<?= date('Y-m-d', strtotime('+1 day')) ?>" max="<?= date('Y-m-d', strtotime('+3 months')) ?>">
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Escribe el nombre o apellido del atleta para buscar" data-tooltip-pos="top">Buscar Atleta</label>
                <input type="text" id="input-buscar" class="form-control" placeholder="Escribe nombre o apellido..." disabled>
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
                <label class="form-label" data-tooltip="Estadio o cancha donde se jugará el partido" data-tooltip-pos="top"><span class="required">*</span> Ubicación</label>
                <input type="text" name="ubicacion" class="form-control" placeholder="Cancha UPTP" value="<?= e(old('ubicacion', 'Cancha UPTP')) ?>" required>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Terreno de juego donde se realiza el partido" data-tooltip-pos="top">Terreno de Juego</label>
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
                <label class="form-label" data-tooltip="Hora de inicio del partido (obligatorio)" data-tooltip-pos="top"><span class="required">*</span> Hora Inicio</label>
                <input type="time" name="hora_inicio" class="form-control" value="<?= e(old('hora_inicio')) ?>" required>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Hora de finalización del partido (obligatorio)" data-tooltip-pos="top"><span class="required">*</span> Hora Fin</label>
                <input type="time" name="hora_fin" class="form-control" value="<?= e(old('hora_fin')) ?>" required>
            </div>
        </div>
    </div>

    <div id="atletas-container" style="display: none;">
        <div class="card" style="padding: 0; overflow: hidden; max-width: 100%;">
            <div style="padding: 20px 24px; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center; background: var(--color-surface-2);">
                <h3 style="margin:0; font-size: 16px;"><i class="ph ph-envelope-simple-open"></i> Lista de Convocables</h3>
                <div id="stats-convocatorias" style="font-size: 13px; font-weight: 600; color: var(--color-white);">
                    Cargando atletas...
                </div>
            </div>

            <!-- Cabecera de la tabla de atletas -->
            <div class="atletas-table-headers" style="display: grid; grid-template-columns: 2fr 1fr 1fr 2.2fr; gap: 16px; padding: 12px 24px; background: var(--color-bg-alt); border-bottom: 1px solid var(--color-border); font-size: 13px; font-weight: 600; color: var(--color-text-muted); align-items: center;">
                <div>Atleta</div>
                <div style="text-align: center;">Asistencia (30d)</div>
                <div style="text-align: center;">Prom. Físico</div>
                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px;">
                    <span style="margin-right: auto;">Estatus Convocatoria</span>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <label class="switch" title="inactivo" data-tooltip="activo significa convocado, inactivo significa no convocado" data-tooltip-pos="top" style="position: relative; display: inline-block; width: 44px; height: 24px; margin: 0;">
                            <input type="checkbox" id="global-convocado-toggle" style="opacity: 0; width: 0; height: 0;">
                            <span class="slider"></span>
                        </label>
                        <span style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Todos</span>
                    </div>
                </div>
            </div>

            <div id="atletas-list-wrap" style="overflow: hidden;"></div>
            <div id="atletas-pagination" style="display: flex; justify-content: center; margin-top: 24px; padding-bottom: 24px;"></div>
        </div>

        <div class="form-actions-btn-group" style="margin-top: 24px;">
            <a href="<?= e(url('/admin/convocatorias')) ?>" class="btn btn-ghost" data-tooltip="Cancelar y volver al directorio" data-tooltip-pos="top">Cancelar</a>
            <button type="submit" class="btn btn-primary" id="btn-save" data-tooltip="Guardar todos los registros de convocatoria" data-tooltip-pos="top">
                <i class="ph ph-check-circle"></i> Guardar Convocatoria
            </button>
        </div>
    </div>

    <div id="no-atletas" class="card" style="display: none; text-align: center; padding: 48px;">
        <i class="ph ph-user-minus" style="font-size: 48px; opacity: 0.2;"></i>
        <p style="margin-top: 16px; color: var(--color-text-muted);">No hay atletas registrados en esta categoría.</p>
    </div>
</form>

<style>
.convocable-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 2.2fr;
    gap: 16px;
    padding: 16px 24px;
    border-bottom: 1px solid var(--color-border);
    align-items: center;
    transition: background 0.2s;
    max-width: 100%;
    box-sizing: border-box;
}
.convocable-row:hover { background: var(--color-bg-alt); }
.convocable-row:last-child { border-bottom: 0; }

/* CSS Donut Chart */
.donut-chart {
    position: relative;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: conic-gradient(var(--color-primary) calc(var(--percent) * 1%), var(--color-surface-2) 0);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}
.donut-chart-inner {
    position: absolute;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--color-surface);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 700;
    color: var(--color-text);
}

.status-options {
    display: flex;
    background: var(--color-surface-2);
    padding: 4px;
    border-radius: 8px;
    gap: 4px;
    width: fit-content;
    margin-left: auto;
    align-self: center;
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
.status-btn.active[data-val="1"] { background: var(--color-success); color: #fff; }
.status-btn.active[data-val="2"] { background: var(--color-danger); color: #fff; }

/* Responsive tweaks */
@media (max-width: 768px) {
    .atletas-table-headers {
        display: none !important;
    }
    .convocable-row {
        grid-template-columns: 1fr;
        gap: 12px;
        text-align: center;
    }
    .donut-chart {
        margin: 8px auto;
    }
    .status-options {
        justify-content: center;
    }
}
</style>

<script>
(function () {
    const $cat = document.getElementById('sel-cat');
    const $container = document.getElementById('atletas-container');
    const $buscar = document.getElementById('input-buscar');
    const $noAtletas = document.getElementById('no-atletas');
    const $listWrap = document.getElementById('atletas-list-wrap');
    const $stats = document.getElementById('stats-convocatorias');

    function updateConvocadosCount() {
        if (!$listWrap) return;
        const checkboxes = Array.from($listWrap.querySelectorAll('.convocado-checkbox:not([disabled])'));
        const total = checkboxes.length;
        const selected = checkboxes.filter(cb => cb.checked).length;
        
        if ($stats) {
            $stats.textContent = `${selected} Seleccionados | ${total} Atletas encontrados`;
        }

        const globalToggle = document.getElementById('global-convocado-toggle');
        if (globalToggle) {
            globalToggle.checked = total > 0 && selected === total;
        }
    }

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

    const oldAtletas = <?= json_encode(old('atletas') ?? []) ?>;
    const oldEstatus = <?= json_encode(old('estatus') ?? []) ?>;

    $cat.addEventListener('change', async () => {
        const id = $cat.value;
        if (!id) {
            $container.style.display = 'none';
            $noAtletas.style.display = 'none';
            if ($buscar) {
                $buscar.disabled = true;
                $buscar.value = '';
            }
            return;
        }

        try {
            $stats.textContent = 'Cargando...';
            const atletas = await API.get(`<?= e(url('/api/convocatorias/categoria')) ?>/${id}`);
            
            if (!atletas || !atletas.length) {
                $container.style.display = 'none';
                $noAtletas.style.display = 'block';
                return;
            }

            $noAtletas.style.display = 'none';
            $container.style.display = 'block';
            $stats.textContent = `${atletas.length} Atletas encontrados`;
            if ($buscar) {
                $buscar.disabled = false;
                $buscar.value = '';
            }

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
                
                // Convocatoria default: Convocado (1) si está activo, No Convocado (2) si está inactivo/suspendido
                const defaultStatus = isDis ? 2 : 1; 
                const currentStatus = isOld && oldEstatus[athleteIdStr] !== undefined ? parseInt(oldEstatus[athleteIdStr]) : defaultStatus;

                // Monthly attendance donut chart percent
                const attPercent = parseFloat(a.asistencia_mensual) || 0;
                
                // Physical average score
                const physScore = parseFloat(a.rendimiento_fisico) > 0 ? parseFloat(a.rendimiento_fisico).toFixed(1) + '%' : 'N/A';

                return `
                <div class="convocable-row" ${rowStyle}>
                    <!-- Columna 1: Atleta -->
                    <div style="display: flex; align-items: center; gap: 12px; text-align: left;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; flex-shrink: 0;">
                            ${a.nombre[0]}${a.apellido[0]}
                        </div>
                        <div>
                            <div style="font-weight: 600; color: var(--color-text); display: flex; align-items: center; flex-wrap: wrap;">
                                ${a.nombre} ${a.apellido}
                                ${statusBadge}
                            </div>
                            <div style="font-size: 12px; color: var(--color-text-muted);">${a.cedula || 'N/E'}</div>
                        </div>
                    </div>
                    
                    <!-- Columna 2: Gráfico de asistencia mensual -->
                    <div style="display: flex; justify-content: center; align-items: center;">
                        <div class="donut-chart" style="--percent: ${attPercent};" data-tooltip="Porcentaje de asistencia últimos 30 días" data-tooltip-pos="top">
                            <div class="donut-chart-inner">
                                ${attPercent}%
                            </div>
                        </div>
                    </div>

                    <!-- Columna 3: Promedio de Rendimiento Físico -->
                    <div style="text-align: center; display: flex; justify-content: center; align-items: center;">
                        <span class="badge ${physScore === 'N/A' ? 'badge-outline' : 'badge-primary'}" style="font-size: 12px; padding: 4px 10px;" data-tooltip="Promedio del test físico más reciente" data-tooltip-pos="top">
                            ${physScore}
                        </span>
                    </div>

                    <!-- Columna 4: Selección de convocatoria con slider -->
                    <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px;">
                        <label class="switch" title="${currentStatus === 1 ? 'activo' : 'inactivo'}" style="position: relative; display: inline-block; width: 44px; height: 24px; margin: 0; ${isDis ? 'cursor: not-allowed; opacity: 0.7;' : ''}">
                            <input type="hidden" name="estatus[${a.atleta_id}]" value="2" ${disAttr}>
                            <input type="checkbox" class="convocado-checkbox" name="estatus[${a.atleta_id}]" value="1" ${currentStatus === 1 ? 'checked' : ''} style="opacity: 0; width: 0; height: 0;" ${disAttr}>
                            <span class="slider"></span>
                        </label>
                        <span class="convocado-label" style="font-size: 12px; font-weight: 600; min-width: 80px; text-align: left;">${currentStatus === 1 ? 'Convocado' : 'No Convocado'}</span>
                    </div>
                    
                    <input type="hidden" name="atletas[]" value="${a.atleta_id}" ${disAttr}>
                </div>
                `;
            }).join('');

            // Lógica de switches de estado individuales
            $listWrap.querySelectorAll('.convocado-checkbox').forEach(cb => {
                cb.addEventListener('change', function() {
                    const label = this.closest('div').querySelector('.convocado-label');
                    if (this.checked) {
                        label.textContent = 'Convocado';
                    } else {
                        label.textContent = 'No Convocado';
                    }
                    updateConvocadosCount();
                });
            });

            // Actualizar contador inicialmente al renderizar
            updateConvocadosCount();

            CadaPagination({
                rowSelector: '.convocable-row',
                containerId: 'atletas-pagination'
            });

        } catch (e) {
            console.error(e);
            CadaModal.alert({ title: 'Error', text: 'No se pudo cargar la lista de atletas convocables.', type: 'danger' });
        }
    });

    // Global toggle checkbox switch listener
    const $globalToggle = document.getElementById('global-convocado-toggle');
    if ($globalToggle) {
        $globalToggle.addEventListener('change', function() {
            const isChecked = this.checked;
            $listWrap.querySelectorAll('.convocado-checkbox:not([disabled])').forEach(cb => {
                if (cb.checked !== isChecked) {
                    cb.checked = isChecked;
                    cb.dispatchEvent(new Event('change'));
                }
            });
        });
    }

    // Validación estándar al submit con custom validation para hora de inicio/fin
    FormValidator.init('#form-convocatoria', {
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

    const formElement = document.getElementById('form-convocatoria');
    if (formElement) {
        formElement.addEventListener('submit', async function(e) {
            e.preventDefault();

            const selectedAthletes = [];
            $listWrap.querySelectorAll('.convocable-row').forEach(row => {
                const cb = row.querySelector('.convocado-checkbox');
                if (cb && cb.checked) {
                    const nameEl = row.querySelector('div[style*="font-weight: 600"]') || 
                                   row.querySelector('div[style*="font-weight:600"]') ||
                                   row.querySelector('.prueba-row__name') || 
                                   row.querySelector('.asig-atleta-row__name');
                    const nameText = nameEl ? nameEl.textContent.trim() : 'Atleta';
                    selectedAthletes.push(nameText);
                }
            });

            let textHtml = '';
            if (selectedAthletes.length === 0) {
                textHtml = '<p style="color: var(--color-danger); font-weight: 600; margin-bottom: 12px;">No has seleccionado a ningún atleta para esta convocatoria.</p><p>¿Estás seguro de que deseas guardar una convocatoria vacía?</p>';
            } else {
                textHtml = '<p style="margin-bottom: 16px;">¿Estás seguro de que deseas programar la convocatoria con los siguientes atletas seleccionados?</p>';
                textHtml += '<div class="modal-atleta-cards-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px; max-height: 200px; overflow-y: auto; margin-top: 10px; padding-right: 5px; text-align: left;">';
                selectedAthletes.forEach(name => {
                    textHtml += `
                        <div class="modal-atleta-card" style="padding: 10px; background: var(--color-surface-2); border: 1px solid var(--color-border); border-radius: 8px; display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 13px; color: var(--color-text);">
                            <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 11px; flex-shrink: 0; font-weight: bold;">
                                ${name[0]}
                            </div>
                            <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${name}</div>
                        </div>
                    `;
                });
                textHtml += '</div>';
            }

            const confirmed = await CadaModal.confirm({
                title: 'Confirmar Convocatoria',
                text: textHtml,
                type: 'danger',
                confirmText: 'Confirmar',
                cancelText: 'Cancelar'
            });

            if (confirmed) {
                const btn = document.getElementById('btn-save');
                btn.disabled = true;
                btn.innerHTML = '<i class="ph ph-spinner-gap spinning"></i> Guardando...';
                formElement.submit();
            }
        });
    }

    if ($buscar) {
        $buscar.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            const rows = $listWrap.querySelectorAll('.convocable-row');
            const pagination = document.getElementById('atletas-pagination');

            if (!query) {
                if (pagination) pagination.style.display = 'flex';
                CadaPagination({
                    rowSelector: '.convocable-row',
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

    // Si hay una categoría seleccionada previamente (por old()), disparar el cambio
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
