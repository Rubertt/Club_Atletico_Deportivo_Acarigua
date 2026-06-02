<?php
/** @var array|null $item @var array $entrenadores @var string $action */
$c = $item ?? [];
$get = fn(string $k, $d = '') => old($k, $c[$k] ?? $d);
$isEdit = !empty($c['categoria_id']);
$hasAthletes = $isEdit && !empty($c['total_atletas']) && (int)$c['total_atletas'] > 0;
?>

<div class="page-header">
    <div>
        <?php if ($isEdit): ?>
            <!-- Elemento oculto intencionalmente -->
        <?php endif; ?>
        <h1><?= $isEdit ? 'Editar' : 'Crear' ?> Categoría</h1>
        <div class="subtitle"><?= $isEdit ? 'Modifica los parámetros de la categoría' : 'Define un nuevo grupo por rango de edad' ?></div>
    </div>
    <div style="display: flex; gap: 12px; align-items: center;">
        <a href="<?= e(url('/admin/categorias')) ?>" class="btn btn-ghost"><i class="ph ph-arrow-left"></i> Volver</a>
    </div>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto; padding: 0; overflow: hidden;">
    <div style="padding: 24px; background: var(--color-surface); border-bottom: 1px solid var(--color-border);">
        <h3 style="margin: 0; font-size: 18px; display: flex; align-items: center; gap: 10px;">
            <i class="ph ph-info" style="color: var(--color-primary)"></i> Información General
        </h3>
    </div>

    <form id="form-categoria" method="POST" action="<?= e($action) ?>" style="padding: 32px;" novalidate>
        <?= csrf_field() ?>
        
        <!-- Fila 1: Sexo, Edad Mínima, Edad Máxima -->
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 24px; margin-bottom: 24px;">
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Género del grupo: Masculino, Femenino o Mixto. Bloqueado si el grupo ya tiene atletas registrados." data-tooltip-pos="top"><span class="required">*</span> Género <?= $hasAthletes ? ' <span class="text-muted" style="font-size:11px; font-weight:normal;">(Bloqueado)</span>' : '' ?></label>
                <div style="position: relative;">
                    <i class="ph ph-gender-intersex" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted); z-index: 10;"></i>
                    <select name="sexo_categoria" class="form-control" style="padding-left: 40px;" required <?= $hasAthletes ? 'disabled' : '' ?>>
                        <option value="">— Seleccione —</option>
                        <option value="M" <?= $get('sexo_categoria', '') === 'M' ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= $get('sexo_categoria', '') === 'F' ? 'selected' : '' ?>>Femenino</option>
                        <option value="X" <?= $get('sexo_categoria', '') === 'X' ? 'selected' : '' ?>>Mixto</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Edad mínima del rango para este grupo (entre 6 y 100 años). Debe ser menor que la edad máxima." data-tooltip-pos="top"><span class="required">*</span> Edad Mínima <?= $hasAthletes ? ' <span class="text-muted" style="font-size:11px; font-weight:normal;">(Bloqueado)</span>' : '' ?></label>
                <div style="position: relative;">
                    <i class="ph ph-user-circle" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted);"></i>
                    <input type="number" name="edad_min" min="6" max="100" class="form-control" style="padding-left: 40px;" 
                           required value="<?= e($get('edad_min', 6)) ?>" <?= $hasAthletes ? 'disabled' : '' ?>>
                </div>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Edad máxima del rango para este grupo (entre 6 y 100 años). Determina el nombre automático (ej: Sub-18)." data-tooltip-pos="top"><span class="required">*</span> Edad Máxima <?= $hasAthletes ? ' <span class="text-muted" style="font-size:11px; font-weight:normal;">(Bloqueado)</span>' : '' ?></label>
                <div style="position: relative;">
                    <i class="ph ph-user-circle-plus" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted);"></i>
                    <input type="number" name="edad_max" min="6" max="100" class="form-control" style="padding-left: 40px;" 
                           required value="<?= e($get('edad_max', 18)) ?>" <?= $hasAthletes ? 'disabled' : '' ?>>
                </div>
            </div>
        </div>

        <?php if ($hasAthletes): ?>
            <input type="hidden" name="sexo_categoria" value="<?= e($get('sexo_categoria')) ?>">
            <input type="hidden" name="edad_min" value="<?= e($get('edad_min')) ?>">
            <input type="hidden" name="edad_max" value="<?= e($get('edad_max')) ?>">
        <?php endif; ?>

        <!-- Fila 2: Nombre de Categoría (Readonly), Enlistador Responsable, Estatus (si es Edición) -->
        <div style="display: grid; grid-template-columns: <?= $isEdit ? '1fr 1fr 1fr' : '1fr 1fr' ?>; gap: 24px; margin-bottom: 24px;">
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Nombre generado automáticamente según la edad máxima y el género seleccionados." data-tooltip-pos="top">Nombre de la Categoría</label>
                <div style="position: relative;">
                    <i class="ph ph-tag" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted);"></i>
                    <input type="text" name="nombre_categoria" id="nombre_categoria" class="form-control" style="padding-left: 40px; background-color: var(--color-bg-alt);" 
                           placeholder="Se generará automáticamente..." required readonly 
                           value="<?= e($get('nombre_categoria', '')) ?>">
                </div>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Personal técnico a cargo de tomar las asistencias y registrar evaluaciones físicas en esta categoría." data-tooltip-pos="top"><span class="required">*</span> Enlistador</label>
                <div style="position: relative;">
                    <i class="ph ph-user-gear" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted); z-index: 10;"></i>
                    <select name="usuario_id" class="form-control" style="padding-left: 40px;" required>
                        <option value="">— Seleccione —</option>
                        <?php foreach ($entrenadores as $e): ?>
                            <option value="<?= (int) $e['usuario_id'] ?>" <?= (int) $get('usuario_id', '') === (int) $e['usuario_id'] ? 'selected' : '' ?>>
                                <?= e($e['nombre'] . ' ' . $e['apellido']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?php if ($isEdit): ?>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" data-tooltip="Estatus del grupo. No se puede desactivar una categoría que tenga atletas asignados." data-tooltip-pos="top">Estado</label>
                <div style="position: relative;">
                    <i class="ph ph-toggle-left" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted); z-index: 10;"></i>
                    <select name="estatus" class="form-control" style="padding-left: 40px;">
                        <option value="1" <?= (int) $get('estatus', 1) === 1 ? 'selected' : '' ?>>Activa</option>
                        <option value="2" <?= (int) $get('estatus', 1) === 2 ? 'selected' : '' ?> <?= $hasAthletes ? 'disabled' : '' ?>>
                            Inactiva <?= $hasAthletes ? ' (Bloqueado: tiene atletas)' : '' ?>
                        </option>
                    </select>
                </div>
            </div>
            <?php else: ?>
                <input type="hidden" name="estatus" value="1">
            <?php endif; ?>
        </div>

        <div style="background: var(--color-surface); margin: 32px -32px -32px; padding: 24px 32px; border-top: 1px solid var(--color-border); display: flex; justify-content: flex-end; align-items: center; gap: 16px;">
            <a href="<?= e(url('/admin/categorias')) ?>" class="btn btn-ghost">Cancelar</a>
            <button type="submit" class="btn btn-primary btn-lg" style="padding-left: 40px; padding-right: 40px;">
                <i class="ph ph-floppy-disk"></i> <?= $isEdit ? 'Guardar Cambios' : 'Crear Categoría' ?>
            </button>
            <button type="button" class="btn-help" id="btn-help-categoria" title="¿Cómo llenar este formulario?" style="width: 44px; height: 44px;">
                <i class="ph ph-question"></i>
            </button>
        </div>
    </form>
</div>

<style>
/* Solucionar visibilidad de las flechitas de input number en modo oscuro */
html.dark input[type="number"] {
    color-scheme: dark;
}

#form-categoria .form-control {
    height: 44px;
    background: var(--color-surface);
    border-color: var(--color-border);
    transition: all 0.2s;
}

#form-categoria .form-control:focus {
    background: var(--color-bg);
    box-shadow: 0 0 0 4px rgba(190, 18, 60, 0.08);
}

#form-categoria select.form-control {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%236b7280' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px;
    padding-right: 40px;
    cursor: pointer;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Botón de ayuda [?]
    document.getElementById('btn-help-categoria')?.addEventListener('click', () => {
        FormValidator.showHelp(
            'Guía: Registro de Categoría',
            '<?= e(asset("img/ayuda/formulario_categoria.png")) ?>'
        );
    });

    const inputNombre = document.getElementById('nombre_categoria');
    const inputEdadMax = document.querySelector('input[name="edad_max"]');
    const selectSexo = document.querySelector('select[name="sexo_categoria"]');

    function actualizarNombre() {
        const edadMaxVal = inputEdadMax.value.trim();
        const sexoVal = selectSexo.value;

        if (!edadMaxVal || !sexoVal) {
            inputNombre.value = '';
            return;
        }

        let sexoLetra = '';
        if (sexoVal === 'M') {
            sexoLetra = '(m)';
        } else if (sexoVal === 'F') {
            sexoLetra = '(f)';
        } else if (sexoVal === 'X') {
            sexoLetra = '(mix)';
        }

        inputNombre.value = 'sub-' + edadMaxVal + sexoLetra;
    }

    inputEdadMax.addEventListener('input', actualizarNombre);
    selectSexo.addEventListener('change', actualizarNombre);

    // Si no tiene valor (creación), inicializar
    if (!inputNombre.value) {
        actualizarNombre();
    }

    // Validación estándar al submit
    FormValidator.init('#form-categoria', {
        custom: (form) => {
            const errors = [];
            const min = parseInt(form.querySelector('input[name="edad_min"]')?.value);
            const max = parseInt(form.querySelector('input[name="edad_max"]')?.value);
            if (!isNaN(min) && !isNaN(max) && min >= max) {
                errors.push({ label: 'La edad mínima debe ser estrictamente menor que la edad máxima', element: form.querySelector('input[name="edad_min"]') });
            }
            return errors;
        }
    });
});
</script>
