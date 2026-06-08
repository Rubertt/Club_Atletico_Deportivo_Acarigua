            <!-- Tab: Antropometría -->
            <div id="tab-antropometria" class="tab-content" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <h3 style="margin: 0;"><i class="ph ph-ruler"></i> Evolución Física</h3>
                    <?php $isDis = in_array((int)($atleta['estatus'] ?? 1), [0, 3], true); ?>
                    <?php if (can('admin') || can('entrenador')): ?>
                        <button type="button" class="btn btn-primary btn-sm" id="btn-nueva-medicion"
                            <?= $isDis ? 'disabled style="cursor: not-allowed; opacity: 0.6;" title="No disponible para atletas inactivos o suspendidos"' : '' ?>><i
                                class="ph ph-plus"></i> Nueva Medición</button>
                    <?php endif; ?>
                </div>

                <!-- Mock Chart Container -->
                <div style="height: 300px; background: var(--color-bg-alt); border-radius: var(--radius); border: 1px solid var(--color-border); margin-bottom: 24px; position: relative;"
                    id="chart-antropometria">
                    <!-- ECharts renders here -->
                </div>

                <div class="perfil-table-wrap">
                    <?php 
                        $hasActions = can('admin') || can('entrenador');
                        $gridCols = $hasActions ? '1.2fr 1fr 1fr 1fr 1fr 1fr 1fr 1fr 1.8fr 1fr' : '1.2fr 1fr 1fr 1fr 1fr 1fr 1fr 1fr 1.8fr';
                    ?>
                    <div class="perfil-table-header" style="grid-template-columns: <?= $gridCols ?>;">
                        <div>Fecha</div>
                        <div>Peso (kg)</div>
                        <div>Altura (cm)</div>
                        <div>% Grasa</div>
                        <div>% Musc.</div>
                        <div>Env. (cm)</div>
                        <div>Pierna (cm)</div>
                        <div>Torso (cm)</div>
                        <div>IMC</div>
                        <?php if ($hasActions): ?>
                            <div style="text-align: center;">Acciones</div>
                        <?php endif; ?>
                    </div>
                    <div id="tabla-medidas-body">
                        <?php if (empty($medidas_historial)): ?>
                            <div style="text-align: center; padding: 32px; color: var(--color-text-muted);">No hay mediciones registradas.</div>
                        <?php else:
                            foreach (array_reverse($medidas_historial) as $m): ?>
                                <div class="perfil-table-row" style="grid-template-columns: <?= $gridCols ?>;">
                                    <div class="perfil-row-col">
                                        <span class="perfil-col-label">Fecha</span>
                                        <span><?= e(date('d/m/Y', strtotime($m['fecha_medicion']))) ?></span>
                                    </div>
                                    <div class="perfil-row-col">
                                        <span class="perfil-col-label">Peso (kg)</span>
                                        <span><?= e($m['peso'] ?? '—') ?></span>
                                    </div>
                                    <div class="perfil-row-col">
                                        <span class="perfil-col-label">Altura (cm)</span>
                                        <span><?= e($m['altura'] ?? '—') ?></span>
                                    </div>
                                    <div class="perfil-row-col">
                                        <span class="perfil-col-label">% Grasa</span>
                                        <span><?= !empty($m['porcentaje_grasa']) ? e($m['porcentaje_grasa']) . '%' : '—' ?></span>
                                    </div>
                                    <div class="perfil-row-col">
                                        <span class="perfil-col-label">% Musc.</span>
                                        <span><?= !empty($m['porcentaje_musculatura']) ? e($m['porcentaje_musculatura']) . '%' : '—' ?></span>
                                    </div>
                                    <div class="perfil-row-col">
                                        <span class="perfil-col-label">Env. (cm)</span>
                                        <span><?= e($m['envergadura'] ?? '—') ?></span>
                                    </div>
                                    <div class="perfil-row-col">
                                        <span class="perfil-col-label">Pierna (cm)</span>
                                        <span><?= e($m['largo_de_pierna'] ?? '—') ?></span>
                                    </div>
                                    <div class="perfil-row-col">
                                        <span class="perfil-col-label">Torso (cm)</span>
                                        <span><?= e($m['largo_de_torso'] ?? '—') ?></span>
                                    </div>
                                    <div class="perfil-row-col">
                                        <span class="perfil-col-label">IMC</span>
                                        <span>
                                            <?php 
                                            $peso = (float)($m['peso'] ?? 0);
                                            $altura = (float)($m['altura'] ?? 0);
                                            $altura = $altura / 100;
                                            if ($peso > 0 && $altura > 0):
                                                $imc = $peso / ($altura * $altura);
                                                $badgeClass = 'success';
                                                $label = 'Normal';
                                                if ($imc < 18.5) {
                                                    $badgeClass = 'warning';
                                                    $label = 'Bajo peso';
                                                } elseif ($imc >= 25 && $imc < 30) {
                                                    $badgeClass = 'warning';
                                                    $label = 'Sobrepeso';
                                                    } elseif ($imc >= 30) {
                                                    $badgeClass = 'danger';
                                                    $label = 'Obesidad';
                                                }
                                                ?>
                                                <span class="badge badge-<?= $badgeClass ?>"><?= number_format($imc, 1) ?> (<?= $label ?>)</span>
                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <?php if ($hasActions): ?>
                                        <div class="perfil-row-col">
                                            <span class="perfil-col-label">Acciones</span>
                                            <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                                                <button type="button" class="btn-icon-premium btn-editar-medicion" 
                                                    data-id="<?= $m['medidas_id'] ?>"
                                                    data-fecha="<?= e($m['fecha_medicion']) ?>"
                                                    data-peso="<?= e($m['peso']) ?>"
                                                    data-altura="<?= e($m['altura']) ?>"
                                                    data-grasa="<?= e($m['porcentaje_grasa']) ?>"
                                                    data-musculo="<?= e($m['porcentaje_musculatura']) ?>"
                                                    data-envergadura="<?= e($m['envergadura']) ?>"
                                                    data-pierna="<?= e($m['largo_de_pierna']) ?>"
                                                    data-torso="<?= e($m['largo_de_torso']) ?>"
                                                    title="Editar medición"
                                                    style="width: 28px; height: 28px; font-size: 14px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                                    <i class="ph ph-pencil-simple"></i>
                                                </button>
                                                <button type="button" class="btn-icon-premium btn-eliminar-medicion"
                                                    data-id="<?= $m['medidas_id'] ?>"
                                                    title="Eliminar medición"
                                                    style="width: 28px; height: 28px; font-size: 14px; color: var(--color-danger); border-color: rgba(239, 68, 68, 0.2); padding: 0; display: flex; align-items: center; justify-content: center;">
                                                    <i class="ph ph-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>

            <!-- Modal: Nueva Medición -->
            <div id="modal-medicion" class="modal-overlay" style="display:none;">
                <form id="form-medicion" action="<?= e(url("/admin/medidas/atleta/{$atleta['atleta_id']}")) ?>"
                    method="POST" class="modal-container" style="max-width: 600px;" novalidate>
                    <div class="modal-header">
                        <h3 class="modal-title"><i class="ph ph-ruler"></i> Nueva Medición Antropométrica</h3>
                        <button type="button" class="modal-close" data-close-modal>&times;</button>
                    </div>
                    <?= csrf_field() ?>
                    <div class="modal-body">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Fecha en la que se tomaron las medidas corporales del atleta. No puede ser futura." data-tooltip-pos="top"><span class="required">*</span> Fecha de Medición</label>
                                <input type="date" name="fecha_medicion" class="form-control"
                                    value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Peso corporal en kilogramos. Se usará junto con la estatura para calcular el IMC." data-tooltip-pos="top">Peso (kg)</label>
                                <input type="number" step="0.1" name="peso" class="form-control" placeholder="Ej: 70.5">
                            </div>
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Estatura de pie en centímetros. Junto al peso calcula el IMC (ej: 175.5 cm)." data-tooltip-pos="top">Altura (cm)</label>
                                <input type="number" step="0.1" name="altura" class="form-control"
                                    placeholder="Ej: 175">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Porcentaje estimado de tejido graso. Obtenido mediante bioimpedancia o pliegues cutáneos." data-tooltip-pos="top">% Grasa</label>
                                <input type="number" step="0.1" name="porcentaje_grasa" class="form-control"
                                    placeholder="Ej: 12.5">
                            </div>
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Porcentaje estimado de masa muscular magra del atleta." data-tooltip-pos="top">% Musculatura</label>
                                <input type="number" step="0.1" name="porcentaje_musculatura" class="form-control"
                                    placeholder="Ej: 40.2">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Largo de brazos totalmente extendidos (punta a punta) en centímetros." data-tooltip-pos="top">Envergadura (cm)</label>
                                <input type="number" step="0.1" name="envergadura" class="form-control"
                                    placeholder="Ej: 180">
                            </div>
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Longitud de la extremidad inferior desde la cadera hasta el pie en centímetros." data-tooltip-pos="top">Pierna (cm)</label>
                                <input type="number" step="0.1" name="largo_de_pierna" class="form-control"
                                    placeholder="Ej: 90">
                            </div>
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Longitud del torso desde los hombros hasta las caderas en centímetros." data-tooltip-pos="top">Torso (cm)</label>
                                <input type="number" step="0.1" name="largo_de_torso" class="form-control"
                                    placeholder="Ej: 50">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" data-close-modal>Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="ph ph-check"></i> Registrar Medición</button>
                    </div>
                </form>
            </div>

            <!-- Modal: Editar Medición -->
            <div id="modal-medicion-editar" class="modal-overlay" style="display:none;">
                <form id="form-medicion-editar" action="" method="POST" class="modal-container" style="max-width: 600px;" novalidate>
                    <div class="modal-header">
                        <h3 class="modal-title"><i class="ph ph-ruler"></i> Editar Medición Antropométrica</h3>
                        <button type="button" class="modal-close" data-close-modal>&times;</button>
                    </div>
                    <?= csrf_field() ?>
                    <div class="modal-body">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Fecha en la que se tomaron las medidas corporales del atleta. No puede ser futura." data-tooltip-pos="top"><span class="required">*</span> Fecha de Medición</label>
                                <input type="date" name="fecha_medicion" id="edit-fecha_medicion" class="form-control" max="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Peso corporal en kilogramos. Se usará junto con la estatura para calcular el IMC." data-tooltip-pos="top">Peso (kg)</label>
                                <input type="number" step="0.1" name="peso" id="edit-peso" class="form-control" placeholder="Ej: 70.5">
                            </div>
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Estatura de pie en centímetros. Junto al peso calcula el IMC (ej: 175.5 cm)." data-tooltip-pos="top">Altura (cm)</label>
                                <input type="number" step="0.1" name="altura" id="edit-altura" class="form-control" placeholder="Ej: 175">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Porcentaje estimado de tejido graso. Obtenido mediante bioimpedancia o pliegues cutáneos." data-tooltip-pos="top">% Grasa</label>
                                <input type="number" step="0.1" name="porcentaje_grasa" id="edit-porcentaje_grasa" class="form-control" placeholder="Ej: 12.5">
                            </div>
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Porcentaje estimado de masa muscular magra del atleta." data-tooltip-pos="top">% Musculatura</label>
                                <input type="number" step="0.1" name="porcentaje_musculatura" id="edit-porcentaje_musculatura" class="form-control" placeholder="Ej: 40.2">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Largo de brazos totalmente extendidos (punta a punta) en centímetros." data-tooltip-pos="top">Envergadura (cm)</label>
                                <input type="number" step="0.1" name="envergadura" id="edit-envergadura" class="form-control" placeholder="Ej: 180">
                            </div>
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Longitud de la extremidad inferior desde la cadera hasta el pie en centímetros." data-tooltip-pos="top">Pierna (cm)</label>
                                <input type="number" step="0.1" name="largo_de_pierna" id="edit-largo_de_pierna" class="form-control" placeholder="Ej: 90">
                            </div>
                            <div class="form-group">
                                <label class="form-label" data-tooltip="Longitud del torso desde los hombros hasta las caderas en centímetros." data-tooltip-pos="top">Torso (cm)</label>
                                <input type="number" step="0.1" name="largo_de_torso" id="edit-largo_de_torso" class="form-control" placeholder="Ej: 50">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" data-close-modal>Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="ph ph-check"></i> Guardar Cambios</button>
                    </div>
                </form>
            </div>
