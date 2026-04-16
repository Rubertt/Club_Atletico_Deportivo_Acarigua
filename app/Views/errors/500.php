<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Error interno</title>
    <link rel="stylesheet" href="<?= e(asset('css/main.css')) ?>">
</head>
<body class="error-page">
    <div class="error-box">
        <h1>500</h1>
        <p>Ocurrió un error inesperado. Por favor, intenta nuevamente.</p>
        <a href="<?= e(url('/')) ?>" class="btn btn-primary">Volver al inicio</a>
    </div>
</body>
</html>
