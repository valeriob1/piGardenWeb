<?php

namespace App\Logging;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Copies the log lines piGarden posts to /api/log to the remote syslog
 * (VictoriaLogs), next to the copy kept in the database.
 *
 * Off unless LOG_SYSLOG_HOST is set. Never throws: the database copy is
 * already saved, and a syslog problem must not turn into a failed request
 * for piGarden's log_send().
 */
class SyslogForwarder
{
    public const CHANNEL = 'pigarden_remote';

    // piGarden's log_write uses info, warning and error; anything unknown is info
    private const LEVELS = [
        'debug' => 'debug',
        'info' => 'info',
        'notice' => 'notice',
        'warning' => 'warning',
        'warn' => 'warning',
        'error' => 'error',
        'err' => 'error',
        'critical' => 'critical',
        'alert' => 'alert',
        'emergency' => 'emergency',
    ];

    public static function enabled(): bool
    {
        return (string) config('logging.channels.'.self::CHANNEL.'.with.host') !== '';
    }

    public static function forward(string $type, string $level, string $message): void
    {
        if (! self::enabled()) {
            return;
        }

        try {
            Log::channel(self::CHANNEL)->log(
                self::LEVELS[strtolower($level)] ?? 'info',
                "[$type] $message"
            );
        } catch (Throwable $e) {
            // see the class comment
        }
    }
}
