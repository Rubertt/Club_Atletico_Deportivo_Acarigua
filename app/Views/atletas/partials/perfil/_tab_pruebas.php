            <!-- Tab: Pruebas Físicas -->
            <style>
                #tab-pruebas {
                    font-size: 1.2rem;
                }
                #tab-pruebas h3 {
                    font-size: 20px !important;
                }
                #tab-pruebas h4 {
                    font-size: 18px !important;
                }
                #tab-pruebas .perfil-table-header {
                    font-size: 15.6px !important;
                }
                #tab-pruebas .perfil-col-label {
                    font-size: 13.2px !important;
                }
                #tab-pruebas .perfil-table-row {
                    font-size: 15.6px !important;
                }
            </style>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <h3 style="margin: 0;"><i class="ph ph-chart-line-up"></i> Rendimiento Físico</h3>
                    <?php if (!empty($pruebas_historial)): ?>
                        <a href="<?= e(url('/admin/resultados-pruebas/sesion/' . $pruebas_historial[0]['actividad_id'])) ?>" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 8px;">
                            <i class="ph ph-eye"></i> Ver Sesión Reciente
                        </a>
                    <?php endif; ?>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; margin-bottom: 24px;">
                    <!-- Gráfico Internacional -->
                    <div style="height: 350px; background: var(--color-bg-alt); border-radius: var(--radius); border: 1px solid var(--color-border); padding: 16px; display: flex; flex-direction: column;">
                        <h4 style="margin-top: 0; margin-bottom: 12px; text-align: center; font-size: 17px;"><i class="ph ph-globe"></i> Comparación Internacional (Élite)</h4>
                        <div id="chart-radar-pruebas" style="flex: 1; width: 100%;" data-historial="<?= e(json_encode($pruebas_historial ?? [])) ?>"></div>
                    </div>
                    
                    <!-- Gráfico Nacional -->
                    <div style="height: 350px; background: var(--color-bg-alt); border-radius: var(--radius); border: 1px solid var(--color-border); padding: 16px; display: flex; flex-direction: column;">
                        <h4 style="margin-top: 0; margin-bottom: 12px; text-align: center; font-size: 17px;"><i class="ph ph-flag"></i> Comparación Nacional (FUTVE)</h4>
                        <div id="chart-radar-pruebas-nacional" style="flex: 1; width: 100%;" data-historial="<?= e(json_encode($pruebas_historial ?? [])) ?>"></div>
                    </div>
                </div>

                 <!-- Tarjeta de Última Evaluación -->
                <div style="background: var(--color-bg-alt); border-radius: var(--radius); padding: 24px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <h4 style="margin-top: 0; margin-bottom: 16px;"><i class="ph ph-medal"></i> Última Evaluación</h4>
                        <?php
                        $ultima = !empty($pruebas_historial) ? $pruebas_historial[0] : null;
                        ?>
                        <?php if ($ultima): ?>
                            <div style="display: flex; gap: 20px; width: 100%;">
                                <!-- Columna 1: Fuerza, Resistencia, Velocidad -->
                                <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 14px;">
                                    <!-- Fuerza -->
                                    <div>
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 16px;">
                                            <span>Fuerza (CMJ)</span> <strong><?= e($ultima['test_de_fuerza_raw'] !== null ? $ultima['test_de_fuerza_raw'] . ' cm' : '—') ?></strong>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                            <span style="font-size: 13px; color: var(--color-text-muted); width: 30px; flex-shrink: 0;">Int:</span>
                                            <div style="flex: 1; height: 6px; background: var(--color-border); border-radius: 3px; overflow: hidden; position: relative;">
                                                <div style="height: 100%; width: <?= e($ultima['test_de_fuerza'] ?? 0) ?>%; background: var(--color-primary);"></div>
                                            </div>
                                            <span style="font-size: 13px; font-weight: 600; width: 45px; text-align: right; flex-shrink: 0;"><?= e($ultima['test_de_fuerza'] ?? 0) ?>/100</span>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span style="font-size: 13px; color: var(--color-text-muted); width: 30px; flex-shrink: 0;">Nac:</span>
                                            <div style="flex: 1; height: 6px; background: var(--color-border); border-radius: 3px; overflow: hidden; position: relative;">
                                                <div style="height: 100%; width: <?= e($ultima['test_de_fuerza_nac'] ?? 0) ?>%; background: var(--color-primary); opacity: 0.7;"></div>
                                            </div>
                                            <span style="font-size: 13px; font-weight: 600; width: 45px; text-align: right; flex-shrink: 0;"><?= e($ultima['test_de_fuerza_nac'] ?? 0) ?>/100</span>
                                        </div>
                                    </div>

                                    <!-- Resistencia -->
                                    <div>
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 16px;">
                                            <span>Resistencia (Yo-Yo)</span> <strong><?= e($ultima['test_resistencia_raw'] !== null ? $ultima['test_resistencia_raw'] . ' m' : '—') ?></strong>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                            <span style="font-size: 13px; color: var(--color-text-muted); width: 30px; flex-shrink: 0;">Int:</span>
                                            <div style="flex: 1; height: 6px; background: var(--color-border); border-radius: 3px; overflow: hidden; position: relative;">
                                                <div style="height: 100%; width: <?= e($ultima['test_resistencia'] ?? 0) ?>%; background: #10B981;"></div>
                                            </div>
                                            <span style="font-size: 13px; font-weight: 600; width: 45px; text-align: right; flex-shrink: 0;"><?= e($ultima['test_resistencia'] ?? 0) ?>/100</span>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span style="font-size: 13px; color: var(--color-text-muted); width: 30px; flex-shrink: 0;">Nac:</span>
                                            <div style="flex: 1; height: 6px; background: var(--color-border); border-radius: 3px; overflow: hidden; position: relative;">
                                                <div style="height: 100%; width: <?= e($ultima['test_resistencia_nac'] ?? 0) ?>%; background: #10B981; opacity: 0.7;"></div>
                                            </div>
                                            <span style="font-size: 13px; font-weight: 600; width: 45px; text-align: right; flex-shrink: 0;"><?= e($ultima['test_resistencia_nac'] ?? 0) ?>/100</span>
                                        </div>
                                    </div>

                                    <!-- Velocidad -->
                                    <div>
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 16px;">
                                            <span>Velocidad (30m)</span> <strong><?= e($ultima['test_velocidad_raw'] !== null ? $ultima['test_velocidad_raw'] . ' s' : '—') ?></strong>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                            <span style="font-size: 13px; color: var(--color-text-muted); width: 30px; flex-shrink: 0;">Int:</span>
                                            <div style="flex: 1; height: 6px; background: var(--color-border); border-radius: 3px; overflow: hidden; position: relative;">
                                                <div style="height: 100%; width: <?= e($ultima['test_velocidad'] ?? 0) ?>%; background: #F59E0B;"></div>
                                            </div>
                                            <span style="font-size: 13px; font-weight: 600; width: 45px; text-align: right; flex-shrink: 0;"><?= e($ultima['test_velocidad'] ?? 0) ?>/100</span>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span style="font-size: 13px; color: var(--color-text-muted); width: 30px; flex-shrink: 0;">Nac:</span>
                                            <div style="flex: 1; height: 6px; background: var(--color-border); border-radius: 3px; overflow: hidden; position: relative;">
                                                <div style="height: 100%; width: <?= e($ultima['test_velocidad_nac'] ?? 0) ?>%; background: #F59E0B; opacity: 0.7;"></div>
                                            </div>
                                            <span style="font-size: 13px; font-weight: 600; width: 45px; text-align: right; flex-shrink: 0;"><?= e($ultima['test_velocidad_nac'] ?? 0) ?>/100</span>
                                        </div>
                                    </div> <!-- Fin Columna 1 -->
                                
                                <!-- Columna 2: Coordinación, Reacción -->
                                <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 14px;">
                                        <!-- Coordinación -->
                                        <div>
                                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 16px;">
                                                <span>Coordinación</span> <strong><?= e($ultima['test_coordinacion_raw'] !== null ? $ultima['test_coordinacion_raw'] . ' s' : '—') ?></strong>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                                <span style="font-size: 13px; color: var(--color-text-muted); width: 30px; flex-shrink: 0;">Int:</span>
                                                <div style="flex: 1; height: 6px; background: var(--color-border); border-radius: 3px; overflow: hidden; position: relative;">
                                                    <div style="height: 100%; width: <?= e($ultima['test_coordinacion'] ?? 0) ?>%; background: #8B5CF6;"></div>
                                                </div>
                                                <span style="font-size: 13px; font-weight: 600; width: 45px; text-align: right; flex-shrink: 0;"><?= e($ultima['test_coordinacion'] ?? 0) ?>/100</span>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span style="font-size: 13px; color: var(--color-text-muted); width: 30px; flex-shrink: 0;">Nac:</span>
                                                <div style="flex: 1; height: 6px; background: var(--color-border); border-radius: 3px; overflow: hidden; position: relative;">
                                                    <div style="height: 100%; width: <?= e($ultima['test_coordinacion_nac'] ?? 0) ?>%; background: #8B5CF6; opacity: 0.7;"></div>
                                                </div>
                                                <span style="font-size: 13px; font-weight: 600; width: 45px; text-align: right; flex-shrink: 0;"><?= e($ultima['test_coordinacion_nac'] ?? 0) ?>/100</span>
                                            </div>
                                        </div>

                                        <!-- Reacción -->
                                        <div>
                                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 16px;">
                                                <span>Reacción</span> <strong><?= e($ultima['test_de_reaccion_raw'] !== null ? $ultima['test_de_reaccion_raw'] . ' ms' : '—') ?></strong>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                                <span style="font-size: 13px; color: var(--color-text-muted); width: 30px; flex-shrink: 0;">Int:</span>
                                                <div style="flex: 1; height: 6px; background: var(--color-border); border-radius: 3px; overflow: hidden; position: relative;">
                                                    <div style="height: 100%; width: <?= e($ultima['test_de_reaccion'] ?? 0) ?>%; background: #EC4899;"></div>
                                                </div>
                                                <span style="font-size: 13px; font-weight: 600; width: 45px; text-align: right; flex-shrink: 0;"><?= e($ultima['test_de_reaccion'] ?? 0) ?>/100</span>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span style="font-size: 13px; color: var(--color-text-muted); width: 30px; flex-shrink: 0;">Nac:</span>
                                                <div style="flex: 1; height: 6px; background: var(--color-border); border-radius: 3px; overflow: hidden; position: relative;">
                                                    <div style="height: 100%; width: <?= e($ultima['test_de_reaccion_nac'] ?? 0) ?>%; background: #EC4899; opacity: 0.7;"></div>
                                                </div>
                                                <span style="font-size: 13px; font-weight: 600; width: 45px; text-align: right; flex-shrink: 0;"><?= e($ultima['test_de_reaccion_nac'] ?? 0) ?>/100</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div style="margin-top: 20px; font-size: 14px; color: var(--color-text-muted);">
                                    <i class="ph ph-calendar"></i> Evaluado el:
                                    <?= e(date('d/m/Y', strtotime($ultima['fecha_evento']))) ?>
                                </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: 32px; color: var(--color-text-muted); flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                <i class="ph ph-chart-bar" style="font-size: 48px; opacity: 0.3; margin-bottom: 12px; display: block;"></i>
                                No hay pruebas registradas aún.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tabla de Historial de Pruebas Físicas -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; margin-top: 24px;">
                    <h4 style="margin: 0;"><i class="ph ph-clock-counter-clockwise"></i> Historial de Evaluaciones</h4>
                    <span style="font-size: 14.4px; color: var(--color-text-muted);">
                        <strong>Leyenda:</strong> <span style="color: var(--color-primary);">I</span>: Intl. (Élite) | <span style="color: var(--color-primary); opacity: 0.7;">N</span>: Nac. (FUTVE)
                    </span>
                </div>
                <div class="perfil-table-wrap" id="tabla-pruebas">
                    <div class="perfil-table-header" style="grid-template-columns: 2fr 1.5fr 1.5fr 1.5fr 1.5fr 1.5fr;">
                        <div>Fecha</div>
                        <div>Fuerza (CMJ)</div>
                        <div>Resist. (Yo-Yo)</div>
                        <div>Veloc. (30m)</div>
                        <div>Coord. (Conos)</div>
                        <div>Reacc. (Cognit.)</div>
                    </div>
                    <?php if (empty($pruebas_historial)): ?>
                        <div style="text-align: center; padding: 32px; color: var(--color-text-muted);">
                            No hay pruebas registradas aún.
                        </div>
                    <?php else:
                        foreach ($pruebas_historial as $p): ?>
                            <div class="perfil-table-row" style="grid-template-columns: 2fr 1.5fr 1.5fr 1.5fr 1.5fr 1.5fr;">
                                <div class="perfil-row-col">
                                    <span class="perfil-col-label">Fecha</span>
                                    <div>
                                        <div style="font-weight: 500; color: var(--color-text);">
                                            <?= e(date('d/m/Y', strtotime($p['fecha_evento']))) ?>
                                        </div>
                                        <div style="font-size: 14.4px; color: var(--color-text-muted);">
                                            <?= e($p['nombre_evento'] ?? 'Registro Manual') ?>
                                        </div>
                                        <?php if (!empty($p['nombre_entrenador'])): ?>
                                            <div style="font-size: 13.2px; color: var(--color-primary); margin-top: 2px;">
                                                <i class="ph ph-user-gear"></i> <?= e($p['nombre_entrenador'] . ' ' . $p['apellido_entrenador']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="perfil-row-col">
                                    <span class="perfil-col-label">Fuerza (CMJ)</span>
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 6px; font-weight: 500;">
                                            <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--color-primary);"></div>
                                            <?= e($p['test_de_fuerza_raw'] !== null ? $p['test_de_fuerza_raw'] . ' cm' : '—') ?>
                                        </div>
                                        <?php if ($p['test_de_fuerza_raw'] !== null): ?>
                                            <div style="font-size: 13.2px; color: var(--color-text-muted); margin-left: 14px; margin-top: 2px;">
                                                I: <?= e($p['test_de_fuerza']) ?> | N: <?= e($p['test_de_fuerza_nac']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="perfil-row-col">
                                    <span class="perfil-col-label">Resist. (Yo-Yo)</span>
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 6px; font-weight: 500;">
                                            <div style="width: 8px; height: 8px; border-radius: 50%; background: #10B981;"></div>
                                            <?= e($p['test_resistencia_raw'] !== null ? $p['test_resistencia_raw'] . ' m' : '—') ?>
                                        </div>
                                        <?php if ($p['test_resistencia_raw'] !== null): ?>
                                            <div style="font-size: 13.2px; color: var(--color-text-muted); margin-left: 14px; margin-top: 2px;">
                                                I: <?= e($p['test_resistencia']) ?> | N: <?= e($p['test_resistencia_nac']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="perfil-row-col">
                                    <span class="perfil-col-label">Veloc. (30m)</span>
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 6px; font-weight: 500;">
                                            <div style="width: 8px; height: 8px; border-radius: 50%; background: #F59E0B;"></div>
                                            <?= e($p['test_velocidad_raw'] !== null ? $p['test_velocidad_raw'] . ' s' : '—') ?>
                                        </div>
                                        <?php if ($p['test_velocidad_raw'] !== null): ?>
                                            <div style="font-size: 13.2px; color: var(--color-text-muted); margin-left: 14px; margin-top: 2px;">
                                                I: <?= e($p['test_velocidad']) ?> | N: <?= e($p['test_velocidad_nac']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="perfil-row-col">
                                    <span class="perfil-col-label">Coord. (Conos)</span>
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 6px; font-weight: 500;">
                                            <div style="width: 8px; height: 8px; border-radius: 50%; background: #8B5CF6;"></div>
                                            <?= e($p['test_coordinacion_raw'] !== null ? $p['test_coordinacion_raw'] . ' s' : '—') ?>
                                        </div>
                                        <?php if ($p['test_coordinacion_raw'] !== null): ?>
                                            <div style="font-size: 13.2px; color: var(--color-text-muted); margin-left: 14px; margin-top: 2px;">
                                                I: <?= e($p['test_coordinacion']) ?> | N: <?= e($p['test_coordinacion_nac']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="perfil-row-col">
                                    <span class="perfil-col-label">Reacc. (Cognit.)</span>
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 6px; font-weight: 500;">
                                            <div style="width: 8px; height: 8px; border-radius: 50%; background: #EC4899;"></div>
                                            <?= e($p['test_de_reaccion_raw'] !== null ? $p['test_de_reaccion_raw'] . ' ms' : '—') ?>
                                        </div>
                                        <?php if ($p['test_de_reaccion_raw'] !== null): ?>
                                            <div style="font-size: 13.2px; color: var(--color-text-muted); margin-left: 14px; margin-top: 2px;">
                                                I: <?= e($p['test_de_reaccion']) ?> | N: <?= e($p['test_de_reaccion_nac']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                </div>
