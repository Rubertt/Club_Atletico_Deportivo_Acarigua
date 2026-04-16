<?php
/**
 * Instalador de la base de datos.
 *
 * Uso:
 *   php database/install.php             (crea DB + schema + seeds)
 *   php database/install.php --fresh     (dropea DB antes de crearla)
 *   php database/install.php --seed-only (solo ejecuta seeds asumiendo schema existente)
 */
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

// Carga .env
if (is_file(BASE_PATH . '/.env')) {
    foreach (file(BASE_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        [$name, $value] = array_pad(explode('=', $line, 2), 2, '');
        $_ENV[trim($name)] = trim($value, " \t\"'");
    }
}

$args = $argv;
array_shift($args);
$fresh    = in_array('--fresh', $args, true);
$seedOnly = in_array('--seed-only', $args, true);

$dbHost = $_ENV['DB_HOST'] ?? '127.0.0.1';
$dbPort = (int) ($_ENV['DB_PORT'] ?? 3306);
$dbName = $_ENV['DB_NAME'] ?? 'club_atletico_db_normalized';
$dbUser = $_ENV['DB_USER'] ?? 'root';
$dbPass = $_ENV['DB_PASS'] ?? '';

$log    = fn(string $msg) => fwrite(STDOUT, $msg . PHP_EOL);
$err    = fn(string $msg) => fwrite(STDERR, "\033[31m$msg\033[0m" . PHP_EOL);
$ok     = fn(string $msg) => fwrite(STDOUT, "\033[32m✓ $msg\033[0m" . PHP_EOL);
$step   = fn(string $msg) => fwrite(STDOUT, "\033[36m→ $msg\033[0m" . PHP_EOL);

try {
    // Conexión sin DB (para crear/dropear la base)
    $serverDsn = "mysql:host=$dbHost;port=$dbPort;charset=utf8mb4";
    $server    = new PDO($serverDsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
    ]);
    $ok("Conectado a MySQL en $dbHost:$dbPort");

    if (!$seedOnly) {
        if ($fresh) {
            $step("Eliminando base de datos `$dbName` (--fresh)...");
            $server->exec("DROP DATABASE IF EXISTS `$dbName`");
        }

        // Importar schema
        $step("Importando schema normalizado...");
        $schema = file_get_contents(__DIR__ . '/normalized_schema.sql');
        if ($schema === false) {
            throw new RuntimeException('No se pudo leer normalized_schema.sql');
        }
        $server->exec($schema);
        $ok("Schema importado: base `$dbName` creada con todas las tablas.");
    }

    // Conectar ya a la base creada
    $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
    ]);
    $pdo->exec('SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci');

    // Ejecutar seeds SQL en orden
    $seedFiles = glob(__DIR__ . '/seeds/*.sql') ?: [];
    sort($seedFiles);
    foreach ($seedFiles as $file) {
        $step('Seed: ' . basename($file));
        $sql = file_get_contents($file);
        if ($sql === false || trim($sql) === '') continue;
        $pdo->exec($sql);
    }

    // Seed del usuario admin (password dinámico con bcrypt)
    $step('Seed: usuario administrador');
    $adminEmail = 'admin@cada.com';
    $adminPass  = 'Admin2026!';
    $hash = password_hash($adminPass, PASSWORD_BCRYPT, ['cost' => 12]);

    $stmt = $pdo->prepare('SELECT usuario_id FROM usuarios WHERE email = :email');
    $stmt->execute([':email' => $adminEmail]);
    if ($stmt->fetchColumn()) {
        $pdo->prepare('UPDATE usuarios SET password = :p, rol_id = 1, estatus = "Activo" WHERE email = :email')
            ->execute([':p' => $hash, ':email' => $adminEmail]);
        $ok("Usuario admin actualizado ($adminEmail)");
    } else {
        $pdo->prepare('INSERT INTO usuarios (email, password, rol_id, estatus) VALUES (:email, :p, 1, "Activo")')
            ->execute([':email' => $adminEmail, ':p' => $hash]);
        $ok("Usuario admin creado ($adminEmail)");
    }

    // Estadísticas
    $step('Verificando instalación...');
    $tables = $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$dbName'")->fetchColumn();
    $users  = $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    $roles  = $pdo->query('SELECT COUNT(*) FROM rol_usuarios')->fetchColumn();
    $posic  = $pdo->query('SELECT COUNT(*) FROM posicion_juego')->fetchColumn();
    $cats   = $pdo->query('SELECT COUNT(*) FROM categoria')->fetchColumn();
    $estados = $pdo->query('SELECT COUNT(*) FROM ubicacion_estado')->fetchColumn();

    $log('');
    $log(str_repeat('=', 60));
    $ok("Instalación completada");
    $log(str_repeat('=', 60));
    $log("  Base de datos : $dbName");
    $log("  Tablas        : $tables");
    $log("  Roles         : $roles");
    $log("  Posiciones    : $posic");
    $log("  Categorías    : $cats");
    $log("  Estados VE    : $estados");
    $log("  Usuarios      : $users");
    $log('');
    $log("  🔐 CREDENCIALES INICIALES");
    $log("     Email    : $adminEmail");
    $log("     Password : $adminPass");
    $log("     ⚠  Cambia esta contraseña al iniciar sesión por primera vez.");
    $log('');
    exit(0);
} catch (Throwable $e) {
    $err('✗ Error: ' . $e->getMessage());
    if (getenv('DEBUG')) {
        $err($e->getTraceAsString());
    }
    $log('');
    $log('Sugerencias:');
    $log('  1. Verifica que MySQL/MariaDB esté corriendo (systemctl status mysql / XAMPP)');
    $log('  2. Revisa las credenciales en .env (DB_HOST, DB_USER, DB_PASS)');
    $log('  3. Intenta --fresh para recrear la base desde cero');
    exit(1);
}
