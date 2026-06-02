            <!-- Tab: Pruebas Físicas -->
            <div id="tab-pruebas" class="tab-content" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <h3 style="margin: 0;"><i class="ph ph-chart-line-up"></i> Rendimiento Físico</h3>
                    <?php $isDis = in_array((int)($atleta['estatus'] ?? 1), [0, 3], true); ?>
                    <button type="button" class="btn btn-primary btn-sm" id="btn-nueva-prueba"
                        <?= $isDis ? 'disabled style="cursor: not-allowed; opacity: 0.6;" title="No disponible para atletas inactivos o suspendidos"' : '' ?>><i
                            class="ph ph-plus"></i> Registrar Prueba</button>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
                    <div style="height: 350px; background: var(--color-bg-alt); border-radius: var(--radius); border: 1px solid var(--color-border);"
                        id="chart-radar-pruebas"></div>
                    <div style="background: var(--color-bg-alt); border-radius: var(--radius); padding: 24px;">
                        <h4 style="margin-top: 0;">Última Evaluación</h4>
                        <?php
                        $ultima = !empty($pruebas_historial) ? $pruebas_historial[0] : null;
                        ?>
                        <?php if ($ultima): ?>
                            <ul
                                style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 16px;">
                                <li>
                                    <div
                                        style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 13px;">
                                        <span>Fuerza</span> <strong><?= e($ultima['test_de_fuerza_raw'] ?? '—') ?> cm (<?= e($ultima['test_de_fuerza'] ?? 0) ?>/100)</strong>
                                    </div>
                                    <div
                                        style="height: 6px; background: var(--color-border); border-radius: 3px; overflow: hidden;">
                                        <div
                                            style="height: 100%; width: <?= e($ultima['test_de_fuerza'] ?? 0) ?>%; background: var(--color-primary);">
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div
                                        style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 13px;">
                                        <span>Resistencia</span>
                                        <strong><?= e($ultima['test_resistencia_raw'] ?? '—') ?> m (<?= e($ultima['test_resistencia'] ?? 0) ?>/100)</strong></div>
                                    <div
                                        style="height: 6px; background: var(--color-border); border-radius: 3px; overflow: hidden;">
                                        <div
                                            style="height: 100%; width: <?= e($ultima['test_resistencia'] ?? 0) ?>%; background: #10B981;">
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div
                                        style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 13px;">
                                        <span>Velocidad</span> <strong><?= e($ultima['test_velocidad_raw'] ?? '—') ?> s (<?= e($ultima['test_velocidad'] ?? 0) ?>/100)</strong>
                                    </div>
                                    <div
                                        style="height: 6px; background: var(--color-border); border-radius: 3px; overflow: hidden;">
                                        <div
                                            style="height: 100%; width: <?= e($ultima['test_velocidad'] ?? 0) ?>%; background: #F59E0B;">
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div
                                        style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 13px;">
                                        <span>Coordinación</span>
                                        <strong><?= e($ultima['test_coordinacion_raw'] ?? '—') ?> s (<?= e($ultima['test_coordinacion'] ?? 0) ?>/100)</strong></div>
                                    <div
                                        style="height: 6px; background: var(--color-border); border-radius: 3px; overflow: hidden;">
                                        <div
                                            style="height: 100%; width: <?= e($ultima['test_coordinacion'] ?? 0) ?>%; background: #8B5CF6;">
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div
                                        style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 13px;">
                                        <span>Reacción</span>
                                        <strong><?= e($ultima['test_de_reaccion_raw'] ?? '—') ?> ms (<?= e($ultima['test_de_reaccion'] ?? 0) ?>/100)</strong></div>
                                    <div
                                        style="height: 6px; background: var(--color-border); border-radius: 3px; overflow: hidden;">
                                        <div
                                            style="height: 100%; width: <?= e($ultima['test_de_reaccion'] ?? 0) ?>%; background: #EC4899;">
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <div style="margin-top: 20px; font-size: 12px; color: var(--color-text-muted);">
                                <i class="ph ph-calendar"></i> Evaluado el:
                                <?= e(date('d/m/Y', strtotime($ultima['fecha_evento']))) ?>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: 32px; color: var(--color-text-muted);">
                                <i class="ph ph-chart-bar"
                                    style="font-size: 40px; opacity: 0.3; margin-bottom: 12px; display: block;"></i>
                                No hay pruebas registradas aún.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tabla de Historial de Pruebas Físicas -->
                <div class="table-responsive" style="overflow-x: auto;">
                    <table class="data-table" id="tabla-pruebas" style="min-width: 650px;">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Fuerza (CMJ)</th>
                                <th>Resist. (Yo-Yo)</th>
                                <th>Veloc. (30m)</th>
                                <th>Coord. (Conos)</th>
                                <th>Reacc. (Cognit.)</th>
                                <?php if (can('admin') || can('entrenador')): ?>
                                    <th style="width: 110px; text-align: center;">Acciones</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pruebas_historial)): ?>
                                <tr>
                                    <td colspan="<?= (can('admin') || can('entrenador')) ? 7 : 6 ?>"
                                        style="text-align: center; padding: 32px; color: var(--color-text-muted);">No hay
                                        pruebas registradas aún.</td>
                                </tr>
                            <?php else:
                                foreach ($pruebas_historial as $p): ?>
                                    <tr>
                                        <td>
                                            <div style="font-weight: 500; color: var(--color-text);">
                                                <?= e(date('d/m/Y', strtotime($p['fecha_evento']))) ?>
                                            </div>
                                            <div style="font-size: 12px; color: var(--color-text-muted);">
                                                <?= e($p['nombre_evento'] ?? 'Registro Manual') ?>
                                            </div>
                                            <?php if (!empty($p['nombre_entrenador'])): ?>
                                                <div style="font-size: 11px; color: var(--color-primary); margin-top: 2px;">
                                                    <i class="ph ph-user-gear"></i> <?= e($p['nombre_entrenador'] . ' ' . $p['apellido_entrenador']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--color-primary);"></div>
                                                <?= e($p['test_de_fuerza_raw'] !== null ? $p['test_de_fuerza_raw'] . ' cm (' . $p['test_de_fuerza'] . '/100)' : '—') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <div style="width: 8px; height: 8px; border-radius: 50%; background: #10B981;"></div>
                                                <?= e($p['test_resistencia_raw'] !== null ? $p['test_resistencia_raw'] . ' m (' . $p['test_resistencia'] . '/100)' : '—') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <div style="width: 8px; height: 8px; border-radius: 50%; background: #F59E0B;"></div>
                                                <?= e($p['test_velocidad_raw'] !== null ? $p['test_velocidad_raw'] . ' s (' . $p['test_velocidad'] . '/100)' : '—') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <div style="width: 8px; height: 8px; border-radius: 50%; background: #8B5CF6;"></div>
                                                <?= e($p['test_coordinacion_raw'] !== null ? $p['test_coordinacion_raw'] . ' s (' . $p['test_coordinacion'] . '/100)' : '—') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <div style="width: 8px; height: 8px; border-radius: 50%; background: #EC4899;"></div>
                                                <?= e($p['test_de_reaccion_raw'] !== null ? $p['test_de_reaccion_raw'] . ' ms (' . $p['test_de_reaccion'] . '/100)' : '—') ?>
                                            </div>
                                        </td>
                                        <?php if (can('admin') || can('entrenador')): ?>
                                            <td style="text-align: center;">
                                                <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                                                    <button type="button" class="btn-icon-premium btn-editar-prueba" 
                                                        data-id="<?= $p['test_id'] ?>"
                                                        data-fecha="<?= e(date('Y-m-d', strtotime($p['fecha_evento']))) ?>"
                                                        data-entrenador-id="<?= e($p['usuario_id'] ?? '') ?>"
                                                        data-fuerza="<?= e($p['test_de_fuerza_raw'] ?? '') ?>"
                                                        data-resistencia="<?= e($p['test_resistencia_raw'] ?? '') ?>"
                                                        data-velocidad="<?= e($p['test_velocidad_raw'] ?? '') ?>"
                                                        data-coordinacion="<?= e($p['test_coordinacion_raw'] ?? '') ?>"
                                                        data-reaccion="<?= e($p['test_de_reaccion_raw'] ?? '') ?>"
                                                        title="Editar prueba"
                                                        style="width: 28px; height: 28px; font-size: 14px;">
                                                        <i class="ph ph-pencil-simple"></i>
                                                    </button>
                                                    <button type="button" class="btn-icon-premium btn-eliminar-prueba"
                                                        data-id="<?= $p['test_id'] ?>"
                                                        title="Eliminar prueba"
                                                        style="width: 28px; height: 28px; font-size: 14px; color: var(--color-danger); border-color: rgba(239, 68, 68, 0.2);">
                                                        <i class="ph ph-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal: Registrar Prueba Física -->
            <div id="modal-prueba" class="modal-overlay" style="display:none;">
                <form id="form-prueba" action="<?= e(url("/admin/resultados-pruebas/atleta/{$atleta['atleta_id']}")) ?>"
                    method="POST" class="modal-container" style="max-width: 550px;" novalidate>
                    <div class="modal-header">
                        <h3 class="modal-title"><i class="ph ph-chart-line-up"></i> Registrar Evaluación Física</h3>
                        <button type="button" class="modal-close" data-close-modal>&times;</button>
                    </div>
                    <?= csrf_field() ?>
                    <div class="modal-body">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px;">
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Fecha en la que se realizó la evaluación física. No puede ser futura." data-tooltip-pos="top"><span class="required">*</span> Fecha de Evaluación</label>
                                <input type="date" name="fecha_evaluacion" class="form-control"
                                    value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Profesional técnico (entrenador o directivo) que supervisó la toma de los resultados." data-tooltip-pos="top"><span class="required">*</span> Entrenador</label>
                                <select name="entrenador_id" class="form-control" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($entrenadores as $entrenador): ?>
                                        <option value="<?= e($entrenador['usuario_id']) ?>"><?= e($entrenador['nombre'] . ' ' . $entrenador['apellido']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px;">
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Altura alcanzada en el salto vertical de fuerza explosiva (CMJ). Rango válido: 1 a 100 cm." data-tooltip-pos="top">Test de Fuerza (Salto CMJ - cm)</label>
                                <input type="number" step="0.01" name="test_de_fuerza" class="form-control" min="1" max="100"
                                    placeholder="Rango Élite (100%): 20 - 45 cm">
                            </div>
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Distancia acumulada en el Yo-Yo Test de resistencia aeróbica. Rango válido: 1 a 1000 metros." data-tooltip-pos="top">Test de Resistencia (Yo-Yo Test - m)</label>
                                <input type="number" step="1" name="test_resistencia" class="form-control" min="1" max="1000"
                                    placeholder="Rango Élite (100%): 600 - 2200 m">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px;">
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Tiempo en la prueba de velocidad de 30 metros llanos. Rango válido: 1.00 a 10.00 segundos." data-tooltip-pos="top">Test de Velocidad (Sprint 30m - s)</label>
                                <input type="number" step="0.01" name="test_velocidad" class="form-control" min="1" max="10"
                                    placeholder="Rango Élite (100%): 5.20 - 4.10 s">
                            </div>
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Tiempo en el circuito de conos para medir agilidad y coordinación. Rango válido: 1.00 a 100.00 segundos." data-tooltip-pos="top">Test de Coordinación (Conos - s)</label>
                                <input type="number" step="0.01" name="test_coordinacion" class="form-control" min="1" max="100"
                                    placeholder="Rango Élite (100%): 22.50 - 16.50 s">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr; gap: 20px;">
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Tiempo de respuesta ante estímulos visuales o auditivos en milisegundos. Rango: 100 a 1000 ms." data-tooltip-pos="top">Test de Reacción (App Cognitiva - ms)</label>
                                <input type="number" step="1" name="test_de_reaccion" class="form-control" min="100" max="1000"
                                    placeholder="Rango Élite (100%): 450 - 220 ms">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" data-close-modal>Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="ph ph-check"></i> Guardar Resultados</button>
                        <button type="button" class="btn-help" id="btn-help-prueba" title="¿Cómo llenar esta sección?">
                            <i class="ph ph-question"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Modal: Editar Prueba Física -->
            <div id="modal-prueba-editar" class="modal-overlay" style="display:none;">
                <form id="form-prueba-editar" action="" method="POST" class="modal-container" style="max-width: 550px;" novalidate>
                    <div class="modal-header">
                        <h3 class="modal-title"><i class="ph ph-chart-line-up"></i> Editar Evaluación Física</h3>
                        <button type="button" class="modal-close" data-close-modal>&times;</button>
                    </div>
                    <?= csrf_field() ?>
                    <div class="modal-body">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px;">
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Fecha en la que se realizó la evaluación física. No puede ser futura." data-tooltip-pos="top"><span class="required">*</span> Fecha de Evaluación</label>
                                <input type="date" name="fecha_evaluacion" id="edit-prueba-fecha" class="form-control" max="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Profesional técnico (entrenador o directivo) que supervisó la toma de los resultados." data-tooltip-pos="top"><span class="required">*</span> Entrenador</label>
                                <select name="entrenador_id" id="edit-prueba-entrenador" class="form-control" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($entrenadores as $entrenador): ?>
                                        <option value="<?= e($entrenador['usuario_id']) ?>"><?= e($entrenador['nombre'] . ' ' . $entrenador['apellido']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px;">
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Altura alcanzada en el salto vertical de fuerza explosiva (CMJ). Rango válido: 1 a 100 cm." data-tooltip-pos="top">Test de Fuerza (Salto CMJ - cm)</label>
                                <input type="number" step="0.01" name="test_de_fuerza" id="edit-prueba-fuerza" class="form-control" min="1" max="100" placeholder="Rango Élite (100%): 20 - 45 cm">
                            </div>
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Distancia acumulada en el Yo-Yo Test de resistencia aeróbica. Rango válido: 1 a 1000 metros." data-tooltip-pos="top">Test de Resistencia (Yo-Yo Test - m)</label>
                                <input type="number" step="1" name="test_resistencia" id="edit-prueba-resistencia" class="form-control" min="1" max="1000" placeholder="Rango Élite (100%): 600 - 2200 m">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px;">
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Tiempo en la prueba de velocidad de 30 metros llanos. Rango válido: 1.00 a 10.00 segundos." data-tooltip-pos="top">Test de Velocidad (Sprint 30m - s)</label>
                                <input type="number" step="0.01" name="test_velocidad" id="edit-prueba-velocidad" class="form-control" min="1" max="10" placeholder="Rango Élite (100%): 5.20 - 4.10 s">
                            </div>
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Tiempo en el circuito de conos para medir agilidad y coordinación. Rango válido: 1.00 a 100.00 segundos." data-tooltip-pos="top">Test de Coordinación (Conos - s)</label>
                                <input type="number" step="0.01" name="test_coordinacion" id="edit-prueba-coordinacion" class="form-control" min="1" max="100" placeholder="Rango Élite (100%): 22.50 - 16.50 s">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr; gap: 20px;">
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Tiempo de respuesta ante estímulos visuales o auditivos en milisegundos. Rango: 100 a 1000 ms." data-tooltip-pos="top">Test de Reacción (App Cognitiva - ms)</label>
                                <input type="number" step="1" name="test_de_reaccion" id="edit-prueba-reaccion" class="form-control" min="100" max="1000" placeholder="Rango Élite (100%): 450 - 220 ms">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" data-close-modal>Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="ph ph-check"></i> Guardar Cambios</button>
                        <button type="button" class="btn-help" id="btn-help-prueba-editar" title="¿Cómo llenar esta sección?">
                            <i class="ph ph-question"></i>
                        </button>
                    </div>
                </form>
            </div>

