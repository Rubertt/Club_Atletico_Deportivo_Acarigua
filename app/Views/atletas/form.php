<?php
/** @var array|null $atleta @var array $paises @var string $action */
$a = $atleta ?? [];
$isEdit = !empty($a['atleta_id']);

$get = fn(string $k, $default = '') => old($k, $a[$k] ?? $default);
?>

<div class="af-container">
    <div class="page-header af-header">
        <div class="af-header__content">
            <h1><?= $isEdit ? 'Editar Atleta' : 'Registrar Atleta' ?></h1>
            <p class="subtitle"><?= $isEdit ? e($a['nombre'] . ' ' . $a['apellido']) : 'Ingresa los datos para la ficha oficial del club' ?></p>
        </div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <a href="<?= e(url('/admin/atletas')) ?>" class="btn btn-ghost af-back-btn">
                <i class="ph ph-arrow-left"></i> <span>Volver</span>
            </a>
        </div>
    </div>

    <form method="POST" action="<?= e($action) ?>" enctype="multipart/form-data" class="card af-card" novalidate>
        <?= csrf_field() ?>

        <div class="af-tabs-wrapper">
            <div class="af-tabs" role="tablist">
                <button type="button" class="ft-tab active" data-tab="tab-personal">
                    <div class="ft-tab__icon"><i class="ph ph-user"></i></div>
                    <div class="ft-tab__text">Personal</div>
                </button>
                <button type="button" class="ft-tab" data-tab="tab-direccion">
                    <div class="ft-tab__icon"><i class="ph ph-map-pin"></i></div>
                    <div class="ft-tab__text">Ubicaci&oacute;n</div>
                </button>
                <button type="button" class="ft-tab" data-tab="tab-tutor">
                    <div class="ft-tab__icon"><i class="ph ph-users-three"></i></div>
                    <div class="ft-tab__text">Representante</div>
                </button>
            </div>
        </div>

        <div class="af-body">
            <?php include __DIR__ . '/partials/form_registro/_tab_personal.php'; ?>
            <?php include __DIR__ . '/partials/form_registro/_tab_ubicacion.php'; ?>
            <?php include __DIR__ . '/partials/form_registro/_tab_representante.php'; ?>
        </div>

        <div class="af-footer">
            <div class="af-footer-info">
                <i class="ph ph-info"></i> Paso <span id="current-step-num">1</span> de 3
            </div>
            <div class="af-actions" style="display: flex; gap: 12px; align-items: center;">
                <button type="button" class="btn btn-ghost" id="btn-reset" title="Borrar todo"><i class="ph ph-trash"></i> Limpiar</button>
                <div class="af-actions-sep"></div>
                <button type="button" class="btn btn-ghost" id="btn-prev" style="display:none;"><i class="ph ph-caret-left"></i> Anterior</button>
                <button type="button" class="btn btn-primary" id="btn-next">Siguiente <i class="ph ph-caret-right"></i></button>
                <button type="submit" class="btn btn-primary af-submit-btn" id="btn-submit" style="display:none;">
                    <span><?= $isEdit ? 'Guardar Cambios' : 'Finalizar Registro' ?></span>
                    <i class="ph ph-check-circle"></i>
                </button>
            </div>
        </div>
    </form>
</div>

<?php include __DIR__ . '/partials/form_registro/_styles.php'; ?>
<?php include __DIR__ . '/partials/form_registro/_scripts.php'; ?>
