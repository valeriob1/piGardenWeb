<?php

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

// Remote syslog (e.g. VictoriaLogs). Off unless LOG_SYSLOG_HOST is set; the
// host must be an IP address (Monolog's UdpSocket does not resolve names).
// Needs the PHP "sockets" extension (installed by the Dockerfile).
$syslogHost = (string) env('LOG_SYSLOG_HOST', '');
$syslogChannel = fn (string $ident) => [
    'driver' => 'monolog',
    'handler' => SyslogUdpHandler::class,
    'with' => [
        'host' => $syslogHost,
        'port' => (int) env('LOG_SYSLOG_PORT', 514),
        'ident' => $ident,
    ],
    // the syslog header already carries time, host and severity
    'formatter' => LineFormatter::class,
    'formatter_with' => [
        'format' => '%message% %context% %extra%',
        'ignoreEmptyContextAndExtra' => true,
    ],
    'level' => env('LOG_SYSLOG_LEVEL', 'debug'),
];

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        // LOG_STACK lists the local channels (comma separated); the remote
        // syslog joins them when LOG_SYSLOG_HOST is set. ignore_exceptions:
        // a broken remote must never take the local logs down with it.
        'stack' => [
            'driver' => 'stack',
            'channels' => array_values(array_filter(array_merge(
                array_map('trim', explode(',', (string) env('LOG_STACK', 'single'))),
                $syslogHost !== '' ? ['syslog_remote'] : []
            ))),
            'ignore_exceptions' => true,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
            'days' => 7,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => 'critical',
        ],

        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => 'debug',
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => 'debug',
        ],

        // piGardenWeb's own logs
        'syslog_remote' => $syslogChannel('pigardenweb'),

        // log lines piGarden posts to /api/log (App\Logging\SyslogForwarder)
        'pigarden_remote' => $syslogChannel('pigarden'),
    ],

];
