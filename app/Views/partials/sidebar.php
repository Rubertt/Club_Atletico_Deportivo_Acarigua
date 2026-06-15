<?php $active = $active ?? ''; ?>
<aside class="sidebar">
    <a href="<?= e(url('/admin')) ?>" class="sidebar__brand" style="text-decoration:none;">
        <div class="brand__logo">
            <img src="<?= e(asset('img/logo.png')) ?>" alt="CADA" style="max-width:100%; max-height:100%; object-fit:contain; border-radius:inherit;">
        </div>
        <div class="brand__text">
            <div class="title">Club Atlético</div>
            <div class="subtitle">Deportivo Acarigua</div>
        </div>
    </a>

    <ul class="sidebar__nav">
        <!-- Inicio (enlace directo, no desplegable) -->
        <li>
            <a href="<?= e(url('/admin')) ?>" class="<?= $active === 'inicio' ? 'active' : '' ?>">
                <span class="icon"><i class="ph ph-house"></i></span>
                <span class="nav-text">Inicio</span>
            </a>
        </li>

        <!-- Gestión Deportiva -->
        <li class="sidebar__has-sub <?= in_array($active, ['categorias', 'atletas']) ? 'is-open' : '' ?>">
            <a href="#">
                <span class="icon"><i class="ph ph-soccer-ball"></i></span>
                <span class="nav-text">Gestión Deportiva</span>
            </a>
            <ul class="sidebar__submenu <?= in_array($active, ['categorias', 'atletas']) ? 'is-open' : '' ?>">
                <?php if (can('admin') || can('entrenador')): ?>
                    <li>
                        <a href="<?= e(url('/admin/categorias')) ?>" class="<?= $active === 'categorias' ? 'active' : '' ?>">
                            <span class="icon"><i class="ph ph-folders"></i></span>
                            <span class="nav-text">Categorías</span>
                        </a>
                    </li>
                <?php endif; ?>
                <li>
                    <a href="<?= e(url('/admin/atletas')) ?>" class="<?= $active === 'atletas' ? 'active' : '' ?>">
                        <span class="icon"><i class="ph ph-users"></i></span>
                        <span class="nav-text">Atletas</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Control y Seguimiento -->
        <?php if (can('admin') || can('entrenador')): ?>
            <li class="sidebar__has-sub <?= in_array($active, ['asistencias', 'resultados_pruebas', 'convocatorias']) ? 'is-open' : '' ?>">
                <a href="#">
                    <span class="icon"><i class="ph ph-clipboard-text"></i></span>
                    <span class="nav-text">Control y Seguimiento</span>
                </a>
                <ul class="sidebar__submenu <?= in_array($active, ['asistencias', 'resultados_pruebas', 'convocatorias']) ? 'is-open' : '' ?>">
                    <li>
                        <a href="<?= e(url('/admin/asistencias')) ?>" class="<?= $active === 'asistencias' ? 'active' : '' ?>">
                            <span class="icon"><i class="ph ph-calendar-check"></i></span>
                            <span class="nav-text">Asistencias</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= e(url('/admin/convocatorias')) ?>" class="<?= $active === 'convocatorias' ? 'active' : '' ?>">
                            <span class="icon"><i class="ph ph-envelope-simple-open"></i></span>
                            <span class="nav-text">Convocatorias</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= e(url('/admin/resultados-pruebas')) ?>" class="<?= $active === 'resultados_pruebas' ? 'active' : '' ?>">
                            <span class="icon"><i class="ph ph-timer"></i></span>
                            <span class="nav-text">Pruebas Físicas</span>
                        </a>
                    </li>
                </ul>
            </li>
        <?php endif; ?>

        <!-- Reportes -->
        <?php if (can('admin') || can('entrenador')): ?>
            <li class="sidebar__has-sub <?= $active === 'reportes' ? 'is-open' : '' ?>">
                <a href="#">
                    <span class="icon"><i class="ph ph-printer"></i></span>
                    <span class="nav-text">Centro de Reportes</span>
                </a>
                <ul class="sidebar__submenu <?= $active === 'reportes' ? 'is-open' : '' ?>">
                    <li>
                        <a href="<?= e(url('/admin/reportes')) ?>" class="<?= $active === 'reportes' ? 'active' : '' ?>">
                            <span class="icon"><i class="ph ph-file-text"></i></span>
                            <span class="nav-text">Generador PDF</span>
                        </a>
                    </li>
                </ul>
            </li>
        <?php endif; ?>

        <?php if (\App\Core\Auth::isAdmin() || can('medico')): ?>
            <!-- Administración -->
            <li class="sidebar__has-sub <?= in_array($active, ['usuarios', 'configuracion']) ? 'is-open' : '' ?>">
                <a href="#">
                    <span class="icon"><i class="ph ph-shield-check"></i></span>
                    <span class="nav-text">Administración</span>
                </a>
                <ul class="sidebar__submenu <?= in_array($active, ['usuarios', 'configuracion']) ? 'is-open' : '' ?>">
                    <?php if (\App\Core\Auth::isAdmin()): ?>
                        <li>
                            <a href="<?= e(url('/admin/usuarios')) ?>" class="<?= $active === 'usuarios' ? 'active' : '' ?>">
                                <span class="icon"><i class="ph ph-user-gear"></i></span>
                                <span class="nav-text">Gestión de Usuarios</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li>
                        <a href="<?= e(url('/admin/configuracion')) ?>" class="<?= $active === 'configuracion' ? 'active' : '' ?>">
                            <span class="icon"><i class="ph ph-gear"></i></span>
                            <span class="nav-text">Configuración</span>
                        </a>
                    </li>
                </ul>
            </li>
        <?php endif; ?>

        <!-- Manuales de Ayuda (visible para todos) -->
        <li>
            <a href="<?= e(url('/admin/manual')) ?>" class="<?= $active === 'manuales' ? 'active' : '' ?>">
                <span class="icon"><i class="ph ph-book-open-text"></i></span>
                <span class="nav-text">Manuales de Ayuda</span>
            </a>
        </li>
    </ul>
</aside>