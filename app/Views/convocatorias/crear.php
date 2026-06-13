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
        <div class="form-header-grid" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 20px; align-items: flex-end;">
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
                <input type="date" name="fecha_evento" class="form-control" required value="<?= e(old('fecha_evento', date('Y-m-d'))) ?>" min="2019-01-01">
            </div>
            <div class="form-group form-header-toggle-group" style="margin: 0;">
                <button type="button" id="btn-toggle-options" class="btn btn-ghost" style="height: 44px; width: 44px; display: inline-flex; align-items: center; justify-content: center; border: 1px dashed var(--color-border);" data-tooltip="ver opciones extra" data-tooltip-pos="top">
                    <i class="ph ph-sliders-horizontal" style="font-size: 20px;"></i>
                </button>
            </div>
        </div>

        <!-- Fila 2: Opciones extras (colapsada por defecto) -->
        <div id="row-opciones-extra" class="form-extra-grid" style="display: none; margin-top: 24px; padding-top: 24px; border-top: 1px dashed var(--color-border); display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Estadio o cancha donde se jugará el partido" data-tooltip-pos="top">Ubicación</label>
                <input type="text" name="ubicacion" class="form-control" placeholder="Cancha UPTP" value="<?= e(old('ubicacion', 'Cancha UPTP')) ?>">
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
                <label class="form-label" data-tooltip="Hora de inicio del partido" data-tooltip-pos="top">Hora Inicio</label>
                <input type="time" name="hora_inicio" class="form-control" value="<?= e(old('hora_inicio')) ?>">
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Hora de finalización del partido" data-tooltip-pos="top">Hora Fin</label>
                <input type="time" name="hora_fin" class="form-control" value="<?= e(old('hora_fin')) ?>">
            </div>
        </div>
    </div>

    <div id="atletas-container" style="display: none;">
        <div class="card" style="padding: 0; overflow: hidden; max-width: 100%;">
            <div style="padding: 20px 24px; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center; background: var(--color-surface-2);">
                <h3 style="margin:0; font-size: 16px;"><i class="ph ph-envelope-simple-open"></i> Lista de Convocables</h3>
                <div id="stats-convocatorias" style="font-size: 13px; font-weight: 600; color: var(--color-primary);">
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
                    <div style="display: flex; gap: 4px;">
                        <button type="button" id="btn-select-all-convocados" class="btn btn-sm btn-outline" style="padding: 2px 8px; font-size: 11px; height: 26px;" data-tooltip="Convocar a todos" data-tooltip-pos="top">
                            <i class="ph ph-check-square"></i> Convocados
                        </button>
                        <button type="button" id="btn-select-all-no-convocados" class="btn btn-sm btn-outline" style="padding: 2px 8px; font-size: 11px; height: 26px;" data-tooltip="Excluir a todos" data-tooltip-pos="top">
                            <i class="ph ph-square-fill"></i> No Convocados
                        </button>
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

    // Toggle para opciones extra
    const $btnToggle = document.getElementById('btn-toggle-options');
    const $rowExtra = document.getElementById('row-opciones-extra');
    if ($btnToggle && $rowExtra) {
        $rowExtra.style.display = 'none'; // Ensure hidden initially
        $btnToggle.addEventListener('click', () => {
            const isHidden = $rowExtra.style.display === 'none';
            $rowExtra.style.display = isHidden ? 'grid' : 'none';
            $btnToggle.classList.toggle('active', isHidden);
            $btnToggle.setAttribute('data-tooltip', isHidden ? 'ocultar opciones extra' : 'ver opciones extra');
        });
    }

    const $noAtletas = document.getElementById('no-atletas');
    const $listWrap = document.getElementById('atletas-list-wrap');
    const $stats = document.getElementById('stats-convocatorias');

    const oldAtletas = <?= json_encode(old('atletas') ?? []) ?>;
    const oldEstatus = <?= json_encode(old('estatus') ?? []) ?>;

    $cat.addEventListener('change', async () => {
        const id = $cat.value;
        if (!id) {
            $container.style.display = 'none';
            $noAtletas.style.display = 'none';
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
                            <div style="font-size: 12px; color: var(--color-text-muted);">C.I. ${a.cedula || 'N/E'}</div>
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

                    <!-- Columna 4: Botones de selección de convocatoria -->
                    <div class="status-options" data-atleta="${a.atleta_id}" style="${isDis ? 'cursor: not-allowed; opacity: 0.7;' : ''}">
                        <input type="hidden" name="estatus[${a.atleta_id}]" value="${currentStatus}" class="status-val" ${disAttr}>
                        <button type="button" class="status-btn ${!isDis && currentStatus === 1 ? 'active' : ''}" data-val="1" data-tooltip="Convocar al partido" data-tooltip-pos="top" ${disAttr} ${disCursor}>Convocado</button>
                        <button type="button" class="status-btn ${isDis || currentStatus === 2 ? 'active' : ''}" data-val="2" data-tooltip="Excluir de la convocatoria" data-tooltip-pos="top" ${disAttr} ${disCursor}>No Convocado</button>
                    </div>
                    
                    <input type="hidden" name="atletas[]" value="${a.atleta_id}" ${disAttr}>
                </div>
                `;
            }).join('');

            // Lógica de botones de estado (1: Convocado, 2: No Convocado)
            $listWrap.querySelectorAll('.status-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const wrap = this.parentElement;
                    wrap.querySelectorAll('.status-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    wrap.querySelector('.status-val').value = parseInt(this.dataset.val);
                });
            });

            CadaPagination({
                rowSelector: '.convocable-row',
                containerId: 'atletas-pagination'
            });

        } catch (e) {
            console.error(e);
            CadaModal.alert({ title: 'Error', text: 'No se pudo cargar la lista de atletas convocables.', type: 'danger' });
        }
    });

    // Bulk selection buttons
    const $btnAllConvocados = document.getElementById('btn-select-all-convocados');
    const $btnAllNoConvocados = document.getElementById('btn-select-all-no-convocados');

    if ($btnAllConvocados) {
        $btnAllConvocados.addEventListener('click', () => {
            $listWrap.querySelectorAll('.status-btn[data-val="1"]:not([disabled])').forEach(btn => {
                btn.click();
            });
        });
    }

    if ($btnAllNoConvocados) {
        $btnAllNoConvocados.addEventListener('click', () => {
            $listWrap.querySelectorAll('.status-btn[data-val="2"]:not([disabled])').forEach(btn => {
                btn.click();
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

    document.getElementById('form-convocatoria').addEventListener('submit', function(e) {
        const btn = document.getElementById('btn-save');
        btn.disabled = true;
        btn.innerHTML = '<i class="ph ph-spinner-gap spinning"></i> Guardando...';
    });

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
