<?php

namespace Tests\Unit;

use App\Logging\SyslogForwarder;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

/**
 * Remote syslog: the channels in config/logging.php and the forwarding of
 * piGarden's /api/log lines. The packet tests go through the real Monolog
 * SyslogUdpHandler to a UDP socket on 127.0.0.1, so they check what
 * VictoriaLogs would actually receive.
 */
class SyslogForwarderTest extends TestCase
{
    private $server;
    private int $port = 0;

    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('sockets')) {
            $this->markTestSkipped('the sockets extension is required');
        }
        $this->server = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_bind($this->server, '127.0.0.1', 0);
        socket_getsockname($this->server, $addr, $this->port);
        socket_set_option($this->server, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 2, 'usec' => 0]);
    }

    protected function tearDown(): void
    {
        if ($this->server) {
            socket_close($this->server);
        }
        foreach (['LOG_SYSLOG_HOST', 'LOG_STACK'] as $var) {
            putenv($var);
            unset($_ENV[$var], $_SERVER[$var]);
        }
        parent::tearDown();
    }

    private function pointChannelAtTestServer(): void
    {
        config([
            'logging.channels.pigarden_remote.with.host' => '127.0.0.1',
            'logging.channels.pigarden_remote.with.port' => $this->port,
        ]);
        app('log')->forgetChannel(SyslogForwarder::CHANNEL);
    }

    private function receive(): ?string
    {
        $buf = null;
        $n = @socket_recv($this->server, $buf, 8192, 0);
        return $n ? $buf : null;
    }

    public function testDoesNothingWithoutHost(): void
    {
        config(['logging.channels.pigarden_remote.with.host' => '']);
        Log::shouldReceive('channel')->never();

        SyslogForwarder::forward('irrigate', 'info', 'never sent');

        $this->assertFalse(SyslogForwarder::enabled());
    }

    public static function levels(): array
    {
        // PRI = facility user (1) * 8 + severity
        return [
            'info'    => ['info', 14],
            'warning' => ['warning', 12],
            'error'   => ['error', 11],
            'unknown level becomes info' => ['strange', 14],
        ];
    }

    #[DataProvider('levels')]
    public function testSendsSyslogPacket(string $level, int $pri): void
    {
        $this->pointChannelAtTestServer();

        SyslogForwarder::forward('irrigate', $level, "close_all - Close solenoid 'Retro_SX' for rain");

        $packet = $this->receive();
        $this->assertNotNull($packet, 'no UDP packet received');
        $this->assertStringStartsWith("<$pri>1 ", $packet);
        $this->assertStringContainsString(' pigarden ', $packet);
        $this->assertStringEndsWith("[irrigate] close_all - Close solenoid 'Retro_SX' for rain", rtrim($packet));
    }

    public function testChannelFailureNeverReachesTheCaller(): void
    {
        config(['logging.channels.pigarden_remote.with.host' => '127.0.0.1']);
        Log::shouldReceive('channel')->andThrow(new RuntimeException('syslog down'));

        SyslogForwarder::forward('irrigate', 'error', 'still fine');

        $this->assertTrue(true);
    }

    public function testStackIncludesRemoteSyslogOnlyWhenHostIsSet(): void
    {
        $load = function (array $env) {
            foreach ($env as $k => $v) {
                putenv("$k=$v");
                $_ENV[$k] = $_SERVER[$k] = $v;
            }
            return require base_path('config/logging.php');
        };

        $off = $load(['LOG_SYSLOG_HOST' => '', 'LOG_STACK' => 'stderr']);
        $this->assertSame(['stderr'], $off['channels']['stack']['channels']);

        $on = $load(['LOG_SYSLOG_HOST' => '192.0.2.24', 'LOG_STACK' => 'stderr']);
        $this->assertSame(['stderr', 'syslog_remote'], $on['channels']['stack']['channels']);
        $this->assertTrue($on['channels']['stack']['ignore_exceptions']);
        $this->assertSame('192.0.2.24', $on['channels']['syslog_remote']['with']['host']);
        $this->assertSame('pigardenweb', $on['channels']['syslog_remote']['with']['ident']);
        $this->assertSame('pigarden', $on['channels']['pigarden_remote']['with']['ident']);
    }
}
