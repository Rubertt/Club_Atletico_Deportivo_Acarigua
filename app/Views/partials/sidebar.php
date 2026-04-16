<?php $active = $active ?? ''; ?>
<aside class="sidebar">
    <a href="<?= e(url('/admin')) ?>" class="sidebar__brand" style="text-decoration:none;">
        <div class="brand__logo">CADA</div>
        <div class="brand__text">
            <div class="title">Club Atlético</div>
            <div class="subtitle">Deportivo Acarigua</div>
        </div>
    </a>

    <ul class="sidebar__nav">
        <li>
            <a href="<?= e(url('/admin')) ?>" class="<?= $active === 'inicio' ? 'active' : '' ?>">
                <span class="icon">🏠</span> Inicio
            </a>
        </li>
        <li>
            <a href="<?= e(url('/admin/asistencia')) ?>" class="<?= $active === 'asistencia' ? 'active' : '' ?>">
                <span class="icon">📋</span> Asistencia
            </a>
        </li>
        <li>
            <a href="<?= e(url('/admin/atletas')) ?>" class="<?= $active === 'atletas' ? 'active' : '' ?>">
                <span class="icon">👥</span> Atletas
            </a>
        </li>
        <li>
            <a href="<?= e(url('/admin/categorias')) ?>" class="<?= $active === 'categorias' ? 'active' : '' ?>">
                <span class="icon">📂</span> Categorías
            </a>
        </li>
        <?php if (can('admin')): ?>
        <li>
            <a href="<?= e(url('/admin/plantel')) ?>" class="<?= $active === 'plantel' ? 'active' : '' ?>">
                <span class="icon">👤</span> Plantel
            </a>
        </li>
        <?php endif; ?>

        <li class="sidebar__has-sub <?= in_array($active, ['reportes', 'antropometria', 'pruebas', 'ficha_medica'], true) ? 'is-open' : '' ?>">
            <a href="#"><span class="icon">📊</span> Reportes</a>
            <ul class="sidebar__submenu <?= in_array($active, ['reportes', 'antropometria', 'pruebas', 'ficha_medica'], true) ? 'is-open' : '' ?>">
                <li><a href="<?= e(url('/admin/reportes')) ?>" class="<?= $active === 'reportes' ? 'active' : '' ?>">General</a></li>
                <li><a href="<?= e(url('/admin/antropometria')) ?>" class="<?= $active === 'antropometria' ? 'active' : '' ?>">Antropometría</a></li>
                <li><a href="<?= e(url('/admin/pruebas')) ?>" class="<?= $active === 'pruebas' ? 'active' : '' ?>">Pruebas físicas</a></li>
            </ul>
        </li>

        <?php if (can('admin')): ?>
        <li class="sidebar__has-sub <?= $active === 'configuracion' ? 'is-open' : '' ?>">
            <a href="#"><span class="icon">⚙️</span> Configuración</a>
            <ul class="sidebar__submenu <?= $active === 'configuracion' ? 'is-open' : '' ?>">
                <li><a href="<?= e(url('/admin/configuracion')) ?>">General</a></li>
                <li><a href="<?= e(url('/admin/configuracion/usuarios')) ?>">Usuarios</a></li>
            </ul>
        </li>
        <?php endif; ?>
    </ul>
</aside>
