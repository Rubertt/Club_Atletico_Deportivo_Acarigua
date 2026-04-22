<?php
/** @var string $_content */
$user = auth() ?? [];
$active = $active ?? '';
$title  = $title ?? 'Panel';
$breadcrumb = $breadcrumb ?? [$title];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($title) ?> - <?= e(config('app.name')) ?></title>
    <!-- Precarga de fuentes para evitar parpadeo (FOUT) -->
    <link rel="preload" href="<?= e(asset('fonts/Inter-Regular.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= e(asset('fonts/Inter-SemiBold.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= e(asset('fonts/Outfit-Bold.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= e(asset('fonts/Phosphor.woff2')) ?>" as="font" type="font/woff2" crossorigin>

    <!-- Tipografías (Local Offline) -->
    <link rel="stylesheet" href="<?= e(asset('css/fonts.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/phosphor/style.css')) ?>">

    <!-- Core CSS -->
    <link rel="stylesheet" href="<?= e(asset('css/main.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/modal.css')) ?>">

    <!-- Scripts Base -->
    <script src="<?= e(asset('js/core/theme.js')) ?>"></script>
    <script src="<?= e(asset('js/core/modal.js')) ?>"></script>
</head>
<body class="admin-body">
    <div class="admin-layout" id="admin-layout">
        <?php include view_path('partials.sidebar'); ?>

        <div class="admin-main">
            <header class="topbar">
                <div class="topbar__left">
                    <button type="button" class="topbar__toggle" id="sidebar-toggle" aria-label="Menu">☰</button>
                    <div class="topbar__breadcrumb">
                        <?= e(implode(' / ', $breadcrumb)) ?>
                    </div>
                </div>
                <div class="topbar__right">
                    <button type="button" class="topbar__theme" data-theme-toggle aria-label="Cambiar tema">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3a9 9 0 1 0 9 9c0-.46-.04-.92-.1-1.36a5.38 5.38 0 0 1-4.4 2.26 5.38 5.38 0 0 1-5.38-5.38A5.38 5.38 0 0 1 13.38 3.1c-.44-.06-.9-.1-1.38-.1z"/></svg>
                    </button>
                    <div class="user-menu" id="user-menu">
                        <button type="button" class="user-menu__btn">
                            <div class="user-menu__avatar"><?= strtoupper(mb_substr($user['email'] ?? '?', 0, 1)) ?></div>
                        </button>
                        <div class="user-menu__dropdown">
                            <div style="padding:8px 12px; font-size:13px;">
                                <strong><?= e($user['email'] ?? '') ?></strong><br>
                                <span class="text-muted"><?= e($user['nombre_rol'] ?? '') ?></span>
                            </div>
                            <hr>
                            <?php if (can('admin')): ?>
                                <a href="<?= e(url('/admin/personal')) ?>"><i class="ph ph-identification-card"></i> Control de Personal</a>
                                <a href="<?= e(url('/admin/configuracion/usuarios')) ?>"><i class="ph ph-users"></i> Usuarios y Permisos</a>
                                <a href="<?= e(url('/admin/configuracion')) ?>"><i class="ph ph-gear"></i> Ajustes Generales</a>
                                <hr>
                            <?php endif; ?>
                            
                            <a href="<?= e(url('/logout')) ?>"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-danger">
                                <i class="ph ph-sign-out"></i> Cerrar sesión
                            </a>
                            <form id="logout-form" method="POST" action="<?= e(url('/logout')) ?>" style="display:none;">
                                <?= csrf_field() ?>
                            </form>
                        </div>
                    </div>
                </div>
            </header>




            <div class="admin-content">
                <?php include view_path('partials.flash'); ?>
                <?= $_content ?? '' ?>
            </div>
        </div>
    </div>

    <script src="<?= e(asset('js/core/toast.js')) ?>"></script>
    <script src="<?= e(asset('js/core/api.js')) ?>"></script>
    <script>
    (function () {
        // Toggle sidebar
        document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
            const layout = document.getElementById('admin-layout');
            if (window.matchMedia('(max-width: 700px)').matches) {
                layout.classList.toggle('is-mobile-open');
            } else {
                layout.classList.toggle('is-collapsed');
            }
        });

        // Dropdown usuario
        const userMenu = document.getElementById('user-menu');
        userMenu?.querySelector('.user-menu__btn').addEventListener('click', (e) => {
            e.stopPropagation();
            userMenu.classList.toggle('is-open');
        });
        document.addEventListener('click', (e) => {
            if (!userMenu?.contains(e.target)) userMenu?.classList.remove('is-open');
        });

        // Submenus del sidebar
        document.querySelectorAll('.sidebar__has-sub > a').forEach(a => {
            a.addEventListener('click', (e) => {
                e.preventDefault();
                a.parentElement.classList.toggle('is-open');
                a.nextElementSibling?.classList.toggle('is-open');
            });
        });
    })();
    </script>
    <?php if (!empty($scripts)): foreach ((array) $scripts as $s): ?>
        <script src="<?= e(asset($s)) ?>"></script>
    <?php endforeach; endif; ?>
    <?php if (!empty($inlineScript)): ?>
        <script><?= $inlineScript ?></script>
    <?php endif; ?>
</body>
</html>
