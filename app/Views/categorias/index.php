<?php /** @var array $items */ ?>
<div class="page-header">
    <div>
        <h1>Categorías</h1>
        <div class="subtitle">Grupos por rango de edad</div>
    </div>
    <?php if (can('admin')): ?>
        <a href="<?= e(url('/admin/categorias/crear')) ?>" class="btn btn-primary">+ Nueva</a>
    <?php endif; ?>
</div>

<div class="data-table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Edad</th>
                <th>Entrenador</th>
                <th>Atletas</th>
                <th>Estatus</th>
                <?php if (can('admin')): ?><th style="width:160px">Acciones</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $c): ?>
            <tr>
                <td><strong><?= e($c['nombre_categoria']) ?></strong></td>
                <td><?= (int) $c['edad_min'] ?> a <?= (int) $c['edad_max'] ?> años</td>
                <td><?= e($c['entrenador'] ?? '—') ?></td>
                <td><?= (int) ($c['total_atletas'] ?? 0) ?></td>
                <td><span class="badge badge-<?= $c['estatus'] === 'Activa' ? 'success' : 'warning' ?>"><?= e($c['estatus']) ?></span></td>
                <?php if (can('admin')): ?>
                    <td>
                        <a href="<?= e(url("/admin/categorias/{$c['categoria_id']}/editar")) ?>" class="btn btn-sm btn-outline">Editar</a>
                        <form method="POST" action="<?= e(url("/admin/categorias/{$c['categoria_id']}/eliminar")) ?>" style="display:inline;" onsubmit="return confirm('¿Eliminar esta categoría?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-ghost" style="color:var(--color-danger)">Eliminar</button>
                        </form>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($items)): ?><tr><td colspan="6" class="text-center text-muted" style="padding:32px">No hay categorías.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
