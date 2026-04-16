<?php
/** @var array|null $item @var array $entrenadores @var string $action */
$c = $item ?? [];
$get = fn(string $k, $d = '') => old($k, $c[$k] ?? $d);
$isEdit = !empty($c['categoria_id']);
?>
<div class="page-header">
    <div>
        <h1><?= $isEdit ? 'Editar' : 'Nueva' ?> categoría</h1>
    </div>
    <a href="<?= e(url('/admin/categorias')) ?>" class="btn btn-ghost">← Volver</a>
</div>

<form method="POST" action="<?= e($action) ?>" class="card" style="max-width:640px">
    <?= csrf_field() ?>
    <div class="form-group">
        <label class="form-label"><span class="required">*</span> Nombre</label>
        <input type="text" name="nombre_categoria" class="form-control" required maxlength="50" value="<?= e($get('nombre_categoria')) ?>">
    </div>
    <div class="form-row">
        <div class="form-group">
            <label class="form-label"><span class="required">*</span> Edad mínima</label>
            <input type="number" name="edad_min" min="3" max="100" class="form-control" required value="<?= e($get('edad_min', 6)) ?>">
        </div>
        <div class="form-group">
            <label class="form-label"><span class="required">*</span> Edad máxima</label>
            <input type="number" name="edad_max" min="3" max="100" class="form-control" required value="<?= e($get('edad_max', 18)) ?>">
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label class="form-label">Entrenador asignado</label>
            <select name="entrenador_id" class="form-control">
                <option value="">Sin asignar</option>
                <?php foreach ($entrenadores as $e): ?>
                    <option value="<?= (int) $e['plantel_id'] ?>" <?= (int) $get('entrenador_id') === (int) $e['plantel_id'] ? 'selected' : '' ?>>
                        <?= e($e['nombre'] . ' ' . $e['apellido']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Estatus</label>
            <select name="estatus" class="form-control">
                <?php foreach (['Activa','Inactiva'] as $op): ?>
                    <option value="<?= e($op) ?>" <?= $get('estatus', 'Activa') === $op ? 'selected' : '' ?>><?= e($op) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="flex gap mt" style="justify-content:flex-end;">
        <a href="<?= e(url('/admin/categorias')) ?>" class="btn btn-ghost">Cancelar</a>
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Guardar' : 'Crear' ?></button>
    </div>
</form>
