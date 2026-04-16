<?php
declare(strict_types=1);

namespace App\Core;

use Throwable;

final class Logger
{
    public static function error(Throwable $e): void
    {
        self::write('ERROR', sprintf(
            "%s in %s:%d\n%s",
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        ));
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message . (empty($context) ? '' : ' ' . json_encode($context, JSON_UNESCAPED_UNICODE)));
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('WARN', $message . (empty($context) ? '' : ' ' . json_encode($context, JSON_UNESCAPED_UNICODE)));
    }

    public static function audit(string $action, array $context = []): void
    {
        $user = Auth::user();
        $context['user_id'] = $user['usuario_id'] ?? null;
        $context['user_email'] = $user['email'] ?? null;
        self::write('AUDIT', $action . ' ' . json_encode($context, JSON_UNESCAPED_UNICODE));
    }

    private static function write(string $level, string $message): void
    {
        $logsDir = BASE_PATH . '/storage/logs';
        if (!is_dir($logsDir)) {
            @mkdir($logsDir, 0775, true);
        }
        $file = $logsDir . '/app-' . date('Y-m-d') . '.log';
        $line = sprintf("[%s] %s: %s\n", date('Y-m-d H:i:s'), $level, $message);
        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}
