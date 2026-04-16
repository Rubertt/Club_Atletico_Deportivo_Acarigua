<?php /** @var array $items */ ?>
<div class="page-header">
    <div><h1>Plantel</h1><div class="subtitle">Entrenadores, personal técnico y administrativo</div></div>
    <a href="<?= e(url('/admin/plantel/crear')) ?>" class="btn btn-primary">+ Nuevo</a>
</div>

<div class="data-table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Nombre</th><th>Cédula</th><th>Teléfono</th><th>Rol</th><th style="width:160px">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $p): ?>
            <tr>
                <td><strong><?= e($p['nombre'] . ' ' . $p['apellido']) ?></strong></td>
                <td><?= e($p['cedula'] ?? '—') ?></td>
                <td><?= e($p['telefono']) ?></td>
                <td><span class="badge badge-primary"><?= e($p['nombre_rol']) ?></span></td>
                <td>
                    <a href="<?= e(url("/admin/plantel/{$p['plantel_id']}/editar")) ?>" class="btn btn-sm btn-outline">Editar</a>
                    <form method="POST" action="<?= e(url("/admin/plantel/{$p['plantel_id']}/eliminar")) ?>" style="display:inline;" onsubmit="return confirm('¿Eliminar?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-ghost" style="color:var(--color-danger)">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($items)): ?><tr><td colspan="5" class="text-center text-muted" style="padding:32px">No hay miembros del plantel.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
