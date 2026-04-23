<?php /** @var array $items */ ?>
<div class="page-header">
    <div>
        <h1>Plantel Técnico y Administrativo</h1>
        <div class="subtitle">Gestión de entrenadores, médicos y directiva</div>
    </div>
    <a href="<?= e(url('/admin/plantel/crear')) ?>" class="btn btn-primary">
        <i class="ph ph-plus"></i> Añadir Personal
    </a>
</div>

<div class="data-table-wrap card" style="padding: 0; overflow: hidden;">
    <table class="data-table" style="margin: 0; border: none;">
        <thead style="background: var(--color-bg-alt);">
            <tr>
                <th style="width: 60px; padding-left: 24px;"></th>
                <th>Nombre Completo</th>
                <th>Datos de Contacto</th>
                <th>Rol / Cargo</th>
                <th style="width: 140px; text-align: right; padding-right: 24px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $p): ?>
            <tr>
                <td style="padding-left: 24px;">
                    <?php if (!empty($p['foto'])): ?>
                        <img src="<?= e(url($p['foto'])) ?>" class="avatar-thumb" alt="" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover;">
                    <?php else: ?>
                        <div class="avatar-placeholder" style="width: 44px; height: 44px; border-radius: 50%; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;">
                            <?= e(mb_substr($p['nombre'], 0, 1) . mb_substr($p['apellido'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="font-weight: 600; font-size: 15px; color: var(--color-text);"><?= e($p['nombre'] . ' ' . $p['apellido']) ?></div>
                    <div style="font-size: 12px; color: var(--color-text-muted); margin-top: 2px;">C.I: <?= e($p['cedula'] ?? '—') ?></div>
                </td>
                <td>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <span style="font-size: 13px; color: var(--color-text);"><i class="ph ph-phone text-muted"></i> <?= e($p['telefono']) ?></span>
                        <span style="font-size: 13px; color: var(--color-text-muted);"><i class="ph ph-envelope text-muted"></i> <?= e($p['email_id'] ?? 'Sin correo') ?></span>
                    </div>
                </td>
                <td>
                    <?php 
                        $badgeColor = match (strtolower($p['nombre_rol'] ?? '')) {
                            'entrenador' => 'primary',
                            'medico', 'médico' => 'success',
                            'directivo', 'admin' => 'danger',
                            default => 'warning'
                        };
                    ?>
                    <span class="badge badge-<?= $badgeColor ?>" style="padding: 6px 12px; border-radius: 20px;">
                        <?= e($p['nombre_rol'] ?? 'Sin Rol') ?>
                    </span>
                </td>
                <td style="text-align: right; padding-right: 24px;">
                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                        <a href="<?= e(url("/admin/plantel/{$p['plantel_id']}/editar")) ?>" class="btn btn-sm btn-outline" title="Editar">
                            <i class="ph ph-pencil-simple"></i>
                        </a>
                        <form method="POST" action="<?= e(url("/admin/plantel/{$p['plantel_id']}/eliminar")) ?>" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas dar de baja a este miembro del personal?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-ghost" style="color: var(--color-danger);" title="Eliminar">
                                <i class="ph ph-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($items)): ?>
            <tr>
                <td colspan="5" style="padding: 64px 24px; text-align: center;">
                    <i class="ph ph-users-three text-muted" style="font-size: 48px; margin-bottom: 16px; display: block; opacity: 0.5;"></i>
                    <h3 class="text-muted" style="margin: 0 0 8px;">No hay personal registrado</h3>
                    <p class="text-muted" style="font-size: 14px; max-width: 400px; margin: 0 auto;">Registra a entrenadores, médicos y personal administrativo aquí.</p>
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
