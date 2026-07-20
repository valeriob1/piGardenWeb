<?php

namespace Tests\Unit;

use App\PiGardenSocketClient;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Test double: capture the wire command built by each method instead of
 * opening a real socket, so we can characterize the exact protocol strings.
 */
class CommandCapturingClient extends PiGardenSocketClient
{
    public $commands = [];

    protected function execCommand($command, $getPrevRequest = false)
    {
        $this->commands[] = $command;
        return (object) ['captured' => true];
    }

    public function callValidateZone($zone)
    {
        return $this->validateZone($zone);
    }

    public function last()
    {
        return end($this->commands);
    }
}

/**
 * Characterization tests for the socket command protocol and the zone
 * validation added during the hardening pass. These pin behaviour before
 * the framework upgrade.
 */
class PiGardenSocketClientTest extends TestCase
{
    /** @var CommandCapturingClient */
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new CommandCapturingClient();
    }

    public function testZoneOpenBuildsCommand()
    {
        $this->client->zoneOpen('zona_1');
        $this->assertSame('open zona_1', $this->client->last());
    }

    public function testZoneOpenWithForce()
    {
        $this->client->zoneOpen('zona_1', true);
        $this->assertSame('open zona_1 force', $this->client->last());
    }

    public function testZoneCloseBuildsCommand()
    {
        $this->client->zoneClose('zona_1');
        $this->assertSame('close zona_1', $this->client->last());
    }

    public function testZoneOpenInCastsTimesToInt()
    {
        $this->client->zoneOpenIn('zona_1', '5', '10');
        $this->assertSame('open_in 5 10 zona_1', $this->client->last());
    }

    public function testZoneOpenInWithForce()
    {
        $this->client->zoneOpenIn('zona_1', 5, 10, true);
        $this->assertSame('open_in 5 10 zona_1 force', $this->client->last());
    }

    public function testZoneCloseAll()
    {
        $this->client->zoneCloseAll();
        $this->assertSame('close_all', $this->client->last());
    }

    public function testZoneCloseAllDisableScheduling()
    {
        $this->client->zoneCloseAll(true);
        $this->assertSame('close_all disable_scheduling', $this->client->last());
    }

    public function testAddCronOpenEnabled()
    {
        $this->client->addCronOpen('zona_1', '0', '6', '*', '*', '*', true);
        $this->assertSame('add_cron_open zona_1 0 6 * * * ', $this->client->last());
    }

    public function testAddCronOpenDisabled()
    {
        $this->client->addCronOpen('zona_1', '0', '6', '*', '*', '*', false);
        $this->assertSame('add_cron_open zona_1 0 6 * * * disabled', $this->client->last());
    }

    public function testSetGeneralCron()
    {
        $this->client->setGeneralCron();
        $this->assertSame(
            'set_general_cron set_cron_init set_cron_start_socket_server set_cron_check_rain_sensor set_cron_check_rain_online set_cron_close_all_for_rain',
            $this->client->last()
        );
    }

    #[DataProvider('validZones')]
    public function testValidateZoneAcceptsSafeAliases($zone)
    {
        $this->assertSame($zone, $this->client->callValidateZone($zone));
    }

    public static function validZones()
    {
        return [['zona_1'], ['zona.1'], ['zona-1'], ['Zone1'], ['1']];
    }

    #[DataProvider('injectionZones')]
    public function testValidateZoneRejectsInjection($zone)
    {
        $this->expectException(Exception::class);
        $this->client->callValidateZone($zone);
    }

    public static function injectionZones()
    {
        return [
            'space'      => ['zona 1'],
            'newline'    => ["zona\n1"],
            'carriage'   => ["zona\r1"],
            'semicolon'  => ['zona;1'],
            'empty'      => [''],
            'extra-cmd'  => ['zona force'],
        ];
    }

    public function testInvalidZoneThrowsBeforeSendingCommand()
    {
        try {
            $this->client->zoneOpen('bad zone');
            $this->fail('Expected an exception for an invalid zone');
        } catch (Exception $e) {
            $this->assertEmpty($this->client->commands, 'No command must be sent for an invalid zone');
        }
    }
}
