<section class="section">
    <h2 class="section__title">Contacto</h2>
    <p class="section__subtitle">Escríbenos y te responderemos a la brevedad.</p>

    <form class="contact-form card" method="POST" action="<?= e(url('/contacto')) ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label class="form-label" for="nombre"><span class="required">*</span> Nombre</label>
            <input type="text" id="nombre" name="nombre" class="form-control" value="<?= e(old('nombre')) ?>" required maxlength="100">
        </div>

        <div class="form-group">
            <label class="form-label" for="correo"><span class="required">*</span> Correo electrónico</label>
            <input type="email" id="correo" name="correo" class="form-control" value="<?= e(old('correo')) ?>" required maxlength="100">
        </div>

        <div class="form-group">
            <label class="form-label" for="mensaje"><span class="required">*</span> Mensaje</label>
            <textarea id="mensaje" name="mensaje" class="form-control" required minlength="10" maxlength="1000" rows="5"><?= e(old('mensaje')) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Enviar mensaje</button>
    </form>
</section>
