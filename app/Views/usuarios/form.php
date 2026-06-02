<?php
/** @var array|null $item @var array $roles @var string $action */
$p = $item ?? [];
if (isset($p['parroquias_id'])) {
    $p['parroquia_id'] = $p['parroquias_id'];
}
$get = function(string $k, $d = '') use ($p) { return old($k, $p[$k] ?? $d); };
$isEdit = !empty($p['usuario_id']);

$maxDate = date('Y-m-d', strtotime('-18 years'));
?>

<div class="af-container">
    <div class="page-header af-header">
        <div class="af-header__content">
            <h1><?= $isEdit ? 'Editar Usuario' : 'Nuevo Usuario' ?></h1>
            <p class="subtitle">Complete los datos personales y de ubicación del usuario</p>
        </div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <a href="<?= e(url('/admin/usuarios')) ?>" class="btn btn-ghost af-back-btn">
                <i class="ph ph-arrow-left"></i> <span>Volver</span>
            </a>
        </div>
    </div>

    <form id="form-usuario" method="POST" action="<?= e($action) ?>" enctype="multipart/form-data" class="card af-card" novalidate>
        <?= csrf_field() ?>

        <div class="af-tabs-wrapper">
            <div class="af-tabs" role="tablist">
                <button type="button" class="ft-tab active" data-tab="tab-personal">
                    <div class="ft-tab__icon"><i class="ph ph-user"></i></div>
                    <div class="ft-tab__text">Personal</div>
                </button>
                <button type="button" class="ft-tab" data-tab="tab-direccion">
                    <div class="ft-tab__icon"><i class="ph ph-map-pin"></i></div>
                    <div class="ft-tab__text">Ubicación</div>
                </button>
            </div>
        </div>

        <div class="af-body">
            <!-- Paso 1: Datos personales -->
            <div id="tab-personal" class="form-tab-panel active">
                <div class="af-section-title">
                    <i class="ph ph-identification-card"></i>
                    Información Básica
                </div>
                
                <div class="af-grid af-grid--2">
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Nombres completos del usuario. Solo letras y espacios (mín. 3, máx. 30 caracteres)." data-tooltip-pos="top"><span class="required">*</span> Nombres</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" required minlength="3" maxlength="30" pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$" title="Solo letras y espacios" value="<?= e($get('nombre', '')) ?>" placeholder="Ej: Juan Carlos">
                    </div>
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Apellidos completos del usuario. Solo letras y espacios (mín. 3, máx. 30 caracteres)." data-tooltip-pos="top"><span class="required">*</span> Apellidos</label>
                        <input type="text" id="apellido" name="apellido" class="form-control" required minlength="3" maxlength="30" pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$" title="Solo letras y espacios" value="<?= e($get('apellido', '')) ?>" placeholder="Ej: Pérez Rodríguez">
                    </div>
                </div>

                <div class="af-grid af-grid--3">
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Cédula de identidad del usuario (V/E-Número) o Pasaporte. Debe ser única en el sistema." data-tooltip-pos="top"><span class="required">*</span> Número de documento de identidad</label>
                        <?php
                            $cedVal   = $get('cedula', '');
                            $cedPref  = 'V';
                            $cedNum   = '';
                            if (!empty($cedVal)) {
                                if (str_contains($cedVal, '-')) {
                                    [$cedPref, $cedNum] = explode('-', $cedVal, 2);
                                } else {
                                    $firstChar = strtoupper($cedVal[0]);
                                    if (in_array($firstChar, ['V', 'E', 'P'])) {
                                        $cedPref = $firstChar;
                                        $cedNum = substr($cedVal, 1);
                                    } else {
                                        $cedNum = $cedVal;
                                    }
                                }
                            }
                        ?>
                        <div class="phone-field" id="phone-wrap-cedula">
                            <select class="phone-prefix" id="cedula_prefix" aria-label="Prefijo">
                                <option value="V" <?= $cedPref==='V'?'selected':'' ?>>V</option>
                                <option value="E" <?= $cedPref==='E'?'selected':'' ?>>E</option>
                                <option value="P" <?= $cedPref==='P'?'selected':'' ?>>P</option>
                            </select>
                            <span class="phone-sep">-</span>
                            <input type="text" class="phone-number" id="cedula_number" maxlength="15" placeholder="12.345.678" autocomplete="off" value="<?= e($cedNum) ?>">
                            <input type="hidden" name="cedula" id="cedula" value="<?= e($cedVal) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Teléfono móvil de contacto del usuario (11 dígitos, ej: 0412-1234567)." data-tooltip-pos="top"><span class="required">*</span> Teléfono</label>
                        <?php
                            $telVal   = $get('telefono', '');
                            $telPref  = '';
                            $telNum   = '';
                            foreach (['0412','0414','0416','0422','0424','0426','0255','0256'] as $_p) {
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
                                <option value="0255" <?= $telPref==='0255'?'selected':'' ?>>0255</option>
                                <option value="0256" <?= $telPref==='0256'?'selected':'' ?>>0256</option>
                            </select>
                            <span class="phone-sep">-</span>
                            <input type="text" class="phone-number" id="telefono_number" maxlength="7" placeholder="1234567" autocomplete="off" inputmode="numeric" value="<?= e($telNum) ?>">
                            <input type="hidden" name="telefono" id="telefono" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Fecha de nacimiento del usuario. Debe ser mayor de edad (18 años mínimo)." data-tooltip-pos="top"><span class="required">*</span> Fecha de nacimiento</label>
                        <input type="date" id="fecha_nac" name="fecha_nac" class="form-control" required max="<?= $maxDate ?>" value="<?= e($get('fecha_nac', '')) ?>">
                    </div>
                </div>

                <div class="af-grid af-grid--3">
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Dirección de correo electrónico que será el nombre de usuario para iniciar sesión. Debe ser única." data-tooltip-pos="top"><span class="required">*</span> Correo Electrónico</label>
                        <input type="email" id="correo" name="correo" class="form-control" required maxlength="50" value="<?= e($get('correo', '')) ?>" placeholder="ejemplo@correo.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Rol que determina los permisos del usuario: Super Usuario, Administrador, Entrenador o Directivo." data-tooltip-pos="top"><span class="required">*</span> Rol / Cargo</label>
                        <select id="rol_id" name="rol_id" class="form-control" required>
                            <option value="">— Seleccione —</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= (int) $r['rol_id'] ?>" <?= (int) $get('rol_id', '') === (int) $r['rol_id'] ? 'selected' : '' ?>>
                                    <?= e($r['nombre_rol']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if ($isEdit): ?>
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Estado actual de la cuenta del usuario en el sistema (Activo o Inactivo)." data-tooltip-pos="top"><span class="required">*</span> Estatus</label>
                        <select id="estatus" name="estatus" class="form-control" required>
                            <option value="Activo" <?= $get('estatus', 'Activo') === 'Activo' ? 'selected' : '' ?>>Activo</option>
                            <option value="Inactivo" <?= $get('estatus', '') === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                    <?php else: ?>
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Foto de perfil (al crear). Formatos aceptados: JPG, PNG, WebP. Opcional." data-tooltip-pos="top">Foto de Perfil</label>
                        <div class="af-file-upload">
                            <input type="file" name="foto" id="foto-input" class="af-file-input" accept="image/jpeg,image/png,image/webp">
                            <label for="foto-input" class="af-file-label" id="foto-label">
                                <i class="ph ph-camera"></i>
                                <span>Subir foto</span>
                            </label>
                            <div class="af-file-preview" id="foto-preview-container" style="<?= empty($p['foto']) ? 'display:none;' : '' ?>">
                                <img src="<?= !empty($p['foto']) ? e(url($p['foto'])) : '' ?>" id="foto-preview-img" alt="Vista previa">
                                <button type="button" class="af-file-remove" id="btn-remove-foto" title="Quitar foto"><i class="ph ph-x"></i></button>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="estatus" value="Activo">
                    <?php endif; ?>
                </div>

                <?php if ($isEdit): ?>
                <div class="af-grid af-grid--3">
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Foto de perfil (al editar). Formatos aceptados: JPG, PNG, WebP. Opcional." data-tooltip-pos="top">Foto de Perfil</label>
                        <div class="af-file-upload">
                            <input type="file" name="foto" id="foto-input" class="af-file-input" accept="image/jpeg,image/png,image/webp">
                            <label for="foto-input" class="af-file-label" id="foto-label">
                                <i class="ph ph-camera"></i>
                                <span>Subir foto</span>
                            </label>
                            <div class="af-file-preview" id="foto-preview-container" style="<?= empty($p['foto']) ? 'display:none;' : '' ?>">
                                <img src="<?= !empty($p['foto']) ? e(url($p['foto'])) : '' ?>" id="foto-preview-img" alt="Vista previa">
                                <button type="button" class="af-file-remove" id="btn-remove-foto" title="Quitar foto"><i class="ph ph-x"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Paso 2: Dirección -->
            <div id="tab-direccion" class="form-tab-panel">
                <div class="af-section-title">
                    <i class="ph ph-map-pin-line"></i>
                    Datos de Residencia
                </div>
                
                <div class="af-grid af-grid--2">
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Estado venezolano correspondiente a la residencia actual del usuario." data-tooltip-pos="top"><span class="required">*</span> Estado</label>
                        <select id="sel-estado" name="estado_id" class="form-control" data-current="<?= (int) $get('estado_id', '') ?>" required>
                            <option value="">— Seleccione Estado —</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Municipio dentro del estado seleccionado donde reside el usuario." data-tooltip-pos="top"><span class="required">*</span> Municipio</label>
                        <select id="sel-municipio" name="municipio_id" class="form-control" data-current="<?= (int) $get('municipio_id', '') ?>" required disabled>
                            <option value="">— Seleccione Municipio —</option>
                        </select>
                    </div>
                </div>

                <div class="af-grid af-grid--2">
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Parroquia dentro del municipio donde reside el usuario." data-tooltip-pos="top"><span class="required">*</span> Parroquia</label>
                        <select id="sel-parroquia" name="parroquia_id" class="form-control" data-current="<?= (int) $get('parroquia_id', '') ?>" required disabled>
                            <option value="">— Seleccione Parroquia —</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Estructura habitacional en la que reside el usuario (casa, apartamento o edificio)." data-tooltip-pos="top"><span class="required">*</span> Tipo de Vivienda</label>
                        <select id="tipo_vivienda" name="tipo_vivienda" class="form-control" required>
                            <option value="">— Seleccione —</option>
                            <option value="casa" <?= $get('tipo_vivienda', '') === 'casa' ? 'selected' : '' ?>>Casa</option>
                            <option value="apto" <?= $get('tipo_vivienda', '') === 'apto' ? 'selected' : '' ?>>Apartamento</option>
                            <option value="edificio" <?= $get('tipo_vivienda', '') === 'edificio' ? 'selected' : '' ?>>Edificio</option>
                        </select>
                    </div>
                </div>

                <div class="af-grid af-grid--2">
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Urbanización, barrio, sector o comunidad donde vive el usuario (mín. 2 caracteres)." data-tooltip-pos="top"><span class="required">*</span> Localidad (Barrio / Urbanización)</label>
                        <input type="text" id="localidad" name="localidad" class="form-control" required maxlength="100" value="<?= e($get('localidad', '')) ?>" placeholder="Ej: Urb. Villas del Pilar, Barrio San Jose">
                    </div>
                    <div class="form-group">
                        <label class="form-label" data-tooltip="Dirección detallada: número de casa, calle, vereda, punto de referencia (mín. 2 caracteres)." data-tooltip-pos="top"><span class="required">*</span> Dirección Exacta</label>
                        <input type="text" id="ubicacion_vivienda" name="ubicacion_vivienda" class="form-control" required maxlength="100" value="<?= e($get('ubicacion_vivienda', '')) ?>" placeholder="Ej: Calle 15A, Casa 412">
                    </div>
                </div>
            </div>
        </div>

        <div class="af-footer">
            <div class="af-footer-info">
                <i class="ph ph-info"></i> Paso <span id="current-step-num">1</span> de 2
            </div>
            <div class="af-actions" style="display: flex; gap: 12px; align-items: center;">
                <button type="button" class="btn btn-ghost" id="btn-reset" title="Borrar todo"><i class="ph ph-trash"></i> Limpiar</button>
                <div class="af-actions-sep"></div>
                <button type="button" class="btn btn-ghost" id="btn-prev" style="display:none;"><i class="ph ph-caret-left"></i> Anterior</button>
                <button type="button" class="btn btn-primary" id="btn-next">Siguiente <i class="ph ph-caret-right"></i></button>
                <button type="submit" class="btn btn-primary af-submit-btn" id="btn-submit" style="display:none;">
                    <span><?= $isEdit ? 'Guardar Cambios' : 'Registrar Usuario' ?></span>
                    <i class="ph ph-check-circle"></i>
                </button>
                <button type="button" class="btn-help js-btn-help-usuario" title="¿Cómo llenar este formulario?" style="width: 38px; height: 38px;">
                    <i class="ph ph-question"></i>
                </button>
            </div>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../atletas/partials/form_registro/_styles.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // —— Filtrado de caracteres no alfabéticos en tiempo real —————————————————————
    const nombreInp = document.getElementById('nombre');
    const apellidoInp = document.getElementById('apellido');
    
    const filterAlphabetic = (e) => {
        const invalidCharRegex = /[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g;
        if (invalidCharRegex.test(e.target.value)) {
            e.target.value = e.target.value.replace(invalidCharRegex, '');
        }
    };

    if (nombreInp) {
        nombreInp.addEventListener('input', filterAlphabetic);
    }
    if (apellidoInp) {
        apellidoInp.addEventListener('input', filterAlphabetic);
    }

    // —— Botón de ayuda [?] ——————————————————————————————————————————————————————
    const btnHelp = document.querySelector('.js-btn-help-usuario');
    if (btnHelp) {
        btnHelp.addEventListener('click', (e) => {
            e.preventDefault();
            FormValidator.showHelp(
                'Guía: Registro de Usuario',
                '<?= e(asset("img/ayuda/formulario_usuario.png")) ?>'
            );
        });
    }

    // —— Configuración del Wizard (Paso a Paso) ————————————————————————————————
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

    // —— Validaciones de Cédula y Teléfono ———————————————————————————————————————
    const CEDULA_REGEX = /^[VE]-\d{1,3}(\.\d{3})*$/;
    const PASAPORTE_REGEX = /^P-[A-Z0-9]{5,15}$/i;

    function formatCedulaNumber(digits) {
        return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function validarCedula(val) {
        if (PASAPORTE_REGEX.test(val)) return true;
        if (!CEDULA_REGEX.test(val)) return false;
        const digitsOnly = val.replace(/[^\d]/g, '');
        return digitsOnly.length >= 7;
    }

    function showError(id, msg) {
        const wrap = document.getElementById('phone-wrap-' + id);
        if (wrap) wrap.style.borderColor = msg ? 'var(--color-danger,#e53e3e)' : '';
        const inp = document.getElementById(id);
        if (inp && !wrap) inp.style.borderColor = msg ? 'var(--color-danger,#e53e3e)' : '';
    }
    function clearError(id) { showError(id, ''); }

    // —— Widget Cédula —————————————————————————————————————————————————————————
    function setupCedulaWidget(prefixId, numberId, hiddenId, errorKey) {
        const prefixEl = document.getElementById(prefixId);
        const numberEl = document.getElementById(numberId);
        const hiddenEl = document.getElementById(hiddenId);
        if (!prefixEl || !numberEl || !hiddenEl) return;

        function isPassport() { return prefixEl.value === 'P'; }

        function sync() {
            if (isPassport()) {
                // Pasaporte: alfanumérico, sin formato de puntos
                let val = numberEl.value.replace(/[^A-Z0-9]/gi, '').substring(0, 15).toUpperCase();
                numberEl.value = val;
                hiddenEl.value = val.length ? 'P-' + val : '';
            } else {
                // Cédula V/E: solo dígitos con formato de puntos, máx 8 dígitos
                let val = numberEl.value.replace(/[^\d]/g, '').substring(0, 8);
                val = formatCedulaNumber(val);
                numberEl.value = val;
                hiddenEl.value = val.length ? prefixEl.value + '-' + val : '';
            }
        }

        function loadExisting() {
            if (!hiddenEl.value) return;
            let raw = hiddenEl.value;
            let prefix = 'V', num = raw;
            if (raw.includes('-')) {
                let parts = raw.split('-');
                prefix = parts[0].toUpperCase();
                num = parts.slice(1).join('-') || '';
            } else {
                let firstChar = raw.charAt(0).toUpperCase();
                if (['V', 'E', 'P'].includes(firstChar)) {
                    prefix = firstChar;
                    num = raw.substring(1);
                }
            }
            prefixEl.value = prefix;
            if (prefix === 'P') {
                numberEl.value = num.replace(/[^A-Z0-9]/gi, '').toUpperCase();
                numberEl.placeholder = 'ABC123456';
                numberEl.maxLength = 15;
            } else {
                numberEl.value = formatCedulaNumber(num.replace(/[^\d]/g, ''));
                numberEl.placeholder = '12.345.678';
                numberEl.maxLength = 10;
            }
        }

        loadExisting();
        sync();

        numberEl.addEventListener('input', () => { sync(); clearError(errorKey); });
        prefixEl.addEventListener('change', () => {
            numberEl.value = '';
            hiddenEl.value = '';
            if (isPassport()) {
                numberEl.placeholder = 'ABC123456';
                numberEl.maxLength = 15;
            } else {
                numberEl.placeholder = '12.345.678';
                numberEl.maxLength = 13;
            }
            sync();
            clearError(errorKey);
            numberEl.focus();
        });

        numberEl.addEventListener('blur', () => {
            const val = hiddenEl.value;
            if (val && !validarCedula(val)) {
                const hint = isPassport() ? 'Ej: P-ABC123456' : 'Ej: ' + prefixEl.value + '-12.345.678';
                showError(errorKey, 'Formato inválido. ' + hint);
            } else {
                clearError(errorKey);
            }
        });
        numberEl.addEventListener('focus', () => clearError(errorKey));
    }

    setupCedulaWidget('cedula_prefix', 'cedula_number', 'cedula', 'cedula');

    // —— Widget Teléfono ———————————————————————————————————————————————————————
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

    setupPhoneWidget('telefono_prefix', 'telefono_number', 'telefono', 'telefono');

    // —— Preview de Foto ——————————————————————————————————————————————————————————
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

                let deleteInput = document.getElementById('delete-foto-flag');
                if (deleteInput) deleteInput.remove();
            }
        });

        btnRemoveFoto.addEventListener('click', (e) => {
            e.preventDefault();
            fotoInput.value = '';
            fotoPreviewCont.style.display = 'none';
            fotoLabel.classList.remove('has-file');
            fotoLabel.querySelector('span').textContent = 'Subir foto';

            let deleteInput = document.getElementById('delete-foto-flag');
            if (!deleteInput) {
                deleteInput = document.createElement('input');
                deleteInput.type = 'hidden';
                deleteInput.name = 'eliminar_foto';
                deleteInput.id = 'delete-foto-flag';
                deleteInput.value = '1';
                fotoInput.parentNode.appendChild(deleteInput);
            }
        });
    }

    // —— Botones de Navegación del Wizard ——————————————————————————————————————
    btnNext.addEventListener('click', () => {
        // Validar paso actual
        const panel = panels[currentIdx];
        const requiredInputs = panel.querySelectorAll('[required]');
        let isValid = true;
        let errors = [];

        requiredInputs.forEach(input => {
            if (input.style.display === 'none' || input.offsetParent === null) return;
            const fg = input.closest('.form-group');
            const labelEl = fg ? fg.querySelector('.form-label') : null;
            const label = labelEl ? labelEl.textContent.replace('*', '').trim() : (input.name || 'Campo');
            
            const wrap = input.closest('.phone-field');
            if (!input.value.trim()) {
                if (wrap) wrap.style.borderColor = 'var(--color-danger,#e53e3e)';
                else input.style.borderColor = 'var(--color-danger,#e53e3e)';
                errors.push('El campo "' + label + '" es obligatorio.');
                isValid = false;
            } else {
                if (wrap) wrap.style.borderColor = '';
                else input.style.borderColor = '';
            }

            // Validar formato de email
            if (input.type === 'email' && input.value.trim()) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(input.value.trim())) {
                    if (wrap) wrap.style.borderColor = 'var(--color-danger,#e53e3e)';
                    else input.style.borderColor = 'var(--color-danger,#e53e3e)';
                    errors.push('El formato del correo electrónico es inválido.');
                    isValid = false;
                }
            }
        });

        if (currentIdx === 0) {
            const cedNum = document.getElementById('cedula_number').value.trim();
            const ced = document.getElementById('cedula').value;
            const cedWrap = document.getElementById('phone-wrap-cedula');
            
            const isPass = document.getElementById('cedula_prefix').value === 'P';
            const docName = isPass ? 'Pasaporte' : 'Cédula';
            if (!cedNum) {
                if (cedWrap) cedWrap.style.borderColor = 'var(--color-danger,#e53e3e)';
                errors.push('El campo "' + docName + '" es obligatorio.');
                isValid = false;
            } else if (!validarCedula(ced)) {
                if (cedWrap) cedWrap.style.borderColor = 'var(--color-danger,#e53e3e)';
                errors.push(isPass ? 'El pasaporte debe tener entre 5 y 15 caracteres alfanuméricos. Ej: P-ABC123456' : 'La cédula debe tener entre 7 y 8 dígitos numéricos. Ej: V-12.345.678');
                isValid = false;
            } else {
                if (cedWrap) cedWrap.style.borderColor = '';
            }

            const telNum = document.getElementById('telefono_number').value.trim();
            const telWrap = document.getElementById('phone-wrap-telefono');
            
            if (!telNum) {
                if (telWrap) telWrap.style.borderColor = 'var(--color-danger,#e53e3e)';
                errors.push('El campo "Teléfono" es obligatorio.');
                isValid = false;
            } else if (telNum.length !== 7) {
                if (telWrap) telWrap.style.borderColor = 'var(--color-danger,#e53e3e)';
                errors.push('El teléfono debe tener exactamente 7 dígitos');
                isValid = false;
            } else {
                if (telWrap) telWrap.style.borderColor = '';
            }
        }

        if (!isValid) {
            FormValidator.showErrors(errors);
            return;
        }

        currentIdx++;
        updateUI();
    });

    btnPrev.addEventListener('click', () => {
        currentIdx--;
        updateUI();
    });

    // —— Botón Limpiar (Reset) ————————————————————————————————————————————————
    const btnReset = document.getElementById('btn-reset');
    if (btnReset) {
        btnReset.addEventListener('click', () => {
            if (typeof CadaModal !== 'undefined') {
                CadaModal.confirm({
                    title: '¿Limpiar Formulario?',
                    text: '¿Estás seguro de que deseas limpiar todo el formulario? Se perderán todos los datos ingresados.',
                    type: 'warning',
                    confirmText: 'Sí, limpiar',
                    cancelText: 'Cancelar'
                }).then(confirmed => {
                    if (confirmed) {
                        const inputs = document.querySelectorAll('#form-usuario input:not([name="_token"]):not([type="hidden"]), #form-usuario select');
                        inputs.forEach(input => {
                            input.value = '';
                            input.style.borderColor = '';
                            const wrap = input.closest('.phone-field');
                            if (wrap) wrap.style.borderColor = '';
                        });
                        // Resetear foto widget
                        if (fotoInput) {
                            fotoInput.value = '';
                            fotoPreviewCont.style.display = 'none';
                            fotoLabel.classList.remove('has-file');
                            fotoLabel.querySelector('span').textContent = 'Subir foto';
                        }
                        // Resetear selects de ubicación
                        if (selMunicipio) {
                            selMunicipio.innerHTML = '<option value="">— Seleccione Municipio —</option>';
                            selMunicipio.disabled = true;
                        }
                        if (selParroquia) {
                            selParroquia.innerHTML = '<option value="">— Seleccione Parroquia —</option>';
                            selParroquia.disabled = true;
                        }
                        currentIdx = 0;
                        updateUI();
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                });
            }
        });
    }

    // —— Carga Dinámica de Ubicaciones ——————————————————————————————————————————
    const selEstado = document.getElementById('sel-estado');
    const selMunicipio = document.getElementById('sel-municipio');
    const selParroquia = document.getElementById('sel-parroquia');

    function loadEstados() {
        fetch('<?= e(url('/api/direcciones/estados/232')) ?>')
            .then(res => res.json())
            .then(data => {
                selEstado.innerHTML = '<option value="">— Seleccione Estado —</option>';
                data.forEach(est => {
                    let selected = (parseInt(selEstado.dataset.current) === est.estado_id) ? 'selected' : '';
                    selEstado.innerHTML += `<option value="${est.estado_id}" ${selected}>${est.estado}</option>`;
                });
                if (selEstado.value) loadMunicipios(selEstado.value);
            })
            .catch(console.error);
    }

    function loadMunicipios(estadoId) {
        if (!estadoId) {
            selMunicipio.innerHTML = '<option value="">— Seleccione Municipio —</option>';
            selMunicipio.disabled = true;
            selParroquia.innerHTML = '<option value="">— Seleccione Parroquia —</option>';
            selParroquia.disabled = true;
            return;
        }
        selMunicipio.disabled = false;
        fetch('<?= e(url('/api/direcciones/municipios')) ?>/' + estadoId)
            .then(res => res.json())
            .then(data => {
                selMunicipio.innerHTML = '<option value="">— Seleccione Municipio —</option>';
                data.forEach(mun => {
                    let selected = (parseInt(selMunicipio.dataset.current) === parseInt(mun.municipio_id)) ? 'selected' : '';
                    selMunicipio.innerHTML += `<option value="${mun.municipio_id}" ${selected}>${mun.municipio}</option>`;
                });
                if (selMunicipio.value) loadParroquias(selMunicipio.value);
            })
            .catch(console.error);
    }

    function loadParroquias(municipioId) {
        if (!municipioId) {
            selParroquia.innerHTML = '<option value="">— Seleccione Parroquia —</option>';
            selParroquia.disabled = true;
            return;
        }
        selParroquia.disabled = false;
        fetch('<?= e(url('/api/direcciones/parroquias')) ?>/' + municipioId)
            .then(res => res.json())
            .then(data => {
                selParroquia.innerHTML = '<option value="">— Seleccione Parroquia —</option>';
                data.forEach(par => {
                    let selected = (parseInt(selParroquia.dataset.current) === parseInt(par.parroquia_id)) ? 'selected' : '';
                    selParroquia.innerHTML += `<option value="${par.parroquia_id}" ${selected}>${par.parroquia}</option>`;
                });
            })
            .catch(console.error);
    }

    loadEstados();

    selEstado.addEventListener('change', () => {
        loadMunicipios(selEstado.value);
    });

    selMunicipio.addEventListener('change', () => {
        loadParroquias(selMunicipio.value);
    });

    // —— Envío y Validación Final del Formulario ——————————————————————————————
    FormValidator.init('#form-usuario', {
        custom: (form) => {
            const errors = [];
            const ced = document.getElementById('cedula').value;
            if (ced && !validarCedula(ced)) {
                const cedInp = document.getElementById('cedula_number');
                const isPass = document.getElementById('cedula_prefix').value === 'P';
                const msg = isPass ? 'El pasaporte tiene formato inválido. Ej: P-ABC123456' : 'La cédula tiene formato inválido. Ej: V-12.345.678';
                errors.push({ label: msg, element: cedInp });
            }
            const tel = document.getElementById('telefono_number').value;
            if (!tel || tel.length !== 7) {
                const telInp = document.getElementById('telefono_number');
                errors.push({ label: 'El teléfono debe tener exactamente 7 dígitos', element: telInp });
            }
            return errors;
        }
    });
});
</script>
