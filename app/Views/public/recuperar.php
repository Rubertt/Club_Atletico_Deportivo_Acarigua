<div class="login-page">
    <a href="<?= e(url('/login')) ?>" class="login-back">&larr; Volver al Login</a>

    <div class="login-card">
        <h1 class="login-card__title">Recuperar contraseña</h1>
        <p class="login-card__subtitle">
            Ingresa el correo electrónico asociado a tu cuenta para verificar tu identidad.
        </p>

        <!-- Indicador de paso -->
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px; font-size: 13px; color: var(--color-text-muted);">
            <span style="background: var(--color-primary); color: #fff; border-radius: 50%; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px;">1</span>
            <span style="color: var(--color-text); font-weight: 600;">Correo</span>
            <span style="flex: 1; height: 1px; background: var(--color-border);"></span>
            <span style="background: var(--color-border); color: var(--color-text-muted); border-radius: 50%; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px;">2</span>
            <span>Preguntas</span>
            <span style="flex: 1; height: 1px; background: var(--color-border);"></span>
            <span style="background: var(--color-border); color: var(--color-text-muted); border-radius: 50%; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px;">3</span>
            <span>Nueva clave</span>
        </div>

        <?php include view_path('partials.flash'); ?>

        <form method="POST" action="<?= e(url('/recuperar')) ?>" novalidate>
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label" for="correo" data-tooltip="Correo de su cuenta activa para verificar su identidad y recuperar la clave." data-tooltip-pos="top"><span class="required">*</span> Correo electrónico</label>
                <input type="email" id="correo" name="correo" class="form-control" required autofocus placeholder="ejemplo@correo.com">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Continuar</button>
            <div class="login-footer">
                ¿Prefieres hablar con alguien? <a href="<?= e(url('/contacto')) ?>">Contáctanos</a>
            </div>
        </form>
    </div>
</div>
