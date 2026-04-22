<?php /** @var string $_content */ ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($title ?? config('app.name')) ?></title>
    <!-- Tipografías (Outfit para Títulos, Inter para Textos, Figtree por si a caso) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= e(asset('css/main.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/landing.css')) ?>">
    <script src="<?= e(asset('js/core/theme.js')) ?>"></script>
</head>
<body>
    <?php if (!($hideHeader ?? false)): ?>
        <?php include view_path('partials.public_header'); ?>
    <?php endif; ?>

    <main>
        <?php include view_path('partials.flash'); ?>
        <?= $_content ?? '' ?>
    </main>

    <?php if (!($hideFooter ?? false)): ?>
        <?php include view_path('partials.public_footer'); ?>
    <?php endif; ?>

    <script src="<?= e(asset('js/core/toast.js')) ?>"></script>
    <script src="<?= e(asset('js/core/api.js')) ?>"></script>
    <?php if (!empty($scripts)): foreach ((array) $scripts as $s): ?>
        <script src="<?= e(asset($s)) ?>"></script>
    <?php endforeach; endif; ?>
</body>
</html>
