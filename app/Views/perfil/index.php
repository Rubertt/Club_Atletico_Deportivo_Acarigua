<?php
/** @var array $item  @var array $preguntas  @var array $respuestas */
$get = function(string $k, $d = '') use ($item) { return old($k, $item[$k] ?? $d); };
?>

<div class="af-container">
    <div class="page-header af-header" style="justify-content: center; text-align: center;">
        <div class="af-header__content">
            <h1><i class="ph ph-user-circle"></i> Mi Perfil</h1>
            <p class="subtitle">Gestiona tu información personal y seguridad</p>
        </div>
    </div>

    <!-- Pestañas Premium -->
    <div class="af-tabs-wrapper" style="margin-bottom: 24px; border-radius: var(--radius-lg); border: 1px solid var(--color-border); overflow: hidden;">
        <div class="af-tabs" role="tablist" id="perfil-tabs">
            <button type="button" class="ft-tab active" data-tab="datos">
                <div class="ft-tab__icon"><i class="ph ph-user"></i></div>
                <div class="ft-tab__text">Datos Personales</div>
            </button>
            <button type="button" class="ft-tab" data-tab="seguridad">
                <div class="ft-tab__icon"><i class="ph ph-shield-check"></i></div>
                <div class="ft-tab__text">Seguridad</div>
            </button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════
         PESTAÑA 1: DATOS PERSONALES
         ═══════════════════════════════════════════════ -->
    <div class="form-tab-panel active" id="tab-datos">
        <form method="POST" action="<?= e(url('/admin/perfil')) ?>" enctype="multipart/form-data" class="card af-card" style="margin: 0 auto; max-width: 900px;" novalidate>
            <?= csrf_field() ?>

            <div class="af-body">
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

                <!-- Foto de Perfil -->
                <div class="af-section-header" style="margin-top: 0;">
                    <div class="af-section-icon"><i class="ph ph-camera"></i></div>
                    <div class="af-section-info">
                        <h3>Foto de Perfil</h3>
                        <p>Personaliza tu foto de perfil visible en la plataforma</p>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 28px;">
                    <label class="form-label">Foto de Perfil</label>
                    <div class="af-file-upload">
                        <input type="file" name="foto" id="perfil-foto-input" class="af-file-input" accept="image/jpeg,image/png,image/webp">
                        <label for="perfil-foto-input" class="af-file-label <?= !empty($item['foto']) ? 'has-file' : '' ?>" id="foto-label">
                            <i class="ph ph-camera"></i>
                            <span><?= !empty($item['foto']) ? 'Cambiar foto' : 'Subir foto' ?></span>
                        </label>
                        <div class="af-file-preview" id="foto-preview-container" style="<?= empty($item['foto']) ? 'display:none;' : '' ?>">
                            <img src="<?= !empty($item['foto']) ? e(url($item['foto'])) : '' ?>" id="foto-preview-img" alt="Vista previa">
                            <button type="button" class="af-file-remove" id="btn-remove-foto" title="Quitar foto"><i class="ph ph-x"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Contacto -->
                <div class="af-section-header">
                    <div class="af-section-icon"><i class="ph ph-envelope"></i></div>
                    <div class="af-section-info">
                        <h3>Datos de Contacto</h3>
                        <p>Información de comunicación del usuario</p>
                    </div>
                </div>
                <div class="af-grid af-grid--2">
                    <div class="form-group">
                        <label class="form-label"><span class="required">*</span> Correo electrónico</label>
                        <input type="email" id="perfil_correo" name="correo" class="form-control" required maxlength="50" value="<?= e($get('correo', '')) ?>" placeholder="ejemplo@correo.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><span class="required">*</span> Teléfono</label>
                        <?php
                            $telVal   = $get('telefono', '');
                            $telPref  = '0412';
                            $telNum   = '';
                            foreach (['0412','0414','0416','0422','0424','0426','0255','0256'] as $_p) {
                                if (str_starts_with($telVal, $_p)) { $telPref = $_p; $telNum = substr($telVal, 4); break; }
                            }
                        ?>
                        <div class="phone-field" id="phone-wrap-perfil_telefono">
                            <select class="phone-prefix" id="perfil_telefono_prefix">
                                <?php foreach (['0412','0414','0416','0422','0424','0426','0255','0256'] as $_p): ?>
                                    <option value="<?= $_p ?>" <?= $telPref === $_p ? 'selected' : '' ?>><?= $_p ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="phone-sep">-</span>
                            <input type="text" class="phone-number" id="perfil_telefono_number" maxlength="7" placeholder="1234567" inputmode="numeric" value="<?= e($telNum) ?>">
                            <input type="hidden" name="telefono" id="perfil_telefono" required>
                        </div>
                    </div>
                </div>

                <!-- Ubicación -->
                <div class="af-section-header" style="margin-top: 24px;">
                    <div class="af-section-icon"><i class="ph ph-map-pin"></i></div>
                    <div class="af-section-info">
                        <h3>Dirección de Residencia</h3>
                        <p>Datos geográficos y detalles del domicilio</p>
                    </div>
                </div>
                <div class="af-grid af-grid--2">
                    <div class="form-group">
                        <label class="form-label"><span class="required">*</span> Estado</label>
                        <select id="perfil_estado" class="form-control" data-current="<?= (int)($item['estado_id'] ?? 0) ?>" required>
                            <option value="">— Seleccione —</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><span class="required">*</span> Municipio</label>
                        <select id="perfil_municipio" class="form-control" data-current="<?= (int)($item['municipio_id'] ?? 0) ?>" required>
                            <option value="">— Seleccione —</option>
                        </select>
                    </div>
                </div>
                <div class="af-grid af-grid--2">
                    <div class="form-group">
                        <label class="form-label"><span class="required">*</span> Parroquia</label>
                        <select id="perfil_parroquia" name="parroquia_id" class="form-control" data-current="<?= (int)($item['parroquias_id'] ?? 0) ?>" required>
                            <option value="">— Seleccione —</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><span class="required">*</span> Tipo de vivienda</label>
                        <select id="perfil_tipo_vivienda" name="tipo_vivienda" class="form-control" required>
                            <option value="">— Seleccione —</option>
                            <option value="casa" <?= $get('tipo_vivienda', '') === 'casa' ? 'selected' : '' ?>>Casa</option>
                            <option value="apto" <?= $get('tipo_vivienda', '') === 'apto' ? 'selected' : '' ?>>Apartamento</option>
                            <option value="edificio" <?= $get('tipo_vivienda', '') === 'edificio' ? 'selected' : '' ?>>Edificio</option>
                        </select>
                    </div>
                </div>
                <div class="af-grid af-grid--2">
                    <div class="form-group">
                        <label class="form-label"><span class="required">*</span> Localidad (Barrio / Urbanización)</label>
                        <input type="text" id="perfil_localidad" name="localidad" class="form-control" value="<?= e($get('localidad', '')) ?>" maxlength="100" placeholder="Ej: Urb. Villas del Pilar, Barrio San Jose" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><span class="required">*</span> Dirección Exacta</label>
                        <input type="text" id="perfil_ubicacion_vivienda" name="ubicacion_vivienda" class="form-control" value="<?= e($get('ubicacion_vivienda', '')) ?>" maxlength="100" placeholder="Ej: Calle 15A, Casa 412" required>
                    </div>
                </div>
            </div>

            <div class="af-footer" style="justify-content: flex-end;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Guardar Cambios</button>
                </div>
            </div>
        </form>
    </div>

    <!-- ═══════════════════════════════════════════════
         PESTAÑA 2: SEGURIDAD
         ═══════════════════════════════════════════════ -->
    <div class="form-tab-panel" id="tab-seguridad">
        <form method="POST" action="<?= e(url('/admin/perfil/seguridad')) ?>" class="card af-card" style="margin: 0 auto; max-width: 700px;" novalidate>
            <?= csrf_field() ?>

            <div class="af-body">
                <!-- Cambiar Contraseña -->
                <div class="af-section-header" style="margin-top: 0;">
                    <div class="af-section-icon"><i class="ph ph-lock"></i></div>
                    <div class="af-section-info">
                        <h3>Cambiar Contraseña</h3>
                        <p>Actualiza tus credenciales de acceso de forma segura</p>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label"><span class="required">*</span> Contraseña Actual</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>

                <div class="af-grid af-grid--2">
                    <div class="form-group">
                        <label class="form-label">Nueva Contraseña</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}" title="Debe tener al menos 8 caracteres, una letra, un número y un símbolo especial">
                        <ul class="pwd-rules" style="list-style: none; padding: 0; margin: 8px 0 0 0; font-size: 13px; color: var(--color-text-muted);">
                            <li id="rule-len" style="margin-bottom: 4px;"><i class="ph ph-x-circle" style="color: var(--color-danger); margin-right: 4px;"></i> Mínimo 8 caracteres</li>
                            <li id="rule-let" style="margin-bottom: 4px;"><i class="ph ph-x-circle" style="color: var(--color-danger); margin-right: 4px;"></i> Al menos una letra</li>
                            <li id="rule-num" style="margin-bottom: 4px;"><i class="ph ph-x-circle" style="color: var(--color-danger); margin-right: 4px;"></i> Al menos un número</li>
                            <li id="rule-sym" style="margin-bottom: 0;"><i class="ph ph-x-circle" style="color: var(--color-danger); margin-right: 4px;"></i> Al menos un símbolo especial</li>
                        </ul>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirmar Nueva Contraseña</label>
                        <input type="password" id="perfil_password_confirm" name="new_password_confirm" class="form-control">
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--color-border); margin: 28px 0;">

                <!-- Preguntas de Seguridad -->
                <div class="af-section-header">
                    <div class="af-section-icon"><i class="ph ph-shield-check"></i></div>
                    <div class="af-section-info">
                        <h3>Preguntas de Seguridad</h3>
                        <p>Configura preguntas secretas para la recuperación de tu cuenta</p>
                    </div>
                </div>

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

                <div class="af-grid af-grid--2">
                    <div class="form-group">
                        <label class="form-label">Pregunta de Seguridad 1</label>
                        <select id="seg_pregunta_1" name="pregunta_1" class="form-control">
                            <option value="">— Seleccione —</option>
                            <?php foreach ($preguntas as $p): ?>
                                <option value="<?= (int) $p['pregunta_id'] ?>"><?= e($p['preguntas']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Respuesta 1</label>
                        <input type="text" name="respuesta_1" class="form-control" autocomplete="off">
                    </div>
                </div>

                <div class="af-grid af-grid--2" style="margin-top: 12px;">
                    <div class="form-group">
                        <label class="form-label">Pregunta de Seguridad 2</label>
                        <select id="seg_pregunta_2" name="pregunta_2" class="form-control">
                            <option value="">— Seleccione —</option>
                            <?php foreach ($preguntas as $p): ?>
                                <option value="<?= (int) $p['pregunta_id'] ?>"><?= e($p['preguntas']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Respuesta 2</label>
                        <input type="text" name="respuesta_2" class="form-control" autocomplete="off">
                    </div>
                </div>
            </div>

            <div class="af-footer" style="justify-content: flex-end;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <button type="submit" class="btn btn-primary"><i class="ph ph-shield-check"></i> Guardar Seguridad</button>
                </div>
            </div>
        </form>
    </div>
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
            
            const url = new URL(window.location);
            url.searchParams.set('tab', btn.dataset.tab === 'seguridad' ? 'seguridad' : 'personal');
            window.history.replaceState({}, '', url);
        });
    });

    if (activeTabParam === 'seguridad') {
        const segBtn = document.querySelector('#perfil-tabs button[data-tab="seguridad"]');
        if (segBtn) segBtn.click();
    }

    // ── Teléfono combinar (solo sync, sin error inline) ──
    const prefix = document.getElementById('perfil_telefono_prefix');
    const number = document.getElementById('perfil_telefono_number');
    const hidden = document.getElementById('perfil_telefono');
    function syncTel() {
        if (prefix && number && hidden) {
            const num = number.value.replace(/\D/g, '').substring(0, 7);
            number.value = num;
            hidden.value = prefix.value + num;
        }
    }
    prefix?.addEventListener('change', () => { syncTel(); number.focus(); });
    number?.addEventListener('input', syncTel);
    syncTel();

    // ── Cascada de ubicación ──
    const estadoSel    = document.getElementById('perfil_estado');
    const municipioSel = document.getElementById('perfil_municipio');
    const parroquiaSel = document.getElementById('perfil_parroquia');
    const apiBase = '/api/direcciones';

    async function loadOptions(url, sel, currentVal) {
        try {
            const res = await fetch(url);
            const data = await res.json();
            const items = data.data ?? data;
            sel.innerHTML = '<option value="">— Seleccione —</option>';
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
    loadOptions(apiBase + '/estados/1', estadoSel, estadoSel.dataset.current).then(() => {
        if (estadoSel.value) {
            loadOptions(apiBase + '/municipios/' + estadoSel.value, municipioSel, municipioSel.dataset.current).then(() => {
                if (municipioSel.value) {
                    loadOptions(apiBase + '/parroquias/' + municipioSel.value, parroquiaSel, parroquiaSel.dataset.current);
                }
            });
        }
    });

    estadoSel?.addEventListener('change', () => {
        municipioSel.innerHTML = '<option value="">— Seleccione —</option>';
        parroquiaSel.innerHTML = '<option value="">— Seleccione —</option>';
        if (estadoSel.value) loadOptions(apiBase + '/municipios/' + estadoSel.value, municipioSel, 0);
    });
    municipioSel?.addEventListener('change', () => {
        parroquiaSel.innerHTML = '<option value="">— Seleccione —</option>';
        if (municipioSel.value) loadOptions(apiBase + '/parroquias/' + municipioSel.value, parroquiaSel, 0);
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

    // ── Password rules checklist (visual guide only) ──
    const passInput = document.getElementById('new_password');
    
    function updateRules() {
        if (!passInput) return;
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

    passInput?.addEventListener('input', updateRules);

    // ── Foto validation, preview & remove ──
    const fotoInput = document.getElementById('perfil-foto-input');
    const fotoLabel = document.getElementById('foto-label');
    const fotoPreviewCont = document.getElementById('foto-preview-container');
    const fotoPreviewImg = document.getElementById('foto-preview-img');
    const btnRemoveFoto = document.getElementById('btn-remove-foto');

    if (fotoInput) {
        fotoInput.addEventListener('change', function(e) {
            const file = this.files[0];
            if (!file) return;

            const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                CadaModal.alert({ title: 'Formato no válido', text: 'Usa JPG, PNG o WEBP.', type: 'warning' });
                this.value = '';
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                CadaModal.alert({ title: 'Archivo muy pesado', text: 'La imagen no puede superar 2MB.', type: 'warning' });
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                if (fotoPreviewImg) fotoPreviewImg.src = e.target.result;
                if (fotoPreviewCont) fotoPreviewCont.style.display = 'block';
                if (fotoLabel) {
                    fotoLabel.classList.add('has-file');
                    const span = fotoLabel.querySelector('span');
                    if (span) span.textContent = 'Cambiar foto';
                }
            }
            reader.readAsDataURL(file);

            let deleteInput = document.getElementById('delete-foto-flag');
            if (deleteInput) deleteInput.remove();
        });

        btnRemoveFoto?.addEventListener('click', (e) => {
            e.preventDefault();
            fotoInput.value = '';
            if (fotoPreviewCont) fotoPreviewCont.style.display = 'none';
            if (fotoLabel) {
                fotoLabel.classList.remove('has-file');
                const span = fotoLabel.querySelector('span');
                if (span) span.textContent = 'Subir foto';
            }

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

    // ── Validación estándar: Pestaña Datos ──
    FormValidator.init('#tab-datos form', {
        custom: (form) => {
            const errors = [];
            const telNum = number?.value;
            if (!telNum || telNum.length !== 7) {
                errors.push({ label: 'El teléfono debe tener 7 dígitos completos', element: number });
            }
            return errors;
        }
    });

    // ── Validación estándar: Pestaña Seguridad ──
    FormValidator.init('#tab-seguridad form', {
        custom: (form) => {
            const errors = [];
            const newPass = document.getElementById('new_password')?.value.trim();
            const confirmPass = document.getElementById('perfil_password_confirm')?.value.trim();
            
            const p1 = document.getElementById('seg_pregunta_1')?.value;
            const r1 = form.querySelector('[name="respuesta_1"]')?.value.trim();
            const p2 = document.getElementById('seg_pregunta_2')?.value;
            const r2 = form.querySelector('[name="respuesta_2"]')?.value.trim();

            const hasPass = newPass.length > 0;
            const hasQuestions = (p1 || r1 || p2 || r2);

            if (!hasPass && !hasQuestions) {
                const fields = [
                    document.getElementById('new_password'),
                    document.getElementById('perfil_password_confirm'),
                    document.getElementById('seg_pregunta_1'),
                    form.querySelector('[name="respuesta_1"]'),
                    document.getElementById('seg_pregunta_2'),
                    form.querySelector('[name="respuesta_2"]')
                ];
                fields.forEach(el => {
                    if (el) FormValidator.markError(el);
                });

                errors.push({ 
                    label: 'Debe ingresar una nueva contraseña o configurar sus preguntas de seguridad.', 
                    element: document.getElementById('new_password') 
                });
            }

            if (hasPass) {
                if (newPass !== confirmPass) {
                    errors.push({ 
                        label: 'Las contraseñas no coinciden.', 
                        element: document.getElementById('perfil_password_confirm') 
                    });
                }
            }

            if (hasQuestions) {
                if (!p1 || !r1 || !p2 || !r2) {
                    errors.push({ 
                        label: 'Para actualizar las preguntas de seguridad, debe completar todas las preguntas y respuestas.', 
                        element: !p1 ? document.getElementById('seg_pregunta_1') : (!r1 ? form.querySelector('[name="respuesta_1"]') : (!p2 ? document.getElementById('seg_pregunta_2') : form.querySelector('[name="respuesta_2"]')))
                    });
                } else if (p1 === p2) {
                    errors.push({ 
                        label: 'Las preguntas de seguridad deben ser diferentes.', 
                        element: document.getElementById('seg_pregunta_2') 
                    });
                } else {
                    if (r1.length < 3) {
                        errors.push({ label: 'La respuesta 1 debe tener al menos 3 caracteres.', element: form.querySelector('[name="respuesta_1"]') });
                    }
                    if (r2.length < 3) {
                        errors.push({ label: 'La respuesta 2 debe tener al menos 3 caracteres.', element: form.querySelector('[name="respuesta_2"]') });
                    }
                }
            }

            return errors;
        }
    });
});
</script>

<?php include __DIR__ . '/../atletas/partials/form_registro/_styles.php'; ?>
<style>
#perfil-tabs .ft-tab {
    cursor: pointer;
}
</style>
