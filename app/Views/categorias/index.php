<?php /** @var array $items */ ?>
<div class="page-header">
    <div>
        <h1>Categorías Deportivas</h1>
        <div class="subtitle">Organización de grupos por rango de edad</div>
    </div>
    <?php if (can('admin')): ?>
        <a href="<?= e(url('/admin/categorias/crear')) ?>" class="btn btn-primary">
            <i class="ph ph-plus"></i> Nueva Categoría
        </a>
    <?php endif; ?>
</div>

<!-- Grid de Categorías (Mock Premium UI) -->
<div class="quick-grid" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px;">
    <?php if (empty($items)): ?>
        <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 64px 24px;">
            <i class="ph ph-shield-slash text-muted" style="font-size: 48px; opacity: 0.5;"></i>
            <h3 class="text-muted" style="margin: 16px 0 8px;">No hay categorías registradas</h3>
            <p class="text-muted" style="font-size: 14px; max-width: 400px; margin: 0 auto;">Agrega la primera categoría para empezar a organizar a los atletas.</p>
        </div>
    <?php else: foreach ($items as $c): ?>
        <div class="card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
            <!-- Header Card -->
            <div style="padding: 24px; background: linear-gradient(135deg, var(--color-bg) 0%, var(--color-bg-alt) 100%); border-bottom: 1px solid var(--color-border); position: relative;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <span class="badge badge-<?= $c['estatus'] === 'Activa' ? 'success' : 'warning' ?>" style="margin-bottom: 12px; font-size: 11px;">
                            <?= e($c['estatus']) ?>
                        </span>
                        <h2 style="margin: 0 0 4px; font-family: var(--font-display); font-size: 20px;"><?= e($c['nombre_categoria']) ?></h2>
                        <div style="color: var(--color-text-muted); font-size: 13px; font-weight: 500;">
                            <i class="ph ph-users"></i> <?= (int) $c['edad_min'] ?> a <?= (int) $c['edad_max'] ?> años
                        </div>
                    </div>
                    <?php if (can('admin')): ?>
                        <div style="display: flex; gap: 4px;">
                            <a href="<?= e(url("/admin/categorias/{$c['categoria_id']}/editar")) ?>" class="btn btn-ghost btn-sm" style="padding: 6px;" title="Editar"><i class="ph ph-pencil-simple"></i></a>
                            <form method="POST" action="<?= e(url("/admin/categorias/{$c['categoria_id']}/eliminar")) ?>" style="display:inline;" onsubmit="return confirm('¿Eliminar esta categoría? Esto podría afectar a los atletas asignados.')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-ghost btn-sm" style="padding: 6px; color: var(--color-danger);" title="Eliminar"><i class="ph ph-trash"></i></button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Body Card -->
            <div style="padding: 24px; flex: 1; display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <div style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600; margin-bottom: 8px;">Entrenador Asignado</div>
                    <?php if (!empty($c['entrenador'])): ?>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div class="avatar-placeholder" style="width: 36px; height: 36px; font-size: 14px; background: var(--color-primary-light); color: var(--color-primary);">
                                <?= e(mb_substr($c['entrenador'], 0, 2)) ?>
                            </div>
                            <div style="font-weight: 500; font-size: 14px;"><?= e($c['entrenador']) ?></div>
                        </div>
                    <?php else: ?>
                        <div style="display: flex; align-items: center; gap: 12px; color: var(--color-text-muted);">
                            <div class="avatar-placeholder" style="width: 36px; height: 36px; font-size: 14px; background: var(--color-bg-alt); color: var(--color-text-muted);">--</div>
                            <div style="font-size: 13px; font-style: italic;">Sin entrenador</div>
                        </div>
                    <?php endif; ?>
                </div>

                <div style="margin-top: auto;">
                    <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px;">
                        <span style="color: var(--color-text-muted); font-weight: 600;">Atletas Inscritos</span>
                        <span style="font-weight: 700; color: var(--color-primary);"><?= (int) ($c['total_atletas'] ?? 0) ?></span>
                    </div>
                    <?php 
                        // Simular barra de progreso (Max 30 por categoría para visualización)
                        $porcentaje = min(100, ((int) ($c['total_atletas'] ?? 0) / 30) * 100);
                    ?>
                    <div style="height: 6px; background: var(--color-bg-alt); border-radius: 3px; overflow: hidden;">
                        <div style="height: 100%; width: <?= $porcentaje ?>%; background: var(--color-primary); border-radius: 3px;"></div>
                    </div>
                </div>
            </div>
            
            <!-- Footer Card -->
            <div style="padding: 16px 24px; border-top: 1px solid var(--color-border); background: var(--color-bg);">
                <a href="<?= e(url('/admin/atletas?categoria_id=' . $c['categoria_id'])) ?>" class="btn btn-outline" style="width: 100%; justify-content: center;">
                    <i class="ph ph-eye"></i> Ver Atletas
                </a>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>
