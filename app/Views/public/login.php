<?php /** @var string|null $error */ ?>
<div class="login-page">
    <a href="<?= e(url('/')) ?>" class="login-back">&larr; Volver al Inicio</a>

    <div class="login-card">
        <div class="login-card__brand">
            <a href="<?= e(url('/')) ?>" class="brand">
                <div class="brand__logo">CADA</div>
                <div class="brand__text" style="text-align:left">
                    <div class="title">Club Atlético</div>
                    <div class="subtitle">Deportivo Acarigua</div>
                    <div class="tagline">"La Armadura de Dios"</div>
                </div>
            </a>
        </div>

        <h1 class="login-card__title">Bienvenido</h1>
        <p class="login-card__subtitle">Ingresa al sistema del club</p>

        <?php include view_path('partials.flash'); ?>

        <form method="POST" action="<?= e(url('/login')) ?>" novalidate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="email"><span class="required">*</span> Usuario o correo</label>
                <input type="text" id="email" name="email" class="form-control"
                       value="<?= e(old('email')) ?>" placeholder="Ingresa tu usuario o correo"
                       required autofocus autocomplete="username">
            </div>

            <div class="form-group">
                <label class="form-label" for="password"><span class="required">*</span> Contraseña</label>
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
                <span></span>
                <a href="<?= e(url('/recuperar')) ?>">¿Olvidaste tu contraseña?</a>
            </div>

            <button type="submit" class="btn btn-primary">Iniciar Sesión</button>

            <div class="login-footer">
                ¿Necesitas acceso? <a href="<?= e(url('/contacto')) ?>">Contacta a la directiva</a>
            </div>
        </form>
    </div>
</div>
