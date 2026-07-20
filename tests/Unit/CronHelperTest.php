<?php

namespace Tests\Unit;

use App\CronHelper;
use Tests\TestCase;

/**
 * Characterization tests: they lock in the CURRENT behaviour of CronHelper's
 * pure parsing helpers so the Laravel/PHP upgrade can't silently change it.
 */
class CronHelperTest extends TestCase
{
    public function testNormalizeKeepsFiveFields()
    {
        $this->assertSame('0 6 * * *', CronHelper::normalize('0 6 * * *'));
    }

    public function testNormalizeTruncatesTrailingCommand()
    {
        // piGarden returns full crontab lines (time + command); only the 5 time fields are kept
        $this->assertSame('30 7 * * *', CronHelper::normalize('30 7 * * * /path/piGarden.sh open zona'));
    }

    public function testNormalizePadsShortInputToFiveFields()
    {
        $this->assertSame('0 6 * * *', CronHelper::normalize('0 6'));
    }

    public function testExplodeSingleValues()
    {
        $cron = CronHelper::explode('0 6 * * *');

        $this->assertSame(['min-0'], $cron['min']);
        $this->assertSame(['hour-6'], $cron['hour']);
        $this->assertSame(['dom-*'], $cron['dom']);
        $this->assertSame(['month-*'], $cron['month']);
        $this->assertSame(['dow-*'], $cron['dow']);
        $this->assertSame(1, $cron['enable']);
    }

    public function testExplodeCommaSeparatedValues()
    {
        $cron = CronHelper::explode('0,30 6 * * *');

        $this->assertSame(['min-0', 'min-30'], $cron['min']);
        $this->assertSame(['hour-6'], $cron['hour']);
    }

    public function testExplodeDisabledEntryStartsWithHash()
    {
        $cron = CronHelper::explode('#0 6 * * *');

        $this->assertSame(0, $cron['enable']);
        $this->assertSame(['min-0'], $cron['min']);
    }
}
