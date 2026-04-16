<?php
/** @var array|null $atleta @var array $categorias @var array $posiciones @var array $paises @var string $action */
$a = $atleta ?? [];
$isEdit = !empty($a['atleta_id']);

$get = fn(string $k, $default = '') => old($k, $a[$k] ?? $default);
?>

<div class="page-header">
    <div>
        <h1><?= $isEdit ? 'Editar atleta' : 'Nuevo atleta' ?></h1>
        <div class="subtitle"><?= $isEdit ? e($a['nombre'] . ' ' . $a['apellido']) : 'Completa el formulario para registrar un nuevo atleta' ?></div>
    </div>
    <a href="<?= e(url('/admin/atletas')) ?>" class="btn btn-ghost">← Volver</a>
</div>

<form method="POST" action="<?= e($action) ?>" enctype="multipart/form-data" class="card" style="max-width:1000px;">
    <?= csrf_field() ?>

    <div class="form-tabs" role="tablist">
        <button type="button" class="active" data-tab="tab-personal">Datos personales</button>
        <button type="button" data-tab="tab-direccion">Dirección</button>
        <button type="button" data-tab="tab-tutor">Representante</button>
        <button type="button" data-tab="tab-medica">Ficha médica</button>
    </div>

    <!-- Datos personales -->
    <div id="tab-personal" class="form-tab-panel active">
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
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono" class="form-control" maxlength="20" value="<?= e($get('telefono')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label"><span class="required">*</span> Fecha de nacimiento</label>
                <input type="date" name="fecha_nacimiento" class="form-control" required value="<?= e($get('fecha_nacimiento')) ?>">
            </div>
        </div>

        <div class="form-row-3">
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
            <div class="form-group">
                <label class="form-label">Pierna dominante</label>
                <select name="pierna_dominante" class="form-control">
                    <option value="">—</option>
                    <?php foreach (PIERNA_DOMINANTE as $op): ?>
                        <option value="<?= e($op) ?>" <?= $get('pierna_dominante') === $op ? 'selected' : '' ?>><?= e($op) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Estatus</label>
                <select name="estatus" class="form-control">
                    <?php foreach (ESTATUS_ATLETA as $op => $label):
                        $cur = $get('estatus', 'Activo'); ?>
                        <option value="<?= e($op) ?>" <?= $cur === $op ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Foto (JPG/PNG/WebP, máx 2MB)</label>
                <input type="file" name="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
                <?php if (!empty($a['foto'])): ?>
                    <div class="form-hint"><img src="<?= e(url($a['foto'])) ?>" style="width:80px; height:80px; border-radius:8px; margin-top:8px; object-fit:cover;"></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Dirección con cascada -->
    <div id="tab-direccion" class="form-tab-panel">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">País</label>
                <select id="sel-pais" class="form-control">
                    <option value="">Selecciona...</option>
                    <?php foreach ($paises as $p): ?>
                        <option value="<?= (int) $p['pais_id'] ?>" <?= ((int) ($a['pais_id'] ?? 0) === (int) $p['pais_id']) ? 'selected' : '' ?>>
                            <?= e($p['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Estado</label>
                <select id="sel-estado" class="form-control" data-current="<?= (int) ($a['estado_id'] ?? 0) ?>">
                    <option value="">Selecciona...</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Municipio</label>
                <select id="sel-municipio" class="form-control" data-current="<?= (int) ($a['municipio_id'] ?? 0) ?>">
                    <option value="">Selecciona...</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Parroquia</label>
                <select id="sel-parroquia" name="parroquia_id" class="form-control" data-current="<?= (int) ($a['parroquia_id'] ?? 0) ?>">
                    <option value="">Selecciona...</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Calle / avenida</label>
                <input type="text" name="calle_avenida" class="form-control" maxlength="100" value="<?= e($get('calle_avenida')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Casa / edificio</label>
                <input type="text" name="casa_edificio" class="form-control" maxlength="50" value="<?= e($get('casa_edificio')) ?>">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Punto de referencia</label>
            <input type="text" name="punto_referencia" class="form-control" maxlength="255" value="<?= e($get('punto_referencia')) ?>">
        </div>
    </div>

    <!-- Tutor / representante -->
    <div id="tab-tutor" class="form-tab-panel">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Nombres del representante</label>
                <input type="text" name="tutor_nombres" class="form-control" maxlength="100" value="<?= e($get('tutor_nombres', $a['tutor_nombres'] ?? '')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Apellidos del representante</label>
                <input type="text" name="tutor_apellidos" class="form-control" maxlength="100" value="<?= e($get('tutor_apellidos', $a['tutor_apellidos'] ?? '')) ?>">
            </div>
        </div>
        <div class="form-row-3">
            <div class="form-group">
                <label class="form-label">Cédula</label>
                <input type="text" name="tutor_cedula" class="form-control" maxlength="20" value="<?= e($get('tutor_cedula', $a['tutor_cedula'] ?? '')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Teléfono</label>
                <input type="text" name="tutor_telefono" class="form-control" maxlength="20" value="<?= e($get('tutor_telefono', $a['tutor_telefono'] ?? '')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Tipo de relación</label>
                <select name="tutor_relacion" class="form-control">
                    <?php foreach (TIPO_RELACION_TUTOR as $op):
                        $cur = $get('tutor_relacion', $a['tutor_relacion'] ?? 'Padre'); ?>
                        <option value="<?= e($op) ?>" <?= $cur === $op ? 'selected' : '' ?>><?= e($op) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Correo electrónico</label>
            <input type="email" name="tutor_correo" class="form-control" maxlength="100" value="<?= e($get('tutor_correo', $a['tutor_correo'] ?? '')) ?>">
        </div>
    </div>

    <!-- Ficha médica -->
    <div id="tab-medica" class="form-tab-panel">
        <?php if (!can('admin')): ?>
            <div class="alert alert-info">Solo el administrador puede editar la ficha médica.</div>
            <fieldset disabled>
        <?php endif; ?>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Tipo sanguíneo</label>
                <select name="tipo_sanguineo" class="form-control">
                    <option value="">—</option>
                    <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $t):
                        $cur = $get('tipo_sanguineo'); ?>
                        <option value="<?= e($t) ?>" <?= $cur === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Alergias</label>
                <input type="text" name="alergias" class="form-control" value="<?= e($get('alergias')) ?>">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Lesiones anteriores</label>
            <textarea name="lesion" class="form-control" rows="2"><?= e($get('lesion')) ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Condiciones médicas</label>
            <textarea name="condicion_medica" class="form-control" rows="2"><?= e($get('condicion_medica')) ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Observaciones</label>
            <textarea name="observacion" class="form-control" rows="2"><?= e($get('observacion')) ?></textarea>
        </div>
        <?php if (!can('admin')): ?></fieldset><?php endif; ?>
    </div>

    <div class="flex gap mt" style="justify-content:flex-end;">
        <a href="<?= e(url('/admin/atletas')) ?>" class="btn btn-ghost">Cancelar</a>
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Guardar cambios' : 'Crear atleta' ?></button>
    </div>
</form>

<script>
// Tabs del formulario
document.querySelectorAll('.form-tabs button').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.form-tabs button').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.form-tab-panel').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(btn.dataset.tab).classList.add('active');
    });
});

// Cascada de ubicaciones
(function () {
    const $pais = document.getElementById('sel-pais');
    const $estado = document.getElementById('sel-estado');
    const $municipio = document.getElementById('sel-municipio');
    const $parroquia = document.getElementById('sel-parroquia');

    async function load(select, url, currentId = 0) {
        // Mantener el primer option placeholder
        const placeholder = select.options[0];
        select.innerHTML = '';
        select.appendChild(placeholder);
        if (!url) return;
        try {
            const items = await API.get(url);
            items.forEach(it => {
                const opt = document.createElement('option');
                const id = it.pais_id || it.estado_id || it.municipio_id || it.parroquia_id;
                opt.value = id;
                opt.textContent = it.nombre;
                if (parseInt(currentId) === parseInt(id)) opt.selected = true;
                select.appendChild(opt);
            });
        } catch (e) {
            console.error('Error cargando ubicaciones', e);
        }
    }

    async function onPais() {
        const id = $pais.value;
        await load($estado, id ? `<?= e(url('/api/ubicaciones/estados')) ?>/${id}` : null, $estado.dataset.current || 0);
        $estado.dataset.current = 0;
        await onEstado();
    }
    async function onEstado() {
        const id = $estado.value;
        await load($municipio, id ? `<?= e(url('/api/ubicaciones/municipios')) ?>/${id}` : null, $municipio.dataset.current || 0);
        $municipio.dataset.current = 0;
        await onMunicipio();
    }
    async function onMunicipio() {
        const id = $municipio.value;
        await load($parroquia, id ? `<?= e(url('/api/ubicaciones/parroquias')) ?>/${id}` : null, $parroquia.dataset.current || 0);
        $parroquia.dataset.current = 0;
    }

    $pais?.addEventListener('change', onPais);
    $estado?.addEventListener('change', onEstado);
    $municipio?.addEventListener('change', onMunicipio);

    // Carga inicial si hay país seleccionado
    if ($pais && $pais.value) onPais();
})();
</script>
