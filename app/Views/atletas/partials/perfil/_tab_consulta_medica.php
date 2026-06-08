<!-- Tab: Consulta Médica -->
<div id="tab-consulta" class="tab-content" style="display: none;">
    <?php
    $tipos = [
        1 => 'Enfermedad',
        2 => 'Lesión',
        3 => 'Control',
        4 => 'Evaluación',
        5 => 'Asesoría',
        6 => 'Terapia o Rehabilitación',
        7 => 'Intervención o Emergencia'
    ];

    $estatuses = [
        1 => ['label' => 'No Apto', 'class' => 'danger'],
        2 => ['label' => 'Apto', 'class' => 'success'],
        3 => ['label' => 'Diferenciado', 'class' => 'warning']
    ];
    ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h3 style="margin: 0;"><i class="ph ph-first-aid"></i> Consultas Médicas</h3>
        <?php if (can('admin') || can('medico')): ?>
            <button type="button" class="btn btn-primary btn-sm" id="btn-agregar-consulta">
                <i class="ph ph-plus"></i> Registrar Consulta
            </button>
        <?php endif; ?>
    </div>

    <?php if (empty($consultas_historial)): ?>
        <!-- Estado vacío -->
        <div style="background: var(--color-bg-alt); border-radius: var(--radius); padding: 48px; text-align: center; margin-bottom: 24px;">
            <i class="ph ph-first-aid" style="font-size: 48px; color: var(--color-text-muted); opacity: 0.4;"></i>
            <p style="color: var(--color-text-muted); margin-top: 12px; margin-bottom: 16px;">No se han registrado consultas médicas para este atleta.</p>
        </div>
    <?php else: ?>
        <div class="perfil-table-wrap">
            <div class="perfil-table-header" style="grid-template-columns: 1.5fr 2fr 1.2fr 1.2fr 1.2fr 1.5fr 1fr;">
                <div>Tipo de Consulta</div>
                <div>Diagnóstico</div>
                <div>Fecha Suceso</div>
                <div>Alta Estimada</div>
                <div>Estatus</div>
                <div>Registrado Por</div>
                <div style="text-align: center;">Acciones</div>
            </div>
            <?php foreach ($consultas_historial as $row): ?>
                <div class="perfil-table-row" style="grid-template-columns: 1.5fr 2fr 1.2fr 1.2fr 1.2fr 1.5fr 1fr;">
                    <div class="perfil-row-col">
                        <span class="perfil-col-label">Tipo de Consulta</span>
                        <span style="font-weight: 600;"><?= e($tipos[$row['tipo_consulta']] ?? 'Otro') ?></span>
                    </div>
                    <div class="perfil-row-col">
                        <span class="perfil-col-label">Diagnóstico</span>
                        <span><?= e($row['diagnostico']) ?></span>
                    </div>
                    <div class="perfil-row-col">
                        <span class="perfil-col-label">Fecha Suceso</span>
                        <span><?= e(date('d/m/Y', strtotime($row['fecha_suceso']))) ?></span>
                    </div>
                    <div class="perfil-row-col">
                        <span class="perfil-col-label">Alta Estimada</span>
                        <span><?= !empty($row['fecha_alta_estimada']) ? e(date('d/m/Y', strtotime($row['fecha_alta_estimada']))) : '<span class="text-muted">—</span>' ?></span>
                    </div>
                    <div class="perfil-row-col">
                        <span class="perfil-col-label">Estatus</span>
                        <?php $est = $estatuses[$row['estatus_disponibilidad']] ?? ['label' => 'Desconocido', 'class' => 'outline']; ?>
                        <span>
                            <span class="badge badge-<?= $est['class'] ?>" style="font-weight: 600; font-size: 11px;">
                                <?= e($est['label']) ?>
                            </span>
                        </span>
                    </div>
                    <div class="perfil-row-col">
                        <span class="perfil-col-label">Registrado Por</span>
                        <span><?= !empty($row['usuario_nombre']) ? e($row['usuario_nombre'] . ' ' . $row['usuario_apellido']) : '<span class="text-muted">Sistema</span>' ?></span>
                    </div>
                    <div class="perfil-row-col">
                        <span class="perfil-col-label">Acciones</span>
                        <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                            <button type="button" class="btn-icon btn-ver-consulta" title="Ver Detalles"
                                style="color:var(--color-primary); background:none; border:none; cursor:pointer; font-size:16px; padding: 0;"
                                data-id="<?= e($row['consulta_id']) ?>"
                                data-tipo-lbl="<?= e($tipos[$row['tipo_consulta']] ?? 'Otro') ?>"
                                data-fecha-suceso="<?= e(date('d/m/Y', strtotime($row['fecha_suceso']))) ?>"
                                data-fecha-alta="<?= !empty($row['fecha_alta_estimada']) ? e(date('d/m/Y', strtotime($row['fecha_alta_estimada']))) : '—' ?>"
                                data-estatus-lbl="<?= e($estatuses[$row['estatus_disponibilidad']]['label'] ?? 'Desconocido') ?>"
                                data-estatus-class="<?= e($estatuses[$row['estatus_disponibilidad']]['class'] ?? 'outline') ?>"
                                data-diagnostico="<?= e($row['diagnostico']) ?>"
                                data-descripcion="<?= e($row['descripcion'] ?? '—') ?>"
                                data-tratamiento="<?= e($row['tratamiento_indicado'] ?? '—') ?>"
                                data-registrado="<?= !empty($row['usuario_nombre']) ? e($row['usuario_nombre'] . ' ' . $row['usuario_apellido']) : 'Sistema' ?>">
                                <i class="ph ph-eye"></i>
                            </button>
                            <?php if (can('admin') || can('medico')): ?>
                                <button type="button" class="btn-icon btn-editar-consulta" title="Editar"
                                    style="color:var(--color-primary); background:none; border:none; cursor:pointer; font-size:16px; padding: 0;"
                                    data-id="<?= e($row['consulta_id']) ?>"
                                    data-tipo="<?= e($row['tipo_consulta']) ?>"
                                    data-fecha-suceso="<?= e($row['fecha_suceso']) ?>"
                                    data-fecha-alta="<?= e($row['fecha_alta_estimada'] ?? '') ?>"
                                    data-estatus="<?= e($row['estatus_disponibilidad'] ?? '') ?>"
                                    data-diagnostico="<?= e($row['diagnostico']) ?>"
                                    data-descripcion="<?= e($row['descripcion'] ?? '') ?>"
                                    data-tratamiento="<?= e($row['tratamiento_indicado'] ?? '') ?>">
                                    <i class="ph ph-pencil-simple"></i>
                                </button>
                                <form class="form-delete-consulta" method="POST"
                                    action="<?= e(url("/admin/atletas/{$atleta['atleta_id']}/consultas-medicas/{$row['consulta_id']}/eliminar")) ?>"
                                    style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button type="button" class="btn-icon btn-delete-consulta" title="Eliminar"
                                        style="color:var(--color-danger); background:none; border:none; cursor:pointer; font-size:16px; padding: 0;">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Modal Único: Crear y Editar Consulta Médica -->
    <div id="modal-consulta-medica" class="modal-overlay" style="display:none;">
        <form id="form-consulta-medica" action="" method="POST" class="modal-container" style="max-width: 600px;" novalidate>
            <?= csrf_field() ?>
            <div class="modal-header">
                <h3 class="modal-title"><i class="ph ph-first-aid"></i> <span id="title-consulta">Registrar Consulta Médica</span></h3>
                <button type="button" class="modal-close" data-close-modal>&times;</button>
            </div>
            <div class="modal-body">
                <div id="consulta-error" class="alert alert-danger" style="display:none; margin-bottom: 16px;"></div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Seleccione el tipo de consulta o registro médico." data-tooltip-pos="top"><span class="required">*</span> Tipo de Consulta</label>
                        <select name="tipo_consulta" id="input-tipo-consulta" class="form-control" required>
                            <option value="">— Seleccionar —</option>
                            <?php foreach ($tipos as $val => $lbl): ?>
                                <option value="<?= $val ?>"><?= e($lbl) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Disponibilidad deportiva actual determinada por la consulta." data-tooltip-pos="top"><span class="required">*</span> Estatus de Disponibilidad</label>
                        <select name="estatus_disponibilidad" id="input-estatus-disp" class="form-control" required>
                            <option value="">— Seleccionar —</option>
                            <?php foreach ($estatuses as $val => $est): ?>
                                <option value="<?= $val ?>"><?= e($est['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Fecha en la que ocurrió el suceso o la consulta (máximo hace 10 años, no futura)." data-tooltip-pos="top"><span class="required">*</span> Fecha Suceso</label>
                        <input type="date" name="fecha_suceso" id="input-fecha-suceso" class="form-control" 
                               min="<?= date('Y-m-d', strtotime('-10 years')) ?>" 
                               max="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Fecha estimada de alta médica (desde ayer en adelante, máximo 3 años en el futuro)." data-tooltip-pos="top">Fecha de Alta Estimada</label>
                        <input type="date" name="fecha_alta_estimada" id="input-fecha-alta" class="form-control"
                               min="<?= date('Y-m-d', strtotime('-1 day')) ?>"
                               max="<?= date('Y-m-d', strtotime('+3 years')) ?>">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" data-tooltip="Diagnóstico médico formal." data-tooltip-pos="top"><span class="required">*</span> Diagnóstico</label>
                    <input type="text" name="diagnostico" id="input-diagnostico" class="form-control" placeholder="Ej: Esguince de tobillo grado 1..." required>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" data-tooltip="Detalles adicionales, síntomas observados o notas extras.">Descripción / Síntomas</label>
                    <textarea name="descripcion" id="input-descripcion" class="form-control" rows="2" placeholder="Detalles de la consulta médica..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" data-tooltip="Tratamiento, reposo, o terapia indicada.">Tratamiento Indicado</label>
                    <textarea name="tratamiento_indicado" id="input-tratamiento" class="form-control" rows="2" placeholder="Medicamentos, reposo, ejercicios indicados..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" data-close-modal>Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> <span id="submit-text-consulta">Guardar Cambios</span></button>
            </div>
        </form>
    </div>

    <!-- Modal: Detalle de Consulta Médica -->
    <div id="modal-ver-consulta" class="modal-overlay" style="display:none;">
        <div class="modal-container" style="max-width: 580px; border-top: 5px solid var(--color-info);">
            <div class="modal-header" style="background: var(--color-surface); padding: 18px 24px;">
                <h3 class="modal-title" style="font-family: var(--font-display); font-weight: 700; color: var(--color-text); font-size: 18px;">
                    <i class="ph ph-first-aid" style="color: var(--color-primary); font-size: 22px; vertical-align: middle; margin-right: 4px;"></i> 
                    Expediente Clínico: Consulta Médica
                </h3>
                <button type="button" class="modal-close" data-close-modal>&times;</button>
            </div>
            <div class="modal-body" style="padding: 24px; display: flex; flex-direction: column; gap: 20px; background: var(--color-bg);">
                
                <!-- Card Principal: Tipo de Consulta & Estatus -->
                <div style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius); padding: 18px; display: flex; align-items: center; justify-content: space-between; gap: 16px; box-shadow: var(--shadow-sm);">
                    <div>
                        <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--color-text-muted); font-weight: 600; display: block; margin-bottom: 4px;">Tipo de Consulta</span>
                        <span id="detail-tipo" style="font-size: 18px; font-weight: 800; color: var(--color-primary); font-family: var(--font-display);"></span>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--color-text-muted); font-weight: 600; display: block; margin-bottom: 6px;">Disponibilidad</span>
                        <span id="detail-estatus" class="badge" style="padding: 6px 14px; font-size: 12px; font-weight: 700; border-radius: 20px;"></span>
                    </div>
                </div>

                <!-- Grid de Fechas: Suceso y Alta Estimada -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <!-- Fecha Suceso -->
                    <div style="display: flex; align-items: center; gap: 12px; padding: 14px; background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-sm); box-shadow: var(--shadow-sm);">
                        <div style="width: 38px; height: 38px; border-radius: var(--radius-sm); background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                            <i class="ph ph-calendar"></i>
                        </div>
                        <div>
                            <span style="font-size: 10px; text-transform: uppercase; color: var(--color-text-muted); font-weight: 600; display: block; margin-bottom: 2px;">Fecha Suceso</span>
                            <span id="detail-fecha-suceso" style="font-weight: 700; color: var(--color-text); font-size: 14px;"></span>
                        </div>
                    </div>
                    <!-- Fecha Alta Estimada -->
                    <div style="display: flex; align-items: center; gap: 12px; padding: 14px; background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-sm); box-shadow: var(--shadow-sm);">
                        <div style="width: 38px; height: 38px; border-radius: var(--radius-sm); background: #DCFCE7; color: #166534; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                            <i class="ph ph-calendar-check"></i>
                        </div>
                        <div>
                            <span style="font-size: 10px; text-transform: uppercase; color: var(--color-text-muted); font-weight: 600; display: block; margin-bottom: 2px;">Alta Estimada</span>
                            <span id="detail-fecha-alta" style="font-weight: 700; color: var(--color-text); font-size: 14px;"></span>
                        </div>
                    </div>
                </div>

                <!-- Diagnóstico Médico -->
                <div style="border-left: 4px solid var(--color-primary); background: var(--color-surface); padding: 16px; border-radius: 0 var(--radius-sm) var(--radius-sm) 0; border-top: 1px solid var(--color-border); border-right: 1px solid var(--color-border); border-bottom: 1px solid var(--color-border); box-shadow: var(--shadow-sm);">
                    <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; text-transform: uppercase; color: var(--color-text-muted); font-weight: 600; margin-bottom: 6px;">
                        <i class="ph ph-heartbeat" style="font-size: 15px; color: var(--color-primary);"></i> Diagnóstico Médico
                    </div>
                    <span id="detail-diagnostico" style="font-size: 15px; font-weight: 700; color: var(--color-text); line-height: 1.4; display: block;"></span>
                </div>

                <!-- Descripción y Sintomas -->
                <div>
                    <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; text-transform: uppercase; color: var(--color-text-muted); font-weight: 600; margin-bottom: 8px; padding-left: 4px;">
                        <i class="ph ph-text-align-left" style="font-size: 14px; color: var(--color-primary);"></i> Descripción / Síntomas
                    </div>
                    <div style="background: var(--color-surface-2); padding: 14px 18px; border-radius: var(--radius-sm); border: 1px solid var(--color-border); box-shadow: inset var(--shadow-sm);">
                        <span id="detail-descripcion" style="white-space: pre-wrap; display: block; line-height: 1.6; color: var(--color-text); font-size: 13.5px;"></span>
                    </div>
                </div>

                <!-- Tratamiento Indicado -->
                <div>
                    <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; text-transform: uppercase; color: var(--color-text-muted); font-weight: 600; margin-bottom: 8px; padding-left: 4px;">
                        <i class="ph ph-pill" style="font-size: 14px; color: var(--color-primary);"></i> Tratamiento Indicado
                    </div>
                    <div style="background: var(--color-surface-2); padding: 14px 18px; border-radius: var(--radius-sm); border: 1px solid var(--color-border); box-shadow: inset var(--shadow-sm);">
                        <span id="detail-tratamiento" style="white-space: pre-wrap; display: block; line-height: 1.6; color: var(--color-text); font-size: 13.5px;"></span>
                    </div>
                </div>

                <!-- Datos de Registro -->
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: var(--color-text-muted); border-top: 1px solid var(--color-border); padding-top: 16px; margin-top: 8px;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <i class="ph ph-user-circle" style="font-size: 16px; color: var(--color-text-muted);"></i>
                        <span>Registrado por: <strong id="detail-registrado" style="color: var(--color-text);"></strong></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 4px;">
                        <i class="ph ph-fingerprint" style="font-size: 14px;"></i>
                        <span>ID Consulta: <span id="detail-id" style="font-family: monospace; font-weight: 700; color: var(--color-text);"></span></span>
                    </div>
                </div>

            </div>
            <div class="modal-footer" style="background: var(--color-surface); padding: 14px 24px;">
                <button type="button" class="btn btn-ghost" data-close-modal style="font-weight: 600; padding: 8px 18px;">Cerrar Ficha</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modalConsulta = document.getElementById('modal-consulta-medica');
    const formConsulta = document.getElementById('form-consulta-medica');
    const baseActionConsulta = "<?= e(url("/admin/atletas/{$atleta['atleta_id']}/consultas-medicas")) ?>";

    const modalVer = document.getElementById('modal-ver-consulta');

    function abrirModalVer(data) {
        if (!modalVer) return;

        document.getElementById('detail-id').textContent = data.id;
        document.getElementById('detail-tipo').textContent = data.tipo;

        const estatusEl = document.getElementById('detail-estatus');
        estatusEl.textContent = data.estatus;
        estatusEl.className = 'badge badge-' + data.estatusClass;

        document.getElementById('detail-fecha-suceso').textContent = data.fechaSuceso;
        document.getElementById('detail-fecha-alta').textContent = data.fechaAlta;
        document.getElementById('detail-diagnostico').textContent = data.diagnostico;
        document.getElementById('detail-registrado').textContent = data.registrado;

        const desc = data.descripcion && data.descripcion !== '—' && data.descripcion.trim() !== '' ? data.descripcion : '';
        const descEl = document.getElementById('detail-descripcion');
        if (desc) {
            descEl.textContent = desc;
            descEl.style.fontStyle = 'normal';
            descEl.style.color = 'var(--color-text)';
        } else {
            descEl.textContent = 'Sin descripción ni síntomas adicionales registrados.';
            descEl.style.fontStyle = 'italic';
            descEl.style.color = 'var(--color-text-muted)';
        }

        const trat = data.tratamiento && data.tratamiento !== '—' && data.tratamiento.trim() !== '' ? data.tratamiento : '';
        const tratEl = document.getElementById('detail-tratamiento');
        if (trat) {
            tratEl.textContent = trat;
            tratEl.style.fontStyle = 'normal';
            tratEl.style.color = 'var(--color-text)';
        } else {
            tratEl.textContent = 'Sin tratamiento específico indicado.';
            tratEl.style.fontStyle = 'italic';
            tratEl.style.color = 'var(--color-text-muted)';
        }

        modalVer.style.display = 'flex';
    }

    function cerrarModalVer() {
        if (modalVer) modalVer.style.display = 'none';
    }

    function abrirModalConsulta(modo = 'agregar', data = {}) {
        if (!modalConsulta) return;

        const title = document.getElementById('title-consulta');
        const submitText = document.getElementById('submit-text-consulta');

        if (modo === 'editar') {
            title.textContent = 'Editar Consulta Médica';
            formConsulta.action = baseActionConsulta + '/' + data.id + '/editar';
            document.getElementById('input-tipo-consulta').value = data.tipo;
            document.getElementById('input-fecha-suceso').value = data.fecha_suceso;
            document.getElementById('input-fecha-alta').value = data.fecha_alta;
            document.getElementById('input-estatus-disp').value = data.estatus;
            document.getElementById('input-diagnostico').value = data.diagnostico;
            document.getElementById('input-descripcion').value = data.descripcion;
            document.getElementById('input-tratamiento').value = data.tratamiento;
        } else {
            title.textContent = 'Registrar Consulta Médica';
            submitText.innerHTML = '<i class="ph ph-plus"></i> Registrar';
            formConsulta.action = baseActionConsulta;
            formConsulta.reset();
            // Poner fecha de hoy por defecto en fecha suceso
            const hoy = new Date().toISOString().split('T')[0];
            document.getElementById('input-fecha-suceso').value = hoy;
        }

        // Sincronizar dinámicamente los límites del input de alta en base a la fecha de suceso cargada
        const sucesoVal = document.getElementById('input-fecha-suceso').value;
        const ayer = new Date();
        ayer.setDate(ayer.getDate() - 1);
        const ayerStr = ayer.toISOString().split('T')[0];

        let minAlta = ayerStr;
        if (sucesoVal && sucesoVal > minAlta) {
            minAlta = sucesoVal;
        }
        document.getElementById('input-fecha-alta').setAttribute('min', minAlta);

        const errorEl = document.getElementById('consulta-error');
        if (errorEl) errorEl.style.display = 'none';
        modalConsulta.style.display = 'flex';
    }

    function cerrarModalConsulta() {
        if (modalConsulta) modalConsulta.style.display = 'none';
    }

    const inputSuceso = document.getElementById('input-fecha-suceso');
    const inputAlta = document.getElementById('input-fecha-alta');

    inputSuceso?.addEventListener('blur', () => {
        const sucesoVal = inputSuceso.value;
        const ayer = new Date();
        ayer.setDate(ayer.getDate() - 1);
        const ayerStr = ayer.toISOString().split('T')[0];

        let minAlta = ayerStr;
        if (sucesoVal && sucesoVal > minAlta) {
            minAlta = sucesoVal;
        }
        inputAlta?.setAttribute('min', minAlta);

        if (inputAlta && inputAlta.value && inputAlta.value < minAlta) {
            inputAlta.value = minAlta;
        }
    });

    function restrictDateInput(input) {
        if (!input) return;
        input.addEventListener('blur', (e) => {
            const min = e.target.getAttribute('min');
            const max = e.target.getAttribute('max');
            let val = e.target.value;
            if (val) {
                const parts = val.split('-');
                if (parts[0] && parts[0].length > 4) {
                    parts[0] = parts[0].substring(0, 4);
                    val = parts.join('-');
                    e.target.value = val;
                }
                if (min && val < min) {
                    e.target.value = min;
                } else if (max && val > max) {
                    e.target.value = max;
                }
            }
        });
    }

    restrictDateInput(inputSuceso);
    restrictDateInput(inputAlta);

    // Botones de Abrir Modal
    document.getElementById('btn-agregar-consulta')?.addEventListener('click', () => abrirModalConsulta('agregar'));

    document.querySelectorAll('.btn-editar-consulta').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const target = e.currentTarget;
            const data = {
                id: target.getAttribute('data-id'),
                tipo: target.getAttribute('data-tipo'),
                fecha_suceso: target.getAttribute('data-fecha-suceso'),
                fecha_alta: target.getAttribute('data-fecha-alta'),
                estatus: target.getAttribute('data-estatus'),
                diagnostico: target.getAttribute('data-diagnostico'),
                descripcion: target.getAttribute('data-descripcion'),
                tratamiento: target.getAttribute('data-tratamiento')
            };
            abrirModalConsulta('editar', data);
        });
    });

    document.querySelectorAll('.btn-ver-consulta').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const target = e.currentTarget;
            const data = {
                id: target.getAttribute('data-id'),
                tipo: target.getAttribute('data-tipo-lbl'),
                estatus: target.getAttribute('data-estatus-lbl'),
                estatusClass: target.getAttribute('data-estatus-class'),
                fechaSuceso: target.getAttribute('data-fecha-suceso'),
                fechaAlta: target.getAttribute('data-fecha-alta'),
                diagnostico: target.getAttribute('data-diagnostico'),
                descripcion: target.getAttribute('data-descripcion'),
                tratamiento: target.getAttribute('data-tratamiento'),
                registrado: target.getAttribute('data-registrado')
            };
            abrirModalVer(data);
        });
    });

    modalVer?.querySelectorAll('[data-close-modal]').forEach(btn => {
        btn.addEventListener('click', cerrarModalVer);
    });

    modalVer?.addEventListener('click', (e) => {
        if (e.target === modalVer) cerrarModalVer();
    });

    // Cerrar modal al hacer clic en cancelar o en la cruz
    modalConsulta?.querySelectorAll('[data-close-modal]').forEach(btn => {
        btn.addEventListener('click', cerrarModalConsulta);
    });

    // Cerrar modal al hacer clic fuera del modal container
    modalConsulta?.addEventListener('click', (e) => {
        if (e.target === modalConsulta) cerrarModalConsulta();
    });

    // Interceptar submit
    formConsulta?.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Validar con FormValidator del sistema
        if (typeof FormValidator !== 'undefined') {
            const validation = FormValidator.validate(formConsulta);
            if (!validation.valid) {
                FormValidator.showErrors(validation.errors);
                return;
            }
        }

        const submitBtn = formConsulta.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Guardando...';

        try {
            const formData = new FormData(formConsulta);
            const response = await fetch(formConsulta.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (result.success) {
                // Cerrar modal
                cerrarModalConsulta();

                // Proceso exitoso con CadaToast (colores verdes, uni-fila, 5s expiración)
                if (typeof CadaToast !== 'undefined') {
                    CadaToast.success(result.message || 'Consulta médica guardada correctamente.', () => {
                        window.location.href = window.location.pathname + '?tab=tab-consulta';
                    });
                } else {
                    window.location.href = window.location.pathname + '?tab=tab-consulta';
                }
            } else {
                // Si hay errores de validación específicos del backend, marcamos los inputs
                if (result.errors) {
                    const errorsList = [];
                    Object.entries(result.errors).forEach(([field, msgs]) => {
                        const input = formConsulta.querySelector(`[name="${field}"]`) || document.getElementById('input-' + field);
                        if (input) {
                            FormValidator.markError(input);
                            input.addEventListener('focus', function clearOnFocus() {
                                FormValidator.clearMark(input);
                                input.removeEventListener('focus', clearOnFocus);
                            });
                        }
                        if (Array.isArray(msgs)) {
                            msgs.forEach(m => errorsList.push(m));
                        } else {
                            errorsList.push(msgs);
                        }
                    });

                    if (typeof CadaModal !== 'undefined') {
                        CadaModal.alert({
                            title: 'Campos Incompletos',
                            text: `Por favor revisa lo siguiente:<br><br>${errorsList.map(e => `• ${e}`).join('<br>')}`,
                            type: 'warning',
                            confirmText: 'Corregir ahora'
                        });
                    } else {
                        alert('Campos incompletos: ' + errorsList.join('\n'));
                    }
                } else {
                    if (typeof CadaModal !== 'undefined') {
                        CadaModal.alert({
                            title: 'Error',
                            text: result.message || 'Ocurrió un error al guardar la consulta.',
                            type: 'danger',
                            confirmText: 'Cerrar'
                        });
                    } else {
                        alert(result.message || 'Ocurrió un error al guardar la consulta.');
                    }
                }
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        } catch (error) {
            if (typeof CadaModal !== 'undefined') {
                CadaModal.alert({
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor. Intente nuevamente.',
                    type: 'danger',
                    confirmText: 'Cerrar'
                });
            } else {
                alert('No se pudo conectar con el servidor. Intente nuevamente.');
            }
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });

    // Confirmación de eliminación
    document.querySelectorAll('.btn-delete-consulta').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const form = e.currentTarget.closest('form');
            if (typeof CadaModal !== 'undefined') {
                CadaModal.confirm({
                    title: 'Eliminar Consulta Médica',
                    text: '¿Estás seguro de que deseas eliminar esta consulta médica?',
                    type: 'danger',
                    confirmText: 'Sí, eliminar'
                }).then(confirmed => {
                    if (confirmed) form.submit();
                });
            } else {
                if (confirm('¿Estás seguro de que deseas eliminar esta consulta médica?')) {
                    form.submit();
                }
            }
        });
    });
});
</script>
