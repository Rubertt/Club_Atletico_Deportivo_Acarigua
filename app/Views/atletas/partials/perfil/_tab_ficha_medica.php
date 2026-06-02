            <!-- Tab: Ficha Médica -->
            <div id="tab-ficha" class="tab-content" style="display: none;">
                <?php
                $tieneData = !empty($atleta['grupo_sanguineo']) || !empty($atleta['alergias']) ||
                    !empty($atleta['antecedentes_familiares']) || !empty($atleta['antecedentes_quirurgicos']) ||
                    !empty($atleta['condicion_cronica']) || !empty($atleta['medicacion_actual']);
                ?>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <h3 style="margin: 0;"><i class="ph ph-first-aid"></i> Información Médica</h3>
                    <?php if (can('admin')): ?>
                        <button type="button" class="btn btn-outline btn-sm" id="btn-editar-ficha"
                            style="<?= !$tieneData ? 'display: none;' : '' ?>">
                            <i class="ph ph-pencil-simple"></i> Editar
                        </button>
                    <?php endif; ?>
                </div>

                <?php if ($tieneData): ?>
                    <!-- Vista de Solo Lectura -->
                    <!-- Fila 1: Datos Cortos (Grupo Sanguíneo, Alergias, Condición Crónica, Medicación) -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 16px;">
                        <!-- Grupo Sanguíneo -->
                        <div style="background: var(--color-bg-alt); border-radius: var(--radius); padding: 20px;">
                            <div style="font-size: 12px; color: var(--color-text-muted); margin-bottom: 6px;">Grupo Sanguíneo</div>
                            <div style="font-weight: 600; font-size: 18px; color: var(--color-primary);">
                                <?= !empty($atleta['grupo_sanguineo']) ? e($atleta['grupo_sanguineo']) : '<span style="color:var(--color-text-muted); font-size:14px; font-weight:400;">Sin registrar</span>' ?>
                            </div>
                        </div>

                        <!-- Alergias -->
                        <div style="background: var(--color-bg-alt); border-radius: var(--radius); padding: 20px;">
                            <div style="font-size: 12px; color: var(--color-text-muted); margin-bottom: 6px;">Alergias</div>
                            <div style="font-weight: 500;">
                                <?= !empty($atleta['alergias']) ? e($atleta['alergias']) : '<span style="color:var(--color-text-muted);">Ninguna</span>' ?>
                            </div>
                        </div>

                        <!-- Condición Crónica -->
                        <div style="background: var(--color-bg-alt); border-radius: var(--radius); padding: 20px;">
                            <div style="font-size: 12px; color: var(--color-text-muted); margin-bottom: 6px;">Condición Crónica</div>
                            <div style="font-weight: 500;">
                                <?= !empty($atleta['condicion_cronica']) ? e($atleta['condicion_cronica']) : '<span style="color:var(--color-text-muted);">Ninguna</span>' ?>
                            </div>
                        </div>

                        <!-- Medicación Actual -->
                        <div style="background: var(--color-bg-alt); border-radius: var(--radius); padding: 20px;">
                            <div style="font-size: 12px; color: var(--color-text-muted); margin-bottom: 6px;">Medicación Actual</div>
                            <div style="font-weight: 500;">
                                <?= !empty($atleta['medicacion_actual']) ? e($atleta['medicacion_actual']) : '<span style="color:var(--color-text-muted);">Ninguna</span>' ?>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 2: Datos Largos (Antecedentes Familiares, Antecedentes Quirúrgicos / Lesiones) -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 24px;">
                        <!-- Antecedentes Familiares -->
                        <div style="background: var(--color-bg-alt); border-radius: var(--radius); padding: 20px;">
                            <div style="font-size: 12px; color: var(--color-text-muted); margin-bottom: 6px;">Antecedentes Familiares</div>
                            <div style="font-weight: 500; line-height: 1.6;">
                                <?= !empty($atleta['antecedentes_familiares']) ? nl2br(e($atleta['antecedentes_familiares'])) : '<span style="color:var(--color-text-muted);">Sin antecedentes registrados</span>' ?>
                            </div>
                        </div>

                        <!-- Antecedentes Quirúrgicos -->
                        <div style="background: var(--color-bg-alt); border-radius: var(--radius); padding: 20px;">
                            <div style="font-size: 12px; color: var(--color-text-muted); margin-bottom: 6px;">Antecedentes Quirúrgicos / Lesiones Previas</div>
                            <div style="font-weight: 500; line-height: 1.6;">
                                <?= !empty($atleta['antecedentes_quirurgicos']) ? nl2br(e($atleta['antecedentes_quirurgicos'])) : '<span style="color:var(--color-text-muted);">Sin antecedentes registrados</span>' ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Estado vacío -->
                    <div
                        style="background: var(--color-bg-alt); border-radius: var(--radius); padding: 48px; text-align: center; margin-bottom: 24px;">
                        <i class="ph ph-first-aid"
                            style="font-size: 48px; color: var(--color-text-muted); opacity: 0.4;"></i>
                        <p style="color: var(--color-text-muted); margin-top: 12px; margin-bottom: 16px;">No se ha
                            registrado ficha médica para este atleta.</p>
                        <?php if (can('admin')): ?>
                            <?php $isDis = in_array((int)($atleta['estatus'] ?? 1), [0, 3], true); ?>
                            <button type="button" class="btn btn-primary btn-sm" id="btn-crear-ficha"
                                <?= $isDis ? 'disabled style="cursor: not-allowed; opacity: 0.6;" title="No disponible para atletas inactivos o suspendidos"' : '' ?>>
                                <i class="ph ph-plus"></i> Registrar Ficha Médica
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Sección: Discapacidades -->
                <div style="border-top: 1px solid var(--color-border); padding-top: 24px;">
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h4 style="margin: 0;"><i class="ph ph-wheelchair"></i> Discapacidades</h4>
                        <?php if (can('admin')): ?>
                            <?php $isDis = in_array((int)($atleta['estatus'] ?? 1), [0, 3], true); ?>
                            <button type="button" class="btn btn-outline btn-sm" id="btn-agregar-discapacidad"
                                <?= $isDis ? 'disabled style="cursor: not-allowed; opacity: 0.6;" title="No disponible para atletas inactivos o suspendidos"' : '' ?>>
                                <i class="ph ph-plus"></i> Agregar
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($atleta['discapacidades']) && count($atleta['discapacidades']) > 0): ?>
                        <div class="table-responsive" style="overflow-x: auto;">
                            <table class="data-table" style="min-width: 600px; margin: 0;">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Nro. Carnet</th>
                                        <th>Porcentaje</th>
                                        <th>Fecha Registro</th>
                                        <?php if (can('admin')): ?>
                                            <th style="width:60px;">Acción</th><?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($atleta['discapacidades'] as $disc): ?>
                                        <tr>
                                            <td><?= e($disc['nombre_tipo'] ?? 'Sin tipo') ?></td>
                                            <td><?= !empty($disc['nro_carnet']) ? e($disc['nro_carnet']) : '<span class="text-muted">Sin carnet</span>' ?>
                                            </td>
                                            <td><?= isset($disc['porcentaje_discapacidad']) ? e($disc['porcentaje_discapacidad']) . '%' : '—' ?>
                                            </td>
                                            <td><?= !empty($disc['fecha_registro']) ? e(date('d/m/Y', strtotime($disc['fecha_registro']))) : '—' ?>
                                            </td>
                                            <?php if (can('admin')): ?>
                                                <td>
                                                    <div style="display: flex; gap: 8px;">
                                                        <button type="button" class="btn-icon btn-editar-discapacidad" title="Editar"
                                                            style="color:var(--color-primary); background:none; border:none; cursor:pointer; font-size:16px;"
                                                            data-id="<?= e($disc['discapacidad_id']) ?>"
                                                            data-tipo="<?= e($disc['tipo_discapacidad_id']) ?>"
                                                            data-carnet="<?= e($disc['nro_carnet'] ?? '') ?>"
                                                            data-porcentaje="<?= e($disc['porcentaje_discapacidad'] ?? '') ?>">
                                                            <i class="ph ph-pencil-simple"></i>
                                                        </button>
                                                        <form class="form-delete-disc" method="POST"
                                                            action="<?= e(url("/admin/ficha-medica/{$atleta['atleta_id']}/discapacidad/{$disc['discapacidad_id']}/eliminar")) ?>"
                                                            style="display:inline;">
                                                            <?= csrf_field() ?>
                                                            <button type="button" class="btn-icon btn-delete-disc" title="Eliminar"
                                                                style="color:var(--color-danger); background:none; border:none; cursor:pointer; font-size:16px;">
                                                                <i class="ph ph-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div
                            style="background: var(--color-bg-alt); border-radius: var(--radius); padding: 24px; text-align: center;">
                            <p class="text-muted" style="margin: 0;">No hay discapacidades registradas.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Modal: Editar Ficha Médica -->
            <div id="modal-ficha-medica" class="modal-overlay" style="display:none;">
                <form id="form-ficha-medica" action="<?= e(url("/admin/ficha-medica/{$atleta['atleta_id']}")) ?>" method="POST"
                    class="modal-container" style="max-width: 600px;" novalidate>
                    <div class="modal-header">
                        <h3 class="modal-title"><i class="ph ph-heartbeat"></i> Editar Ficha Médica</h3>
                        <button type="button" class="modal-close" data-close-modal>&times;</button>
                    </div>
                    <?= csrf_field() ?>
                    <div class="modal-body">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Grupo sanguíneo y factor RH del atleta. Crucial en caso de emergencias." data-tooltip-pos="top">Grupo Sanguíneo</label>
                                <select name="grupo_sanguineo" class="form-control">
                                    <option value="">— Seleccionar —</option>
                                    <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $gs): ?>
                                        <option value="<?= $gs ?>" <?= ($atleta['grupo_sanguineo'] ?? '') === $gs ? 'selected' : '' ?>><?= $gs ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Sustancias, medicamentos o alimentos a los que el atleta sea alérgico. Deje en blanco si no tiene." data-tooltip-pos="top">Alergias</label>
                                <input type="text" name="alergias" class="form-control"
                                    value="<?= e($atleta['alergias'] ?? '') ?>" placeholder="Ej: Penicilina, Maní...">
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 16px;">
                            <label class="form-label" data-tooltip="Enfermedades hereditarias o condiciones de salud relevantes en la familia del atleta." data-tooltip-pos="top">Antecedentes Familiares</label>
                            <textarea name="antecedentes_familiares" class="form-control" rows="2"
                                placeholder="Enfermedades hereditarias relevantes..."><?= e($atleta['antecedentes_familiares'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group" style="margin-bottom: 16px;">
                            <label class="form-label" data-tooltip="Cirujías, fracturas o lesiones previas importantes que haya sufrido el atleta." data-tooltip-pos="top">Antecedentes Quirúrgicos / Lesiones Previas</label>
                            <textarea name="antecedentes_quirurgicos" class="form-control" rows="2"
                                placeholder="Operaciones o fracturas importantes..."><?= e($atleta['antecedentes_quirurgicos'] ?? '') ?></textarea>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Diagnósticos médicos activos (ej: asma, diabetes) que el atleta tenga actualmente." data-tooltip-pos="top">Condición Crónica</label>
                                <input type="text" name="condicion_cronica" class="form-control"
                                    value="<?= e($atleta['condicion_cronica'] ?? '') ?>"
                                    placeholder="Ej: Asma, Diabetes...">
                            </div>
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Medicamentos que el atleta toma regularmente. Deje en blanco si no toma ninguno." data-tooltip-pos="top">Medicación Actual</label>
                                <input type="text" name="medicacion_actual" class="form-control"
                                    value="<?= e($atleta['medicacion_actual'] ?? '') ?>"
                                    placeholder="Medicamentos regulares...">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" data-close-modal>Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Guardar</button>
                        <button type="button" class="btn-help" id="btn-help-ficha-medica" title="¿Cómo llenar esta sección?">
                            <i class="ph ph-question"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Modal: Agregar Discapacidad -->
            <div id="modal-discapacidad" class="modal-overlay" style="display:none;">
                <form id="form-discapacidad"
                    action="<?= e(url("/admin/ficha-medica/{$atleta['atleta_id']}/discapacidad")) ?>" method="POST"
                    class="modal-container" style="max-width: 500px;" novalidate>
                    <div class="modal-header">
                        <h3 class="modal-title"><i class="ph ph-wheelchair"></i> <span id="title-discapacidad">Agregar
                                Discapacidad</span></h3>
                        <button type="button" class="modal-close" data-close-modal>&times;</button>
                    </div>
                    <?= csrf_field() ?>
                    <div class="modal-body">
                        <div class="form-group" style="margin-bottom: 16px;">
                            <label class="form-label" data-tooltip="Clasificación de la discapacidad según el carnet nacional oficial." data-tooltip-pos="top"><span class="required">*</span> Tipo de Discapacidad</label>
                            <select name="tipo_discapacidad_id" id="input-tipo-disc" class="form-control" required>
                                <option value="">— Seleccionar —</option>
                                <?php foreach ($tipos_discapacidades ?? [] as $tipo): ?>
                                    <option value="<?= $tipo['tipo_discapacidad_id'] ?>"><?= e($tipo['nombre_tipo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 16px;">
                            <label class="form-label" data-tooltip="Código único de carnet de discapacidad. No puede repetirse en el sistema." data-tooltip-pos="top"><span class="required">*</span> Nro. de Carnet</label>
                            <input type="text" name="nro_carnet" id="input-carnet-disc" class="form-control"
                                placeholder="Ej: V-12345678-D" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" data-tooltip="Grado o porcentaje de discapacidad diagnosticado (número entero entre 1 y 100)." data-tooltip-pos="top"><span class="required">*</span> Porcentaje de Discapacidad</label>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <input type="number" name="porcentaje_discapacidad" id="input-porcentaje-disc"
                                    class="form-control" min="1" max="100" placeholder="Ej: 50" required>
                                <span style="font-weight: 600; color: var(--color-text-muted);">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" data-close-modal>Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="ph ph-check"></i> <span
                                id="submit-text-discapacidad">Agregar</span></button>
                        <button type="button" class="btn-help" id="btn-help-discapacidad" title="¿Cómo llenar esta sección?">
                            <i class="ph ph-question"></i>
                        </button>
                    </div>
                </form>
            </div>

