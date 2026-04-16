<?php /** @var array $pag @var array $categorias @var array $filters */ ?>
<div class="page-header">
    <div>
        <h1>Atletas</h1>
        <div class="subtitle">Gestión del equipo - <?= (int) $pag['total'] ?> atleta(s) en total</div>
    </div>
    <?php if (can('admin')): ?>
        <a href="<?= e(url('/admin/atletas/crear')) ?>" class="btn btn-primary">+ Nuevo atleta</a>
    <?php endif; ?>
</div>

<form method="GET" class="table-filters">
    <div class="form-group">
        <label class="form-label" for="q">Buscar</label>
        <input type="search" id="q" name="q" class="form-control" placeholder="Nombre, apellido o cédula" value="<?= e($filters['q'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label class="form-label" for="categoria_id">Categoría</label>
        <select id="categoria_id" name="categoria_id" class="form-control">
            <option value="">Todas</option>
            <?php foreach ($categorias as $c): ?>
                <option value="<?= (int) $c['categoria_id'] ?>" <?= ((int) ($filters['categoria_id'] ?? 0) === (int) $c['categoria_id']) ? 'selected' : '' ?>>
                    <?= e($c['nombre_categoria']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label class="form-label" for="estatus">Estatus</label>
        <select id="estatus" name="estatus" class="form-control">
            <option value="">Todos</option>
            <?php foreach (['Activo','Inactivo','Lesionado','Suspendido'] as $op): ?>
                <option value="<?= e($op) ?>" <?= ($filters['estatus'] ?? '') === $op ? 'selected' : '' ?>><?= e($op) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-outline">Filtrar</button>
    <a href="<?= e(url('/admin/atletas')) ?>" class="btn btn-ghost">Limpiar</a>
</form>

<div class="data-table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:52px"></th>
                <th>Nombre</th>
                <th>Cédula</th>
                <th>Categoría</th>
                <th>Posición</th>
                <th>Estatus</th>
                <th style="width:160px">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($pag['data'])): ?>
            <tr><td colspan="7" class="text-center text-muted" style="padding:32px">No hay atletas registrados.</td></tr>
        <?php else: foreach ($pag['data'] as $a): ?>
            <tr>
                <td>
                    <?php if (!empty($a['foto'])): ?>
                        <img src="<?= e(url($a['foto'])) ?>" class="avatar-thumb" alt="">
                    <?php else: ?>
                        <span class="avatar-placeholder"><?= e(mb_substr($a['nombre'], 0, 1) . mb_substr($a['apellido'], 0, 1)) ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <strong><?= e($a['nombre'] . ' ' . $a['apellido']) ?></strong>
                </td>
                <td><?= e($a['cedula'] ?? '—') ?></td>
                <td><?= e($a['nombre_categoria'] ?? '—') ?></td>
                <td><?= e($a['nombre_posicion'] ?? '—') ?></td>
                <td>
                    <?php $badge = match ($a['estatus']) {
                        'Activo' => 'success', 'Lesionado' => 'warning', 'Suspendido' => 'danger', default => 'primary'
                    }; ?>
                    <span class="badge badge-<?= $badge ?>"><?= e($a['estatus']) ?></span>
                </td>
                <td>
                    <a href="<?= e(url('/admin/atletas/' . $a['atleta_id'])) ?>" class="btn btn-sm btn-ghost">Ver</a>
                    <?php if (can('admin')): ?>
                        <a href="<?= e(url('/admin/atletas/' . $a['atleta_id'] . '/editar')) ?>" class="btn btn-sm btn-outline">Editar</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php if (($pag['last_page'] ?? 1) > 1): ?>
    <ul class="pagination">
        <?php for ($p = 1; $p <= $pag['last_page']; $p++):
            $qs = array_filter(array_merge($filters, ['page' => $p]), fn($v) => $v !== null && $v !== ''); ?>
            <li class="<?= $p === (int) $pag['page'] ? 'active' : '' ?>">
                <?php if ($p === (int) $pag['page']): ?>
                    <span><?= $p ?></span>
                <?php else: ?>
                    <a href="<?= e(url('/admin/atletas?' . http_build_query($qs))) ?>"><?= $p ?></a>
                <?php endif; ?>
            </li>
        <?php endfor; ?>
    </ul>
<?php endif; ?>
