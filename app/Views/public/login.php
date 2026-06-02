<?php /** @var string|null $error */ ?>
<div class="login-page">
    <a href="<?= e(url('/')) ?>" class="login-back">&larr; Volver al Inicio</a>

    <div class="login-card">
        <div class="login-card__brand">
            <a href="<?= e(url('/')) ?>" class="brand">
                <div class="brand__logo">
                    <img src="<?= e(asset('img/logo.png')) ?>" alt="CADA" style="max-width:100%; max-height:100%; object-fit:contain; border-radius:inherit;">
                </div>
                <div class="brand__text" style="text-align:left">
                    <div class="title">Club Atlético</div>
                    <div class="subtitle">Deportivo Acarigua</div>
                    <div class="tagline">"La Armadura de Dios"</div>
                </div>
            </a>
        </div>

        <div style="margin-bottom: 8px;">
            <h1 class="login-card__title" style="margin-bottom: 0;">Bienvenido</h1>
        </div>
        <p class="login-card__subtitle">Ingresa al sistema del club</p>

        <?php include view_path('partials.flash'); ?>

        <form id="form-login" method="POST" action="<?= e(url('/login')) ?>" novalidate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="email" data-tooltip="Ingrese su correo electrónico registrado. Campo obligatorio (máx. 100 caracteres)." data-tooltip-pos="top"><span class="required">*</span> Usuario o correo</label>
                <input type="text" id="email" name="email" class="form-control"
                       value="<?= e(old('email')) ?>" placeholder="Ingresa tu usuario o correo"
                       required autofocus autocomplete="username">
            </div>

            <div class="form-group">
                <label class="form-label" for="password" data-tooltip="Su contraseña secreta de acceso. Debe contener al menos 4 letras o números." data-tooltip-pos="top"><span class="required">*</span> Contraseña</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" class="form-control"
                           placeholder="Ingresa tu contraseña" required autocomplete="current-password">
                    <button type="button" class="password-toggle" aria-label="Mostrar contraseña"
                            onclick="const i=document.getElementById('password'); i.type = i.type==='password'?'text':'password';">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="login-links">
                <!-- <a href="<?= e(url('/registro')) ?>">Registrarse</a> -->
                <span></span>
                <a href="<?= e(url('/recuperar')) ?>">¿Olvidaste tu contraseña?</a>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 8px;">Iniciar Sesión</button>

            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 24px;">
                <div class="login-footer" style="margin: 0;">
                    ¿Necesitas acceso? <a href="<?= e(url('/contacto')) ?>">Contacta a la directiva</a>
                </div>
                <button type="button" class="btn-help" id="btn-help-login" title="¿Cómo iniciar sesión?" style="width: 38px; height: 38px;">
                    <i class="ph ph-question"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Botón de ayuda [?]
    document.getElementById('btn-help-login')?.addEventListener('click', () => {
        FormValidator.showHelp(
            'Guía: Inicio de Sesión',
            '<?= e(asset("img/ayuda/login.png")) ?>'
        );
    });

    // Validación estándar al clic en "Iniciar Sesión"
    FormValidator.init('#form-login');
});
</script>
