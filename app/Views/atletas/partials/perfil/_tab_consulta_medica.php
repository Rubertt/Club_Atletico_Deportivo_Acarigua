<!-- Tab: Consulta Médica -->
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
        0 => ['label' => 'No Apto', 'class' => 'danger'],
        1 => ['label' => 'Apto', 'class' => 'success']
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
                            <button type="button" class="btn-view-premium btn-ver-consulta" title="Ver Detalles"
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
                                <button type="button" class="btn-edit-premium btn-editar-consulta" title="Editar"
                                    data-id="<?= e($row['consulta_id']) ?>"
                                    data-tipo="<?= e($row['tipo_consulta']) ?>"
                                    data-fecha-suceso="<?= e($row['fecha_suceso']) ?>"
                                    data-fecha-alta="<?= e($row['fecha_alta_estimada'] ?? '') ?>"
                                    data-estatus="<?= e($row['estatus_disponibilidad'] ?? '') ?>"
                                    data-creado-en="<?= e($row['creado_en']) ?>"
                                    data-diagnostico="<?= e($row['diagnostico']) ?>"
                                    data-descripcion="<?= e($row['descripcion'] ?? '') ?>"
                                    data-tratamiento="<?= e($row['tratamiento_indicado'] ?? '') ?>">
                                    <i class="ph ph-pencil-simple"></i>
                                </button>
                                <form class="form-delete-consulta" method="POST"
                                    action="<?= e(url("/admin/atletas/{$atleta['atleta_id']}/consultas-medicas/{$row['consulta_id']}/eliminar")) ?>"
                                    style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button type="button" class="btn-delete-premium btn-delete-consulta" title="Eliminar">
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

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Fecha de registro de la consulta en el sistema." data-tooltip-pos="top">Fecha de Consulta</label>
                        <input type="text" name="creado_en" id="input-creado-en" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Fecha en la que ocurrió el suceso o la consulta (máximo hace 10 años, no futura)." data-tooltip-pos="top"><span class="required">*</span> Fecha Suceso</label>
                        <input type="date" name="fecha_suceso" id="input-fecha-suceso" class="form-control" 
                               min="<?= date('Y-m-d', strtotime('-10 years')) ?>" 
                               max="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Fecha estimada de recuperación (posterior al suceso, máximo 3 años a futuro)." data-tooltip-pos="top">Fecha de Recuperación Estimada</label>
                        <input type="date" name="fecha_alta_estimada" id="input-fecha-alta" class="form-control"
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
