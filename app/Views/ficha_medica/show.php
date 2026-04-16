<?php /** @var array $atleta */ $readonly = !can('admin'); ?>
<div class="page-header">
    <div>
        <h1>Ficha médica</h1>
        <div class="subtitle"><?= e($atleta['nombre'] . ' ' . $atleta['apellido']) ?></div>
    </div>
    <a href="<?= e(url("/admin/atletas/{$atleta['atleta_id']}")) ?>" class="btn btn-ghost">← Ver atleta</a>
</div>

<?php if ($readonly): ?>
    <div class="alert alert-info">Solo el administrador puede editar la ficha médica.</div>
<?php endif; ?>

<form method="POST" action="<?= e(url("/admin/ficha-medica/{$atleta['atleta_id']}")) ?>" class="card" style="max-width:800px">
    <?= csrf_field() ?>
    <?php if ($readonly): ?><fieldset disabled><?php endif; ?>
    <div class="form-row">
        <div class="form-group">
            <label class="form-label">Tipo sanguíneo</label>
            <select name="tipo_sanguineo" class="form-control">
                <option value="">—</option>
                <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $t): ?>
                    <option value="<?= e($t) ?>" <?= ($atleta['tipo_sanguineo'] ?? '') === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Alergias</label>
            <input type="text" name="alergias" class="form-control" value="<?= e($atleta['alergias'] ?? '') ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="form-label">Lesiones</label>
        <textarea name="lesion" class="form-control" rows="3"><?= e($atleta['lesion'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
        <label class="form-label">Condiciones médicas</label>
        <textarea name="condicion_medica" class="form-control" rows="3"><?= e($atleta['condicion_medica'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
        <label class="form-label">Observaciones</label>
        <textarea name="observacion" class="form-control" rows="3"><?= e($atleta['observacion'] ?? '') ?></textarea>
    </div>
    <?php if ($readonly): ?>
        </fieldset>
    <?php else: ?>
        <button type="submit" class="btn btn-primary">Guardar ficha médica</button>
    <?php endif; ?>
</form>
