<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso denegado</title>
    <link rel="stylesheet" href="<?= e(asset('css/main.css')) ?>">
</head>
<body class="error-page">
    <div class="error-box">
        <h1>403</h1>
        <p>No tienes permiso para acceder a este recurso.</p>
        <a href="<?= e(url('/admin')) ?>" class="btn btn-primary">Volver al panel</a>
    </div>
</body>
</html>
