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
<div class="page-header">
    <div>
        <h1><?= $isEdit ? 'Editar' : 'Nuevo' ?> Usuario</h1>
        <div class="subtitle">Complete los datos personales y de ubicación</div>
    </div>
    <a href="<?= e(url('/admin/usuarios')) ?>" class="btn btn-ghost"><i class="ph ph-arrow-left"></i> Volver</a>
</div>

<form method="POST" action="<?= e($action) ?>" enctype="multipart/form-data" class="card" style="max-width:1000px; padding:0; overflow:hidden;">
    <?= csrf_field() ?>

    <!-- Indicador de pasos -->
    <div class="form-tabs" style="background: var(--color-bg-alt); padding: 16px 24px; border-bottom: 1px solid var(--color-border); display: flex; gap: 32px;">
        <span id="step-indicator-1" style="font-weight: 600; color: var(--color-primary); border-bottom: 2px solid var(--color-primary); padding-bottom: 12px;">
            <i class="ph ph-user"></i> 1. Datos Personales
        </span>
        <span id="step-indicator-2" style="font-weight: 500; color: var(--color-text-muted); padding-bottom: 12px;">
            <i class="ph ph-map-pin"></i> 2. Ubicación
        </span>
    </div>

    <div style="padding: 32px;">
        <!-- Paso 1: Datos personales -->
        <div id="step-personal" class="form-step">
            <h3 style="margin-top: 0; margin-bottom: 24px; font-family: var(--font-display); color: var(--color-text);"><i class="ph ph-identification-card text-muted"></i> Información Básica</h3>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Nombres</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" required minlength="3" maxlength="30" pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$" title="Solo letras y espacios" value="<?= e($get('nombre', '')) ?>">
                    <span class="field-error" id="nombre-error" style="display:none; color: var(--color-danger); font-size: 12px; margin-top: 4px;"></span>
                </div>
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Apellidos</label>
                    <input type="text" id="apellido" name="apellido" class="form-control" required minlength="3" maxlength="30" pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$" title="Solo letras y espacios" value="<?= e($get('apellido', '')) ?>">
                    <span class="field-error" id="apellido-error" style="display:none; color: var(--color-danger); font-size: 12px; margin-top: 4px;"></span>
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Cédula</label>
                    <input type="text" id="cedula" name="cedula" class="form-control" required maxlength="13" placeholder="V-12.345.678" autocomplete="off" value="<?= e($get('cedula', '')) ?>">
                    <span class="field-error" id="cedula-error" style="display:none; color: var(--color-danger); font-size: 12px; margin-top: 4px;"></span>
                    <?php if (!$isEdit): ?>
                        <div class="form-hint">Se usarán solo los números como contraseña inicial.</div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Teléfono</label>
                    <?php
                        $telVal   = $get('telefono', '');
                        $telPref  = '';
                        $telNum   = '';
                        foreach (['0412','0414','0416','0422','0424','0426'] as $_p) {
                            if (str_starts_with($telVal, $_p)) { $telPref = $_p; $telNum = substr($telVal, 4); break; }
                        }
                    ?>
                    <div class="phone-field" id="phone-wrap-telefono" style="display: flex; align-items: center; border: 1px solid var(--color-border); border-radius: 6px; overflow: hidden; background: var(--color-bg);">
                        <select class="phone-prefix" id="telefono_prefix" aria-label="Prefijo" style="border: none; background: transparent; padding: 10px; font-size: 14px; outline: none; border-right: 1px solid var(--color-border); cursor: pointer;">
                            <option value="0412" <?= $telPref==='0412'?'selected':'' ?>>0412</option>
                            <option value="0414" <?= $telPref==='0414'?'selected':'' ?>>0414</option>
                            <option value="0416" <?= $telPref==='0416'?'selected':'' ?>>0416</option>
                            <option value="0422" <?= $telPref==='0422'?'selected':'' ?>>0422</option>
                            <option value="0424" <?= $telPref==='0424'?'selected':'' ?>>0424</option>
                            <option value="0426" <?= $telPref==='0426'?'selected':'' ?>>0426</option>
                        </select>
                        <input type="text" class="phone-number" id="telefono_number" maxlength="7" placeholder="1234567" autocomplete="off" inputmode="numeric" value="<?= e($telNum) ?>" style="border: none; background: transparent; padding: 10px; font-size: 14px; outline: none; width: 100%;">>
                        <input type="hidden" name="telefono" id="telefono" required>
                    </div>
                    <span class="field-error" id="telefono-error" style="display:none; color: var(--color-danger); font-size: 12px; margin-top: 4px;"></span>
                </div>
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Fecha de nacimiento</label>
                    <input type="date" id="fecha_nac" name="fecha_nac" class="form-control" required max="<?= $maxDate ?>" value="<?= e($get('fecha_nac', '')) ?>">
                    <span class="field-error" id="fecha_nac-error" style="display:none; color: var(--color-danger); font-size: 12px; margin-top: 4px;"></span>
                    <div class="form-hint">Debe ser mayor de edad (min. 18 años).</div>
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Correo Electrónico</label>
                    <input type="email" id="correo" name="correo" class="form-control" required maxlength="50" value="<?= e($get('correo', '')) ?>" placeholder="ejemplo@correo.com">
                    <span class="field-error" id="correo-error" style="display:none; color: var(--color-danger); font-size: 12px; margin-top: 4px;"></span>
                </div>
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Rol / Cargo</label>
                    <select id="rol_id" name="rol_id" class="form-control" required>
                        <option value="">Selecciona...</option>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= (int) $r['rol_id'] ?>" <?= (int) $get('rol_id', '') === (int) $r['rol_id'] ? 'selected' : '' ?>>
                                <?= e($r['nombre_rol']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="field-error" id="rol_id-error" style="display:none; color: var(--color-danger); font-size: 12px; margin-top: 4px;"></span>
                </div>
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Estatus</label>
                    <select id="estatus" name="estatus" class="form-control" required>
                        <option value="Activo" <?= $get('estatus', 'Activo') === 'Activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="Inactivo" <?= $get('estatus') === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                    <span class="field-error" id="estatus-error" style="display:none; color: var(--color-danger); font-size: 12px; margin-top: 4px;"></span>
                </div>
                <div class="form-group">
                    <label class="form-label">Foto de Perfil</label>
                    <input type="file" name="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
                    <?php if (!empty($p['foto'])): ?>
                        <div class="form-hint mt-2">
                            <img src="<?= e(url($p['foto'])) ?>" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid var(--color-border);">
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--color-border);">
                <a href="<?= e(url('/admin/usuarios')) ?>" class="btn btn-ghost">Cancelar</a>
                <button type="button" id="btn-siguiente" class="btn btn-primary"><i class="ph ph-arrow-right"></i> Siguiente</button>
            </div>
        </div>

        <!-- Paso 2: Dirección -->
        <div id="step-direccion" class="form-step" style="display: none;">
            <h3 style="margin-top: 0; margin-bottom: 24px; font-family: var(--font-display); color: var(--color-text);"><i class="ph ph-map-pin-line text-muted"></i> Datos de Residencia</h3>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Estado</label>
                    <select id="sel-estado" name="estado_id" class="form-control" data-current="<?= (int) $get('estado_id', '') ?>" required>
                        <option value="">Selecciona Estado...</option>
                    </select>
                    <span class="field-error" id="sel-estado-error" style="display:none; color: var(--color-danger); font-size: 12px; margin-top: 4px;"></span>
                </div>
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Municipio</label>
                    <select id="sel-municipio" name="municipio_id" class="form-control" data-current="<?= (int) $get('municipio_id', '') ?>" required>
                        <option value="">Selecciona Municipio...</option>
                    </select>
                    <span class="field-error" id="sel-municipio-error" style="display:none; color: var(--color-danger); font-size: 12px; margin-top: 4px;"></span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Parroquia</label>
                    <select id="sel-parroquia" name="parroquia_id" class="form-control" data-current="<?= (int) $get('parroquia_id', '') ?>" required>
                        <option value="">Selecciona Parroquia...</option>
                    </select>
                    <span class="field-error" id="sel-parroquia-error" style="display:none; color: var(--color-danger); font-size: 12px; margin-top: 4px;"></span>
                </div>
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Tipo de Vivienda</label>
                    <select id="tipo_vivienda" name="tipo_vivienda" class="form-control" required>
                        <option value="casa" <?= $get('tipo_vivienda', '') === 'casa' ? 'selected' : '' ?>>Casa</option>
                        <option value="apto" <?= $get('tipo_vivienda', '') === 'apto' ? 'selected' : '' ?>>Apartamento</option>
                        <option value="edificio" <?= $get('tipo_vivienda', '') === 'edificio' ? 'selected' : '' ?>>Edificio</option>
                    </select>
                    <span class="field-error" id="tipo_vivienda-error" style="display:none; color: var(--color-danger); font-size: 12px; margin-top: 4px;"></span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Localidad (Barrio / Urbanización)</label>
                    <input type="text" id="localidad" name="localidad" class="form-control" required maxlength="100" value="<?= e($get('localidad', '')) ?>" placeholder="Ej: Urb. Villas del Pilar, Barrio San Jose">
                    <span class="field-error" id="localidad-error" style="display:none; color: var(--color-danger); font-size: 12px; margin-top: 4px;"></span>
                </div>
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Dirección Exacta</label>
                    <input type="text" id="ubicacion_vivienda" name="ubicacion_vivienda" class="form-control" required maxlength="100" value="<?= e($get('ubicacion_vivienda', '')) ?>" placeholder="Ej: Calle 15A, Casa 412">
                    <span class="field-error" id="ubicacion_vivienda-error" style="display:none; color: var(--color-danger); font-size: 12px; margin-top: 4px;"></span>
                </div>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--color-border);">
                <button type="button" id="btn-atras" class="btn btn-ghost"><i class="ph ph-arrow-left"></i> Atrás</button>
                <button type="submit" class="btn btn-primary"><i class="ph ph-check"></i> <?= $isEdit ? 'Guardar Cambios' : 'Registrar Usuario' ?></button>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function showError(id, msg) {
        const el = document.getElementById(id + '-error');
        if (el) { el.textContent = msg; el.style.display = msg ? 'block' : 'none'; }
        const wrap = document.getElementById('phone-wrap-' + id);
        if (wrap) wrap.style.borderColor = msg ? 'var(--color-danger,#e53e3e)' : 'var(--color-border)';
        const inp = document.getElementById(id);
        if (inp && !wrap) inp.style.borderColor = msg ? 'var(--color-danger,#e53e3e)' : 'var(--color-border)';
    }
    function clearError(id) { showError(id, ''); }

    // Validación Dinámica en tiempo real para todos los campos
    const allInputs = document.querySelectorAll('input[required], select[required]');
    allInputs.forEach(input => {
        if (input.id === 'cedula' || input.id === 'telefono' || input.id === 'telefono_number') return;
        
        const validateField = () => {
            if (!input.value) {
                showError(input.id, 'Este campo es obligatorio.');
            } else if (input.type === 'email') {
                const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!regex.test(input.value)) {
                    showError(input.id, 'Por favor ingresa un correo válido (ej: usuario@correo.com).');
                } else {
                    clearError(input.id);
                }
            } else if (!input.checkValidity()) {
                let msg = 'El valor ingresado es inválido.';
                if (input.validity.tooShort) {
                    msg = `Debe tener al menos ${input.minLength} caracteres.`;
                } else if (input.type === 'date') {
                    msg = 'Ingresa una fecha válida (mayor de 18 años).';
                } else if (input.pattern && input.validity.patternMismatch) {
                    msg = input.title || 'El formato es incorrecto.';
                }
                showError(input.id, msg);
            } else {
                clearError(input.id);
            }
        };
        input.addEventListener('input', validateField);
        input.addEventListener('blur', validateField);
        input.addEventListener('change', validateField);
    });

    // Navegación Wizard (Paso a Paso)
    const stepPersonal = document.getElementById('step-personal');
    const stepDireccion = document.getElementById('step-direccion');
    const indicator1 = document.getElementById('step-indicator-1');
    const indicator2 = document.getElementById('step-indicator-2');

    document.getElementById('btn-siguiente').addEventListener('click', () => {
        const inputs = stepPersonal.querySelectorAll('[required]');
        let valid = true;
        inputs.forEach(input => { 
            // Trigger blur to show errors if they bypassed input
            input.dispatchEvent(new Event('blur'));
            if (!input.checkValidity()) valid = false; 
        });
        
        // Validación explícita para el widget de teléfono (ya que no usa el validador genérico)
        const telNumInput = document.getElementById('telefono_number');
        if (telNumInput) {
            telNumInput.dispatchEvent(new Event('blur'));
            if (!telNumInput.value || telNumInput.value.length !== 7) valid = false;
        }

        if (!valid) return;

        stepPersonal.style.display = 'none';
        stepDireccion.style.display = 'block';
        indicator1.style.fontWeight = '500'; indicator1.style.color = 'var(--color-text-muted)'; indicator1.style.borderBottom = 'none';
        indicator2.style.fontWeight = '600'; indicator2.style.color = 'var(--color-primary)'; indicator2.style.borderBottom = '2px solid var(--color-primary)';
    });

    document.getElementById('btn-atras').addEventListener('click', () => {
        stepDireccion.style.display = 'none';
        stepPersonal.style.display = 'block';
        indicator2.style.fontWeight = '500'; indicator2.style.color = 'var(--color-text-muted)'; indicator2.style.borderBottom = 'none';
        indicator1.style.fontWeight = '600'; indicator1.style.color = 'var(--color-primary)'; indicator1.style.borderBottom = '2px solid var(--color-primary)';
    });

    // Carga dinámica de direcciones (cascada Estado → Municipio → Parroquia)
    const selEstado = document.getElementById('sel-estado');
    const selMunicipio = document.getElementById('sel-municipio');
    const selParroquia = document.getElementById('sel-parroquia');

    async function loadSelect(sel, url, currentId) {
        try {
            if (typeof API === 'undefined') {
                console.warn('API no está definido aún. Esperando...');
                return;
            }
            const data = await API.get(url);
            sel.innerHTML = '<option value="">Selecciona...</option>';
            data.forEach(item => {
                const id = item.estado_id || item.municipio_id || item.parroquia_id;
                const name = item.nombre || item.estado || item.municipio || item.parroquia;
                const opt = document.createElement('option');
                opt.value = id;
                opt.textContent = name;
                if (parseInt(currentId) === parseInt(id)) opt.selected = true;
                sel.appendChild(opt);
            });
        } catch (e) { console.error('Error cargando select:', e); }
    }

    // Cargar estados al iniciar
    async function initLocationData() {
        if (typeof API === 'undefined') {
            setTimeout(initLocationData, 100);
            return;
        }
        await loadSelect(selEstado, '/api/direcciones/estados/1', selEstado.dataset.current);
        if (selEstado.dataset.current) {
            await loadSelect(selMunicipio, '/api/direcciones/municipios/' + selEstado.dataset.current, selMunicipio.dataset.current);
        }
        if (selMunicipio.dataset.current) {
            await loadSelect(selParroquia, '/api/direcciones/parroquias/' + selMunicipio.dataset.current, selParroquia.dataset.current);
        }
    }
    initLocationData();

    selEstado.addEventListener('change', async () => {
        selMunicipio.innerHTML = '<option value="">Selecciona Municipio...</option>';
        selParroquia.innerHTML = '<option value="">Selecciona Parroquia...</option>';
        if (selEstado.value) {
            await loadSelect(selMunicipio, '/api/direcciones/municipios/' + selEstado.value, 0);
        }
    });

    selMunicipio.addEventListener('change', async () => {
        selParroquia.innerHTML = '<option value="">Selecciona Parroquia...</option>';
        if (selMunicipio.value) {
            await loadSelect(selParroquia, '/api/direcciones/parroquias/' + selMunicipio.value, 0);
        }
    });

    // ── Cédula venezolana ────────────────────────────────────────────────────────
    const CEDULA_REGEX = /^[VE]-\d{1,3}(\.\d{3})*$/;
    const cedulaInput = document.getElementById('cedula');

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
    function validarCedula(val) { 
        if (!CEDULA_REGEX.test(val)) return false;
        const digitsOnly = val.replace(/[^\d]/g, '');
        return digitsOnly.length >= 7;
    }

    if (cedulaInput) {
        if (cedulaInput.value && !cedulaInput.value.includes('-')) {
             cedulaInput.value = normalizarCedula(cedulaInput.value);
        }
        cedulaInput.addEventListener('input', function() { 
            this.value = normalizarCedula(this.value); 
            if (this.value && this.value !== 'V-' && this.value !== 'E-') { 
                validarCedula(this.value) ? clearError(this.id) : showError(this.id, 'Formato inválido. Mínimo 7 números. Ej: V-12.345.678'); 
            }
        });
        cedulaInput.addEventListener('blur', function() {
            if (!this.value || this.value === 'V-' || this.value === 'E-') { showError(this.id, 'Este campo es obligatorio'); }
            else { validarCedula(this.value) ? clearError(this.id) : showError(this.id, 'Formato inválido. Mínimo 7 números. Ej: V-12.345.678'); }
        });
        cedulaInput.addEventListener('focus', function() { clearError(this.id); });
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

        numberEl.addEventListener('input', () => { 
            sync(); 
            if (numberEl.value && numberEl.value.length === 7) clearError(errorKey); 
            else if (numberEl.value) showError(errorKey, 'Ingresa 7 dígitos');
        });
        prefixEl.addEventListener('change', () => { sync(); clearError(errorKey); numberEl.focus(); });
        numberEl.addEventListener('blur', () => {
            const num = numberEl.value;
            if (!num) showError(errorKey, 'Este campo es obligatorio');
            else if (num.length !== 7) showError(errorKey, 'Ingresa 7 dígitos completos');
            else clearError(errorKey);
        });
        numberEl.addEventListener('focus', () => clearError(errorKey));
    }

    setupPhoneWidget('telefono_prefix', 'telefono_number', 'telefono', 'telefono');

    // Validación al enviar el formulario
    document.querySelector('form').addEventListener('submit', function(e) {
        let hasError = false;
        
        // Trigger blur on all to show messages
        const inputs = this.querySelectorAll('[required]');
        inputs.forEach(input => {
            input.dispatchEvent(new Event('blur'));
            if (!input.checkValidity()) hasError = true;
        });

        if (cedulaInput && !validarCedula(cedulaInput.value)) {
            showError('cedula', 'Revisa el formato de la cédula');
            hasError = true;
        }
        
        const telNum = document.getElementById('telefono_number')?.value;
        if (!telNum || telNum.length !== 7) {
            showError('telefono', 'Ingresa los 7 dígitos completos');
            hasError = true;
        }

        if (hasError) {
            e.preventDefault();
            // Ver si el error es del paso 1
            const firstErr = document.querySelector('.field-error[style*="block"]');
            if (firstErr) {
                const isStep1 = document.getElementById('step-personal').contains(firstErr);
                if (isStep1) document.getElementById('btn-atras').click();
                firstErr.parentElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
});
</script>
