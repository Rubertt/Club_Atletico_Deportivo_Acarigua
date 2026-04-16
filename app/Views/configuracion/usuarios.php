<?php /** @var array $usuarios @var array $roles */ ?>
<div class="page-header">
    <div><h1>Usuarios del sistema</h1><div class="subtitle">Accesos al panel administrativo</div></div>
</div>

<div class="card mb">
    <h3 style="margin-top:0;">Crear usuario</h3>
    <form method="POST" action="<?= e(url('/admin/configuracion/usuarios')) ?>">
        <?= csrf_field() ?>
        <div class="form-row-3">
            <div class="form-group">
                <label class="form-label">Correo</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Contraseña (mín. 8)</label>
                <input type="password" name="password" class="form-control" minlength="8" required>
            </div>
            <div class="form-group">
                <label class="form-label">Rol</label>
                <select name="rol_id" class="form-control" required>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= (int) $r['rol_id'] ?>"><?= e($r['nombre_rol']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Crear usuario</button>
    </form>
</div>

<div class="data-table-wrap">
    <table class="data-table">
        <thead><tr><th>Correo</th><th>Rol</th><th>Estatus</th><th>Último acceso</th></tr></thead>
        <tbody>
        <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><strong><?= e($u['email']) ?></strong></td>
                <td><span class="badge badge-primary"><?= e($u['nombre_rol']) ?></span></td>
                <td><span class="badge badge-<?= $u['estatus'] === 'Activo' ? 'success' : 'warning' ?>"><?= e($u['estatus']) ?></span></td>
                <td><?= e($u['ultimo_acceso'] ?? '—') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
