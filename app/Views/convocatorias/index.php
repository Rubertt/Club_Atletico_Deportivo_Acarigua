<?php /** @var array $eventos @var array $filters @var array $categorias @var array $enlistadores */ ?>
<div class="page-header">
    <div>
        <h1>Convocatorias (Partidos)</h1>
        <div class="subtitle">Gestión y registro de convocatorias por partido</div>
    </div>
    <a href="<?= e(url('/admin/convocatorias/crear')) ?>" class="btn btn-primary">
        <i class="ph ph-plus-circle"></i> Nueva Convocatoria
    </a>
</div>

<form method="GET" class="table-filters card" style="display: flex; gap: 16px; align-items: flex-end; padding: 16px; margin-bottom: 24px; flex-wrap: wrap;">
    <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
        <label class="form-label" for="usuario_id"><i class="ph ph-user"></i> Enlistador</label>
        <select id="usuario_id" name="usuario_id" class="form-control">
            <option value="">Todos los enlistadores</option>
            <?php foreach ($enlistadores as $enl): ?>
                <option value="<?= (int) $enl['usuario_id'] ?>" <?= ($filters['usuario_id'] ?? '') == $enl['usuario_id'] ? 'selected' : '' ?>><?= e($enl['nombre'] . ' ' . $enl['apellido']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
        <label class="form-label" data-tooltip="Filtrar por categoría deportiva" data-tooltip-pos="top" for="categoria_id"><i class="ph ph-tag"></i> Categoría</label>
        <select id="categoria_id" name="categoria_id" class="form-control">
            <option value="">Todas las categorías</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= (int) $cat['categoria_id'] ?>" <?= ($filters['categoria_id'] ?? '') == $cat['categoria_id'] ? 'selected' : '' ?>><?= e($cat['nombre_categoria']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div style="display: flex; gap: 8px;">
        <a href="<?= e(url('/admin/convocatorias')) ?>" class="btn btn-outline" title="Limpiar filtros" style="height: 44px; display: inline-flex; align-items: center; justify-content: center;"><i class="ph ph-trash"></i> Limpiar</a>
    </div>
</form>

<div class="data-table-wrap card" style="padding: 0; border: none; border-radius: 0; border-top: 1px solid var(--color-border);">
    <!-- Cabeceras en PC -->
    <div class="sesion-headers-desktop" style="display: flex; align-items: center; gap: 16px; padding: 12px 24px; background: var(--color-bg-alt); border-bottom: 1px solid var(--color-border); position: sticky; top: 0; z-index: 10; font-size: 13px; font-weight: 600; color: var(--color-text-muted);">
        <div style="width: 240px; flex-shrink: 0; padding-left: 36px; box-sizing: border-box;">Fecha</div>
        <div style="display: grid; grid-template-columns: 1.2fr 1.2fr 1fr 1fr; gap: 16px; flex: 1;">
            <div>Categoría</div>
            <div>Enlistador</div>
            <div>Convocados</div>
            <div>Estado / Asistencia</div>
        </div>
        <div style="width: 140px; text-align: right; flex-shrink: 0; padding-right: 12px;">Acciones</div>
    </div>

    <!-- Listado de convocatorias -->
    <div class="sesiones-list-container">
        <?php if (empty($eventos)): ?>
            <div style="padding: 64px 24px; text-align: center; background: var(--color-surface);">
                <i class="ph ph-envelope-simple-open text-muted" style="font-size: 48px; margin-bottom: 16px; display: block; opacity: 0.5;"></i>
                <h3 class="text-muted" style="margin: 0 0 8px;">No hay convocatorias</h3>
                <p class="text-muted" style="font-size: 14px; max-width: 400px; margin: 0 auto;">No hay registros de convocatorias para mostrar.</p>
            </div>
        <?php else: foreach ($eventos as $ev): ?>
            <div class="sesion-row-card convocatoria-row">
                <div class="sesion-row-card__info">
                    <i class="ph ph-soccer-ball text-muted" style="font-size: 24px; flex-shrink: 0;"></i>
                    <div class="sesion-row-card__date-wrap">
                        <div class="sesion-row-card__date"><?= e(date('d/m/Y', strtotime($ev['fecha_evento']))) ?></div>
                    </div>
                </div>

                <div class="sesion-row-card__details sesion-row-card__details--4cols" style="display: grid; grid-template-columns: 1.2fr 1.2fr 1fr 1fr; gap: 16px; flex: 1;">
                    <div class="asig-input-group">
                        <span class="sesion-label">Categoría</span>
                        <span style="font-weight: 600; color: var(--color-text);"><i class="ph ph-users-three text-muted"></i> <?= e($ev['nombre_categoria'] ?? 'Sin Categoría') ?></span>
                    </div>

                    <div class="asig-input-group">
                        <span class="sesion-label">Enlistador</span>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="ph ph-user-circle text-muted" style="font-size: 20px;"></i>
                            <?= e($ev['entrenador'] ?? 'No definido') ?>
                        </div>
                    </div>

                    <div class="asig-input-group">
                        <span class="sesion-label">Convocados</span>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="badge badge-outline" style="font-weight: 600; font-size: 12px; border-color: var(--color-primary); color: var(--color-primary);">
                                <i class="ph ph-users"></i> <?= (int)$ev['convocados_si'] ?> Convocados
                            </span>
                        </div>
                    </div>

                    <div class="asig-input-group">
                        <span class="sesion-label">Estado / Asistencia</span>
                        <?php if ((int)$ev['actividad_estatus'] === 2): ?>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span class="badge badge-success" style="font-size: 11px;">FINALIZADO</span>
                                <span style="font-size: 13px; font-weight: 500;" title="Asistieron / Convocados">
                                    <?= (int) $ev['asistieron'] ?> / <?= (int) $ev['convocados_si'] ?>
                                </span>
                            </div>
                        <?php else: ?>
                            <div>
                                <span class="badge badge-primary" style="font-size: 11px;">PROGRAMADO</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="sesion-row-card__actions">
                    <a href="<?= e(url('/admin/convocatorias/' . $ev['evento_id'])) ?>" class="btn-view-premium" title="Ver Detalles">
                        <i class="ph ph-eye"></i>
                    </a>
                    <a href="<?= e(url('/admin/convocatorias/' . $ev['evento_id'] . '/editar')) ?>" class="btn-edit-premium" title="Pase de Asistencia / Editar">
                        <i class="ph ph-pencil-simple"></i>
                    </a>
                    <form action="<?= e(url('/admin/convocatorias/' . $ev['evento_id'] . '/eliminar')) ?>" method="POST" style="display:inline;">
                        <?= csrf_field() ?>
                        <button type="button" class="btn-delete-premium btn-delete-convocatoria" title="Eliminar Convocatoria" data-date="<?= e(date('d/m/Y', strtotime($ev['fecha_evento']))) ?>">
                            <i class="ph ph-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>
<div id="convocatorias-pagination" style="display: flex; justify-content: center; margin-top: 24px;"></div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function bindDeleteButtons() {
            document.querySelectorAll('.btn-delete-convocatoria').forEach(btn => {
                btn.onclick = () => {
                    const form = btn.closest('form');
                    const date = btn.getAttribute('data-date');

                    CadaModal.confirm({
                        title: '¿Eliminar Convocatoria?',
                        text: `¿Estás seguro de eliminar el registro del partido del día ${date}? Esta acción eliminará el partido y sus convocatorias asociadas.`,
                        type: 'danger',
                        confirmText: 'Sí, Eliminar',
                        cancelText: 'Cancelar'
                    }).then((confirmed) => {
                        if (confirmed) {
                            form.submit();
                        }
                    });
                };
            });
        }

        // --- Paginación Client-Side ---
        function initClientPagination() {
            CadaPagination({
                rowSelector: '.convocatoria-row',
                containerId: 'convocatorias-pagination'
            });
        }

        // --- AJAX Filtering ---
        const form = document.querySelector('.table-filters');
        if (form) {
            const usuarioSelect = form.querySelector('#usuario_id');
            const categoriaSelect = form.querySelector('#categoria_id');

            const performFilter = () => {
                const formData = new FormData(form);
                formData.append('ajax', '1');
                const queryString = new URLSearchParams(formData).toString();
                
                const newUrl = `${window.location.pathname}?${new URLSearchParams(new FormData(form)).toString()}`;
                window.history.replaceState({ path: newUrl }, '', newUrl);

                fetch(`${window.location.pathname}?${queryString}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    const oldList = document.querySelector('.sesiones-list-container');
                    const newList = doc.querySelector('.sesiones-list-container');
                    if (oldList && newList) {
                        oldList.innerHTML = newList.innerHTML;
                    }
                    
                    bindDeleteButtons();
                    initClientPagination();
                })
                .catch(err => console.error('Error al filtrar:', err));
            };

            if (usuarioSelect) usuarioSelect.addEventListener('change', performFilter);
            if (categoriaSelect) categoriaSelect.addEventListener('change', performFilter);

            form.addEventListener('submit', (e) => e.preventDefault());
        }

        bindDeleteButtons();
        initClientPagination();
    });
</script>
