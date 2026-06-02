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
    <link rel="icon" type="image/x-icon" href="<?= e(asset('img/favicon.ico')) ?>">
    <!-- Precarga de fuentes para evitar parpadeo (FOUT) -->
    <link rel="preload" href="<?= e(asset('fonts/Inter-Regular.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= e(asset('fonts/Inter-SemiBold.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= e(asset('fonts/Outfit-Bold.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= e(asset('fonts/Phosphor.woff2')) ?>" as="font" type="font/woff2" crossorigin>

    <!-- Tipografías (Local Offline) -->
    <link rel="stylesheet" href="<?= e(asset('css/fonts.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/phosphor/style.css')) ?>">

    <!-- Core CSS -->
    <link rel="stylesheet" href="<?= e(asset('css/main.css')) ?>?v=<?= filemtime(BASE_PATH . '/public/assets/css/main.css') ?>">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>?v=<?= filemtime(BASE_PATH . '/public/assets/css/admin.css') ?>">
    <link rel="stylesheet" href="<?= e(asset('css/modal.css')) ?>?v=<?= filemtime(BASE_PATH . '/public/assets/css/modal.css') ?>">



    <!-- Scripts Base -->
    <script src="<?= e(asset('js/core/theme.js')) ?>?v=<?= filemtime(BASE_PATH . '/public/assets/js/core/theme.js') ?>"></script>
    <script src="<?= e(asset('js/core/modal.js')) ?>"></script>
    <script src="<?= e(asset('js/core/form-validator.js')) ?>"></script>
</head>
<body class="admin-body">
    <div class="admin-layout" id="admin-layout">
        <?php include view_path('partials.sidebar'); ?>

        <div class="admin-main">
            <header class="topbar">
                <div class="topbar__left">
                    <button type="button" class="topbar__toggle" id="sidebar-toggle" aria-label="Menu">☰</button>
                    <div class="topbar__breadcrumb">
                        <?php 
                        $breadcrumbHtml = [];
                        $staticRoutes = [
                            'Inicio' => url('/admin'),
                            'Categorías' => url('/admin/categorias'),
                            'Atletas' => url('/admin/atletas'),
                            'Usuarios' => url('/admin/usuarios'),
                            'Configuración' => url('/admin/configuracion'),
                            'Reportes' => url('/admin/reportes'),
                            'Asistencia' => url('/admin/asistencias'),
                            'Antropometría' => url('/admin/medidas'),
                            'Mi Perfil' => url('/admin/perfil'),
                        ];

                        foreach ($breadcrumb as $index => $item) {
                            $isLast = ($index === count($breadcrumb) - 1);
                            
                            if (is_array($item)) {
                                $label = $item['label'] ?? '';
                                $url = $item['url'] ?? '';
                            } else {
                                $label = $item;
                                $url = $staticRoutes[$label] ?? '';
                            }

                            if ($isLast || empty($url)) {
                                $breadcrumbHtml[] = '<span class="breadcrumb-item active">' . e($label) . '</span>';
                            } else {
                                $breadcrumbHtml[] = '<a href="' . e($url) . '" class="breadcrumb-item">' . e($label) . '</a>';
                            }
                        }
                        echo implode(' <span class="breadcrumb-separator">/</span> ', $breadcrumbHtml);
                        ?>
                    </div>
                </div>
                <div class="topbar__right">
                    <button type="button" class="topbar__theme" data-theme-toggle aria-label="Cambiar tema">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3a9 9 0 1 0 9 9c0-.46-.04-.92-.1-1.36a5.38 5.38 0 0 1-4.4 2.26 5.38 5.38 0 0 1-5.38-5.38A5.38 5.38 0 0 1 13.38 3.1c-.44-.06-.9-.1-1.38-.1z"/></svg>
                    </button>
                    <div class="user-menu" id="user-menu">
                        <button type="button" class="user-menu__btn">
                            <div class="user-menu__avatar"><?= strtoupper(mb_substr(auth()['nombre'] ?? '?', 0, 1, 'UTF-8')) ?></div>
                        </button>
                        <div class="user-menu__dropdown">
                            <div style="padding:8px 12px; font-size:13px;">
                                <strong><?= e((auth()['nombre'] ?? '') . ' ' . (auth()['apellido'] ?? '')) ?></strong><br>
                                <span class="text-muted" style="font-size:11px;"><?= e(auth()['correo'] ?? '') ?></span><br>
                                <span class="text-muted"><?= e(auth()['nombre_rol'] ?? '') ?></span>
                            </div>
                            <hr>
                            <a href="<?= e(url('/admin/perfil')) ?>"><i class="ph ph-user-circle"></i> Mi Perfil</a>
                            <?php if (\App\Core\Auth::isAdmin()): ?>
                                <a href="<?= e(url('/admin/usuarios')) ?>"><i class="ph ph-identification-card"></i> Gestión de Usuarios</a>
                                <a href="<?= e(url('/admin/configuracion')) ?>"><i class="ph ph-gear"></i> Ajustes Generales</a>
                            <?php endif; ?>
                            <hr>
                            
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
                <?= $_content ?? '' ?>
                <?php include view_path('partials.flash'); ?>
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
                // No abrir grupos deshabilitados
                if (a.closest('.sidebar__disabled')) return;
                
                const parent = a.parentElement;
                const submenu = a.nextElementSibling;
                if (!submenu) return;

                parent.classList.toggle('is-open');
                submenu.classList.toggle('is-open');
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

    <!-- Modal de Advertencia de Expiración de Sesión -->
    <div class="modal-overlay" id="modal-session-timeout" style="display: none; z-index: 9999;">
        <div class="modal-container" style="max-width: 400px; width: 90%; padding: 24px; text-align: center;">
            <div style="font-size: 48px; color: var(--color-warning); margin-bottom: 16px;">
                <i class="ph ph-clock-countdown"></i>
            </div>
            <h3 style="margin-top: 0; font-size: 18px; color: var(--color-text);">Tu sesión está por vencer</h3>
            <p style="font-size: 14px; color: var(--color-text-muted); margin-bottom: 24px;">
                Por motivos de seguridad, tu sesión se cerrará automáticamente en <strong id="session-countdown" style="color: var(--color-primary); font-size: 16px;">120</strong> segundos debido a inactividad. ¿Deseas extender la sesión?
            </p>
            <div style="display: flex; gap: 12px; justify-content: center;">
                <button type="button" id="btn-logout-session" class="btn btn-ghost">Cerrar Sesión</button>
                <button type="button" id="btn-extend-session" class="btn btn-primary">Extender Sesión</button>
            </div>
        </div>
    </div>

    <?php
    $tiempoSesionMin = (int) config_db('tiempo_sesion', 120);
    $sessionLifetimeMs = $tiempoSesionMin * 60 * 1000;
    // Mostrar advertencia 2 minutos (120 segundos) antes de que venza
    $warningTimeMs = max(0, $sessionLifetimeMs - (120 * 1000));
    ?>
    <script>
    (function () {
        const SESSION_LIFETIME = <?= $sessionLifetimeMs ?>;
        const WARNING_TIME = <?= $warningTimeMs ?>;
        const EXTEND_INTERVAL = Math.min(5 * 60 * 1000, SESSION_LIFETIME / 4);
        
        let lastExtension = Date.now();
        let warningTimer = null;
        let countdownInterval = null;
        let countdownSeconds = 120;
        
        function extendSession() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch('<?= e(url("/api/keep-alive")) ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken || ''
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    lastExtension = Date.now();
                    resetTimers();
                    closeWarningModal();
                } else {
                    logout();
                }
            })
            .catch(() => {
                closeWarningModal();
            });
        }

        function logout() {
            window.location.href = '<?= e(url("/logout")) ?>';
        }

        function resetTimers() {
            clearTimeout(warningTimer);
            clearInterval(countdownInterval);
            warningTimer = setTimeout(showWarningModal, WARNING_TIME);
        }
        
        function showWarningModal() {
            const modal = document.getElementById('modal-session-timeout');
            if (!modal) return;
            
            modal.style.display = 'flex';
            countdownSeconds = 120;
            document.getElementById('session-countdown').textContent = countdownSeconds;
            
            clearInterval(countdownInterval);
            countdownInterval = setInterval(() => {
                countdownSeconds--;
                document.getElementById('session-countdown').textContent = countdownSeconds;
                if (countdownSeconds <= 0) {
                    clearInterval(countdownInterval);
                    logout();
                }
            }, 1000);
        }
        
        function closeWarningModal() {
            const modal = document.getElementById('modal-session-timeout');
            if (modal) modal.style.display = 'none';
            clearInterval(countdownInterval);
        }

        const activityEvents = ['mousedown', 'keydown', 'scroll', 'touchstart'];
        
        activityEvents.forEach(eventName => {
            document.addEventListener(eventName, () => {
                const modal = document.getElementById('modal-session-timeout');
                if (modal && modal.style.display === 'flex') return;
                
                resetTimers();
                
                const now = Date.now();
                if (now - lastExtension > EXTEND_INTERVAL) {
                    extendSession();
                }
            });
        });
        
        document.getElementById('btn-extend-session')?.addEventListener('click', extendSession);
        document.getElementById('btn-logout-session')?.addEventListener('click', logout);
        
        resetTimers();
    })();
    </script>
</body>
</html>
