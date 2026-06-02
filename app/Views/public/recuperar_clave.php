<div class="login-page">
    <div class="login-card" style="max-width: 460px;">
        <h1 class="login-card__title">Nueva contraseña</h1>
        <p class="login-card__subtitle">
            ¡Identidad verificada! Ahora establece tu nueva contraseña segura.
        </p>

        <!-- Indicador de paso -->
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px; font-size: 13px; color: var(--color-text-muted);">
            <span style="background: var(--color-success, #48bb78); color: #fff; border-radius: 50%; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px;">✓</span>
            <span>Correo</span>
            <span style="flex: 1; height: 1px; background: var(--color-success, #48bb78);"></span>
            <span style="background: var(--color-success, #48bb78); color: #fff; border-radius: 50%; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px;">✓</span>
            <span>Preguntas</span>
            <span style="flex: 1; height: 1px; background: var(--color-primary);"></span>
            <span style="background: var(--color-primary); color: #fff; border-radius: 50%; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px;">3</span>
            <span style="color: var(--color-text); font-weight: 600;">Nueva clave</span>
        </div>

        <?php include view_path('partials.flash'); ?>

        <form method="POST" action="<?= e(url('/recuperar/nueva-clave')) ?>" id="form-nueva-clave" novalidate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="password" data-tooltip="Nueva clave segura: mínimo 8 caracteres, incluyendo letras, números y un símbolo. No puede usar su número de cédula." data-tooltip-pos="top"><span class="required">*</span> Nueva Contraseña</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" class="form-control" required minlength="8"
                           pattern="(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}"
                           title="Debe tener al menos 8 caracteres, una letra, un número y un símbolo especial">
                    <button type="button" class="password-toggle" aria-label="Mostrar contraseña"
                            onclick="const i=document.getElementById('password'); i.type = i.type==='password'?'text':'password';">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                <ul class="pwd-rules" style="list-style: none; padding: 0; margin: 8px 0 0 0; font-size: 13px; color: var(--color-text-muted);">
                    <li id="rule-len" style="margin-bottom: 4px;"><i class="ph ph-x-circle" style="color: var(--color-danger); margin-right: 4px;"></i> Mínimo 8 caracteres</li>
                    <li id="rule-let" style="margin-bottom: 4px;"><i class="ph ph-x-circle" style="color: var(--color-danger); margin-right: 4px;"></i> Al menos una letra</li>
                    <li id="rule-num" style="margin-bottom: 4px;"><i class="ph ph-x-circle" style="color: var(--color-danger); margin-right: 4px;"></i> Al menos un número</li>
                    <li id="rule-sym" style="margin-bottom: 0;"><i class="ph ph-x-circle" style="color: var(--color-danger); margin-right: 4px;"></i> Al menos un símbolo especial</li>
                </ul>
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirm" data-tooltip="Escriba exactamente la misma contraseña para confirmar que no haya errores." data-tooltip-pos="top"><span class="required">*</span> Confirmar Contraseña</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-control" required minlength="8">
                <span class="field-error" id="password_confirm-error" style="display:none; color: var(--color-danger, #e53e3e); font-size: 12px; margin-top: 4px;"></span>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 12px;">Cambiar contraseña</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const pass = document.getElementById('password');
    const passConf = document.getElementById('password_confirm');
    const errorEl = document.getElementById('password_confirm-error');

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

    function checkMatch() {
        if (!pass.value && !passConf.value) {
            errorEl.style.display = 'none';
            return;
        }
        if (passConf.value && pass.value !== passConf.value) {
            errorEl.textContent = 'Las contraseñas no coinciden.';
            errorEl.style.display = 'block';
            passConf.style.borderColor = 'var(--color-danger, #e53e3e)';
        } else {
            errorEl.style.display = 'none';
            passConf.style.borderColor = 'var(--color-border, #ccc)';
        }
    }

    pass.addEventListener('input', () => { updateRules(); checkMatch(); });
    passConf.addEventListener('input', checkMatch);

    document.getElementById('form-nueva-clave').addEventListener('submit', function(e) {
        if (pass.value !== passConf.value) {
            e.preventDefault();
            errorEl.textContent = 'Las contraseñas no coinciden.';
            errorEl.style.display = 'block';
        }
    });
});
</script>
