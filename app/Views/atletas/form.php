<?php
/** @var array|null $atleta @var array $categorias @var array $posiciones @var array $paises @var string $action */
$a = $atleta ?? [];
$isEdit = !empty($a['atleta_id']);

$get = fn(string $k, $default = '') => $a[$k] ?? $default;
?>

<div class="af-container">
    <div class="page-header af-header">
        <div class="af-header__content">
            <h1><?= $isEdit ? 'Editar Atleta' : 'Registrar Atleta' ?></h1>
            <p class="subtitle"><?= $isEdit ? e($a['nombre'] . ' ' . $a['apellido']) : 'Ingresa los datos para la ficha oficial del club' ?></p>
        </div>
        <a href="<?= e(url('/admin/atletas')) ?>" class="btn btn-ghost af-back-btn">
            <i class="ph ph-arrow-left"></i> <span>Volver</span>
        </a>
    </div>

    <form method="POST" action="<?= e($action) ?>" enctype="multipart/form-data" class="card af-card">
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
                <button type="button" class="ft-tab" data-tab="tab-medica">
                    <div class="ft-tab__icon"><i class="ph ph-heartbeat"></i></div>
                    <div class="ft-tab__text">Ficha M&eacute;dica</div>
                </button>
            </div>
        </div>

        <div class="af-body">
            <!-- Datos personales -->
            <div id="tab-personal" class="form-tab-panel active">
                <div class="af-section-header">
                    <div class="af-section-icon"><i class="ph ph-identification-card"></i></div>
                    <div class="af-section-info">
                        <h3>Información Básica</h3>
                        <p>Datos de identificación y contacto del deportista</p>
                    </div>
                </div>

                <div class="af-grid af-grid--2">
                    <div class="form-group">
                        <label class="form-label"><span class="required">*</span> Nombres</label>
                        <input type="text" name="nombre" class="form-control" required maxlength="50" value="<?= e($get('nombre')) ?>" placeholder="Ej: Juan Carlos">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><span class="required">*</span> Apellidos</label>
                        <input type="text" name="apellido" class="form-control" required maxlength="50" value="<?= e($get('apellido')) ?>" placeholder="Ej: Pérez Rodríguez">
                    </div>
                </div>

                <div class="af-grid af-grid--3">
                    <div class="form-group">
                        <label class="form-label">Cédula</label>
                        <input type="text" id="cedula" name="cedula" class="form-control" maxlength="13"
                               placeholder="V-12.345.678"
                               autocomplete="off"
                               value="<?= e($get('cedula')) ?>">
                        <span class="field-error" id="cedula-error"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tel&eacute;fono</label>
                        <?php
                            $telVal   = $get('telefono');
                            $telPref  = '';
                            $telNum   = '';
                            foreach (['0412','0414','0416','0422','0424','0426'] as $_p) {
                                if (str_starts_with($telVal, $_p)) { $telPref = $_p; $telNum = substr($telVal, 4); break; }
                            }
                        ?>
                        <div class="phone-field" id="phone-wrap-telefono">
                            <select class="phone-prefix" id="telefono_prefix" aria-label="Prefijo">
                                <option value="0412" <?= $telPref==='0412'?'selected':'' ?>>0412</option>
                                <option value="0414" <?= $telPref==='0414'?'selected':'' ?>>0414</option>
                                <option value="0416" <?= $telPref==='0416'?'selected':'' ?>>0416</option>
                                <option value="0422" <?= $telPref==='0422'?'selected':'' ?>>0422</option>
                                <option value="0424" <?= $telPref==='0424'?'selected':'' ?>>0424</option>
                                <option value="0426" <?= $telPref==='0426'?'selected':'' ?>>0426</option>
                            </select>
                            <span class="phone-sep">-</span>
                            <input type="text" class="phone-number" id="telefono_number"
                                   maxlength="7" placeholder="1234567"
                                   autocomplete="off" inputmode="numeric"
                                   value="<?= e($telNum) ?>">
                            <input type="hidden" name="telefono" id="telefono">
                        </div>
                        <span class="field-error" id="telefono-error"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><span class="required">*</span> Fecha de nacimiento</label>
                        <input type="date" name="fecha_nacimiento" class="form-control" required value="<?= e($get('fecha_nacimiento')) ?>">
                    </div>
                </div>

                <div class="af-grid af-grid--3">
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

                <div class="af-grid af-grid--3">
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
                        <div class="af-file-upload">
                            <input type="file" name="foto" id="foto-input" class="af-file-input" accept="image/jpeg,image/png,image/webp">
                            <label for="foto-input" class="af-file-label" id="foto-label">
                                <i class="ph ph-camera"></i>
                                <span>Subir foto</span>
                            </label>
                            <div class="af-file-preview" id="foto-preview-container" style="<?= empty($a['foto']) ? 'display:none;' : '' ?>">
                                <img src="<?= !empty($a['foto']) ? e(url($a['foto'])) : '' ?>" id="foto-preview-img" alt="Vista previa">
                                <button type="button" class="af-file-remove" id="btn-remove-foto" title="Quitar foto"><i class="ph ph-x"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dirección -->
            <div id="tab-direccion" class="form-tab-panel">
                <div class="af-section-header">
                    <div class="af-section-icon"><i class="ph ph-map-pin-line"></i></div>
                    <div class="af-section-info">
                        <h3>Datos de Residencia</h3>
                        <p>Ubicación geográfica del domicilio del atleta</p>
                    </div>
                </div>

                <div class="af-grid af-grid--2">
                    <div class="form-group">
                        <label class="form-label"><span class="required">*</span> Estado</label>
                        <select id="sel-estado" name="estado_id" class="form-control" data-current="<?= (int) ($a['estado_id'] ?? 0) ?>">
                            <option value="">Selecciona Estado...</option>
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

                <div class="af-grid af-grid--2">
                    <div class="form-group">
                        <label class="form-label"><span class="required">*</span> Parroquia</label>
                        <select id="sel-parroquia" name="parroquia_id" class="form-control" data-current="<?= (int) ($a['parroquia_id'] ?? 0) ?>">
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

                <div class="af-grid af-grid--2">
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

            <!-- Representante -->
            <div id="tab-tutor" class="form-tab-panel">
                <div class="af-section-header">
                    <div class="af-section-icon"><i class="ph ph-users"></i></div>
                    <div class="af-section-info">
                        <h3>Representante Legal</h3>
                        <p>Persona responsable del menor de edad</p>
                    </div>
                </div>

                <div class="af-grid af-grid--2">
                    <div class="form-group">
                        <label class="form-label"><span class="required">*</span> Nombres</label>
                        <input type="text" name="tutor_nombres" class="form-control" maxlength="100" value="<?= e($get('tutor_nombres', $a['tutor_nombres'] ?? '')) ?>" placeholder="Nombres del representante">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><span class="required">*</span> Apellidos</label>
                        <input type="text" name="tutor_apellidos" class="form-control" maxlength="100" value="<?= e($get('tutor_apellidos', $a['tutor_apellidos'] ?? '')) ?>" placeholder="Apellidos del representante">
                    </div>
                </div>

                <div class="af-grid af-grid--2">
                    <div class="form-group">
                        <label class="form-label"><span class="required">*</span> Cédula</label>
                        <input type="text" id="tutor_cedula" name="tutor_cedula" class="form-control" maxlength="13"
                               placeholder="V-12.345.678"
                               autocomplete="off"
                               value="<?= e($get('tutor_cedula', $a['tutor_cedula'] ?? '')) ?>">
                        <span class="field-error" id="tutor_cedula-error"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><span class="required">*</span> Tel&eacute;fono</label>
                        <?php
                            $repTelVal  = $get('tutor_telefono', $a['tutor_telefono'] ?? '');
                            $repTelPref = '';
                            $repTelNum  = '';
                            foreach (['0412','0414','0416','0422','0424','0426'] as $_p) {
                                if (str_starts_with($repTelVal, $_p)) { $repTelPref = $_p; $repTelNum = substr($repTelVal, 4); break; }
                            }
                        ?>
                        <div class="phone-field" id="phone-wrap-tutor_telefono">
                            <select class="phone-prefix" id="tutor_telefono_prefix" aria-label="Prefijo">
                                <option value="0412" <?= $repTelPref==='0412'?'selected':'' ?>>0412</option>
                                <option value="0414" <?= $repTelPref==='0414'?'selected':'' ?>>0414</option>
                                <option value="0416" <?= $repTelPref==='0416'?'selected':'' ?>>0416</option>
                                <option value="0422" <?= $repTelPref==='0422'?'selected':'' ?>>0422</option>
                                <option value="0424" <?= $repTelPref==='0424'?'selected':'' ?>>0424</option>
                                <option value="0426" <?= $repTelPref==='0426'?'selected':'' ?>>0426</option>
                            </select>
                            <span class="phone-sep">-</span>
                            <input type="text" class="phone-number" id="tutor_telefono_number"
                                   maxlength="7" placeholder="1234567"
                                   autocomplete="off" inputmode="numeric"
                                   value="<?= e($repTelNum) ?>">
                            <input type="hidden" name="tutor_telefono" id="tutor_telefono">
                        </div>
                        <span class="field-error" id="tutor_telefono-error"></span>
                    </div>
                </div>
                
                <div class="af-grid af-grid--2">
                    <div class="form-group">
                        <label class="form-label"><span class="required">*</span> Tipo de Relación</label>
                        <select name="tutor_relacion" class="form-control">
                            <?php foreach (TIPO_RELACION_REPRESENTANTE as $op):
                                $cur = $get('tutor_relacion', $a['tutor_relacion'] ?? 'padres'); ?>
                                <option value="<?= e($op) ?>" <?= $cur === $op ? 'selected' : '' ?>><?= e(ucfirst($op)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Ficha médica -->
            <div id="tab-medica" class="form-tab-panel">
                <div class="af-section-header">
                    <div class="af-section-icon"><i class="ph ph-first-aid"></i></div>
                    <div class="af-section-info">
                        <h3>Registro Médico Inicial</h3>
                        <p>Información relevante para la salud del deportista</p>
                    </div>
                </div>
                
                <?php if (!can('admin') && !can('medico')): ?>
                    <div class="alert alert-info"><i class="ph ph-info"></i> Solo personal médico puede editar estos campos.</div>
                    <fieldset disabled>
                <?php endif; ?>

                <div class="af-grid af-grid--2">
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

                <div class="af-grid af-grid--2">
                    <div class="form-group">
                        <label class="form-label">Antecedentes Familiares</label>
                        <textarea name="antecedentes_familiares" class="form-control" rows="2" placeholder="Enfermedades hereditarias..."><?= e($get('antecedentes_familiares')) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Antecedentes Quirúrgicos</label>
                        <textarea name="antecedentes_quirurgicos" class="form-control" rows="2" placeholder="Operaciones previas..."><?= e($get('antecedentes_quirurgicos')) ?></textarea>
                    </div>
                </div>

                <div class="af-grid af-grid--2">
                    <div class="form-group">
                        <label class="form-label">Condición Crónica</label>
                        <textarea name="condicion_cronica" class="form-control" rows="2" placeholder="Asma, diabetes, etc."><?= e($get('condicion_cronica')) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Medicamento Actual</label>
                        <textarea name="medicacion_actual" class="form-control" rows="2" placeholder="Si toma algún medicamento actualmente..."><?= e($get('medicacion_actual')) ?></textarea>
                    </div>
                </div>

                <?php if (!can('admin') && !can('medico')): ?></fieldset><?php endif; ?>
            </div>
        </div>

        <div class="af-footer">
            <div class="af-footer-info">
                <i class="ph ph-info"></i> Paso <span id="current-step-num">1</span> de 4
            </div>
            <div class="af-actions">
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

<style>
/* ────────────────────────────────────────────────────────────────
   Atletas Form — Premium Design
──────────────────────────────────────────────────────────────── */

.af-container {
    max-width: 900px;
    margin: 0 auto;
    padding-bottom: 40px;
}

.af-header {
    margin-bottom: 24px;
    align-items: flex-end;
}

.af-header h1 {
    font-size: 28px;
    font-weight: 800;
    color: var(--color-text);
    margin: 0;
    font-family: var(--font-display);
}

.af-header .subtitle {
    margin: 4px 0 0;
}

.af-back-btn {
    border-radius: 50px;
    padding: 8px 20px;
}

/* — Card Estilizado — */
.af-card {
    border: none;
    padding: 0;
    box-shadow: 0 10px 40px -10px rgba(0,0,0,0.08), 
                0 0 1px rgba(0,0,0,0.1);
    overflow: hidden;
    background: #fff;
    border-radius: var(--radius-lg);
    display: flex;
    flex-direction: column;
}

/* — Tabs Premium — */
.af-tabs-wrapper {
    background: var(--color-surface);
    border-bottom: 1px solid var(--color-border);
    padding: 0 24px;
}

.af-tabs {
    display: flex;
    gap: 0;
    overflow-x: auto;
    scrollbar-width: none;
}
.af-tabs::-webkit-scrollbar { display: none; }

.ft-tab {
    flex: 1;
    min-width: 140px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 16px 10px;
    border: none;
    background: transparent;
    cursor: pointer;
    position: relative;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    color: var(--color-text-muted);
}

.ft-tab__icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    background: var(--color-surface-2);
    transition: all 0.2s;
}

.ft-tab__text {
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
}

.ft-tab:hover { color: var(--color-text); }
.ft-tab:hover .ft-tab__icon { background: var(--color-border); }

.ft-tab.active { color: var(--color-primary); }
.ft-tab.active .ft-tab__icon {
    background: var(--color-primary);
    color: #fff;
    box-shadow: 0 4px 12px rgba(190, 18, 60, 0.25);
}

.ft-tab::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 20%;
    right: 20%;
    height: 3px;
    background: var(--color-primary);
    border-radius: 3px 3px 0 0;
    transform: scaleX(0);
    transition: transform 0.2s;
}

.ft-tab.active::after { transform: scaleX(1); }

/* — Cuerpo del formulario — */
.af-body {
    padding: 32px 40px;
    min-height: 450px;
}

.form-tab-panel {
    display: none;
    animation: fadeInSlide .3s ease-out;
}
.form-tab-panel.active { display: block; }

@keyframes fadeInSlide {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* — Sección Header — */
.af-section-header {
    display: flex;
    gap: 16px;
    margin-bottom: 28px;
    padding-bottom: 16px;
    border-bottom: 1px dashed var(--color-border);
}

.af-section-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: var(--color-primary-light);
    color: var(--color-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}

.af-section-info h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: var(--color-text);
}

.af-section-info p {
    margin: 2px 0 0;
    font-size: 13px;
    color: var(--color-text-muted);
}

/* — Grid Responsivo — */
.af-grid {
    display: grid;
    gap: 20px;
    margin-bottom: 8px;
}

.af-grid--2 { grid-template-columns: repeat(2, 1fr); }
.af-grid--3 { grid-template-columns: repeat(3, 1fr); }

@media (max-width: 768px) {
    .af-grid--2, .af-grid--3 { grid-template-columns: 1fr; }
    .af-body { padding: 24px; }
    .af-header { flex-direction: column; align-items: flex-start; gap: 16px; }
    .af-back-btn span { display: none; }
    .af-back-btn { padding: 8px; width: 40px; height: 40px; border-radius: 50%; }
}

/* — Mejoras de Input — */
.form-control {
    height: 44px;
    background: var(--color-surface);
    border-color: var(--color-border);
    transition: all 0.2s;
}

.form-control:focus {
    background: #fff;
    box-shadow: 0 0 0 4px rgba(190, 18, 60, 0.08);
}

/* — Upload de Foto — */
.af-file-upload {
    display: flex;
    align-items: center;
    gap: 12px;
}

.af-file-input { display: none; }

.af-file-label {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: var(--color-surface-2);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    color: var(--color-text);
    transition: all 0.2s;
}

.af-file-label:hover { background: var(--color-border); }

.af-file-label.has-file {
    background: var(--color-success);
    color: #fff;
    border-color: var(--color-success);
}

.af-file-preview {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    overflow: hidden;
    border: 2px solid var(--color-primary-light);
    position: relative;
}
.af-file-preview img { width: 100%; height: 100%; object-fit: cover; }

.af-file-remove {
    position: absolute;
    top: -2px;
    right: -2px;
    width: 18px;
    height: 18px;
    background: var(--color-danger);
    color: #fff;
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

/* — Widget Teléfono — */
.phone-field {
    display: flex;
    align-items: stretch;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
    overflow: hidden;
    background: var(--color-surface);
    transition: all 0.2s;
    height: 44px;
}
.phone-field:focus-within {
    border-color: var(--color-primary);
    background: #fff;
    box-shadow: 0 0 0 4px rgba(190, 18, 60, 0.08);
}
.phone-field .phone-prefix {
    border: none;
    background: var(--color-surface-2);
    font-weight: 700;
    font-size: 13px;
    padding: 0 12px;
    cursor: pointer;
    border-right: 1px solid var(--color-border);
}
.phone-field .phone-number {
    flex: 1;
    border: none;
    background: transparent;
    padding: 0 12px;
    font-size: 14px;
    outline: none;
}

/* — Footer — */
.af-footer {
    padding: 24px 40px;
    background: var(--color-surface);
    border-top: 1px solid var(--color-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.af-footer-info {
    font-size: 13px;
    color: var(--color-text-muted);
    display: flex;
    align-items: center;
    gap: 6px;
}

.af-actions {
    display: flex;
    gap: 12px;
    align-items: center;
}

.af-actions-sep {
    width: 1px;
    height: 24px;
    background: var(--color-border);
    margin: 0 4px;
}

.af-submit-btn {
    padding: 10px 24px;
    border-radius: 50px;
    gap: 10px;
}

@media (max-width: 600px) {
    .af-footer { flex-direction: column-reverse; padding: 24px; text-align: center; }
    .af-actions { width: 100%; flex-direction: column; }
    .af-actions .btn { width: 100%; }
}

.field-error {
    display: none;
    color: var(--color-danger);
    font-size: 12px;
    margin-top: 4px;
    font-weight: 500;
}
</style>

<script>
// ── Tabs & Navegación ────────────────────────────────────────────────────────
const tabs = document.querySelectorAll('.ft-tab');
const panels = document.querySelectorAll('.form-tab-panel');
const btnNext = document.getElementById('btn-next');
const btnPrev = document.getElementById('btn-prev');
const btnSubmit = document.getElementById('btn-submit');
const stepNumEl = document.getElementById('current-step-num');

let currentIdx = 0;

function updateUI() {
    const isLast = currentIdx === tabs.length - 1;
    const isFirst = currentIdx === 0;

    // Actualizar Tabs y Paneles
    tabs.forEach((tab, i) => {
        tab.classList.toggle('active', i === currentIdx);
        panels[i].classList.toggle('active', i === currentIdx);
    });

    // Actualizar Botones
    btnPrev.style.display = isFirst ? 'none' : 'inline-flex';
    btnNext.style.display = isLast ? 'none' : 'inline-flex';
    btnSubmit.style.display = isLast ? 'inline-flex' : 'none';
    
    // Actualizar Contador
    if (stepNumEl) stepNumEl.textContent = currentIdx + 1;
}

// Click en botones
btnNext.addEventListener('click', () => {
    if (currentIdx < tabs.length - 1) {
        currentIdx++;
        updateUI();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
});

btnPrev.addEventListener('click', () => {
    if (currentIdx > 0) {
        currentIdx--;
        updateUI();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
});

// Click en los tabs directamente
tabs.forEach((btn, idx) => {
    btn.addEventListener('click', () => {
        currentIdx = idx;
        updateUI();
        btn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    });
});

// Botón Limpiar
const btnReset = document.getElementById('btn-reset');
const form = document.querySelector('.af-card');

if (btnReset) {
    btnReset.addEventListener('click', () => {
        if (confirm('¿Estás seguro de que deseas limpiar todo el formulario?')) {
            // Reset campos nativos
            form.reset();
            
            // Reset widgets custom
            document.querySelectorAll('.field-error').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.phone-field').forEach(el => el.style.borderColor = '');
            
            // Reset Foto
            if (fotoPreviewCont) {
                fotoPreviewCont.style.display = 'none';
                fotoPreviewImg.src = '';
                fotoLabel.classList.remove('has-file');
                fotoLabel.querySelector('span').textContent = 'Subir foto';
            }
            
            // Volver al inicio
            currentIdx = 0;
            updateUI();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
}

// Inicializar
updateUI();

// ── Cédula venezolana ────────────────────────────────────────────────────────
const CEDULA_REGEX = /^[VE]-\d{1,3}(\.\d{3})*$/;

function formatearNumeroCedula(digits) {
    return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}
function normalizarCedula(raw) {
    raw = raw.toUpperCase().trim();
    let prefix = 'V-', rest = raw;
    if (/^[VE]-/.test(raw))      { prefix = raw.substring(0, 2); rest = raw.substring(2); }
    else if (/^[VE]/.test(raw)) { prefix = raw[0] + '-';         rest = raw.substring(1); }
    const digits = rest.replace(/[^\d]/g, '').substring(0, 8);
    return digits ? prefix + formatearNumeroCedula(digits) : prefix;
}
function showError(id, msg) {
    const el = document.getElementById(id + '-error');
    const wrap = document.getElementById('phone-wrap-' + id);
    if (el) { el.textContent = msg; el.style.display = msg ? 'block' : 'none'; }
    if (wrap) wrap.style.borderColor = msg ? 'var(--color-danger,#e53e3e)' : '';
    const inp = document.getElementById(id);
    if (inp && !wrap) inp.style.borderColor = msg ? 'var(--color-danger,#e53e3e)' : '';
}
function clearError(id) { showError(id, ''); }
function validarCedula(val) { return !val || CEDULA_REGEX.test(val); }

// ── Eventos Cédulas ────────────────────────────────────────────────────────────
[document.getElementById('cedula'), document.getElementById('tutor_cedula')].forEach(inp => {
    if (!inp) return;
    inp.addEventListener('input', function() { this.value = normalizarCedula(this.value); });
    inp.addEventListener('blur', function() {
        validarCedula(this.value) ? clearError(this.id) : showError(this.id, 'Formato inválido. Ej: V-12.345.678');
    });
    inp.addEventListener('focus', function() { clearError(this.id); });
});

// ── Preview de Foto ──────────────────────────────────────────────────────────
const fotoInput = document.getElementById('foto-input');
const fotoLabel = document.getElementById('foto-label');
const fotoPreviewCont = document.getElementById('foto-preview-container');
const fotoPreviewImg = document.getElementById('foto-preview-img');
const btnRemoveFoto = document.getElementById('btn-remove-foto');

if (fotoInput) {
    fotoInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                fotoPreviewImg.src = e.target.result;
                fotoPreviewCont.style.display = 'block';
                fotoLabel.classList.add('has-file');
                fotoLabel.querySelector('span').textContent = 'Cambiar foto';
            }
            reader.readAsDataURL(file);
        }
    });

    btnRemoveFoto.addEventListener('click', (e) => {
        e.preventDefault();
        fotoInput.value = '';
        fotoPreviewCont.style.display = 'none';
        fotoLabel.classList.remove('has-file');
        fotoLabel.querySelector('span').textContent = 'Subir foto';
    });
}

// ── Widget teléfono ──────────────────────────────────────────────────────────
function setupPhoneWidget(prefixId, numberId, hiddenId, errorKey) {
    const prefixEl = document.getElementById(prefixId);
    const numberEl = document.getElementById(numberId);
    const hiddenEl = document.getElementById(hiddenId);
    if (!prefixEl || !numberEl || !hiddenEl) return;

    function sync() {
        const num = numberEl.value.replace(/[^\d]/g, '').substring(0, 7);
        numberEl.value = num;
        hiddenEl.value = num.length ? prefixEl.value + num : '';
    }
    sync();

    numberEl.addEventListener('input', () => { sync(); clearError(errorKey); });
    prefixEl.addEventListener('change', () => { sync(); clearError(errorKey); numberEl.focus(); });
    numberEl.addEventListener('blur', () => {
        const num = numberEl.value;
        if (num && num.length !== 7) showError(errorKey, 'Ingresa 7 dígitos');
        else clearError(errorKey);
    });
    numberEl.addEventListener('focus', () => clearError(errorKey));
}

setupPhoneWidget('telefono_prefix',     'telefono_number',     'telefono',     'telefono');
setupPhoneWidget('tutor_telefono_prefix', 'tutor_telefono_number', 'tutor_telefono', 'tutor_telefono');

// ── Validación Final ───────────────────────────────────────────────────────
document.querySelector('form').addEventListener('submit', function(e) {
    let hasError = false;
    const check = (id, valid, msg) => {
        const val = document.getElementById(id)?.value ?? '';
        if (!valid(val)) { showError(id, msg); hasError = true; }
    };

    check('cedula', validarCedula, 'Revisa el formato');
    check('tutor_cedula', validarCedula, 'Revisa el formato');
    
    const telAtleta = document.getElementById('telefono_number')?.value;
    if (telAtleta && telAtleta.length !== 7) { showError('telefono', 'Ingresa 7 dígitos'); hasError = true; }

    const telRep = document.getElementById('tutor_telefono_number')?.value;
    if (telRep && telRep.length !== 7) { showError('tutor_telefono', 'Ingresa 7 dígitos'); hasError = true; }

    if (hasError) {
        e.preventDefault();
        const firstErr = document.querySelector('.field-error[style*="block"]');
        if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>
