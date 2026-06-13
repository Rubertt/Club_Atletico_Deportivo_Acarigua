<?php /** @var array $items */ ?>
<div class="page-header">
    <div>
        <h1>Gestión de Usuarios</h1>
        <div class="subtitle">Entrenadores y personal administrativo del club</div>
    </div>
    <div class="flex gap">
        <a href="<?= e(url('/admin/reportes/usuarios/listado')) ?>" class="btn btn-outline" target="_blank" title="Generar Listado de Usuarios PDF">
            <i class="ph ph-file-pdf"></i> Reporte
        </a>
        <a href="<?= e(url('/admin/usuarios/crear')) ?>" class="btn btn-primary">
            <i class="ph ph-plus"></i> Nuevo Usuario
        </a>
    </div>
</div>

<div class="responsive-table-wrap">
    <div class="responsive-table-header" style="grid-template-columns: 2fr 1.8fr 1.2fr 1fr; padding-left: 24px; padding-right: 24px;">
        <div>Usuario / Nombre Completo</div>
        <div>Datos de Contacto</div>
        <div>Rol / Cargo</div>
        <div style="text-align: right;">Acciones</div>
    </div>
    <div class="responsive-table-body">
    <?php foreach ($items as $p): ?>
        <div class="responsive-table-row user-row" style="grid-template-columns: 2fr 1.8fr 1.2fr 1fr; padding-left: 24px; padding-right: 24px;">
            <div class="responsive-row-col">
                <span class="responsive-col-label">Usuario</span>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <?php if (!empty($p['foto'])): ?>
                        <img src="<?= e(url($p['foto'])) ?>" class="avatar-thumb" alt="" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                    <?php else: ?>
                        <div class="avatar-placeholder" style="width: 44px; height: 44px; border-radius: 50%; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; flex-shrink: 0;">
                            <?= e(mb_substr($p['nombre'], 0, 1) . mb_substr($p['apellido'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <div style="font-weight: 600; font-size: 15px; color: var(--color-text);"><?= e($p['nombre'] . ' ' . $p['apellido']) ?></div>
                        <div style="font-size: 12px; color: var(--color-text-muted); margin-top: 2px;"><?= !empty($p['cedula_formateada']) ? e($p['cedula_formateada']) : '—' ?></div>
                    </div>
                </div>
            </div>
            <div class="responsive-row-col">
                <span class="responsive-col-label">Datos de Contacto</span>
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <span style="font-size: 13px; color: var(--color-text);"><i class="ph ph-phone text-muted"></i> <?= e($p['telefono']) ?></span>
                    <span style="font-size: 13px; color: var(--color-text-muted);"><i class="ph ph-envelope text-muted"></i> <?= e($p['correo'] ?? 'Sin correo') ?></span>
                </div>
            </div>
            <div class="responsive-row-col">
                <span class="responsive-col-label">Rol / Cargo</span>
                <div>
                    <?php 
                        $badgeColor = match (strtolower($p['nombre_rol'] ?? '')) {
                            'entrenador' => 'primary',
                            'medico', 'médico' => 'success',
                            'directivo', 'administrador' => 'danger',
                            'super_user' => 'danger',
                            default => 'warning'
                        };
                    ?>
                    <span class="badge badge-<?= $badgeColor ?>" style="padding: 6px 12px; border-radius: 20px;">
                        <?= e($p['nombre_rol'] ?? 'Sin Rol') ?>
                    </span>
                </div>
            </div>
            <div class="responsive-row-col" style="text-align: right;">
                <span class="responsive-col-label" style="text-align: right;">Acciones</span>
                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <a href="<?= e(url("/admin/usuarios/{$p['usuario_id']}/perfil")) ?>" class="btn-view-premium" title="Ver Perfil">
                        <i class="ph ph-eye"></i>
                    </a>
                    <form method="POST" action="<?= e(url("/admin/usuarios/{$p['usuario_id']}/restablecer")) ?>" style="display:inline;" class="form-restablecer-usuario">
                        <?= csrf_field() ?>
                        <button type="button" class="btn btn-sm btn-outline btn-restablecer-usuario" style="color: var(--color-warning);" title="Restablecer Credenciales">
                            <i class="ph ph-key"></i>
                        </button>
                    </form>
                    <form method="POST" action="<?= e(url("/admin/usuarios/{$p['usuario_id']}/eliminar")) ?>" style="display:inline;" class="form-eliminar-usuario">
                        <?= csrf_field() ?>
                        <button type="button" class="btn-delete-premium btn-eliminar-usuario" title="Eliminar">
                            <i class="ph ph-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($items)): ?>
        <div class="responsive-table-row no-results-row" style="grid-template-columns: 1fr; justify-content: center; text-align: center; padding: 64px 24px;">
            <div class="text-center text-muted">
                <i class="ph ph-users-three text-muted" style="font-size: 48px; margin-bottom: 16px; display: block; opacity: 0.5;"></i>
                <h3 class="text-muted" style="margin: 0 0 8px;">No hay usuarios registrados</h3>
                <p class="text-muted" style="font-size: 14px; max-width: 400px; margin: 0 auto;">Registra a entrenadores y personal administrativo aquí.</p>
            </div>
        </div>
    <?php endif; ?>
    </div>
</div>

<div id="usuarios-pagination" style="display: flex; justify-content: center; margin-top: 24px;"></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Confirmar eliminación de usuario
    document.querySelectorAll('.btn-eliminar-usuario').forEach(btn => {
        btn.addEventListener('click', () => {
            const form = btn.closest('form');
            CadaModal.confirm({
                title: '¿Eliminar Usuario?',
                text: '¿Estás seguro de que deseas eliminar a este usuario? Esta acción no se puede deshacer.',
                type: 'danger',
                confirmText: 'Sí, Eliminar',
                cancelText: 'Cancelar'
            }).then(confirmed => {
                if (confirmed) form.submit();
            });
        });
    });

    // Confirmar restablecimiento de credenciales
    document.querySelectorAll('.btn-restablecer-usuario').forEach(btn => {
        btn.addEventListener('click', () => {
            const form = btn.closest('form');
            CadaModal.confirm({
                title: '¿Restablecer Credenciales?',
                text: '¿Estás seguro de que deseas restablecer las credenciales de este usuario? Su contraseña volverá a ser su número de cédula y se eliminarán sus respuestas de seguridad para forzarlo a reconfigurar su cuenta al ingresar.',
                type: 'warning',
                confirmText: 'Sí, Restablecer',
                cancelText: 'Cancelar'
            }).then(confirmed => {
                if (confirmed) form.submit();
            });
        });
    });

    // --- Paginación ---
    CadaPagination({
        rowSelector: '.user-row',
        containerId: 'usuarios-pagination'
    });
});
</script>
