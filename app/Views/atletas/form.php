<?php
/** @var array|null $atleta @var array $categorias @var array $posiciones @var array $paises @var string $action */
$a = $atleta ?? [];
$isEdit = !empty($a['atleta_id']);

$get = fn(string $k, $default = '') => old($k, $a[$k] ?? $default);
?>

<div class="page-header">
    <div>
        <h1><?= $isEdit ? 'Editar Atleta' : 'Registrar Nuevo Atleta' ?></h1>
        <div class="subtitle"><?= $isEdit ? e($a['nombre'] . ' ' . $a['apellido']) : 'Completa el formulario por pasos para añadir al equipo' ?></div>
    </div>
    <a href="<?= e(url('/admin/atletas')) ?>" class="btn btn-ghost"><i class="ph ph-arrow-left"></i> Volver</a>
</div>

<form method="POST" action="<?= e($action) ?>" enctype="multipart/form-data" class="card" style="max-width: 1000px; padding: 0; overflow: hidden;">
    <?= csrf_field() ?>

    <div class="form-tabs" role="tablist" style="background: var(--color-bg-alt); padding: 0 24px; border-bottom: 1px solid var(--color-border); display: flex; gap: 32px;">
        <button type="button" class="active" data-tab="tab-personal" style="padding: 16px 0; border: none; background: transparent; font-weight: 600; color: var(--color-primary); border-bottom: 2px solid var(--color-primary); cursor: pointer;"><i class="ph ph-user"></i> Datos Personales</button>
        <button type="button" data-tab="tab-direccion" style="padding: 16px 0; border: none; background: transparent; font-weight: 500; color: var(--color-text-muted); cursor: pointer;"><i class="ph ph-map-pin"></i> Ubicación</button>
        <button type="button" data-tab="tab-tutor" style="padding: 16px 0; border: none; background: transparent; font-weight: 500; color: var(--color-text-muted); cursor: pointer;"><i class="ph ph-users"></i> Representante</button>
        <button type="button" data-tab="tab-medica" style="padding: 16px 0; border: none; background: transparent; font-weight: 500; color: var(--color-text-muted); cursor: pointer;"><i class="ph ph-heartbeat"></i> Ficha Médica</button>
    </div>

    <div style="padding: 32px;">
        <!-- Datos personales -->
        <div id="tab-personal" class="form-tab-panel active">
            <h3 style="margin-top: 0; margin-bottom: 24px; font-family: var(--font-display); color: var(--color-text);"><i class="ph ph-identification-card text-muted"></i> Información Básica</h3>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Nombres</label>
                    <input type="text" name="nombre" class="form-control" required maxlength="50" value="<?= e($get('nombre')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Apellidos</label>
                    <input type="text" name="apellido" class="form-control" required maxlength="50" value="<?= e($get('apellido')) ?>">
                </div>
            </div>

            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Cédula</label>
                    <input type="text" name="cedula" class="form-control" maxlength="12" value="<?= e($get('cedula')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control" maxlength="15" value="<?= e($get('telefono')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Fecha de nacimiento</label>
                    <input type="date" name="fecha_nacimiento" class="form-control" required value="<?= e($get('fecha_nacimiento')) ?>">
                </div>
            </div>

            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Sexo</label>
                    <select name="sexo" class="form-control" required>
                        <option value="">Selecciona...</option>
                        <option value="M" <?= $get('sexo') === 'M' ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= $get('sexo') === 'F' ? 'selected' : '' ?>>Femenino</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Categoría</label>
                    <select name="categoria_id" class="form-control">
                        <option value="">Sin asignar</option>
                        <?php foreach ($categorias as $c): ?>
                            <option value="<?= (int) $c['categoria_id'] ?>" <?= ((int) $get('categoria_id') === (int) $c['categoria_id']) ? 'selected' : '' ?>>
                                <?= e($c['nombre_categoria']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Posición de juego</label>
                    <select name="posicion_de_juego" class="form-control">
                        <option value="">Sin definir</option>
                        <?php foreach ($posiciones as $p): ?>
                            <option value="<?= (int) $p['posicion_id'] ?>" <?= ((int) $get('posicion_de_juego') === (int) $p['posicion_id']) ? 'selected' : '' ?>>
                                <?= e($p['nombre_posicion']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Pierna dominante</label>
                    <select name="pierna_dominante" class="form-control">
                        <option value="">Selecciona...</option>
                        <?php foreach (PIERNA_DOMINANTE as $op): ?>
                            <option value="<?= e($op) ?>" <?= $get('pierna_dominante') === $op ? 'selected' : '' ?>><?= e(ucfirst($op)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Estatus</label>
                    <select name="estatus" class="form-control">
                        <?php foreach (ESTATUS_ATLETA as $op => $label):
                            $cur = $get('estatus', 1); ?>
                            <option value="<?= (int)$op ?>" <?= (int)$cur === (int)$op ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Foto de Perfil</label>
                    <input type="file" name="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
                    <?php if (!empty($a['foto'])): ?>
                        <div class="form-hint mt-2">
                            <img src="<?= e(url($a['foto'])) ?>" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid var(--color-border);">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Dirección con cascada -->
        <div id="tab-direccion" class="form-tab-panel" style="display: none;">
            <h3 style="margin-top: 0; margin-bottom: 24px; font-family: var(--font-display); color: var(--color-text);"><i class="ph ph-map-pin-line text-muted"></i> Datos de Residencia</h3>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Estado</label>
                    <select id="sel-estado" name="estado_id" class="form-control" data-current="<?= (int) ($a['estado_id'] ?? 0) ?>">
                        <option value="">Selecciona Estado...</option>
                        <!-- Simulando carga de estados por JS para el mock -->
                        <option value="17" selected>Portuguesa</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Municipio</label>
                    <select id="sel-municipio" name="municipio_id" class="form-control" data-current="<?= (int) ($a['municipio_id'] ?? 0) ?>">
                        <option value="">Selecciona Municipio...</option>
                        <option value="283" selected>Páez</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Parroquia</label>
                    <select id="sel-parroquia" name="parroquias_id" class="form-control" data-current="<?= (int) ($a['parroquias_id'] ?? 0) ?>">
                        <option value="">Selecciona Parroquia...</option>
                        <option value="723" selected>Acarigua</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Tipo de Vivienda</label>
                    <select name="tipo_vivienda" class="form-control">
                        <option value="casa">Casa</option>
                        <option value="apto">Apartamento</option>
                        <option value="edificio">Edificio</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Localidad (Barrio / Urbanización)</label>
                    <input type="text" name="localidad" class="form-control" maxlength="100" value="<?= e($get('localidad')) ?>" placeholder="Ej: Urb. La Goajira">
                </div>
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Dirección Exacta</label>
                    <input type="text" name="ubicacion_vivienda" class="form-control" maxlength="100" value="<?= e($get('ubicacion_vivienda')) ?>" placeholder="Ej: Calle 3, Vereda 5, Casa 12">
                </div>
            </div>
        </div>

        <!-- Tutor / representante -->
        <div id="tab-tutor" class="form-tab-panel" style="display: none;">
            <h3 style="margin-top: 0; margin-bottom: 24px; font-family: var(--font-display); color: var(--color-text);"><i class="ph ph-users text-muted"></i> Representante Legal</h3>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Nombre Completo</label>
                    <input type="text" name="rep_nombre" class="form-control" maxlength="150" value="<?= e($get('rep_nombre', $a['rep_nombre'] ?? '')) ?>" placeholder="Nombres y apellidos">
                </div>
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Cédula</label>
                    <input type="text" name="rep_cedula" class="form-control" maxlength="12" value="<?= e($get('rep_cedula', $a['rep_cedula'] ?? '')) ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Teléfono</label>
                    <input type="text" name="rep_telefono" class="form-control" maxlength="15" value="<?= e($get('rep_telefono', $a['rep_telefono'] ?? '')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Tipo de Relación</label>
                    <select name="rep_relacion" class="form-control">
                        <?php foreach (TIPO_RELACION_REPRESENTANTE as $op):
                            $cur = $get('rep_relacion', $a['rep_relacion'] ?? 'padres'); ?>
                            <option value="<?= e($op) ?>" <?= $cur === $op ? 'selected' : '' ?>><?= e(ucfirst($op)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Ficha médica -->
        <div id="tab-medica" class="form-tab-panel" style="display: none;">
            <h3 style="margin-top: 0; margin-bottom: 24px; font-family: var(--font-display); color: var(--color-text);"><i class="ph ph-first-aid text-muted"></i> Registro Médico Inicial</h3>
            <?php if (!can('admin') && !can('medico')): ?>
                <div class="alert alert-info"><i class="ph ph-info"></i> Solo el personal médico o administrativo puede editar la ficha médica.</div>
                <fieldset disabled>
            <?php endif; ?>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Grupo Sanguíneo</label>
                    <select name="grupo_sanguineo" class="form-control">
                        <option value="">Selecciona...</option>
                        <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $t):
                            $cur = $get('grupo_sanguineo'); ?>
                            <option value="<?= e($t) ?>" <?= $cur === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Alergias Conocidas</label>
                    <input type="text" name="alergias" class="form-control" value="<?= e($get('alergias')) ?>" placeholder="Medicamentos, alimentos, etc.">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Antecedentes Quirúrgicos</label>
                    <textarea name="antecedentes_quirurgicos" class="form-control" rows="2" placeholder="Operaciones previas..."><?= e($get('antecedentes_quirurgicos')) ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Condición Crónica</label>
                    <textarea name="condicion_cronica" class="form-control" rows="2" placeholder="Asma, diabetes, etc."><?= e($get('condicion_cronica')) ?></textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Medicamento Actual</label>
                <textarea name="medicacion_actual" class="form-control" rows="2"><?= e($get('medicacion_actual')) ?></textarea>
            </div>
            <?php if (!can('admin') && !can('medico')): ?></fieldset><?php endif; ?>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--color-border);">
            <a href="<?= e(url('/admin/atletas')) ?>" class="btn btn-ghost">Cancelar</a>
            <button type="submit" class="btn btn-primary"><i class="ph ph-check"></i> <?= $isEdit ? 'Guardar Cambios' : 'Finalizar Registro' ?></button>
        </div>
    </div>
</form>

<script>
// Manejo de Pestañas (Tabs)
document.querySelectorAll('.form-tabs button').forEach(btn => {
    btn.addEventListener('click', () => {
        // Limpiar estados activos
        document.querySelectorAll('.form-tabs button').forEach(b => {
            b.classList.remove('active');
            b.style.color = 'var(--color-text-muted)';
            b.style.borderBottom = 'none';
            b.style.fontWeight = '500';
        });
        document.querySelectorAll('.form-tab-panel').forEach(p => p.style.display = 'none');
        
        // Activar seleccionado
        btn.classList.add('active');
        btn.style.color = 'var(--color-primary)';
        btn.style.borderBottom = '2px solid var(--color-primary)';
        btn.style.fontWeight = '600';
        document.getElementById(btn.dataset.tab).style.display = 'block';
    });
});
</script>
