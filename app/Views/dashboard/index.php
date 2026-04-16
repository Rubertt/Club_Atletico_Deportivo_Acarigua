<?php /** @var array $stats */ $user = auth() ?? []; ?>

<div class="welcome-card">
    <div class="welcome-card__head">
        <div class="welcome-card__avatar"><?= strtoupper(mb_substr($user['email'] ?? '?', 0, 1)) ?></div>
        <div>
            <div class="welcome-card__title">Bienvenido, <?= e($user['email'] ?? 'usuario') ?></div>
            <div class="welcome-card__role"><?= e($user['nombre_rol'] ?? '') ?></div>
        </div>
    </div>

    <div class="quick-grid">
        <a class="quick-card" href="<?= e(url('/admin/atletas')) ?>">
            <div class="quick-card__icon atletas">👥</div>
            <div>
                <p class="quick-card__title">Atletas</p>
                <p class="quick-card__desc">Gestión de equipo</p>
                <p class="quick-card__desc" style="margin-top:6px;"><strong><?= (int) $stats['activos'] ?></strong> activos de <?= (int) $stats['atletas'] ?></p>
            </div>
        </a>

        <a class="quick-card" href="<?= e(url('/admin/asistencia/pase')) ?>">
            <div class="quick-card__icon asistencia">📋</div>
            <div>
                <p class="quick-card__title">Asistencia</p>
                <p class="quick-card__desc">Pase de lista</p>
            </div>
        </a>

        <a class="quick-card" href="<?= e(url('/admin/reportes')) ?>">
            <div class="quick-card__icon reportes">📊</div>
            <div>
                <p class="quick-card__title">Reportes</p>
                <p class="quick-card__desc">Estadísticas y PDF</p>
            </div>
        </a>

        <a class="quick-card" href="<?= e(url('/admin/antropometria')) ?>">
            <div class="quick-card__icon antropometria">📏</div>
            <div>
                <p class="quick-card__title">Antropometría</p>
                <p class="quick-card__desc">Mediciones físicas</p>
            </div>
        </a>

        <a class="quick-card" href="<?= e(url('/admin/pruebas')) ?>">
            <div class="quick-card__icon pruebas">⚡</div>
            <div>
                <p class="quick-card__title">Pruebas</p>
                <p class="quick-card__desc">Tests de rendimiento</p>
            </div>
        </a>

        <a class="quick-card" href="<?= e(url('/admin/categorias')) ?>">
            <div class="quick-card__icon ficha">📂</div>
            <div>
                <p class="quick-card__title">Categorías</p>
                <p class="quick-card__desc"><?= (int) $stats['categorias'] ?> activas</p>
            </div>
        </a>
    </div>
</div>
