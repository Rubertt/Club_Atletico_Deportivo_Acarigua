<?php /** @var array $usuarios @var array $roles */ ?>
<div class="page-header">
    <div>
        <h1>Gestión de Usuarios</h1>
        <div class="subtitle">Control de accesos y permisos al sistema</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 320px 1fr; gap: 24px;">
    
    <!-- Formulario de Creación -->
    <div class="card" style="align-self: start;">
        <h3 style="margin-top:0; font-size: 16px; border-bottom: 1px solid var(--color-border); padding-bottom: 12px; margin-bottom: 20px;"><i class="ph ph-user-plus"></i> Nuevo Usuario</h3>
        <form method="POST" action="<?= e(url('/admin/configuracion/usuarios')) ?>">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label">Correo Electrónico</label>
                <div style="position: relative;">
                    <i class="ph ph-envelope-simple text-muted" style="position: absolute; left: 12px; top: 11px; font-size: 18px;"></i>
                    <input type="email" name="email" class="form-control" style="padding-left: 36px;" placeholder="usuario@correo.com" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Contraseña (Mín. 8 caracteres)</label>
                <div style="position: relative;">
                    <i class="ph ph-lock-key text-muted" style="position: absolute; left: 12px; top: 11px; font-size: 18px;"></i>
                    <input type="password" name="password" class="form-control" style="padding-left: 36px;" placeholder="••••••••" minlength="8" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Asignar Rol</label>
                <select name="rol_id" class="form-control" required>
                    <option value="">Selecciona un rol...</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= (int) $r['rol_id'] ?>"><?= e($r['nombre_rol']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 16px;">
                Registrar Usuario
            </button>
        </form>
    </div>

    <!-- Lista de Usuarios -->
    <div class="data-table-wrap card" style="padding: 0; overflow: hidden;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--color-border); background: var(--color-bg-alt); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px;">Usuarios Activos</h3>
            <div style="font-size: 13px; color: var(--color-text-muted);">Total: <?= count($usuarios) ?></div>
        </div>
        <table class="data-table" style="margin: 0; border: none;">
            <thead style="background: var(--color-bg-alt);">
                <tr>
                    <th style="padding-left: 24px;">Usuario / Correo</th>
                    <th>Rol Asignado</th>
                    <th>Estatus</th>
                    <th>Último Acceso</th>
                    <th style="width: 80px; text-align: center; padding-right: 24px;">Acción</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td style="padding-left: 24px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div class="avatar-placeholder" style="width: 36px; height: 36px; border-radius: 50%; font-size: 14px; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                <?= e(strtoupper(substr($u['email'], 0, 2))) ?>
                            </div>
                            <div style="font-weight: 600; color: var(--color-text);"><?= e($u['email']) ?></div>
                        </div>
                    </td>
                    <td>
                        <?php 
                            $badgeColor = match (strtolower($u['nombre_rol'])) {
                                'admin', 'administrador' => 'danger',
                                'entrenador' => 'primary',
                                'médico', 'medico' => 'success',
                                default => 'warning'
                            };
                        ?>
                        <span class="badge badge-<?= $badgeColor ?>" style="padding: 4px 10px; border-radius: 6px;">
                            <i class="ph ph-shield-check" style="margin-right: 4px;"></i> <?= e($u['nombre_rol']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-<?= $u['estatus'] === 'Activo' ? 'success' : 'warning' ?>" style="padding: 4px 10px; border-radius: 20px;">
                            <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:currentColor; margin-right:4px;"></span>
                            <?= e($u['estatus']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if(!empty($u['ultimo_acceso'])): ?>
                            <div style="font-size: 13px; color: var(--color-text-muted);">
                                <?= e((new DateTime($u['ultimo_acceso']))->format('d/m/Y h:i A')) ?>
                            </div>
                        <?php else: ?>
                            <span style="color: var(--color-text-muted); font-size: 13px; font-style: italic;">Nunca</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: right; padding-right: 24px;">
                        <button class="btn btn-sm btn-ghost" title="Desactivar" style="color: var(--color-danger); padding: 6px;">
                            <i class="ph ph-prohibit"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($usuarios)): ?>
                <tr>
                    <td colspan="5" style="padding: 48px; text-align: center; color: var(--color-text-muted);">
                        <i class="ph ph-users text-muted" style="font-size: 32px; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                        No hay usuarios registrados en el sistema.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
