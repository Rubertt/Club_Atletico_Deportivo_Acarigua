<?php /** @var array $preguntas */ ?>
<div class="login-page">
    <div class="login-card" style="max-width: 500px;">
        <div class="login-card__brand">
            <div class="brand">
                <img class="brand__logo" src="<?= e(asset('img/brand/logo-club.png')) ?>" alt="<?= e(config('app.name')) ?>" width="200" height="200" decoding="async" loading="eager">
                <div class="brand__text" style="text-align:left">
                    <div class="title">Configuración Inicial</div>
                    <div class="subtitle">Seguridad de la Cuenta</div>
                </div>
            </div>
        </div>

        <p class="login-card__subtitle" style="margin-bottom: 24px;">
            Por seguridad, es obligatorio que cambies tu contraseña predeterminada y configures tus preguntas de recuperación antes de acceder al sistema.
        </p>

        <?php include view_path('partials.flash'); ?>

        <form method="POST" action="<?= e(url('/admin/setup/save')) ?>">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="password"><span class="required">*</span> Nueva Contraseña</label>
                <input type="password" id="password" name="password" class="form-control" required minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}" title="Debe tener al menos 8 caracteres, una letra, un número y un símbolo especial">
                <ul class="pwd-rules" style="list-style: none; padding: 0; margin: 8px 0 0 0; font-size: 13px; color: var(--color-text-muted);">
                    <li id="rule-len" style="margin-bottom: 4px;"><i class="ph ph-x-circle" style="color: var(--color-danger); margin-right: 4px;"></i> Mínimo 8 caracteres</li>
                    <li id="rule-let" style="margin-bottom: 4px;"><i class="ph ph-x-circle" style="color: var(--color-danger); margin-right: 4px;"></i> Al menos una letra</li>
                    <li id="rule-num" style="margin-bottom: 4px;"><i class="ph ph-x-circle" style="color: var(--color-danger); margin-right: 4px;"></i> Al menos un número</li>
                    <li id="rule-sym" style="margin-bottom: 0;"><i class="ph ph-x-circle" style="color: var(--color-danger); margin-right: 4px;"></i> Al menos un símbolo especial</li>
                </ul>
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirm"><span class="required">*</span> Confirmar Contraseña</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-control" required minlength="8">
                <span class="field-error" id="password_confirm-error" style="display:none; color: var(--color-danger, #e53e3e); font-size: 12px; margin-top: 4px;"></span>
            </div>

            <hr style="border:0; border-top:1px solid var(--border-color); margin: 24px 0;">

            <div class="form-group">
                <label class="form-label" for="pregunta_1"><span class="required">*</span> Pregunta de Seguridad 1</label>
                <select id="pregunta_1" name="pregunta_1" class="form-control" required>
                    <option value="">Selecciona una pregunta...</option>
                    <?php foreach ($preguntas as $p): ?>
                        <option value="<?= (int) $p['pregunta_id'] ?>"><?= e($p['preguntas']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="respuesta_1"><span class="required">*</span> Respuesta 1</label>
                <input type="text" id="respuesta_1" name="respuesta_1" class="form-control" required autocomplete="off">
                <span class="field-error" id="respuesta_1-error" style="display:none; color: var(--color-danger, #e53e3e); font-size: 12px; margin-top: 4px;"></span>
            </div>

            <div class="form-group">
                <label class="form-label" for="pregunta_2"><span class="required">*</span> Pregunta de Seguridad 2</label>
                <select id="pregunta_2" name="pregunta_2" class="form-control" required>
                    <option value="">Selecciona una pregunta...</option>
                    <?php foreach ($preguntas as $p): ?>
                        <option value="<?= (int) $p['pregunta_id'] ?>"><?= e($p['preguntas']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="respuesta_2"><span class="required">*</span> Respuesta 2</label>
                <input type="text" id="respuesta_2" name="respuesta_2" class="form-control" required autocomplete="off">
                <span class="field-error" id="respuesta_2-error" style="display:none; color: var(--color-danger, #e53e3e); font-size: 12px; margin-top: 4px;"></span>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 16px; width: 100%;">Guardar y Entrar al Sistema</button>

            <div style="margin-top: 20px; text-align: center; border-top: 1px solid var(--color-border, #eee); padding-top: 16px;">
                <a href="<?= e(url('/logout')) ?>" style="color: var(--color-text-muted); text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="ph ph-arrow-left"></i> Volver al inicio y cerrar sesión
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const p1 = document.getElementById('pregunta_1');
    const p2 = document.getElementById('pregunta_2');

    function checkSelects() {
        const v1 = p1.value;
        const v2 = p2.value;

        // Reset all options
        Array.from(p1.options).forEach(opt => opt.disabled = false);
        Array.from(p2.options).forEach(opt => opt.disabled = false);

        if (v1) {
            const opt2 = p2.querySelector(`option[value="${v1}"]`);
            if (opt2) opt2.disabled = true;
        }
        if (v2) {
            const opt1 = p1.querySelector(`option[value="${v2}"]`);
            if (opt1) opt1.disabled = true;
        }
    }

    p1.addEventListener('change', checkSelects);
    p2.addEventListener('change', checkSelects);

    // Funciones de ayuda visual
    function showError(id, msg) {
        const el = document.getElementById(id + '-error');
        const inp = document.getElementById(id);
        if (el) { el.textContent = msg; el.style.display = msg ? 'block' : 'none'; }
        if (inp) inp.style.borderColor = msg ? 'var(--color-danger, #e53e3e)' : 'var(--color-border, #ccc)';
    }

    // Validación de contraseñas dinámicas
    const pass = document.getElementById('password');
    const passConf = document.getElementById('password_confirm');
    
    function checkPass() {
        if (!pass.value && !passConf.value) {
            showError('password_confirm', '');
            return;
        }
        if (pass.value !== passConf.value) {
            showError('password_confirm', 'Las contraseñas no coinciden.');
        } else {
            showError('password_confirm', '');
        }
    }
    
    function updateRules() {
        const val = pass.value;
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
            if (r.valid) {
                icon.className = 'ph ph-check-circle';
                icon.style.color = 'var(--color-success, #48bb78)';
                li.style.color = 'var(--color-success, #48bb78)';
            } else {
                icon.className = 'ph ph-x-circle';
                icon.style.color = 'var(--color-danger, #e53e3e)';
                li.style.color = 'var(--color-text-muted, #718096)';
            }
        });
    }

    pass.addEventListener('input', () => {
        checkPass();
        updateRules();
    });
    passConf.addEventListener('input', checkPass);

    // Validación de longitud de respuestas
    const resp1 = document.getElementById('respuesta_1');
    const resp2 = document.getElementById('respuesta_2');

    function checkResp(e) {
        if (e.target.value.length > 0 && e.target.value.length < 3) {
            showError(e.target.id, 'La respuesta debe tener al menos 3 caracteres.');
        } else {
            showError(e.target.id, '');
        }
    }
    resp1.addEventListener('input', checkResp);
    resp2.addEventListener('input', checkResp);

    // Validación antes de enviar
    document.querySelector('form').addEventListener('submit', function(e) {
        let hasError = false;
        
        if (pass.value !== passConf.value) {
            showError('password_confirm', 'Las contraseñas no coinciden.');
            hasError = true;
        } 
        if (resp1.value.length < 3) {
            showError('respuesta_1', 'La respuesta debe tener al menos 3 caracteres.');
            hasError = true;
        }
        if (resp2.value.length < 3) {
            showError('respuesta_2', 'La respuesta debe tener al menos 3 caracteres.');
            hasError = true;
        }

        if (hasError) {
            e.preventDefault();
        }
    });
});
</script>
