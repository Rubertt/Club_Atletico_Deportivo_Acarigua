<?php $active = $active ?? ''; ?>
<header class="site-header">
    <div class="site-header__inner">
        <a href="<?= e(url('/')) ?>" class="brand">
            <div class="brand__logo" aria-label="Escudo CADA">CADA</div>
            <div class="brand__text">
                <div class="title">Club Atlético</div>
                <div class="subtitle">Deportivo Acarigua</div>
                <div class="tagline">"La Armadura de Dios"</div>
            </div>
        </a>

        <nav class="main-nav" aria-label="Principal">
            <a href="<?= e(url('/')) ?>" class="<?= $active === 'home' ? 'active' : '' ?>">Inicio</a>
            <a href="<?= e(url('/nosotros')) ?>" class="<?= $active === 'nosotros' ? 'active' : '' ?>">Nosotros</a>
            <a href="<?= e(url('/contacto')) ?>" class="<?= $active === 'contacto' ? 'active' : '' ?>">Contacto</a>
        </nav>

        <div class="header-actions">
            <button type="button" class="theme-toggle" data-theme-toggle aria-label="Cambiar tema">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 3a9 9 0 1 0 9 9c0-.46-.04-.92-.1-1.36a5.38 5.38 0 0 1-4.4 2.26 5.38 5.38 0 0 1-5.38-5.38A5.38 5.38 0 0 1 13.38 3.1c-.44-.06-.9-.1-1.38-.1z"/></svg>
            </button>
            <?php if (auth()): ?>
                <a href="<?= e(url('/admin')) ?>" class="btn btn-outline btn-sm">Panel</a>
            <?php else: ?>
                <a href="<?= e(url('/login')) ?>" class="btn btn-outline btn-sm">Acceder</a>
            <?php endif; ?>
        </div>
    </div>
</header>
