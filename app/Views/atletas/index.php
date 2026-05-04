<?php /** @var array $pag @var array $categorias @var array $filters */ ?>
<div class="page-header">
    <div>
        <h1>Directorio de Atletas</h1>
        <div class="subtitle">Gestión y control del plantel deportivo</div>
    </div>
    <?php if (can('admin')): ?>
        <a href="<?= e(url('/admin/atletas/crear')) ?>" class="btn btn-primary">
            <i class="ph ph-user-plus"></i> Nuevo Atleta
        </a>
    <?php endif; ?>
</div>

<!-- Tarjetas de Estadísticas (Mock/Dummy Data for UI) -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number" style="color: var(--color-primary);"><?= (int) ($pag['total'] ?? 0) ?></div>
        <div class="stat-label">Total Registrados</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: #10B981;"><?= (int) ($stats['activo'] ?? 0) ?></div>
        <div class="stat-label">Atletas Activos</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: #F59E0B;"><?= (int) ($stats['lesionado'] ?? 0) ?></div>
        <div class="stat-label">Lesionados</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: #EF4444;"><?= (int) ($stats['suspendido'] ?? 0) ?></div>
        <div class="stat-label">Suspendidos</div>
    </div>
</div>

<form method="GET" class="table-filters card" style="display: flex; gap: 16px; align-items: flex-end; padding: 16px; margin-bottom: 24px; flex-wrap: wrap;">
    <div class="form-group" style="flex: 1; min-width: 250px; margin-bottom: 0;">
        <label class="form-label" for="q"><i class="ph ph-magnifying-glass"></i> Buscar Atleta</label>
        <input type="search" id="q" name="q" class="form-control" placeholder="Nombre, apellido o cédula..." value="<?= e($filters['q'] ?? '') ?>">
    </div>
    <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
        <label class="form-label" for="categoria_id"><i class="ph ph-users-three"></i> Categoría</label>
        <select id="categoria_id" name="categoria_id" class="form-control">
            <option value="">Todas las categorías</option>
            <?php foreach ($categorias as $c): ?>
                <option value="<?= (int) $c['categoria_id'] ?>" <?= ((int) ($filters['categoria_id'] ?? 0) === (int) $c['categoria_id']) ? 'selected' : '' ?>>
                    <?= e($c['nombre_categoria']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
        <label class="form-label" for="estatus"><i class="ph ph-activity"></i> Estatus</label>
        <select id="estatus" name="estatus" class="form-control">
            <option value="">Todos los estatus</option>
            <option value="1" <?= ($filters['estatus'] ?? '') == '1' ? 'selected' : '' ?>>Activo</option>
            <option value="0" <?= ($filters['estatus'] ?? '') == '0' ? 'selected' : '' ?>>Inactivo</option>
            <option value="2" <?= ($filters['estatus'] ?? '') == '2' ? 'selected' : '' ?>>Lesionado</option>
            <option value="3" <?= ($filters['estatus'] ?? '') == '3' ? 'selected' : '' ?>>Suspendido</option>
        </select>
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="submit" class="btn btn-outline"><i class="ph ph-funnel"></i> Filtrar</button>
        <a href="<?= e(url('/admin/atletas')) ?>" class="btn btn-ghost" title="Limpiar filtros"><i class="ph ph-x"></i></a>
    </div>
</form>

<div class="data-table-wrap card" style="padding: 0; overflow: hidden;">
    <table class="data-table" style="margin: 0; border: none;">
        <thead style="background: var(--color-bg-alt);">
            <tr>
                <th style="width:52px; padding-left: 24px;"></th>
                <th>Atleta</th>
                <th>Categoría</th>
                <th>Posición</th>
                <th>Estatus</th>
                <th style="width:160px; text-align: right; padding-right: 24px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($pag['data'])): ?>
            <tr>
                <td colspan="6" style="padding: 64px 24px; text-align: center;">
                    <i class="ph ph-users text-muted" style="font-size: 48px; margin-bottom: 16px; display: block; opacity: 0.5;"></i>
                    <h3 class="text-muted" style="margin: 0 0 8px;">No hay atletas registrados</h3>
                    <p class="text-muted" style="font-size: 14px; max-width: 400px; margin: 0 auto;">No se encontraron atletas con los filtros actuales o no hay datos registrados en el sistema.</p>
                </td>
            </tr>
        <?php else: foreach ($pag['data'] as $a): ?>
            <tr>
                <td style="padding-left: 24px;">
                    <?php if (!empty($a['foto'])): ?>
                        <img src="<?= e(url($a['foto'])) ?>" class="avatar-thumb" alt="" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                    <?php else: ?>
                        <div class="avatar-placeholder" style="width: 40px; height: 40px; border-radius: 50%; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: bold;">
                            <?= e(mb_substr($a['nombre'], 0, 1) . mb_substr($a['apellido'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="font-weight: 600; color: var(--color-text);"><?= e($a['nombre'] . ' ' . $a['apellido']) ?></div>
                    <div style="font-size: 12px; color: var(--color-text-muted); margin-top: 2px;">C.I: <?= e($a['cedula'] ?? 'N/A') ?></div>
                </td>
                <td>
                    <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: var(--color-bg-alt); border-radius: 12px; font-size: 13px; font-weight: 500;">
                        <i class="ph ph-shield-chevron text-muted"></i> <?= e($a['nombre_categoria'] ?? 'Sin Categoría') ?>
                    </span>
                </td>
                <td>
                    <span style="font-size: 14px; color: var(--color-text-muted);">
                        <?= e($a['nombre_posicion'] ?? 'No definida') ?>
                    </span>
                </td>
                <td>
                    <?php 
                        $val = (int) $a['estatus'];
                        [$label, $badge] = match ($val) {
                            1 => ['Activo', 'success'],
                            2 => ['Lesionado', 'warning'],
                            3 => ['Suspendido', 'danger'],
                            0 => ['Inactivo', 'outline'],
                            default => ['Desconocido', 'primary']
                        }; 
                    ?>
                    <span class="badge badge-<?= $badge ?>" style="padding: 6px 12px; border-radius: 20px;">
                        <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: currentColor; margin-right: 6px; vertical-align: middle;"></span>
                        <?= e($label) ?>
                    </span>
                </td>
                <td style="text-align: right; padding-right: 24px;">
                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                        <a href="<?= e(url('/admin/atletas/' . $a['atleta_id'])) ?>" class="btn btn-sm btn-ghost" title="Ver Perfil">
                            <i class="ph ph-eye"></i> Perfil
                        </a>
                        <?php if (can('admin')): ?>
                            <a href="<?= e(url('/admin/atletas/' . $a['atleta_id'] . '/editar')) ?>" class="btn btn-sm btn-outline" title="Editar">
                                <i class="ph ph-pencil-simple"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php if (($pag['last_page'] ?? 1) > 1): ?>
    <div style="display: flex; justify-content: center; margin-top: 24px;">
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
    </div>
<?php endif; ?>
