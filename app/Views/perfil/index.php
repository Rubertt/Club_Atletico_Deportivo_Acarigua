<?php
/** @var array $item  @var array $preguntas  @var array $respuestas */
$get = function(string $k, $d = '') use ($item) { return old($k, $item[$k] ?? $d); };
$errors = $_SESSION['_errors'] ?? [];
unset($_SESSION['_errors']);
?>

<div class="page-header">
    <div>
        <h1><i class="ph ph-user-circle"></i> Mi Perfil</h1>
        <div class="subtitle">Gestiona tu información personal y seguridad</div>
    </div>
</div>

<!-- Pestañas -->
<div class="form-tabs" id="perfil-tabs">
    <button type="button" class="active" data-tab="datos">
        <i class="ph ph-user"></i> Datos Personales
    </button>
    <button type="button" data-tab="seguridad">
        <i class="ph ph-shield-check"></i> Seguridad
    </button>
</div>

<!-- ═══════════════════════════════════════════════
     PESTAÑA 1: DATOS PERSONALES
     ═══════════════════════════════════════════════ -->
<div class="form-tab-panel active" id="tab-datos">
    <form method="POST" action="<?= e(url('/admin/perfil')) ?>" enctype="multipart/form-data" class="card" style="max-width: 900px;">
        <?= csrf_field() ?>

        <!-- Info no editable -->
        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--color-border);">
            <div style="width: 72px; height: 72px; border-radius: 50%; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 800; font-family: var(--font-display); flex-shrink: 0; overflow: hidden;">
                <?php if (!empty($item['foto'])): ?>
                    <img src="<?= e(asset($item['foto'])) ?>" alt="Foto" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <?= strtoupper(mb_substr($item['nombre'] ?? '?', 0, 1, 'UTF-8')) ?>
                <?php endif; ?>
            </div>
            <div>
                <div style="font-size: 20px; font-weight: 700; color: var(--color-text); font-family: var(--font-display);">
                    <?= e(($item['nombre'] ?? '') . ' ' . ($item['apellido'] ?? '')) ?>
                </div>
                <div style="color: var(--color-text-muted); font-size: 14px;">
                    <i class="ph ph-identification-card"></i> <?= e($item['cedula'] ?? 'N/A') ?>
                    &nbsp;&middot;&nbsp;
                    <span class="badge badge-primary"><?= e(auth()['nombre_rol'] ?? '') ?></span>
                </div>
            </div>
        </div>

        <h3 style="margin-top: 0; margin-bottom: 20px; font-family: var(--font-display); color: var(--color-text);"><i class="ph ph-camera text-muted"></i> Foto de Perfil</h3>
        <div class="form-group" style="margin-bottom: 24px;">
            <label class="form-label">Cambiar foto</label>
            <input type="file" name="foto" accept="image/*" class="form-control">
            <div class="form-hint">Formatos: JPG, PNG, WebP. Máx 2MB.</div>
        </div>

        <h3 style="margin-bottom: 20px; font-family: var(--font-display); color: var(--color-text);"><i class="ph ph-envelope text-muted"></i> Contacto</h3>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label"><span class="required">*</span> Correo electrónico</label>
                <input type="email" id="perfil_correo" name="correo" class="form-control<?= isset($errors['correo']) ? ' is-invalid' : '' ?>" required maxlength="50" value="<?= e($get('correo', '')) ?>">
                <span class="field-error" id="perfil_correo-error" style="display:<?= isset($errors['correo']) ? 'block' : 'none' ?>; color: var(--color-danger); font-size: 12px; margin-top: 4px;"><?= e($errors['correo'] ?? '') ?></span>
            </div>
            <div class="form-group">
                <label class="form-label"><span class="required">*</span> Teléfono</label>
                <?php
                    $telVal   = $get('telefono', '');
                    $telPref  = '0412';
                    $telNum   = '';
                    foreach (['0412','0414','0416','0422','0424','0426'] as $_p) {
                        if (str_starts_with($telVal, $_p)) { $telPref = $_p; $telNum = substr($telVal, 4); break; }
                    }
                ?>
                <div class="phone-field" id="phone-wrap-perfil_telefono" style="display: flex; align-items: center; border: 1px solid var(--color-border); border-radius: 6px; overflow: hidden; background: var(--color-bg);">
                    <select class="phone-prefix" id="perfil_telefono_prefix" style="border: none; background: transparent; padding: 10px; font-size: 14px; outline: none; border-right: 1px solid var(--color-border); cursor: pointer; color: var(--color-text);">
                        <?php foreach (['0412','0414','0416','0422','0424','0426'] as $_p): ?>
                            <option value="<?= $_p ?>" <?= $telPref === $_p ? 'selected' : '' ?>><?= $_p ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" class="phone-number" id="perfil_telefono_number" maxlength="7" placeholder="1234567" inputmode="numeric" value="<?= e($telNum) ?>" style="border: none; background: transparent; padding: 10px; font-size: 14px; outline: none; width: 100%; color: var(--color-text);">>
                    <input type="hidden" name="telefono" id="perfil_telefono" required>
                </div>
                <span class="field-error" id="perfil_telefono-error" style="display:none; color: var(--color-danger); font-size: 12px; margin-top: 4px;"></span>
            </div>
        </div>

        <h3 style="margin-bottom: 20px; font-family: var(--font-display); color: var(--color-text);"><i class="ph ph-map-pin text-muted"></i> Ubicación</h3>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label"><span class="required">*</span> Estado</label>
                <select id="perfil_estado" class="form-control" data-current="<?= (int)($item['estado_id'] ?? 0) ?>" required>
                    <option value="">Seleccione...</option>
                </select>
                <span class="field-error" id="perfil_estado-error" style="display:none; color: var(--color-danger); font-size: 12px; margin-top: 4px;"></span>
            </div>
            <div class="form-group">
                <label class="form-label"><span class="required">*</span> Municipio</label>
                <select id="perfil_municipio" class="form-control" data-current="<?= (int)($item['municipio_id'] ?? 0) ?>" required>
                    <option value="">Seleccione...</option>
                </select>
                <span class="field-error" id="perfil_municipio-error" style="display:none; color: var(--color-danger); font-size: 12px; margin-top: 4px;"></span>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label"><span class="required">*</span> Parroquia</label>
                <select id="perfil_parroquia" name="parroquia_id" class="form-control" data-current="<?= (int)($item['parroquias_id'] ?? 0) ?>" required>
                    <option value="">Seleccione...</option>
                </select>
                <span class="field-error" id="perfil_parroquia-error" style="display:none; color: var(--color-danger); font-size: 12px; margin-top: 4px;"></span>
            </div>
            <div class="form-group">
                <label class="form-label"><span class="required">*</span> Tipo de vivienda</label>
                <select id="perfil_tipo_vivienda" name="tipo_vivienda" class="form-control" required>
                    <option value="">Seleccione...</option>
                    <option value="casa" <?= $get('tipo_vivienda', '') === 'casa' ? 'selected' : '' ?>>Casa</option>
                    <option value="apto" <?= $get('tipo_vivienda', '') === 'apto' ? 'selected' : '' ?>>Apartamento</option>
                    <option value="edificio" <?= $get('tipo_vivienda', '') === 'edificio' ? 'selected' : '' ?>>Edificio</option>
                </select>
                <span class="field-error" id="perfil_tipo_vivienda-error" style="display:none; color: var(--color-danger); font-size: 12px; margin-top: 4px;"></span>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label"><span class="required">*</span> Localidad (Barrio / Urbanización)</label>
                <input type="text" id="perfil_localidad" name="localidad" class="form-control" value="<?= e($get('localidad', '')) ?>" maxlength="100" placeholder="Ej: Urb. Villas del Pilar, Barrio San Jose" required>
                <span class="field-error" id="perfil_localidad-error" style="display:none; color: var(--color-danger); font-size: 12px; margin-top: 4px;"></span>
            </div>
            <div class="form-group">
                <label class="form-label"><span class="required">*</span> Dirección Exacta</label>
                <input type="text" id="perfil_ubicacion_vivienda" name="ubicacion_vivienda" class="form-control" value="<?= e($get('ubicacion_vivienda', '')) ?>" maxlength="100" placeholder="Ej: Calle 15A, Casa 412" required>
                <span class="field-error" id="perfil_ubicacion_vivienda-error" style="display:none; color: var(--color-danger); font-size: 12px; margin-top: 4px;"></span>
            </div>
        </div>

        <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
            <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Guardar Cambios</button>
        </div>
    </form>
</div>

<!-- ═══════════════════════════════════════════════
     PESTAÑA 2: SEGURIDAD
     ═══════════════════════════════════════════════ -->
<div class="form-tab-panel" id="tab-seguridad">
    <form method="POST" action="<?= e(url('/admin/perfil/seguridad')) ?>" class="card" style="max-width: 700px;">
        <?= csrf_field() ?>

        <h3 style="margin-top: 0; margin-bottom: 20px; font-family: var(--font-display); color: var(--color-text);"><i class="ph ph-lock text-muted"></i> Cambiar Contraseña</h3>
        <div class="form-group">
            <label class="form-label"><span class="required">*</span> Contraseña Actual</label>
            <input type="password" name="current_password" class="form-control<?= isset($errors['current_password']) ? ' is-invalid' : '' ?>" required>
            <?php if (isset($errors['current_password'])): ?><div class="form-error"><?= e($errors['current_password']) ?></div><?php endif; ?>
            <div class="form-hint">Necesaria para confirmar cualquier cambio de seguridad.</div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Nueva Contraseña</label>
                <input type="password" id="new_password" name="new_password" class="form-control<?= isset($errors['new_password']) ? ' is-invalid' : '' ?>" minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}" title="Debe tener al menos 8 caracteres, una letra, un número y un símbolo especial">
                <?php if (isset($errors['new_password'])): ?><div class="form-error"><?= e($errors['new_password']) ?></div><?php endif; ?>
                <ul class="pwd-rules" style="list-style: none; padding: 0; margin: 8px 0 0 0; font-size: 13px; color: var(--color-text-muted);">
                    <li id="rule-len" style="margin-bottom: 4px;"><i class="ph ph-x-circle" style="color: var(--color-danger); margin-right: 4px;"></i> Mínimo 8 caracteres</li>
                    <li id="rule-let" style="margin-bottom: 4px;"><i class="ph ph-x-circle" style="color: var(--color-danger); margin-right: 4px;"></i> Al menos una letra</li>
                    <li id="rule-num" style="margin-bottom: 4px;"><i class="ph ph-x-circle" style="color: var(--color-danger); margin-right: 4px;"></i> Al menos un número</li>
                    <li id="rule-sym" style="margin-bottom: 0;"><i class="ph ph-x-circle" style="color: var(--color-danger); margin-right: 4px;"></i> Al menos un símbolo especial</li>
                </ul>
                <div class="form-hint" style="margin-top: 8px;">Déjalo vacío si no deseas cambiarla.</div>
            </div>
            <div class="form-group">
                <label class="form-label">Confirmar Nueva Contraseña</label>
                <input type="password" id="perfil_password_confirm" name="new_password_confirm" class="form-control<?= isset($errors['new_password_confirm']) ? ' is-invalid' : '' ?>">
                <span class="field-error" id="perfil_password_confirm-error" style="display:none; color: var(--color-danger); font-size: 12px; margin-top: 4px;"></span>
                <?php if (isset($errors['new_password_confirm'])): ?><div class="form-error"><?= e($errors['new_password_confirm']) ?></div><?php endif; ?>
            </div>
        </div>

        <hr style="border: 0; border-top: 1px solid var(--color-border); margin: 28px 0;">

        <h3 style="margin-bottom: 8px; font-family: var(--font-display); color: var(--color-text);"><i class="ph ph-shield-check text-muted"></i> Preguntas de Seguridad</h3>
        <p style="color: var(--color-text-muted); font-size: 14px; margin-bottom: 20px;">Estas preguntas te permitirán recuperar tu cuenta si olvidas tu contraseña.</p>

        <?php if (!empty($respuestas)): ?>
            <div style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-sm); padding: 12px 16px; margin-bottom: 20px; font-size: 13px; color: var(--color-text-muted);">
                <strong>Preguntas actuales:</strong>
                <ul style="margin: 6px 0 0; padding-left: 20px;">
                    <?php foreach ($respuestas as $r): ?>
                        <li><?= e($r['preguntas']) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label class="form-label">Pregunta de Seguridad 1</label>
            <select id="seg_pregunta_1" name="pregunta_1" class="form-control">
                <option value="">Selecciona una pregunta...</option>
                <?php foreach ($preguntas as $p): ?>
                    <option value="<?= (int) $p['pregunta_id'] ?>"><?= e($p['preguntas']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Respuesta 1</label>
            <input type="text" name="respuesta_1" class="form-control" autocomplete="off">
        </div>

        <div class="form-group">
            <label class="form-label">Pregunta de Seguridad 2</label>
            <select id="seg_pregunta_2" name="pregunta_2" class="form-control">
                <option value="">Selecciona una pregunta...</option>
                <?php foreach ($preguntas as $p): ?>
                    <option value="<?= (int) $p['pregunta_id'] ?>"><?= e($p['preguntas']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Respuesta 2</label>
            <input type="text" name="respuesta_2" class="form-control" autocomplete="off">
        </div>

        <div class="form-hint" style="margin-bottom: 16px;">
            <i class="ph ph-info"></i> Para actualizar las preguntas, debes llenar las 4 casillas (2 preguntas + 2 respuestas). Si solo deseas cambiar la contraseña, déjalas vacías.
        </div>

        <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
            <button type="submit" class="btn btn-primary"><i class="ph ph-shield-check"></i> Guardar Seguridad</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // ── Tabs ──
    const urlParams = new URLSearchParams(window.location.search);
    const activeTabParam = urlParams.get('tab');

    document.querySelectorAll('#perfil-tabs button').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('#perfil-tabs button').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.form-tab-panel').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(btn.dataset.tab === 'seguridad' ? 'tab-seguridad' : 'tab-datos').classList.add('active');
            
            // Opcional: Actualizar la URL sin recargar para que si el usuario refresca manualmente se quede ahí
            const url = new URL(window.location);
            url.searchParams.set('tab', btn.dataset.tab === 'seguridad' ? 'seguridad' : 'personal');
            window.history.replaceState({}, '', url);
        });
    });

    if (activeTabParam === 'seguridad') {
        const segBtn = document.querySelector('#perfil-tabs button[data-tab="seguridad"]');
        if (segBtn) segBtn.click();
    }

    // ── Teléfono combinar ──
    const prefix = document.getElementById('perfil_telefono_prefix');
    const number = document.getElementById('perfil_telefono_number');
    const hidden = document.getElementById('perfil_telefono');
    function syncTel() {
        if (prefix && number && hidden) hidden.value = prefix.value + number.value;
    }
    prefix?.addEventListener('change', syncTel);
    number?.addEventListener('input', syncTel);
    syncTel();

    // ── Cascada de ubicación ──
    const estadoSel    = document.getElementById('perfil_estado');
    const municipioSel = document.getElementById('perfil_municipio');
    const parroquiaSel = document.getElementById('perfil_parroquia');
    const API = '/api/direcciones';

    async function loadOptions(url, sel, currentVal) {
        try {
            const res = await fetch(url);
            const data = await res.json();
            const items = data.data ?? data;
            sel.innerHTML = '<option value="">Seleccione...</option>';
            items.forEach(i => {
                const opt = document.createElement('option');
                opt.value = i.id ?? i.estado_id ?? i.municipio_id ?? i.parroquia_id;
                opt.textContent = i.nombre ?? i.estado ?? i.municipio ?? i.parroquia;
                if (String(opt.value) === String(currentVal)) opt.selected = true;
                sel.appendChild(opt);
            });
        } catch (e) { console.error(e); }
    }

    // Load estados
    loadOptions(API + '/estados/1', estadoSel, estadoSel.dataset.current).then(() => {
        if (estadoSel.value) {
            loadOptions(API + '/municipios/' + estadoSel.value, municipioSel, municipioSel.dataset.current).then(() => {
                if (municipioSel.value) {
                    loadOptions(API + '/parroquias/' + municipioSel.value, parroquiaSel, parroquiaSel.dataset.current);
                }
            });
        }
    });

    estadoSel?.addEventListener('change', () => {
        municipioSel.innerHTML = '<option value="">Seleccione...</option>';
        parroquiaSel.innerHTML = '<option value="">Seleccione...</option>';
        if (estadoSel.value) loadOptions(API + '/municipios/' + estadoSel.value, municipioSel, 0);
    });
    municipioSel?.addEventListener('change', () => {
        parroquiaSel.innerHTML = '<option value="">Seleccione...</option>';
        if (municipioSel.value) loadOptions(API + '/parroquias/' + municipioSel.value, parroquiaSel, 0);
    });

    // ── Preguntas: evitar repetir ──
    const p1 = document.getElementById('seg_pregunta_1');
    const p2 = document.getElementById('seg_pregunta_2');
    function checkPreguntas() {
        if (!p1 || !p2) return;
        Array.from(p1.options).forEach(opt => opt.disabled = false);
        Array.from(p2.options).forEach(opt => opt.disabled = false);
        if (p1.value) { const o = p2.querySelector(`option[value="${p1.value}"]`); if (o) o.disabled = true; }
        if (p2.value) { const o = p1.querySelector(`option[value="${p2.value}"]`); if (o) o.disabled = true; }
    }
    p1?.addEventListener('change', checkPreguntas);
    p2?.addEventListener('change', checkPreguntas);
    // ── Utilidades de validación ──
    function showError(id, msg) {
        const el = document.getElementById(id + '-error');
        if (el) { el.textContent = msg; el.style.display = msg ? 'block' : 'none'; }
        const wrap = document.getElementById('phone-wrap-' + id);
        if (wrap) wrap.style.borderColor = msg ? 'var(--color-danger)' : 'var(--color-border)';
        const inp = document.getElementById(id);
        if (inp && !wrap) inp.style.borderColor = msg ? 'var(--color-danger)' : 'var(--color-border)';
    }
    function clearError(id) { showError(id, ''); }

    // ── Validaciones Dinámicas genéricas ──
    const allInputs = document.querySelectorAll('input[required], select[required]');
    allInputs.forEach(input => {
        if (input.id === 'perfil_telefono' || input.id === 'perfil_telefono_number') return;
        
        const validateField = () => {
            if (!input.value) {
                showError(input.id, 'Este campo es obligatorio.');
            } else if (input.type === 'email') {
                const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!regex.test(input.value)) showError(input.id, 'Por favor ingresa un correo válido (ej: usuario@correo.com).');
                else clearError(input.id);
            } else if (!input.checkValidity()) {
                let msg = 'El valor ingresado es inválido.';
                if (input.validity.tooShort) {
                    msg = `Debe tener al menos ${input.minLength} caracteres.`;
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

    // ── Validaciones específicas ──
    
    // 1. Teléfono
    number?.addEventListener('input', (e) => {
        e.target.value = e.target.value.replace(/\D/g, '');
        if (e.target.value.length > 7) e.target.value = e.target.value.slice(0, 7);
        syncTel();
        if (e.target.value.length === 7) clearError('perfil_telefono');
        else if (e.target.value) showError('perfil_telefono', 'Ingresa 7 dígitos');
    });
    number?.addEventListener('blur', (e) => {
        if (!e.target.value) showError('perfil_telefono', 'Este campo es obligatorio.');
        else if (e.target.value.length !== 7) showError('perfil_telefono', 'Ingresa los 7 dígitos completos.');
        else clearError('perfil_telefono');
    });
    prefix?.addEventListener('change', () => { syncTel(); clearError('perfil_telefono'); number.focus(); });
    number?.addEventListener('focus', () => clearError('perfil_telefono'));

    // 2. Validar Foto (Peso y Tipo)
    const fotoInput = document.querySelector('input[name="foto"]');
    fotoInput?.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;
        
        const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            alert('Formato no válido. Usa JPG, PNG o WEBP.');
            e.target.value = '';
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            alert('La imagen es muy pesada. El límite es 2MB.');
            e.target.value = '';
            return;
        }
    });

    // 3. Confirmación de Contraseña y Checklist
    const passInput = document.getElementById('new_password');
    const passConfirm = document.getElementById('perfil_password_confirm');
    
    function checkPasswords() {
        if (!passInput.value && !passConfirm.value) {
            clearError('perfil_password_confirm');
            return;
        }
        if (passInput.value !== passConfirm.value) {
            showError('perfil_password_confirm', 'Las contraseñas no coinciden.');
        } else {
            clearError('perfil_password_confirm');
        }
    }
    
    function updateRules() {
        const val = passInput.value;
        const rules = [
            { id: 'rule-len', valid: val.length >= 8 },
            { id: 'rule-let', valid: /[A-Za-z]/.test(val) },
            { id: 'rule-num', valid: /[0-9]/.test(val) },
            { id: 'rule-sym', valid: /[^A-Za-z0-9]/.test(val) }
        ];
        
        rules.forEach(r => {
            const li = document.getElementById(r.id);
            if (!li) return;
            const icon = li.querySelector('i');
            if (!val) {
                // If empty, reset all
                icon.className = 'ph ph-x-circle';
                icon.style.color = 'var(--color-danger)';
                li.style.color = 'var(--color-text-muted)';
                return;
            }
            if (r.valid) {
                icon.className = 'ph ph-check-circle';
                icon.style.color = 'var(--color-success)';
                li.style.color = 'var(--color-success)';
            } else {
                icon.className = 'ph ph-x-circle';
                icon.style.color = 'var(--color-danger)';
                li.style.color = 'var(--color-text-muted)';
            }
        });
    }

    passInput?.addEventListener('input', () => {
        checkPasswords();
        updateRules();
    });
    passConfirm?.addEventListener('input', checkPasswords);

    // Validación al enviar el formulario (Pestaña datos)
    document.querySelector('#tab-datos form').addEventListener('submit', function(e) {
        let hasError = false;
        const inputs = this.querySelectorAll('[required]');
        inputs.forEach(input => {
            input.dispatchEvent(new Event('blur'));
            if (input.type === 'email') {
                 const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                 if (!regex.test(input.value)) hasError = true;
            } else if (!input.checkValidity()) {
                 hasError = true;
            }
        });
        
        const telNum = number?.value;
        if (!telNum || telNum.length !== 7) {
            showError('perfil_telefono', 'Ingresa los 7 dígitos completos');
            hasError = true;
        }
        if (hasError) e.preventDefault();
    });

});
</script>
