<?php
/** @var array|null $item @var array $roles @var string $action */
$p = $item ?? [];
$get = fn(string $k, $d = '') => old($k, $p[$k] ?? $d);
$isEdit = !empty($p['plantel_id']);
?>
<div class="page-header">
    <div><h1><?= $isEdit ? 'Editar' : 'Nuevo' ?> miembro del plantel</h1></div>
    <a href="<?= e(url('/admin/plantel')) ?>" class="btn btn-ghost">← Volver</a>
</div>

<form method="POST" action="<?= e($action) ?>" class="card" style="max-width:720px">
    <?= csrf_field() ?>
    <div class="form-row">
        <div class="form-group">
            <label class="form-label"><span class="required">*</span> Nombre</label>
            <input type="text" name="nombre" class="form-control" required maxlength="100" value="<?= e($get('nombre')) ?>">
        </div>
        <div class="form-group">
            <label class="form-label"><span class="required">*</span> Apellido</label>
            <input type="text" name="apellido" class="form-control" required maxlength="100" value="<?= e($get('apellido')) ?>">
        </div>
    </div>
    <div class="form-row-3">
        <div class="form-group">
            <label class="form-label">Cédula</label>
            <input type="text" name="cedula" class="form-control" maxlength="20" value="<?= e($get('cedula')) ?>">
        </div>
        <div class="form-group">
            <label class="form-label"><span class="required">*</span> Teléfono</label>
            <input type="text" name="telefono" class="form-control" required maxlength="20" value="<?= e($get('telefono')) ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Fecha de nacimiento</label>
            <input type="date" name="fecha_nac" class="form-control" value="<?= e($get('fecha_nac')) ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="form-label"><span class="required">*</span> Rol</label>
        <select name="rol_id" class="form-control" required>
            <option value="">Selecciona...</option>
            <?php foreach ($roles as $r): ?>
                <option value="<?= (int) $r['rol_id'] ?>" <?= (int) $get('rol_id') === (int) $r['rol_id'] ? 'selected' : '' ?>>
                    <?= e($r['nombre_rol']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="flex gap mt" style="justify-content:flex-end;">
        <a href="<?= e(url('/admin/plantel')) ?>" class="btn btn-ghost">Cancelar</a>
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Guardar' : 'Crear' ?></button>
    </div>
</form>
