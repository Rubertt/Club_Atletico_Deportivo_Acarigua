<?php /** @var array $preguntas */ ?>
<div class="login-page">
    <a href="<?= e(url('/recuperar')) ?>" class="login-back">&larr; Volver al paso anterior</a>

    <div class="login-card">
        <h1 class="login-card__title">Verificar identidad</h1>
        <p class="login-card__subtitle">
            Responde las preguntas de seguridad que configuraste al crear tu cuenta.
        </p>

        <!-- Indicador de paso -->
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px; font-size: 13px; color: var(--color-text-muted);">
            <span style="background: var(--color-success, #48bb78); color: #fff; border-radius: 50%; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px;">✓</span>
            <span>Correo</span>
            <span style="flex: 1; height: 1px; background: var(--color-primary);"></span>
            <span style="background: var(--color-primary); color: #fff; border-radius: 50%; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px;">2</span>
            <span style="color: var(--color-text); font-weight: 600;">Preguntas</span>
            <span style="flex: 1; height: 1px; background: var(--color-border);"></span>
            <span style="background: var(--color-border); color: var(--color-text-muted); border-radius: 50%; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px;">3</span>
            <span>Nueva clave</span>
        </div>

        <?php include view_path('partials.flash'); ?>

        <form method="POST" action="<?= e(url('/recuperar/preguntas')) ?>" novalidate>
            <?= csrf_field() ?>

            <?php foreach ($preguntas as $i => $pregunta): ?>
                <div class="form-group">
                    <label class="form-label" for="respuesta_<?= $i + 1 ?>" data-tooltip="Respuesta a la pregunta. Debe ser idéntica a la que configuró originalmente (no distingue mayúsculas)." data-tooltip-pos="top">
                        <span class="required">*</span> <?= e($pregunta['preguntas']) ?>
                    </label>
                    <input type="text" id="respuesta_<?= $i + 1 ?>" name="respuesta_<?= $i + 1 ?>" 
                           class="form-control" required autocomplete="off"
                           placeholder="Tu respuesta...">
                </div>
            <?php endforeach; ?>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 8px;">Verificar respuestas</button>
            <div class="login-footer">
                ¿No recuerdas tus respuestas? <a href="<?= e(url('/contacto')) ?>">Contacta a la directiva</a>
            </div>
        </form>
    </div>
</div>
