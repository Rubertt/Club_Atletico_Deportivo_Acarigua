<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Página no encontrada</title>
    <link rel="stylesheet" href="<?= e(asset('css/main.css')) ?>">
</head>
<body class="error-page">
    <div class="error-box">
        <h1>404</h1>
        <p>La página que buscas no existe.</p>
        <a href="<?= e(url('/')) ?>" class="btn btn-primary">Volver al inicio</a>
    </div>
</body>
</html>
