<div class="login-page">
    <a href="<?= e(url('/login')) ?>" class="login-back">&larr; Volver al Login</a>

    <div class="login-card">
        <h1 class="login-card__title">Recuperar contraseña</h1>
        <p class="login-card__subtitle">
            Ingresa tu correo y te enviaremos instrucciones para restablecer tu contraseña.
        </p>

        <?php include view_path('partials.flash'); ?>

        <form method="POST" action="<?= e(url('/recuperar')) ?>">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label" for="correo">Correo electrónico</label>
                <input type="email" id="correo" name="correo" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Enviar instrucciones</button>
            <div class="login-footer">
                ¿Prefieres hablar con alguien? <a href="<?= e(url('/contacto')) ?>">Contáctanos</a>
            </div>
        </form>
    </div>
</div>
