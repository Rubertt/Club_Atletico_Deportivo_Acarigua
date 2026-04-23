<?php /** @var array $stats @var array $atletas */ ?>
<div class="page-header">
    <div>
        <h1>Centro de Reportes y Estadísticas</h1>
        <div class="subtitle">Generación de fichas, exportación de datos y analíticas</div>
    </div>
</div>

<?php if (!empty($stats)): ?>
<div class="quick-grid" style="margin-bottom:32px;">
    <div class="card" style="display: flex; align-items: center; gap: 16px; padding: 24px;">
        <div style="width: 56px; height: 56px; border-radius: 12px; background: rgba(37, 99, 235, 0.1); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 28px;">
            <i class="ph ph-users-three"></i>
        </div>
        <div>
            <div style="font-size: 13px; color: var(--color-text-muted); font-weight: 600; text-transform: uppercase;">Atletas Totales</div>
            <div style="font-size: 28px; font-weight: 800; font-family: var(--font-display);"><?= (int) ($stats['atletas'] ?? 0) ?></div>
        </div>
    </div>

    <div class="card" style="display: flex; align-items: center; gap: 16px; padding: 24px;">
        <div style="width: 56px; height: 56px; border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: var(--color-success); display: flex; align-items: center; justify-content: center; font-size: 28px;">
            <i class="ph ph-check-circle"></i>
        </div>
        <div>
            <div style="font-size: 13px; color: var(--color-text-muted); font-weight: 600; text-transform: uppercase;">Activos</div>
            <div style="font-size: 28px; font-weight: 800; font-family: var(--font-display);"><?= (int) ($stats['activos'] ?? 0) ?></div>
        </div>
    </div>

    <div class="card" style="display: flex; align-items: center; gap: 16px; padding: 24px;">
        <div style="width: 56px; height: 56px; border-radius: 12px; background: rgba(139, 92, 246, 0.1); color: var(--color-info); display: flex; align-items: center; justify-content: center; font-size: 28px;">
            <i class="ph ph-shield-chevron"></i>
        </div>
        <div>
            <div style="font-size: 13px; color: var(--color-text-muted); font-weight: 600; text-transform: uppercase;">Categorías</div>
            <div style="font-size: 28px; font-weight: 800; font-family: var(--font-display);"><?= (int) ($stats['categorias'] ?? 0) ?></div>
        </div>
    </div>

    <div class="card" style="display: flex; align-items: center; gap: 16px; padding: 24px;">
        <div style="width: 56px; height: 56px; border-radius: 12px; background: rgba(245, 158, 11, 0.1); color: var(--color-warning); display: flex; align-items: center; justify-content: center; font-size: 28px;">
            <i class="ph ph-calendar-check"></i>
        </div>
        <div>
            <div style="font-size: 13px; color: var(--color-text-muted); font-weight: 600; text-transform: uppercase;">Eventos (30D)</div>
            <div style="font-size: 28px; font-weight: 800; font-family: var(--font-display);"><?= (int) ($stats['eventos_30dias'] ?? 0) ?></div>
        </div>
    </div>
</div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 350px; gap: 24px;">
    <!-- Main Content: Ficha Técnica -->
    <div class="card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
        <div style="padding: 24px; border-bottom: 1px solid var(--color-border); background: var(--color-bg-alt);">
            <h3 style="margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="ph ph-file-pdf" style="color: var(--color-danger); font-size: 24px;"></i> Fichas Técnicas Individuales
            </h3>
            <p class="text-muted" style="margin: 8px 0 0; font-size: 14px;">Genera una ficha técnica completa (PDF) por atleta con datos personales, antropometría, pruebas físicas, resumen de asistencia y ficha médica.</p>
        </div>

        <div class="data-table-wrap" style="flex: 1;">
            <table class="data-table" style="margin: 0; border: none;">
                <thead style="background: var(--color-bg);">
                    <tr>
                        <th style="padding-left: 24px;">Atleta</th>
                        <th>Cédula</th>
                        <th style="width:160px; text-align: right; padding-right: 24px;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($atletas as $a): ?>
                    <tr>
                        <td style="padding-left: 24px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <?php if (!empty($a['foto'])): ?>
                                    <img src="<?= e(url($a['foto'])) ?>" class="avatar-thumb" alt="" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                                <?php else: ?>
                                    <div class="avatar-placeholder" style="width: 32px; height: 32px; font-size: 12px; background: var(--color-primary-light); color: var(--color-primary);">
                                        <?= e(mb_substr($a['nombre'], 0, 1) . mb_substr($a['apellido'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <strong style="color: var(--color-text);"><?= e($a['nombre'] . ' ' . $a['apellido']) ?></strong>
                            </div>
                        </td>
                        <td><span style="color: var(--color-text-muted);"><i class="ph ph-identification-card"></i> <?= e($a['cedula'] ?? '—') ?></span></td>
                        <td style="text-align: right; padding-right: 24px;">
                            <a href="<?= e(url("/admin/reportes/atleta/{$a['atleta_id']}")) ?>" class="btn btn-sm btn-outline" target="_blank" title="Exportar PDF">
                                <i class="ph ph-download-simple"></i> PDF
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($atletas)): ?>
                    <tr><td colspan="3" class="text-center text-muted" style="padding:48px"><i class="ph ph-user-list text-muted" style="font-size:32px; display:block; margin-bottom:8px; opacity:0.5;"></i>No hay atletas registrados.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sidebar: Otros Reportes (Mock) -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <div class="card">
            <h3 style="margin-top: 0; font-size: 16px;"><i class="ph ph-microsoft-excel-logo" style="color: #107c41;"></i> Exportaciones Masivas</h3>
            <p class="text-muted" style="font-size: 13px;">Descarga la data global en formato hoja de cálculo para análisis externo.</p>
            
            <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 16px;">
                <button class="btn btn-outline" style="justify-content: flex-start;" disabled>
                    <i class="ph ph-download-simple"></i> Padrón de Atletas (CSV)
                </button>
                <button class="btn btn-outline" style="justify-content: flex-start;" disabled>
                    <i class="ph ph-download-simple"></i> Asistencias Mensuales (Excel)
                </button>
                <button class="btn btn-outline" style="justify-content: flex-start;" disabled>
                    <i class="ph ph-download-simple"></i> Resultados de Pruebas (Excel)
                </button>
            </div>
        </div>

        <div class="card" style="background: linear-gradient(135deg, var(--color-primary-light) 0%, rgba(37, 99, 235, 0.05) 100%); border-color: var(--color-primary-light);">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                <i class="ph ph-info" style="font-size: 24px; color: var(--color-primary);"></i>
                <h4 style="margin: 0; color: var(--color-primary);">Nota del Sistema</h4>
            </div>
            <p style="margin: 0; font-size: 13px; color: var(--color-text); line-height: 1.5;">
                Las exportaciones masivas a Excel/CSV se encuentran en desarrollo por el equipo de backend y estarán disponibles próximamente. Por ahora, puede generar los PDFs individuales sin problema.
            </p>
        </div>
    </div>
</div>
