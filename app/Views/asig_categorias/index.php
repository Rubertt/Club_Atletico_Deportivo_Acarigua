<?php /** @var array $categoria @var array $atletas */ ?>
<div class="page-header">
    <div>
        <h1><?= e($categoria['nombre_categoria']) ?></h1>
        <div class="subtitle">Detalles de la categoría e historial de atletas asignados</div>
    </div>
    <div style="display: flex; gap: 12px; align-items: center;">
        <a href="<?= e(url('/admin/categorias')) ?>" class="btn btn-ghost">
            <i class="ph ph-arrow-left"></i> Volver
        </a>
        <?php if (can('admin')): ?>
            <a href="<?= e(url('/admin/categorias/' . $categoria['categoria_id'] . '/asignar')) ?>" class="btn btn-primary">
                <i class="ph ph-user-plus"></i> Asignar Atletas
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Tarjetas de Información Rápida -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-number" style="color: var(--color-primary);"><?= count($atletas) ?></div>
        <div class="stat-label">Atletas Asignados</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">
            <i class="ph ph-gender-<?= strtolower($categoria['sexo_categoria']) === 'f' ? 'female' : (strtolower($categoria['sexo_categoria']) === 'm' ? 'male' : 'intersex') ?>" style="font-size: 28px; vertical-align: middle;"></i>
            <?= $categoria['sexo_categoria'] === 'F' ? 'Femenino' : ($categoria['sexo_categoria'] === 'M' ? 'Masculino' : 'Mixto') ?>
        </div>
        <div class="stat-label">Género de Categoría</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= (int)$categoria['edad_min'] ?> - <?= (int)$categoria['edad_max'] ?></div>
        <div class="stat-label">Rango de Edad (Años)</div>
    </div>
</div>

<div class="data-table-wrap card" style="padding: 0; border: none; border-radius: 0; border-top: 1px solid var(--color-border);">
    <!-- Cabeceras en PC -->
    <div class="asig-headers-desktop" style="display: flex; align-items: center; gap: 16px; padding: 12px 24px; background: var(--color-bg-alt); border-bottom: 1px solid var(--color-border); position: sticky; top: 0; z-index: 10; font-size: 13px; font-weight: 600; color: var(--color-text-muted);">
        <div style="width: 320px; flex-shrink: 0; display: flex; align-items: center; gap: 12px;">
            <div style="width: 44px;"></div>
            <div>Atleta</div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr 120px; gap: 16px; flex: 1;">
            <div>Posición Principal</div>
            <div>Posición Secundaria</div>
            <div>Dorsal</div>
        </div>
        <div style="width: 140px; text-align: right; flex-shrink: 0; padding-right: 12px;">Acciones</div>
    </div>

    <!-- Listado de atletas asignados -->
    <?php if (empty($atletas)): ?>
        <div style="padding: 64px 24px; text-align: center; background: var(--color-surface);">
            <i class="ph ph-users-three text-muted" style="font-size: 48px; margin-bottom: 16px; display: block; opacity: 0.5;"></i>
            <h3 class="text-muted" style="margin: 0 0 8px;">No hay atletas asignados</h3>
            <p class="text-muted" style="font-size: 14px; max-width: 400px; margin: 0 auto;">Usa el botón de <strong>Asignar Atletas</strong> para inscribir deportistas en este grupo.</p>
        </div>
    <?php else: foreach ($atletas as $a): ?>
        <div class="asig-atleta-row">
            <div class="asig-atleta-row__athlete">
                <?php if (!empty($a['foto'])): ?>
                    <div style="position: relative; width: 44px; height: 44px; padding: 2px; border: 1px solid var(--color-border); border-radius: 50%; background: var(--color-bg); flex-shrink: 0;">
                        <img src="<?= e(url($a['foto'])) ?>" class="avatar-thumb" alt="" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; display: block;">
                    </div>
                <?php else: ?>
                    <div class="avatar-placeholder" style="width: 44px; height: 44px; border-radius: 50%; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: bold; border: 1px solid var(--color-primary-light); flex-shrink: 0;">
                        <?= e(mb_substr($a['nombre'], 0, 1) . mb_substr($a['apellido'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <div class="asig-atleta-row__name-wrap">
                    <div class="asig-atleta-row__name">
                        <?= e($a['nombre'] . ' ' . $a['apellido']) ?>
                        <?php if ((int)($a['estatus'] ?? 1) === 1): ?>
                            <span class="badge badge-success" style="font-weight: 600; font-size: 10px; padding: 2px 6px; border-radius: 4px;">Vigente</span>
                        <?php else: ?>
                            <span class="badge badge-danger" style="font-weight: 600; font-size: 10px; padding: 2px 6px; border-radius: 4px;">Vencido</span>
                        <?php endif; ?>
                    </div>
                    <div class="asig-atleta-row__meta">
                        <?= !empty($a['cedula']) ? e($a['cedula']) : 'Sin Cédula' ?>
                    </div>
                </div>
            </div>

            <div class="asig-atleta-row__inputs">
                <div class="asig-input-group">
                    <span class="asig-input-label">Posición Principal</span>
                    <span style="font-size: 14px; color: var(--color-text-muted);">
                        <?= e($a['posicion_principal'] ?? 'No definida') ?>
                    </span>
                </div>
                <div class="asig-input-group">
                    <span class="asig-input-label">Posición Secundaria</span>
                    <span style="font-size: 14px; color: var(--color-text-muted);">
                        <?= e($a['posicion_secundaria'] ?? 'Ninguna') ?>
                    </span>
                </div>
                <div class="asig-input-group">
                    <span class="asig-input-label">Dorsal</span>
                    <div>
                        <span class="badge badge-outline" style="font-size: 13px; font-weight: 700; padding: 4px 10px;">
                            <?= $a['nun_dorsal'] !== null ? '#' . (int)$a['nun_dorsal'] : 'S/D' ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="asig-atleta-row__actions">
                <a href="<?= e(url('/admin/atletas/' . $a['atleta_id'])) ?>" class="btn btn-sm btn-ghost" title="Ver Perfil Atleta" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                    <i class="ph ph-eye"></i>
                </a>
                <?php if (can('admin')): ?>
                    <a href="<?= e(url('/admin/asig-categorias/' . $a['asignacion_id'] . '/editar')) ?>" class="btn btn-sm btn-ghost" title="Editar Asignación" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                        <i class="ph ph-pencil-simple"></i>
                    </a>
                    <form method="POST" action="<?= e(url('/admin/asig-categorias/' . $a['asignacion_id'] . '/eliminar')) ?>" style="display:inline;">
                        <?= csrf_field() ?>
                        <button type="button" class="btn btn-sm btn-ghost text-danger btn-retirar-atleta" title="Retirar de Categoría" data-nombre="<?= e($a['nombre'] . ' ' . $a['apellido']) ?>" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="ph ph-trash"></i>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.btn-retirar-atleta').forEach(btn => {
        btn.addEventListener('click', () => {
            const form = btn.closest('form');
            const nombre = btn.getAttribute('data-nombre');

            CadaModal.confirm({
                title: '¿Retirar de la Categoría?',
                text: `¿Estás seguro de que deseas retirar a <strong>${nombre}</strong> de esta categoría? El atleta no será eliminado del sistema, pero perderá su dorsal y posición en este grupo.`,
                type: 'danger',
                confirmText: 'Sí, Retirar',
                cancelText: 'Cancelar'
            }).then(confirmed => {
                if (confirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
