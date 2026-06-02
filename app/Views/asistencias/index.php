<?php /** @var array $eventos @var array $pag @var array $filters */ ?>
<div class="page-header">
    <div>
        <h1>Control de Asistencia</h1>
        <div class="subtitle">Gestión y registro de asistencia por categoría</div>
    </div>
    <a href="<?= e(url('/admin/asistencias/crear')) ?>" class="btn btn-primary">
        <i class="ph ph-plus-circle"></i> Registrar Asistencia
    </a>
</div>

<div class="data-table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th style="padding-left: 24px;">Fecha</th>
                <th>Categoría</th>
                <th>Tipo de Actividad</th>
                <th>Enlistador</th>
                <th>Asistencia</th>
                <th style="text-align: right; padding-right: 24px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($eventos as $ev): ?>
                <tr class="asistencia-row">
                    <td style="padding-left: 24px;">
                        <div style="font-weight: 600;"><?= e(date('d/m/Y', strtotime($ev['fecha_evento']))) ?></div>
                    </td>
                    <td>
                        <span style="font-weight: 600; color: var(--color-text);"><i class="ph ph-users-three text-muted"></i> <?= e($ev['nombre_categoria'] ?? 'Sin Categoría') ?></span>
                    </td>
                    <td>
                        <?php
                        $label = TIPO_ACTIVIDAD[(int) ($ev['tipo_evento'] ?? 1)] ?? 'General';
                        ?>
                        <span class="badge badge-primary"
                            style="text-transform: uppercase; font-size: 11px;"><?= e($label) ?></span>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="ph ph-user-circle text-muted" style="font-size: 20px;"></i>
                            <?= e($ev['entrenador'] ?? 'No definido') ?>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div
                                style="width: 100px; height: 8px; background: var(--color-surface-2); border-radius: 4px; overflow: hidden;">
                                <div
                                    style="width: <?= ($ev['total'] > 0) ? ($ev['presentes'] / $ev['total'] * 100) : 0 ?>%; height: 100%; background: var(--color-success);">
                                </div>
                            </div>
                            <span style="font-size: 13px; font-weight: 500;">
                                <?= (int) $ev['presentes'] ?> / <?= (int) $ev['total'] ?>
                            </span>
                        </div>
                    </td>
                    <td style="text-align: right; padding-right: 24px;">
                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                            <a href="<?= e(url('/admin/asistencias/' . $ev['evento_id'])) ?>" class="btn btn-sm btn-ghost"
                                title="Ver Detalles">
                                <i class="ph ph-eye"></i>
                            </a>
                            <a href="<?= e(url('/admin/asistencias/' . $ev['evento_id'] . '/editar')) ?>"
                                class="btn btn-sm btn-outline" title="Editar">
                                <i class="ph ph-pencil-simple"></i>
                            </a>
                            <form action="<?= e(url('/admin/asistencias/' . $ev['evento_id'] . '/eliminar')) ?>"
                                method="POST" style="display:inline;">
                                <?= csrf_field() ?>
                                <button type="button" class="btn btn-sm btn-outline btn-delete-asistencia"
                                    title="Eliminar Registro"
                                    data-date="<?= e(date('d/m/Y', strtotime($ev['fecha_evento']))) ?>">
                                    <i class="ph ph-trash" style="color: var(--color-danger);"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($eventos)): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted" style="padding: 48px;">
                        <i class="ph ph-calendar-x"
                            style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 12px;"></i>
                        No hay registros de asistencia para mostrar.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<div id="asistencias-pagination" style="display: flex; justify-content: center; margin-top: 24px;"></div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-delete-asistencia').forEach(btn => {
            btn.addEventListener('click', function () {
                const form = this.closest('form');
                const date = this.getAttribute('data-date');

                CadaModal.confirm({
                    title: '¿Eliminar Asistencia?',
                    text: `¿Estás seguro de eliminar el registro del día ${date}? Esta acción no se puede deshacer.`,
                    type: 'danger',
                    confirmText: 'Sí, Eliminar',
                    cancelText: 'Cancelar'
                }).then((confirmed) => {
                    if (confirmed) {
                        form.submit();
                    }
                });
            });
        });

        // --- Paginación Client-Side ---
        const rowsPerPage = 15;
        const rows = Array.from(document.querySelectorAll('.asistencia-row'));
        const totalCount = rows.length;

        if (totalCount > rowsPerPage) {
            const totalPages = Math.ceil(totalCount / rowsPerPage);
            const container = document.getElementById('asistencias-pagination');

            function showPage(page) {
                rows.forEach(r => r.style.display = 'none');
                const start = (page - 1) * rowsPerPage;
                const end = start + rowsPerPage;
                rows.slice(start, end).forEach(r => r.style.display = '');

                if (container) {
                    container.innerHTML = '';
                    const ul = document.createElement('ul');
                    ul.className = 'pagination';

                    for (let i = 1; i <= totalPages; i++) {
                        const li = document.createElement('li');
                        if (i === page) {
                            li.className = 'active';
                            const span = document.createElement('span');
                            span.textContent = i;
                            li.appendChild(span);
                        } else {
                            const a = document.createElement('a');
                            a.href = '#';
                            a.textContent = i;
                            a.onclick = (e) => {
                                e.preventDefault();
                                showPage(i);
                            };
                            li.appendChild(a);
                        }
                        ul.appendChild(li);
                    }
                    container.appendChild(ul);
                }
            }

            showPage(1);
        }
    });
</script>