<?php /** @var array $stats */ $user = auth() ?? []; ?>

<div class="welcome-card">
    <div class="wc-avatar"><?= strtoupper(mb_substr($user['email'] ?? '?', 0, 1)) ?></div>
    <div>
        <div class="wc-title">Bienvenido, <?= e($user['email'] ?? 'usuario') ?></div>
        <div class="wc-sub"><?= e($user['nombre_rol'] ?? 'Administrador') ?> — Club Atlético Deportivo Acarigua</div>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number" style="color: var(--color-primary);"><?= (int) $stats['atletas'] ?></div>
        <div class="stat-label">Atletas registrados</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: var(--color-success);"><?= (int) $stats['activos'] ?></div>
        <div class="stat-label">Atletas activos</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: var(--color-info);"><?= (int) $stats['categorias'] ?></div>
        <div class="stat-label">Categorías activas</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: var(--color-warning);"><?= (int) ($stats['plantel'] ?? 0) ?></div>
        <div class="stat-label">Personal técnico</div>
    </div>
</div>

<h3 style="font-family: var(--font-display); margin-bottom: 16px;">Accesos rápidos</h3>
<div class="quick-grid">
    <a href="<?= e(url('/admin/atletas')) ?>" class="quick-card">
        <div class="qc-icon red"><i class="ph ph-users"></i></div>
        <div>
            <div class="qc-title">Atletas</div>
            <div class="qc-desc">Gestión del equipo</div>
        </div>
    </a>
    
    <a href="<?= e(url('/admin/asistencias/pase')) ?>" class="quick-card">
        <div class="qc-icon blue"><i class="ph ph-clipboard-text"></i></div>
        <div>
            <div class="qc-title">Pase de Lista</div>
            <div class="qc-desc">Registrar asistencia</div>
        </div>
    </a>

    <a href="<?= e(url('/admin/reportes')) ?>" class="quick-card">
        <div class="qc-icon green"><i class="ph ph-chart-bar"></i></div>
        <div>
            <div class="qc-title">Reportes</div>
            <div class="qc-desc">Estadísticas y PDF</div>
        </div>
    </a>

    <a href="<?= e(url('/admin/medidas')) ?>" class="quick-card">
        <div class="qc-icon orange"><i class="ph ph-ruler"></i></div>
        <div>
            <div class="qc-title">Antropometría</div>
            <div class="qc-desc">Mediciones físicas</div>
        </div>
    </a>

    <a href="<?= e(url('/admin/resultados-pruebas')) ?>" class="quick-card">
        <div class="qc-icon blue"><i class="ph ph-timer"></i></div>
        <div>
            <div class="qc-title">Pruebas Físicas</div>
            <div class="qc-desc">Tests de rendimiento</div>
        </div>
    </a>

    <a href="<?= e(url('/admin/categorias')) ?>" class="quick-card">
        <div class="qc-icon red"><i class="ph ph-folders"></i></div>
        <div>
            <div class="qc-title">Categorías</div>
            <div class="qc-desc"><?= (int) $stats['categorias'] ?> activas</div>
        </div>
    </a>
</div>
