            <!-- Tab: Asistencia -->
            <div id="tab-asistencia" class="tab-content" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <h3 style="margin: 0;"><i class="ph ph-calendar-check"></i> Historial de Asistencias</h3>
                </div>
                <div>
                    <div class="stats-grid"
                        style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); margin-bottom: 24px; gap: 16px; display: grid;">
                        <?php
                        $total_asist = count($asistencias_historial ?? []);
                        $presentes = count(array_filter($asistencias_historial ?? [], fn($a) => (int) $a['estatus'] === 1));
                        $justificadas = count(array_filter($asistencias_historial ?? [], fn($a) => (int) $a['estatus'] === 2));
                        $ausentes = $total_asist - $presentes - $justificadas;
                        $porcentaje = $total_asist > 0 ? round(($presentes / $total_asist) * 100) : 0;
                        ?>
                        <div class="stat-card"
                            style="padding: 16px; background: var(--color-bg-alt); border: 1px solid var(--color-border); border-radius: 8px; text-align: center;">
                            <div class="stat-number"
                                style="font-size: 24px; font-weight: bold; color: var(--color-primary);">
                                <?= $porcentaje ?>%</div>
                            <div class="stat-label"
                                style="font-size: 12px; color: var(--color-text-muted); margin-top: 4px;">Asistencia
                                Total</div>
                        </div>
                        <div class="stat-card"
                            style="padding: 16px; background: var(--color-bg-alt); border: 1px solid var(--color-border); border-radius: 8px; text-align: center;">
                            <div class="stat-number" style="font-size: 24px; font-weight: bold; color: #10B981;">
                                <?= $presentes ?></div>
                            <div class="stat-label"
                                style="font-size: 12px; color: var(--color-text-muted); margin-top: 4px;">Presentes
                            </div>
                        </div>
                        <div class="stat-card"
                            style="padding: 16px; background: var(--color-bg-alt); border: 1px solid var(--color-border); border-radius: 8px; text-align: center;">
                            <div class="stat-number" style="font-size: 24px; font-weight: bold; color: #F59E0B;">
                                <?= $justificadas ?></div>
                            <div class="stat-label"
                                style="font-size: 12px; color: var(--color-text-muted); margin-top: 4px;">Justificadas
                            </div>
                        </div>
                        <div class="stat-card"
                            style="padding: 16px; background: var(--color-bg-alt); border: 1px solid var(--color-border); border-radius: 8px; text-align: center;">
                            <div class="stat-number" style="font-size: 24px; font-weight: bold; color: #EF4444;">
                                <?= $ausentes ?></div>
                            <div class="stat-label"
                                style="font-size: 12px; color: var(--color-text-muted); margin-top: 4px;">Inasistencias
                            </div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 24px;">
                        <!-- Calendario Interactivo -->
                        <div style="background: var(--color-bg-alt); border: 1px solid var(--color-border); border-radius: 12px; padding: 20px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                                <h4 style="margin: 0; display: flex; align-items: center; gap: 8px;"><i class="ph ph-calendar-blank"></i> Calendario Mensual</h4>
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <button type="button" class="btn-icon-premium" id="btn-prev-month" style="width: 28px; height: 28px;"><i class="ph ph-caret-left"></i></button>
                                    <span id="calendar-month-year" style="font-weight: 600; min-width: 130px; text-align: center; text-transform: capitalize;">Mayo 2026</span>
                                    <button type="button" class="btn-icon-premium" id="btn-next-month" style="width: 28px; height: 28px;"><i class="ph ph-caret-right"></i></button>
                                </div>
                            </div>
                            
                            <!-- Días de la semana -->
                            <div style="display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; font-weight: 600; font-size: 12px; color: var(--color-text-muted); margin-bottom: 8px;">
                                <div>Lun</div><div>Mar</div><div>Mié</div><div>Jue</div><div>Vie</div><div>Sáb</div><div>Dom</div>
                            </div>
                            
                            <!-- Cuadrícula del calendario -->
                            <div id="calendar-grid" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px;">
                                <!-- Generado por JS -->
                            </div>
                            
                            <!-- Leyenda -->
                            <div style="display: flex; gap: 16px; margin-top: 20px; font-size: 12px; justify-content: center; flex-wrap: wrap;">
                                <div style="display: flex; align-items: center; gap: 6px;"><div style="width: 10px; height: 10px; border-radius: 50%; background: #10B981;"></div> Presente</div>
                                <div style="display: flex; align-items: center; gap: 6px;"><div style="width: 10px; height: 10px; border-radius: 50%; background: #EF4444;"></div> Ausente</div>
                                <div style="display: flex; align-items: center; gap: 6px;"><div style="width: 10px; height: 10px; border-radius: 50%; background: #F59E0B;"></div> Justificado</div>
                                <div style="display: flex; align-items: center; gap: 6px;"><div style="width: 10px; height: 10px; border-radius: 50%; background: var(--color-primary);"></div> Partido</div>
                            </div>
                        </div>

                        <!-- Gráfico de Dona -->
                        <div style="background: var(--color-bg-alt); border-radius: 12px; padding: 20px; display: flex; flex-direction: column; border: 1px solid var(--color-border);">
                            <h4 style="margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;"><i class="ph ph-chart-pie-slice"></i> Distribución</h4>
                            <div id="chart-asistencia-dona" style="flex: 1; min-height: 250px;"></div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h4 style="margin: 0;"><i class="ph ph-list-dashes"></i> Registro Detallado</h4>
                    </div>
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="data-table" id="tabla-asistencias" style="min-width: 650px;">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Evento / Tipo</th>
                                    <th>Ubicación</th>
                                    <th>Estado</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($asistencias_historial)): ?>
                                    <tr>
                                        <td colspan="5"
                                            style="text-align: center; padding: 32px; color: var(--color-text-muted);">No
                                            hay registros de asistencia.</td>
                                    </tr>
                                <?php else:
                                    foreach ($asistencias_historial as $as): ?>
                                        <tr>
                                            <td>
                                                <div style="font-weight: 500; color: var(--color-text);">
                                                    <?= e(date('d/m/Y', strtotime($as['fecha']))) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $tipoStr = match ((int) $as['tipo_actividad']) {
                                                    0 => 'Partido',
                                                    1 => 'Entrenamiento',
                                                    2 => 'Pruebas Físicas',
                                                    3 => 'Evento Especial',
                                                    default => 'Otro'
                                                };
                                                ?>
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <?php if ((int)$as['tipo_actividad'] === 0): ?>
                                                        <i class="ph ph-soccer-ball" style="color: var(--color-primary);"></i>
                                                    <?php else: ?>
                                                        <i class="ph ph-person-simple-run" style="color: var(--color-text-muted);"></i>
                                                    <?php endif; ?>
                                                    <span style="font-weight: 500;"><?= e($tipoStr) ?></span>
                                                </div>
                                            </td>
                                            <td><span
                                                    style="font-size: 13px; color: var(--color-text-muted);"><i class="ph ph-map-pin"></i> <?= e($as['ubicacion'] ?? '—') ?></span>
                                            </td>
                                            <td>
                                                <?php
                                                [$lbl, $cls] = match ((int) $as['estatus']) {
                                                    1 => ['Presente', 'success'],
                                                    2 => ['Justificado', 'warning'],
                                                    0 => ['Ausente', 'danger'],
                                                    default => ['—', 'outline']
                                                };
                                                ?>
                                                <span class="badge badge-<?= $cls ?>"><?= $lbl ?></span>
                                            </td>
                                            <td><span
                                                    style="font-size: 12px; color: var(--color-text-muted);"><?= e($as['observaciones'] ?? '—') ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
