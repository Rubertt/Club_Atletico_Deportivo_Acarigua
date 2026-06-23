<?php 
/** @var bool $isSuper */
/** @var array $manuals */
?>

<div class="af-container">
    <div class="page-header af-header" style="justify-content: center; text-align: center; margin-bottom: 32px;">
        <div class="af-header__content">
            <h1><i class="ph ph-book-open-text"></i> Manuales de Ayuda</h1>
            <p class="subtitle">Accede a las guías y documentación oficial del sistema</p>
        </div>
    </div>

    <?php include view_path('partials.flash'); ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; max-width: 1100px; margin: 0 auto;">
        
        <?php foreach ($manuals as $key => $manual): ?>
            <?php 
                // Only Super User can see 'sistema' and 'instalacion' manual cards
                if (in_array($key, ['sistema', 'instalacion']) && !$isSuper) {
                    continue;
                }
            ?>
            <div class="af-card" style="display: flex; flex-direction: column; border: 1px solid var(--color-border); border-radius: var(--radius-lg); transition: transform 0.2s, box-shadow 0.2s; position: relative; background: var(--color-bg); box-shadow: var(--shadow-sm);">
                
                <div style="padding: 28px 24px; flex-grow: 1;">
                    <!-- Badge de Estado -->
                    <div style="position: absolute; top: 18px; right: 18px;">
                        <?php if ($manual['exists']): ?>
                            <span class="badge" style="background: rgba(46, 204, 113, 0.15); color: #2ecc71; border: 1px solid rgba(46, 204, 113, 0.3); padding: 4px 8px; border-radius: 20px; font-size: 11px; font-weight: 600;">
                                <i class="ph ph-check-circle"></i> Disponible
                            </span>
                        <?php else: ?>
                            <span class="badge" style="background: rgba(241, 196, 15, 0.15); color: #f1c40f; border: 1px solid rgba(241, 196, 15, 0.3); padding: 4px 8px; border-radius: 20px; font-size: 11px; font-weight: 600;">
                                <i class="ph ph-hourglass"></i> Pendiente
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Icono -->
                    <div style="width: 56px; height: 56px; border-radius: var(--radius-md); background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 20px;">
                        <i class="<?= e($manual['icon']) ?>"></i>
                    </div>

                    <!-- Titulo -->
                    <h3 style="font-family: var(--font-display); font-size: 18px; font-weight: 700; color: var(--color-text); margin: 0 0 10px;">
                        <?= e($manual['title']) ?>
                    </h3>

                    <!-- Descripción -->
                    <p style="color: var(--color-text-muted); font-size: 14px; line-height: 1.6; margin: 0 0 20px;">
                        <?= e($manual['desc']) ?>
                    </p>

                    <!-- Instrucciones si no existe y es Super Usuario -->
                    <?php if (!$manual['exists'] && $isSuper): ?>
                        <div style="background: var(--color-surface); border: 1px dashed var(--color-border); border-radius: var(--radius-md); padding: 12px 14px; font-size: 12px; color: var(--color-text-muted); margin-top: 16px; line-height: 1.5;">
                            <strong style="color: var(--color-text); display: block; margin-bottom: 4px;">Instrucciones para activar:</strong>
                            Carga el archivo PDF como:<br>
                            <code>public/uploads/manuales/<?= e($manual['file']) ?></code>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Botón Acción -->
                <div style="padding: 16px 24px 28px; background: rgba(0, 0, 0, 0.02); border-top: 1px solid var(--color-border); border-bottom-left-radius: var(--radius-lg); border-bottom-right-radius: var(--radius-lg); display: flex; flex-direction: column; gap: 10px;">
                    <?php if ($manual['exists']): ?>
                        <?php if ($key === 'usuario'): ?>
                            <a href="<?= e($manual['url']) ?>" target="_blank" class="btn btn-primary" style="width: 100%; justify-content: center; gap: 8px;">
                                <i class="ph ph-desktop"></i> Ver Manual Interactivo
                            </a>
                        <?php else: ?>
                            <a href="<?= e($manual['url']) ?>" target="_blank" class="btn btn-primary" style="width: 100%; justify-content: center; gap: 8px;">
                                <i class="ph ph-file-pdf"></i> Visualizar Manual
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($manual['pdf_exists'])): ?>
                            <a href="<?= e($manual['pdf_url']) ?>" download class="btn btn-outline" style="width: 100%; justify-content: center; gap: 8px;">
                                <i class="ph ph-file-pdf"></i> Descargar PDF
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="btn" style="width: 100%; justify-content: center; background: var(--color-border); color: var(--color-text-muted); cursor: not-allowed;" disabled>
                            <i class="ph ph-warning-circle"></i> No disponible
                        </button>
                    <?php endif; ?>
                </div>

            </div>
        <?php endforeach; ?>

    </div>
</div>
