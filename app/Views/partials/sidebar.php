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
                <span class="icon"><i class="ph ph-house"></i></span>
                <span class="nav-text">Inicio</span>
            </a>
        </li>

        <div class="sidebar__group">
            <div class="sidebar__group-title">Gestión Deportiva</div>
            <li>
                <a href="<?= e(url('/admin/categorias')) ?>" class="<?= $active === 'categorias' ? 'active' : '' ?>">
                    <span class="icon"><i class="ph ph-folders"></i></span>
                    <span class="nav-text">Categorías</span>
                </a>
            </li>
            <li>
                <a href="<?= e(url('/admin/atletas')) ?>" class="<?= $active === 'atletas' ? 'active' : '' ?>">
                    <span class="icon"><i class="ph ph-users"></i></span>
                    <span class="nav-text">Atletas</span>
                </a>
            </li>
        </div>

        <div class="sidebar__group">
            <div class="sidebar__group-title">Evaluaciones</div>
            <li>
                <a href="<?= e(url('/admin/asistencias')) ?>" class="<?= $active === 'asistencias' ? 'active' : '' ?>">
                    <span class="icon"><i class="ph ph-clipboard-text"></i></span>
                    <span class="nav-text">Pase de Lista</span>
                </a>
            </li>
            <li>
                <a href="<?= e(url('/admin/medidas')) ?>" class="<?= $active === 'medidas' ? 'active' : '' ?>">
                    <span class="icon"><i class="ph ph-ruler"></i></span>
                    <span class="nav-text">Antropometría</span>
                </a>
            </li>
            <li>
                <a href="<?= e(url('/admin/resultados-pruebas')) ?>" class="<?= $active === 'resultados_pruebas' ? 'active' : '' ?>">
                    <span class="icon"><i class="ph ph-timer"></i></span>
                    <span class="nav-text">Pruebas Físicas</span>
                </a>
            </li>
        </div>

        <div class="sidebar__group">
            <div class="sidebar__group-title">Centro de Reportes</div>
            <li>
                <a href="<?= e(url('/admin/reportes')) ?>" class="<?= $active === 'reportes' ? 'active' : '' ?>">
                    <span class="icon"><i class="ph ph-chart-bar"></i></span>
                    <span class="nav-text">Reportes</span>
                </a>
            </li>
        </div>
    </ul>
</aside>
